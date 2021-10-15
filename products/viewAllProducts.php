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

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use \local_shop\Catalog;
use \local_shop\Shop;

$action = optional_param('what', '', PARAM_ALPHA);
$order = optional_param('order', 'code', PARAM_ALPHA);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);

$SESSION->shop->categoryid = optional_param('categoryid', 0 + @$SESSION->shop->categoryid, PARAM_INT);

if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = $USER->id;
} else {
    $shopowner = null;
    $shoprenderer = $PAGE->get_renderer('local_shop');
    $shoprenderer->print_owner_menu($url, 0);
}

// Execute controller.
$hashandlersstr = get_string('hashandlers', 'local_shop');

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/products/products.controller.php');
    $controller = new \local_shop\backoffice\product_controller($thecatalog);
    $controller->receive($action);
    $controller->process($action);
}
$products = array();

$thecatalog->get_all_products_for_admin($products);

$shopinstances = Shop::get_instances(array('catalogid' => $thecatalog->id));
$shopcount = 0 + count($shopinstances);
if ($shopcount == 1) {
    $theshop = array_pop($shopinstances);
    if ($SESSION->shop->id != $theshop->id) {
        $SESSION->shop = $theshop;
        $params = ['view' => 'viewAllProducts', 'catalogid' => $thecatalog->id, 'shopid' => $theshop->id];
        $redirecturl = new moodle_url('/local/shop/products/view.php', $params);
        redirect($redirecturl);
    }
    echo $out;
    echo $OUTPUT->heading(get_string('activeshop', 'local_shop'));
    echo $renderer->shop_header();
} else if ($shopcount > 2) {
    echo $out;
    echo $OUTPUT->heading(get_string('activeshop', 'local_shop'));
    echo $renderer->shop_header();
} else {
    echo $out;    
}

echo $OUTPUT->heading(get_string('catalogue', 'local_shop'));

echo $renderer->catalog_header();

$params = array('view' => 'viewAllProducts', 'id' => $theshop->id, 'catalogid' => $thecatalog->id);
$viewurl = new moodle_url('/local/shop/products/view.php', $params);
echo $renderer->category_chooser($viewurl);

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
        $portlet->catalog = $thecatalog;

        if (file_exists($CFG->dirroot.'/local/shop/datahandling/handlers/'.$portlet->code.'.class.php')) {
            if ($portlet->enablehandler) {
                $portlet->code .= $OUTPUT->pix_icon('hashandler', $hashandlersstr, 'local_shop');
            } else {
                $portlet->code .= $OUTPUT->pix_icon('hashandlerdisabled', $hashandlersstr, 'local_shop');
            }
        }

        if (!$portlet->isset) {
            // Product is a standalone standard product.
            echo $renderer->product_admin_line($portlet, true);
        } else {
            // Product is either a set or a bundle.

            if ($portlet->isset == PRODUCT_SET) {
                // Is a product set.
                echo $renderer->set_admin_line($portlet, true);
            } else {
                // CHANGE : Let bundle have their own pricing.
                /*
                // Is a product bundle.
                // Update bundle price info.
                $bundleprice = 0;
                $bundlettcprice = 0;
                if ($portlet->elements) {
                    foreach (array_values($portlet->elements) as $element) {
                        // Accumulate untaxed.
                        $bundleprice += $element->price1;
                        // Accumulate taxed after tax transform.
                        $price = $element->price1;
                        $element->TTCprice = shop_calculate_taxed($element->price1, $element->taxcode);
                        $bundlettcprice += $element->TTCprice;
                    }
                } else {
                    $bundleprice = 0;
                    $bundlettcprice = 0;
                }

                /*
                 * update bundle price in database for other applications. Note that only visible product entry
                 * is updated.
                 */
                /*
                $record = new StdClass;
                $record->id = $portlet->id;
                $record->price1 = $bundleprice;
                $DB->update_record('local_shop_catalogitem', $record);

                $portlet->price1 = $bundleprice;
                $portlet->bundleTTCPrice = $bundlettcprice;
                */
                echo $renderer->bundle_admin_line($portlet);
            }
        }
    }
}
echo '</table>';
echo '</form>';

if (!$thecatalog->isslave) {
    echo $renderer->catlinks($thecatalog);
} else {
    echo '<div class="shop-cat-notice">';
    print_string('nocatsslave', 'local_shop');
    echo '</div>';
}
