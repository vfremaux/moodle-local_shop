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

require_once($CFG->dirroot.'/local/shop/renderer.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Shop;
use local_shop\Product;
use local_shop\BillItem;
use local_shop\CatalogItem;

class shop_purchasemanager_renderer extends local_shop_base_renderer {

    /**
     * Displays a single product instance admin line.
     * @param Productref &$productinstance a full Product instance.
     * @param array $viewparams contextual query params from the view.
     */
    public function productinstance_admin_form(&$productinstances, $viewparams = array(), $customerid, $shopowner) {
        global $OUTPUT, $CFG;

        $this->check_context();

        $template = new StdClass;

        $template->selstr = get_string('sel', 'local_shop');
        $template->imagestr = get_string('image', 'local_shop');
        $template->billstr = get_string('bill', 'local_shop');
        $template->codestr = get_string('code', 'local_shop');
        $template->designationstr = get_string('designation', 'local_shop');
        $template->renewablestr = get_string('renewable', 'local_shop');
        $template->contexttypestr = get_string('contexttype', 'local_shop');
        $template->instancestr = get_string('instance', 'local_shop');
        $template->purchasedpricestr = get_string('purchasedprice', 'local_shop');
        $template->referencestr = get_string('reference', 'local_shop');
        $template->startdatestr = get_string('startdate', 'local_shop');
        $template->enddatestr = get_string('enddate', 'local_shop');

        $template->customerid = $customerid;
        $template->shopowner = $shopowner;

        $template->formurl = new moodle_url('/local/shop/purchasemanager/view.php');

        foreach (array_values($productinstances) as $productinstance) {
            $producttpl = new StdClass;
            $billitem = null;
            if ($productinstance->initialbillitemid) {
                $billitem = new BillItem($productinstance->initialbillitemid, false, $this->theshop);
            }
            if ($productinstance->currentbillitemid) {
                $currentbillitem = new BillItem($productinstance->currentbillitemid, false, $this->theshop);
                $pix = $OUTPUT->pix_icon('bill', '', 'local_shop');
                $params = array('view' => 'viewBill', 'billid' => $currentbillitem->billid);
                $linkurl = new moodle_url('/local/shop/bills/view.php', $params);
                $attrs = array('target' => 'blank');
                $producttpl->currentbilllink = html_writer::link($linkurl, $pix, $attrs);
            }
            $product = new CatalogItem($productinstance->catalogitemid);

            $expiredcount = 0;
            $expiringcount = 0;
            $pendingcount = 0;
            $runningcount = 0;
            $producttpl->statusclass = '';
            $producturl = new moodle_url('/local/shop/products/view.php', array('view' => 'ProductDetail', 'itemid' => $product->id));
            $producttpl->code = '<a href="'.$producturl.'">'.$product->code.'</a>';
            $producttpl->designation = format_string($product->name);
            $producttpl->reference = $productinstance->reference;
            $producttpl->renewable = ($product->renewable) ? get_string('yes') : '';
            $producttpl->pend = ($productinstance->enddate) ? date('Y/m/d H:i', $productinstance->enddate) : 'N.C.';
            $producttpl->pstart = date('Y/m/d H:i', $productinstance->startdate);
            $now = time();
            if ($product->renewable) {
                if ($productinstance->enddate && ($now > $productinstance->enddate)) {
                    // Expired.
                    $producttpl->statusclass = 'cs-product-expired';
                    $expiredcount++;
                } else if ($productinstance->enddate && $now > $productinstance->enddate - DAYSECS * 3) {
                    // Expiring.
                    $producttpl->statusclass = 'cs-product-expiring';
                } else if ($now < $productinstance->startdate) {
                    // Pending.
                    $producttpl->statusclass = 'cs-product-pending';
                    $pendingcount++;
                } else {
                    // Running.
                    $producttpl->statusclass = 'cs-product-running';
                    $runningcount++;
                }
            }

            if (has_capability('local/shop:salesadmin', context_system::instance())) {
                $producttpl->selcheckbox = '<input type="checkbox" id="" name="productids" value="'.$productinstance->id.'" />';
            }
            $producttpl->thumburl = $product->get_thumb_url();
            $producttpl->contexttype = get_string($productinstance->contexttype, 'local_shop');
            $producttpl->instancelink = $productinstance->get_instance_link();
            $producttpl->unitcost = ($billitem) ? $billitem->unitcost : 'N.C.';
            $producttpl->currency = $this->theshop->get_currency();

            if (has_capability('local/shop:salesadmin', context_system::instance())) {
                $pix = $OUTPUT->pix_icon('t/delete', get_string('delete'), 'moodle');
                $params = array('what' => 'delete',
                                'productids[]' => $productinstance->id,
                                'sesskey' => sesskey());
                                $params = array_merge($params, $viewparams);
                $deleteurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
                $producttpl->commands = '<a href="'.$deleteurl.'" title="'.get_string('delete').'">'.$pix.'</a>';

                if ($productinstance->deleted) {
                    $title = get_string('softrestore', 'local_shop');
                    $pix = $OUTPUT->pix_icon('t/stop', $title, 'moodle');
                } else {
                    $title = get_string('softdelete', 'local_shop');
                    $pix = $OUTPUT->pix_icon('t/go', $title, 'moodle');
                }
                $params = array('what' => 'softdelete',
                                'productids[]' => $productinstance->id,
                                'sesskey' => sesskey());
                                $params = array_merge($params, $viewparams);
                $deleteurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
                $producttpl->commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$title.'">'.$pix.'</a>';

                if (local_shop_supports_feature('products/editable')) {
                    $pix = $OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle');
                    $params = array('instanceid' => $productinstance->id,
                                    'sesskey' => sesskey());
                    $linkurl = new moodle_url('/local/shop/pro/purchasemanager/edit_instance.php', $params);
                    $producttpl->commands .= '&nbsp;<a href="'.$linkurl.'">'.$pix.'</a>';
                }
            }

            $template->products[] = $producttpl;
        }

        return $this->output->render_from_template('local_shop/purchaselist', $template);
    }

    public function filters($ownermenu, $customermenu) {
        $str = '';

        $str .= '<div class="form-filter-menus">';
        $str .= '<div class="form-filter-owner">';
        $str .= $ownermenu;
        $str .= '</div>';
        $str .= '<div class="form-filter-customer">';
        $str .= $customermenu;
        $str .= '</div>';
        $str .= '</div>';

        return $str;
    }

    public function add_instance_button() {
        global $OUTPUT;

        $customerid = optional_param('customer', 0, PARAM_INT);
        $params = array('customer' => $customerid);
        $buttonurl = new moodle_url('/local/shop/pro/purchasemanager/edit_instance.php', $params);
        $str = $OUTPUT->box_start('shop-add-instance');
        $str .= $OUTPUT->single_button($buttonurl, get_string('addproductinstance', 'local_shop'));
        $str .= $OUTPUT->box_end();

        return $str;
    }
}