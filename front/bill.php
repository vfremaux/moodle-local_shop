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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use \local_shop\Bill;

$transid = required_param('transid', PARAM_TEXT);

$billrenderer = shop_get_renderer('bills');
$billrenderer->load_context($theshop, $thecatalog, $theblock);

if (!$bill = Bill::get_by_transaction($transid)) {
    $params = array('view' => 'shop', 'id' => $id, 'blockid' => (0 + @$theblock->id));
    $viewurl = new moodle_url('/local/shop/front/view.php', $params);
    $errormessage = get_string('invalidtransid', 'local_shop', $viewurl);
}

echo $out;

if (!empty($errormessage)) {
    echo $OUTPUT->notification($errormessage, 'error');
    echo $OUTPUT->footer;
    die;
}

$realized = array(SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PARTIAL, SHOP_BILL_PREPROD);
if (!in_array($bill->status, $realized)) {
    $billtitlestr = get_string('ordersheet', 'local_shop');
    print_string('ordertempstatusadvice', 'local_shop');
} else {
    if (empty($bill->idnumber)) {
        $billtitlestr = get_string('proformabill', 'local_shop');
    } else {
        $billtitlestr = get_string('bill', 'local_shop');
    }
}
echo '<div style="float:right">';
echo $renderer->printable_bill_link($bill->id, $transid);
echo '</div ';

echo $OUTPUT->heading($billtitlestr, 1);

echo $renderer->customer_info($bill);

echo '<div id="order">';

echo '<table cellspacing="5" class="generaltable" width="100%">';
echo $renderer->order_line(null);
$hasrequireddata = array();

if (!empty($bill->items)) {
    foreach ($bill->items as $biid => $bi) {
        if ($bi->type == 'BILLING') {
            echo $renderer->order_line($bi->catalogitem->shortname, $bi->quantity);
        } else {
            echo $renderer->bill_line($bi);
        }
    }
}
echo '</table>';

if (!in_array($bill->status, $realized)) {
    echo $renderer->full_order_totals($bill, $theshop);
} else {
    echo $billrenderer->full_bill_totals($bill, $theshop);
}

echo $renderer->full_order_taxes($bill, $theshop);

echo $billrenderer->bill_footer($bill);

echo $renderer->sales_contact();

$params = array('view' => 'shop' , 'id' => $theshop->id, 'blockid' => 0 + @$theblock->id);
$backurl = new moodle_url('/local/shop/front/view.php', $params);
echo '<center>';
echo $OUTPUT->single_button($backurl, get_string('backtoshop', 'local_shop'));
echo '</center>';
