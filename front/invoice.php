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

require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;

$action = optional_param('what', '', PARAM_TEXT);
$transid = required_param('transid', PARAM_RAW);
if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/invoice.controller.php');
    $controller = new \local_shop\front\invoice_controller($theshop, $thecatalog, $theblock);
    $result = $controller->process($action);
}

$supports = array();
if ($config->sellermailsupport) $supports[] = get_string('byemailat', 'local_shop'). ' '. $config->sellermailsupport;
if ($config->sellerphonesupport) $supports[] = get_string('byphoneat', 'local_shop'). ' '. $config->sellerphonesupport;
$supportstr = implode(' '.get_string('or', 'local_shop').' ', $supports);
$supportstr = (empty($supportstr)) ? '(No support info)' : '';

echo $out;

// Start ptinting page.

echo $OUTPUT->box_start('', 'shop-invoice');

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

$aFullBill = Bill::get_by_transaction($transid);

if ($aFullBill->status == SHOP_BILL_SOLDOUT || $aFullBill->status == SHOP_BILL_COMPLETE) {

    echo '<center>';
    echo $renderer->progress('BILL');
    echo '</center>';

    echo $renderer->invoice_header($aFullBill);

    echo '<table cellspacing="5" class="generaltable" width="100%">';

    echo '<div id="order" style="margin-top:20px">';

    echo '<table cellspacing="5" class="generaltable" width="100%">';
    echo $renderer->order_line(null);
    $hasrequireddata = array();

    foreach ($aFullBill->items as $biid => $bi) {
        if ($bi->type == 'BILLING') {
            echo $renderer->order_line($bi->catalogitem->shortname, $bi->quantity);
        } else if ($bi->type == 'SHIPPING') {
            echo $renderer->bill_line($bi);
        }
    }
    echo '</table>';

    echo $renderer->full_order_totals($aFullBill);
    echo $renderer->full_order_taxes($aFullBill);

    echo $OUTPUT->heading(get_string('paymentmode', 'local_shop'), 2);

    require_once $CFG->dirroot.'/local/shop/paymodes/'.$aFullBill->paymode.'/'.$aFullBill->paymode.'.class.php';

    $classname = 'shop_paymode_'.$aFullBill->paymode;

    echo '<div id="shop-order-paymode">';
    $pm = new $classname($theshop);
    $pm->print_name();
    echo '</div>';

    // A specific report.
    if (!empty($aFullBill->productiondata->public)) {
        echo $OUTPUT->box_start();
        echo $aFullBill->productiondata->public;
        echo $OUTPUT->box_end();
    }
} else {
    echo '<center>';
    echo $renderer->progress('PENDING');
    echo '</center>';

    echo $OUTPUT->box_start();
    echo $config->sellername.' ';
    echo shop_compile_mail_template('post_billing_message', array(), '');
    echo shop_compile_mail_template('pending_followup_text', array('SUPPORT' => $supportstr), 'shoppaymodes_'.$aFullBill->paymode);
    echo $OUTPUT->box_end();
}

echo $renderer->printable_bill_link($aFullBill);

// If testing the shop, provide a manual link to generate the paypal_ipn call.
if ($config->test && $aFullBill->paymode == 'paypal') {
    require_once($CFG->dirroot.'/local/shop/paymodes/paypal/ipn_lib.php');
    paypal_print_test_ipn_link($aFullBill->id, $transid, $id, $pinned);
}

echo $OUTPUT->box_end();

echo '<form action="/local/shop/front/view.php" method="post" >';

$options['nextstring'] = 'backtoshop';
$options['hideback'] = true;
$options['inform'] = true;
$options['transid'] = $aFullBill->transactionid;
echo $renderer->action_form('invoice', $options);

// If we are sure the customer has a customer account.
if (!empty($theshop->defaultcustomersupportcourse) && $SESSION->shoppingcart->customerinfo->hasaccount) {
    echo '&nbsp;<input type="submit"
                       name="customerservice"
                       class="shop-next-button"
                       value="'.get_string('gotocustomerservice', 'local_shop').'" />';
}

echo '</p>';
echo '</form>';