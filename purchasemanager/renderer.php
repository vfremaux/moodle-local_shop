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
    public function productinstance_admin_line(&$productinstance, $viewparams = array()) {
        global $OUTPUT, $CFG;

        $this->check_context();

        $str = '';

        if (is_null($productinstance)) {

            $str .= '<tr class="shop-products-caption" valign="top">';
            $str .= '<th class="header c0">';
            $str .= get_string('sel', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c1">';
            $str .= get_string('image', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c1">';
            $str .= get_string('code', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c3">';
            $str .= get_string('designation', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c11">';
            $str .= get_string('renewable', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c11">';
            $str .= get_string('contexttype', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c11">';
            $str .= get_string('instance', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c11">';
            $str .= get_string('startdate', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c11">';
            $str .= get_string('enddate', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c11" align="right">';
            $str .= get_string('purchasedprice', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header lastcol" width="30">';
            $str .= '</th>';
            $str .= '</tr>';
        } else {
            $billitem = new BillItem($productinstance->initialbillitemid, $this->theshop);
            $product = new CatalogItem($productinstance->catalogitemid);

            $expiredcount = 0;
            $expiringocunt = 0;
            $pendingcount = 0;
            $runningcount = 0;
            $statusclass = '';
            $pend = ($productinstance->enddate) ? date('Y/m/d H:i', $productinstance->enddate) : 'N.C.';
            $pstart = date('Y/m/d H:i', $productinstance->startdate);
            $now = time();
            if ($product->renewable) {
                if ($now > $productinstance->enddate) {
                    // Expired.
                    $statusclass = 'cs-product-expired';
                    $expiredcount++;
                } else if ($now > $productinstance->enddate - DAYSECS * 3) {
                    // Expiring.
                    $statusclass = 'cs-product-expiring';
                } else if ($now < $productinstance->startdate) {
                    // Pending.
                    $statusclass = 'cs-product-pending';
                    $pendingcount++;
                } else {
                    // Running.
                    $statusclass = 'cs-product-running';
                    $runningcount++;
                }
            }

            $str .= '<tr class="shop-productinstance-row" valign="top">';
            $str .= '<td class="cell '.$statusclass.'" align="center">';
            if (has_capability('local/shop:salesadmin', context_system::instance())) {
                $str .= '<input type="checkbox" id="" name="productids" value="'.$productinstance->id.'" />';
            }
            $str .= '</td>';
            $str .= '<td class="cell" align="center">';
            $str .= '<img src="'.$product->get_thumb_url().'" vspace="10" border="0" height="50">';
            $str .= '</td>';
            $str .= '<td class="name cell" align="left">';
            $str .= $product->code;
            $str .= '</td>';
            $str .= '<td class="name cell" align="left">';
            $str .= $product->name;
            $str .= '</td>';
            $str .= '<td class="cell">';
            $str .= ($product->renewable) ? get_string('yes') : '';
            $str .= '</td>';
            $str .= '<td class="cell">';
            $str .= get_string($productinstance->contexttype, 'local_shop');
            $str .= '</td>';
            $str .= '<td class="cell">';
            $str .= $productinstance->get_instance_link();
            $str .= '</td>';
            $str .= '<td class="cell">';
            $str .= $pstart;
            $str .= '</td>';
            $str .= '<td class="cell">';
            $str .= $pend;
            $str .= '</td>';
            $str .= '<td class="amount cell" align="right">';
            $str .= $billitem->unicost.' '.$this->theshop->get_currency();
            $str .= '</td>';
            $str .= '<td align="right" class="lastcol '.$statusclass.'">';

            if (has_capability('local/shop:salesadmin', context_system::instance())) {
                $pix = $OUTPUT->pix_icon('t/delete', get_string('delete'), 'core');
                $params = array('what' => 'delete',
                                'productids[]' => $productinstance->id,
                                'sesskey' => sesskey());
                                $params = array_merge($params, $viewparams);
                $deleteurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
                $commands = '<a href="'.$deleteurl.'" title="'.get_string('delete').'">'.$pix.'</a>';

                if ($productinstance->deleted) {
                    $pix = $OUTPUT->pix_icon('t/stop', '', 'core');
                    $title = get_string('softrestore', 'local_shop');
                } else {
                    $pix = $OUTPUT->pix_icon('t/go', '', 'core');
                    $title = get_string('softdelete', 'local_shop');
                }
                $params = array('what' => 'softdelete',
                                'productids[]' => $productinstance->id,
                                'sesskey' => sesskey());
                                $params = array_merge($params, $viewparams);
                $deleteurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
                $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$title.'">'.$pix.'</a>';
            }
            $str .= '<div class="shop-line-commands">'.$commands.'</div>';
            $str .= '</td>';
            $str .= '</tr>';
        }

        return $str;
    }
}