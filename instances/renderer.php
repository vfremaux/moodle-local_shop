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
 * @package    local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A dedicated renderer for product instances
 */
class shop_instances_renderer {

    /**
     * Print admin line of a shop instance
     * @param object $instance
     */
    public function instance_admin_line($instance) {
        global $OUTPUT;

        $str = '';

        if (is_null($instance)) {

            $str .= '<tr class="shop-products-caption" valign="top">';
            $str .= '<!--<th class="header c0">';
            $str .= get_string('sel', 'local_shop');
            $str .= '</th>-->';
            $str .= '<th class="header c0">';
            $str .= get_string('image', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c1">';
            $str .= get_string('code', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c3">';
            $str .= get_string('designation', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c4">';
            $str .= get_string('price', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c5">';
            $str .= get_string('ttc', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c8">';
            $str .= get_string('status', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header lastcol" width="30">';
            $str .= '</th>';
            $str .= '</tr>';
        } else {
            $pricelines = [];
            $prices = $instance->get_printable_prices();
            foreach ($prices as $key => $price) {
                $pl = '<span class="shop-admin-pricerange">'.$key.' : </span>';
                $pl .= '<span class="shop-admin-amount">'.$price.'</span>';
                $pricelines[] = $pl;
            }

            $taxedpricelines = [];
            $prices = $product->get_printable_prices(true);
            foreach ($prices as $key => $price) {
                $pl = '<span class="shop-admin-pricerange">'.$key.' : </span>';
                $pl .= '<span class="shop-admin-amount">'.$price.'</span>';
                $taxedpricelines[] = $pl;
            }

            $statusclass = strtolower($product->status);
            $str .= '<tr class="shop-'.$statusclass.'line shop-product-row" valign="top">';
            $slaveclass  = (@$portlet->masterrecord == 0) ? '' : 'engraved slaved';
            $str .= '<td class="cell '.$slaveclass.'"align="center">';
            $str .= '<img src="'.$product->thumb.'" vspace="10" border="0" height="50">';
            $str .= '</td>';
            $str .= '<td class="name cell '.$slaveclass.'" align="left">';
            $str .= $product->code;
            $str . '</td>';
            $str .= '<td class="name cell '.$slaveclass.'" align="left">';
            $str .= $product->name;
            $str .= '</td>';
            $str .= '<td class="amount cell '.$slaveclass.'"align="right">';
            $str .= implode('<br/>', $taxedpricelines);
            $str .= '<br/>';
            $str .= '</td>';
            $str .= '<td class="amount cell '.$slaveclass.'" align="center">';
            switch ($product->quantaddressesusers) {

                case SHOP_QUANT_NO_SEATS:
                    $str .= get_string('no');
                    break;

                case SHOP_QUANT_ONE_SEAT:
                    $str .= get_string('oneseat', 'local_shop');
                    break;

                case SHOP_QUANT_AS_SEATS:
                    $str .= get_string('yes');
                    break;
            }

            $str .= '</td>';
            $str .= '<td align="right" class="lastcol">';

            if (@$portlet->masterrecord == 0) {
                $editurl = new moodle_url('/local/shop/instances/edit_instance.php', ['productid' => $product->id]);
                $str .= '<a href="'.$editurl.'">'.$OUTPUT->pix_icon('t/edit', get_string('edit')).'</a> ';
            }

            $deletestr = get_string('deleteproduct', 'local_shop');
            $params = ['view' => 'viewAllProducts', 'what' => 'delete', 'itemid[]' => $product->id];
            $deleteurl = new moodle_url('/local/shop/instances/view.php', $params);
            $str .= '&nbsp;<a href="'.$deleteurl.'">'.$OUTPUT->pix_icon('t/delete', get_string('delete')).'</a>';

            $str .= '</td>';
            $str .= '</tr>';
        }
        return $str;
    }

    /**
     * Print global commands
     */
    public function global_commands() {
        $str = '';

        $str .= '<table width="100%">';
        $str .= '<tr>';
        $str .= '<td align="left">';
        $editinstanceurl .= new moodle_url('/local/shop/instances/edit_instance.php');
        $str .= '<a href="'.$editinstanceurl.'">'.get_string('newproduct', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }
}
