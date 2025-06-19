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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;

$action = optional_param('what', '', PARAM_TEXT);
$transid = required_param('transid', PARAM_RAW);

if (!$afullbill = Bill::get_by_transaction($transid)) {
    $params = ['view' => 'shop', 'id' => $id, 'blockid' => (0 + @$theblock->id)];
    $viewurl = new moodle_url('/local/shop/front/view.php', $params);
    throw new moodle_exception(get_string('invalidtransid', 'local_shop', $viewurl));
}

if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/invoice.controller.php');
    $controller = new \local_shop\front\invoice_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $result = $controller->process($action);
}

$supports = [];
if ($config->sellermailsupport) {
    $supports[] = get_string('byemailat', 'local_shop'). ' '. $config->sellermailsupport;
}
if ($config->sellerphonesupport) {
    $supports[] = get_string('byphoneat', 'local_shop'). ' '. $config->sellerphonesupport;
}
$supportstr = implode(' '.get_string('or', 'local_shop').' ', $supports);
$supportstr = (empty($supportstr)) ? '(No support info)' : '';

echo $out;

// Start ptinting page.

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

if ($afullbill->status == SHOP_BILL_SOLDOUT || $afullbill->status == SHOP_BILL_COMPLETE) {
    echo '<center>';
    echo $renderer->progress('BILL');
    echo '</center>';
} else {
    echo '<center>';
    echo $renderer->progress('PENDING');
    echo '</center>';
}

echo $OUTPUT->box_start('', 'shop-invoice');

$afullbill = Bill::get_by_transaction($transid);

if ($afullbill->status == SHOP_BILL_SOLDOUT || $afullbill->status == SHOP_BILL_COMPLETE) {

    echo $renderer->invoice_header($afullbill);

    echo '<div id="online-order" style="margin-top:20px">';

    echo $renderer->order($afullbill, $theshop);
    echo $renderer->full_order_totals($afullbill, $theshop);
    echo $renderer->full_order_taxes($afullbill, $theshop);

    echo '</div>';

    echo $OUTPUT->heading(get_string('paymode', 'local_shop'), 2, '', 'invoice-paymode');

    require_once($CFG->dirroot.'/local/shop/paymodes/'.$afullbill->paymode.'/'.$afullbill->paymode.'.class.php');

    $classname = 'shop_paymode_'.$afullbill->paymode;

    echo '<div id="shop-order-paymode">';

    $pm = new $classname($theshop);
    $pm->print_name();

    echo '</div>';

    // A specific report.
    if (!empty($afullbill->productiondata->public)) {
        echo $OUTPUT->box_start();
        echo $afullbill->productiondata->public;
        echo $OUTPUT->box_end();
    }
} else {
    echo $OUTPUT->box_start();
    echo $config->sellername.' ';
    echo shop_compile_mail_template('post_billing_message', [], '');
    echo shop_compile_mail_template('pending_followup_text', ['SUPPORT' => $supportstr], 'shoppaymodes_'.$afullbill->paymode);
    echo $OUTPUT->box_end();
}

echo $renderer->printable_bill_link($afullbill, $transid);

// If testing the shop, provide a manual link to generate the paypal_ipn call.
if ($config->test && $afullbill->paymode == 'paypal') {
    include_once($CFG->dirroot.'/local/shop/paymodes/paypal/ipn_lib.php');
    paypal_print_test_ipn_link($transid, $theshop->id);
}

echo $OUTPUT->box_end();

echo '<form action="/local/shop/front/view.php" method="post" >';

$options['nextstring'] = 'backtoshop';
$options['hideback'] = true;
$options['inform'] = true;
$options['transid'] = $afullbill->transactionid;
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
