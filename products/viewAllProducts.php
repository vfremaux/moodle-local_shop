<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use \local_shop\Catalog;

$action = optional_param('what', '', PARAM_ALPHA);
$order = optional_param('order', 'code', PARAM_ALPHA);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);

$SESSION->shop->categoryid = optional_param('categoryid', 0 + @$SESSION->shop->categoryid, PARAM_INT);

if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = $USER->id;
} else {
    $shopowner = null;
    $shoprenderer = $PAGE->get_renderer('local_shop');
    $shoprenderer->print_owner_menu($url);
}

// execute controller
//echo "[$view:$cmd]";
$hashandlersstr = get_string('hashandlers', 'local_shop');

if ($action != '') {
   include_once($CFG->dirroot.'/local/shop/products/products.controller.php');
   $controller = new product_controller($theCatalog);
   $controller->receive($action);
   $controller->process($action);
}
$products = array();

// if slave get entries in master catalog and then overrides whith local descriptions
/*
$masterproducts = array();
if (!$localproducts = $theCatalog->get_products($order, $dir, @$SESSION->shop->categoryid)) {
    $localproducts = array();
}
if ($theCatalog->isslave) {
    $masterCatalog = new Catalog($theCatalog->groupid);
    if (!$masterproducts = $masterCatalog->get_products($order, $dir)) {
        $masterproducts = array();
    }
    foreach ($localproducts as $code => $product) {
        $localproducts[$code]->masterid = $masterproducts[$product->code]->id;
    }
}

$products = array_merge($masterproducts, $localproducts);
*/
$theCatalog->get_all_products_for_admin($products);
if ($theCatalog->isslave) {
    $masterCatalog = new Catalog($theCatalog->groupid);
}

echo $out;

echo $OUTPUT->heading(get_string('catalogue', 'local_shop'));

echo $renderer->catalog_header();

$viewurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'id' => $theShop->id, 'catalogid' => $theCatalog->id));
echo $renderer->category_chooser($viewurl, $theCatalog);

echo $OUTPUT->heading(get_string('products', 'local_shop'));

if (count(array_keys($products)) == 0) {
    if (@$SESSION->shop->categoryid) {
       echo $OUTPUT->notification(get_string('noproducts', 'local_shop'));
    } else {
       echo $OUTPUT->notification(get_string('noproductincategory', 'local_shop'));
    }
} else {
    $formurl = new moodle_url('/local/shop/products/view.php');
    echo '<form name="selection" action="'.$formurl.'" method="get">';
    echo '<input type="hidden" name="view" value="viewAllProducts" />';
    echo '<input type="hidden" name="what" value="" />';
    echo '<table width="100%" class="shop-catalog-admin">';
    echo $renderer->product_admin_line(null);

    foreach (array_values($products) as $portlet) {
        $portlet->selector = 'productSelector';
        $portlet->catalog = $theCatalog;

        if (file_exists($CFG->dirroot.'/local/shop/datahandling/handlers/'.$portlet->code.'.class.php')) {
            if ($portlet->enablehandler) {
                $portlet->code .= ' <img title="'.$hashandlersstr.'" src="'.$OUTPUT->pix_url('hashandler', 'local_shop').'" />';
            } else {
                $portlet->code .= ' <img title="'.$hashandlersstr.'" src="'.$OUTPUT->pix_url('hashandlerdisabled', 'local_shop').'" />';
            }
        }

        if (!$portlet->isset) {
            // Product is a standalone standard product.
            $portlet->thumb = $portlet->get_thumb_url();
            echo $renderer->product_admin_line($portlet, true);
        } else {
            // Product is either a set or a bundle.

            /*
            if ($theCatalog->isslave) {
                // Get the master pieace that has same code and replace master record overriden by local.
                $masteritem = $masterCatalog->get_product_by_code($portlet->code);
                $localitem = $portlet;
                $portlet = $masteritem->apply($localitem);
            }
            */

            if ($portlet->isset == PRODUCT_SET) {
                // is a product set
                $portlet->thumb = $OUTPUT->pix_url('productset', 'local_shop');
                echo $renderer->set_admin_line($portlet, true);
            } else {
                // is a product bundle
                // update bundle price info
                $bundlePrice = 0;
                $bundleTTCPrice = 0;
                if ($portlet->elements) {
                    foreach (array_values($portlet->elements) as $aBundleElement) {
                        // accumulate untaxed
                        $bundlePrice += $aBundleElement->price1;
                        // accumulate taxed after tax transform
                        $price = $aBundleElement->price1;
                        $aBundleElement->TTCprice = shop_calculate_taxed($aBundleElement->price1, $aBundleElement->taxcode);
                        $bundleTTCPrice += $aBundleElement->TTCprice;
                    }
                } else {
                    $bundlePrice = 0;
                    $bundleTTCPrice = 0;
                }
                /*
                 * update bundle price in database for other applications. Note that only visible product entry
                 * is updated.
                 */
                $record = new StdClass;
                $record->id = $portlet->id;
                $record->price1 = $bundlePrice;
                $DB->update_record('local_shop_catalogitem', $record);
                $portlet->price1 = $bundlePrice;
                $portlet->bundleTTCPrice = $bundleTTCPrice;
                $portlet->thumb = $portlet->get_thumb_url();
                echo $renderer->bundle_admin_line($portlet);
          }
       }
   }
}
echo '</table>';
echo '</form>';

echo $renderer->catlinks($theCatalog);
