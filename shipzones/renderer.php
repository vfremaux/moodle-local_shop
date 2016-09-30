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

require_once($CFG->dirroot.'/local/shop/classes/CatalogShipping.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\CatalogShipping;
use local_shop\Tax;
use local_shop\Shop;

class shop_shipzones_renderer {

    protected $thecatalog;

    public function load_context(&$thecatalog) {
        $this->thecatalog = $thecatalog;
    }

    protected function _check_context() {
        if (empty($this->thecatalog)) {
            throw new \Exception('Catalog instance is missing in ShipZones renderer');
        }
    }

    public function catalog_data($catalog) {

        $str = '<div class="shop-table container-fluid">';
        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param">'.get_string('catalogue', 'local_shop').'</div>';
        $str .= '<div class="shop-cell value">'.format_string($catalog->name).'</div>';
        $str .= '</div>';
        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param">'.get_string('description').'</div>';
        $str .= '<div class="shop-cell value">'.format_string($catalog->description).'</div>';
        $str .= '</div>';
        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param">'.get_string('shops', 'local_shop').'</div>';
        $shopcount = Shop::count(array('catalogid' => $catalog->id));
        $str .= '<div class="value">'.$shopcount.'</div>';
        $str .= '</div>';

        $str .= '</div>';

        return $str;
    }

    public function zone_data($shipzone) {

        $str = '';
        $str .= '<div class="shop-table container-fluid">';
        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param">'.get_string('shipzone', 'local_shop').'</div>';
        $str .= '<div class="shop-cell value">'.format_string($shipzone->zonecode).'</div>';
        $str .= '</div>';

        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param">'.get_string('description').'</div>';
        $str .= '<div class="shop-cell value">'.format_string($shipzone->description).'</div>';
        $str .= '</div>';

        $tax = new Tax($shipzone->taxid);
        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param">'.get_string('tax', 'local_shop').'</div>';
        $str .= '<div class="shop-cell value">'.$tax->title.'</div>';
        $str .= '</div>';

        if (!empty($shipzone->appicability)) {
            $str .= '<div class="shop-row row-fluid">';
            $str .= '<div class="shop-cell param">'.get_string('applicability', 'local_shop').'</div>';
            $str .= '<div class="shop-cell value">'.format_string($shipzone->applicability).'</div>';
            $str .= '</div>';
        }

        $str .= '</div>';

        return $str;
    }

    public function shippings($shippings) {
        echo $OUTPUT;

        $codestr = get_string('productcode', 'local_shop');
        $valuestr = get_string('value', 'local_shop');
        $formulastr = get_string('formula', 'local_shop');
        $astr = get_string('a', 'local_shop');
        $bstr = get_string('b', 'local_shop');
        $cstr = get_string('c', 'local_shop');

        $table = new html_table();
        $table->header = array('', "<b>$codestr</b>", "<b>$valuestr</b>", "<b>$formulastr</b>", "<b>$astr</b>", "<b>$bstr</b>", "<b>$cstr</b>", '');
        $table->width = '100%';
        $table->align = array('center', 'left', 'left', 'left', 'left', 'left', 'right');

        foreach ($shippings as $shipping) {

            $row = array();
            $row[] = '<input type="checkbox" name="shipid[]" value="'.$shipping->id.'" />';
            $row[] = $shipping->productcode;
            $row[] = $shipping->value;
            $row[] = $shipping->formula;
            $row[] = $shipping->a;
            $row[] = $shipping->b;
            $row[] = $shipping->c;

            $params = array('what' => 'edit', 'shippingid' => $shipping->id, 'zoneid' => $zoneid);
            $cmdurl = new moodle_url('/local/shop/shipzones/edit_shipping.php', $params);
            $commands = '<a href="'.$cmdurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" /></a>';

            $params = array('what' => 'deleteshipping', 'shipid[]' => $shipping->id);
            $cmdurl = new moodle_url('/local/shop/shipzones/zoneindex.php', $params);
            $commands .= '&nbsp;<a href="'.$cmdurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
            $row[] = $commands;

            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    public function zones($zones) {
        global $OUTPUT;

        $codestr = get_string('code', 'local_shop');
        $namestr = get_string('name', 'local_shop');
        $billscopeamountstr = get_string('billscopeamount', 'local_shop');
        $taxstr = get_string('tax', 'local_shop');
        $usedentriesstr = print_string('usedentries', 'local_shop');

        $table = new html_table();
        $table->header = array('',
                               "<b>$codestr</b>",
                               "<b>$namestr</b>",
                               "<b>$billscopeamountstr</b>",
                               "<b>$taxstr</b>",
                               "<b>$usedentriesstr</b>",
                               '');

        foreach ($zones as $z) {
            $row = array();
            $row[] = '<!-- input type="checkbox" name="zoneids[]" value="'.$z->id.'" / -->'; // Not yet
            $row[] = $z->zonecode;
            $row[] = $z->description;
            $row[] = $z->billscopeamount;
            $tax = new Tax($z->taxid);
            $row[] = format_string($tax->title);
            $row[] = CatalogShipping::count(array('zoneid' => $z->id));

            if ($z->entries == 0) {
                $params = array('what' => 'deletezone', 'zoneid' => $z->id);
                $indexurl = new moodle_url('/local/shop/shipzones/index.php', $params);
                $commands = '<a href="'.$indexurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';

                $addshippingstr = get_string('newshipping', 'local_shop');
                $params = array('zoneid' => $z->id);
                $addzoneurl = new moodle_url('/local/shop/shipzones/edit_shipping.php', $params);
                $commands .= '&nbsp;<a href="'.$addzoneurl.'">'.$addshippingstr.'</a>';
            } else {
                $editzonestr = get_string('editshippingzone', 'local_shop');
                $params = array('zoneid' => $z->id);
                $zoneindexurl = new moodle_url('/local/shop/shipzones/zoneindex.php', $params);
                $commands = ' <a href="'.$zoneindexurl.'">'.$editzonestr.'</a>';
            }

            $params = array('what' => 'update', 'item' => $z->id);
            $editzoneurl = new moodle_url('/local/shop/shipzones/edit_shippingzone.php', $params);
            $commands .= '&nbsp;<a href="'.$editzoneurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" /></a>';
            $row[] = $commands;

            $table->data[] = $row;

        }

        return html_writer::table($table);
    }

    public function catalogitem_shipping_zones($zones) {
        $codestr = get_string('code', 'local_shop');
        $descriptionstr = get_string('description');
        $scopeamountstr = get_string('billscopeamount', 'local_shop');
        $taxstr = get_string('tax', 'local_shop');
        $entriesstr = get_string('entries', 'local_shop');
        $applicabilitystr = get_string('applicability', 'local_shop');

        $table = new html_table();
        $table->header = array('',
                               "<b>$codestr</b>",
                               "<b>$descriptionstr</b>",
                               "<b>$scopeamountstr</b>",
                               "<b>$taxstr</b>",
                               "<b>$entriesstr</b>",
                               "<b>$applicabilitystr</b>",
                               '');
        $table->width = "100%";
        $table->size = array('5%', '%10', '%30', '%10', '%20', '%5', '%5', '20%');
        $table->align = array('center', 'left', 'left', 'left', 'left', 'center', 'center', 'right');
        $table->data = array();
        foreach ($zones as $z) {
            $row[] = '<input type="checkbox" name="zoneid[]" value="'.$z->id.'\" />';
            $row[] = $z->zonecode;
            $row[] = $z->description;
            $row[] = $z->billscopeamount;
            $row[] = $z->tax;
            $row[] = $z->entries;
            $row[] = $z->applicability;

            if ($z->entries == 0) {
                $params = array('what' => 'delete', 'zoneid[]' => $z->id);
                $indexurl = new moodle_url('/local/shop/shipzones/index.php', $params);
                $commands = '<a href="'.$indexurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
                $addzonestr = get_string('newshipping', 'local_shop');
                $editurl = new moodle_url('/local/shop/shipzones/edit_shipping.php', array('zoneid' => $z->id));
                $command .= '&nbsp;<a href="'.$editurl.'">'.$addzonestr.'</a>';
            } else {
                $editzonestr = get_string('editshippingzone', 'local_shop');
                $indexurl = new moodle_url('/local/shop/shipzones/zoneindex.php', array('zoneid' => $z->id));
                $commands = '&nbsp;<a href="'.$indexurl.'">'.$editzonestr.'</a>';
            }
            $params = array('what' => 'update', 'item' => $z->id);
            $zoneurl = new moodle_url('/local/shop/shipzones/edit_shippingzone.php', $params);
            $commands .= ' <a href="'.$zoneurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" /></a>';

            $row[] = $commands;
        }

        echo $OUTPUT->table($table);
    }
}