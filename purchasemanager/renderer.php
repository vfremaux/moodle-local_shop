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

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Shop;
use local_shop\Product;
use local_shop\BillItem;
use local_shop\CatalogItem;

class shop_purchasemanager_renderer {

    protected $theshop;

    protected $thecatalog;

    protected $theblock;

    public function load_context(&$theshop, &$thecatalog, &$theblock = null) {
        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;
    }

    private function _check_context() {
        if (empty($this->thecatalog)) {
            throw new coding_exception('context not ready in products_renderer. Missing Catlaog instance');
        }
    }

    public function productinstance_admin_line(&$productinstance) {
        global $OUTPUT, $CFG;

        $this->_check_context();

        $str = '';

        if (is_null($productinstance)) {

            $str .= '<tr class="shop-products-caption" valign="top">';
            $str .= '<!--<th class="header c0">';
            $str .= get_string('sel', 'local_shop');
            $str .= '</th>-->';
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
            $str .= '<th class="header c11">';
            $str .= get_string('purchasedprice', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header lastcol" width="30">';
            $str .= '</th>';
            $str .= '</tr>';
        } else {
            $billitem = new BillItem($productinstance->initialbillitemid);
            $product = new CatalogItem($productinstance->catalogitemid);

            $expiredcount = 0;
            $expiringocunt = 0;
            $runningcount = 0;
            $statusclass = '';
            $pend = ($product->enddate) ? date('Y/m/d H:i', $product->enddate) : 'N.C.';
            $pstart = date('Y/m/d H:i', $product->startdate);
            if ($product->renewable) {
                if (time() > $product->enddate) {
                    // Expired.
                    $statusclass = 'cs-product-expired';
                    $expiredcount++;
                } else if (time() > $product->enddate - DAYSECS * 3) {
                    // Expiring.
                    $statusclass = 'cs-product-expiring';
                } else {
                    // Running.
                    $statusclass = 'cs-product-running';
                    $runningcount++;
                }
            }

            $str .= '<tr class="shop-productinstance-row '.$statusclass.'" valign="top">';
            $str.= '<td class="cell" align="center">';
            $str .= '<img src="'.$product->get_thumb_url().'" vspace="10" border="0" height="50">';
            $str .= '</td>';
            $str .= '<td class="name cell" align="left">';
            $str .= $product->code;
            $str . '</td>';
            $str .= '<td class="name cell" align="left">';
            $str .= $product->name;
            $str .= '</td>';
            $str .= '<td class="cell" align="center">';
            $str .= ($product->renewable) ? get_string('yes') : '';
            $str .= '</td>';
            $str .= '<td class="cell" align="right">';
            $str .= get_string($productinstance->contexttype, 'local_shop');
            $str .= '</td>';
            $str .= '<td class="cell" align="right">';
            $str .= $productinstance->get_instance_link();
            $str .= '</td>';
            $str .= '<td class="cell" align="right">';
            $str .= $pstart;
            $str .= '</td>';
            $str .= '<td class="cell" align="right">';
            $str .= $pend;
            $str .= '</td>';
            $str .= '<td class="amount cell" align="right">';
            $str .= $billitem->unicost.' '.$this->theshop->get_currency();
            $str .= '</td>';
            $str .= '<td align="right" class="lastcol">';

            $str .= '</td>';
            $str .= '</tr>';
        }

        return $str;
    }
}