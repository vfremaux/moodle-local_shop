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
require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

use \local_shop\Bill;

$config = get_config('local_shop');

list($theshop, $thecatalog, $theblock) = shop_build_context();
$transid = required_param('transid', PARAM_TEXT);
$billid = required_param('billid', PARAM_INT);

if ($transid) {
    if (!$afullbill = Bill::get_by_transaction($transid)) {
        $params = array('view' => 'shop', 'shopid' => $theshop->id, 'blockid' => 0 + @$theblock->instance->id);
        $viewurl = new moodle_url('/local/shop/front/view.php', $params);
        print_error('invalidtransid', 'local_shop', $viewurl);
    }
} else if ($billid) {
    require_login();
    if (!$afullbill = new Bill($billid)) {
        $params = array('view' => 'shop', 'shopid' => $theshop->id, 'blockid' => 0 + @$theblock->instance->id);
        $viewurl = new moodle_url('/local/shop/front/view.php', $params);
        print_error('invalidbillid', 'local_shop', $viewurl);
    }

    $systemcontext = context_system::instance();
    if (($afullbill->customer->hasaccount != $USER->id) &&
            !has_any_capability(array('local/shop:salesadmin', 'moodle/site:config'), $systemcontext)) {
        $params = array('view' => 'shop', 'id' => $id, 'blockid' => 0 + @$theblock->instance->id);
        $viewurl = new moodle_url('/local/shop/front/view.php', $params);
        print_error('errornotownedbill', 'local_shop', $viewurl);
    }

    $realized = array('SOLDOUT', 'COMPLETE', 'PARTIAL');
    $printcommand = (in_array($afullbill->status, $realized)) ? 'printbilllink' : 'printorderlink';
}

$usercontext = context_user::instance($afullbill->customeruser->id);

$params = array('transid' => $transid, 'billid' => $billid, 'id' => $theshop->id);
$url = new moodle_url('/local/shop/front/bill.popup.php', $params);
$PAGE->set_url($url);
$PAGE->set_context($usercontext);
$PAGE->set_pagelayout('popup');

$renderer = shop_get_renderer();
$renderer->load_context($theshop, $thecatalog, $theblock);

$billrenderer = shop_get_renderer('bills');
$billrenderer->load_context($theshop, $thecatalog, $theblock);

$realized = array(SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PARTIAL, SHOP_BILL_PREPROD);

if (!in_array($afullbill->status, $realized)) {
    $headerstring = get_string('ordersheet', 'local_shop');
    print_string('ordertempstatusadvice', 'local_shop');
} else {
    if (empty($afullbill->idnumber)) {
        $headerstring = get_string('proformabill', 'local_shop');
    } else {
        $headerstring = get_string('bill', 'local_shop');
    }
}

echo $OUTPUT->header();
echo '<div style="max-width:780px">';

$afullbill->withlogo = true;
echo $renderer->invoice_header($afullbill);

echo '<div id="order" style="margin-top:20px">';

echo '<table cellspacing="5" class="generaltable" width="100%">';
echo $renderer->order_line(null);
$hasrequireddata = array();

foreach ($afullbill->items as $biid => $bi) {
    if ($bi->type == 'BILLING') {
        echo $renderer->order_line($bi->catalogitem->shortname, $bi->quantity);
    } else {
        echo $renderer->bill_line($bi);
    }
}
echo '</table>';

if (!in_array($afullbill->status, $realized)) {
    echo $renderer->full_order_totals($afullbill);
} else {
    echo $billrenderer->full_bill_totals($afullbill);
}
echo $renderer->full_order_taxes($afullbill);

echo $renderer->paymode($afullbill);

echo $renderer->sales_contact();

echo '</div>';

echo $renderer->print_screen_button();

echo $billrenderer->bill_footer($afullbill);

echo $OUTPUT->footer();
