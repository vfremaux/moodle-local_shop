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
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use \local_shop\Bill;

// Resolving invoice identity and command.

$action = optional_param('what', '', PARAM_TEXT);
$transid = optional_param('transid', @$SESSION->shoppingcart->transid, PARAM_TEXT);
try {
    $afullbill = Bill::get_by_transaction($transid);
} catch (Exception $e) {
    die("Transaction exception \n");
}

// In case session is lost, go to the public entrance of the shop.
if ((!isset($SESSION->shoppingcart) ||
        !isset($SESSION->shoppingcart->customerinfo)) &&
                $action != 'navigate' && !empty($transid)) {
    $params = array('id' => $theshop->id, 'blockid' => @$theblock->instance->id, 'view' => 'shop');
    redirect(new moodle_url('/local/shop/front/view.php', $params));
}

/*
 * All payment process (internal or external) is supposed to be done here !!
 * produce is only for giving interactive answer to user, and produce interactively if invoice is soldout
 * $payplugin->process($cmd, $afullbill, $theblock);
 */

$return = 0;
$interactive = true;
if ($action != '') {
    $instanceid = $theshop->id; // Unify interactive and non interactive processing.
    include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
    $controller = new \local_shop\front\production_controller($theshop, $thecatalog, $theblock, $afullbill, false, $interactive);
    $controller->receive($action);
    $result = $controller->process($action);
}

$supports = array();
if ($config->sellermailsupport) {
    $supports[] = get_string('byemailat', 'local_shop').' '. $config->sellermailsupport;
}
if ($config->sellerphonesupport) {
    $supports[] = get_string('byphoneat', 'local_shop').' '. $config->sellerphonesupport;
}
$supportstr = implode(' '.get_string('or', 'local_shop').' ', $supports);
$supportstr = (empty($supportstr)) ? '(No support info)' : $supportstr;

echo $out;

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

$completestates = array(SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PREPROD);
if (in_array($afullbill->status, $completestates) || $return == -1) {
    echo '<center>';

    echo $renderer->progress('PRODUCE');

    // Controller tells us we already produced that.
    if ($return == -1) {
        echo $OUTPUT->box_start('shop-notification');
        echo $OUTPUT->notification(get_string('productionbounceadvice', 'local_shop'), 'shop-notice');
        echo $OUTPUT->box_end();
    }

    echo $OUTPUT->box_start('shop-notification');
    echo $OUTPUT->box_start('shop-notification-message');
    echo $config->sellername.' ';
    echo shop_compile_mail_template('post_billing_message', array(), '');
    echo '<img id="prod-waiter" src="'.$OUTPUT->pix_url('waitingforprod', 'local_shop').'" />';
    echo $OUTPUT->box_start('shop-message-hidden', 'shop-notification-message-followup');
    echo shop_compile_mail_template('success_followup_text', array('SUPPORT' => $supportstr), 'shoppaymodes_'.$afullbill->paymode);
    echo $OUTPUT->box_end();
    echo $OUTPUT->box_end();
    echo $OUTPUT->box_end();

    // A specific report.
    if (!empty($afullbill->onlinefeedback->public)) {
        echo $OUTPUT->box_start('shop-message-hidden', 'shop-notification-result');
        echo $OUTPUT->heading(get_string('productionresults', 'local_shop'));
        if (empty($afullbill->onlinefeedback->public)) {
            $afullbill->onlinefeedback->public = get_string('productioncomplete', 'local_shop');
        }
        echo $afullbill->onlinefeedback->public;
        echo $OUTPUT->box_end();
    }

    echo $OUTPUT->box_start('shop-message-hidden', 'shop-continue-form');
    $options['nextstring'] = 'getinvoice';
    $options['transid'] = $afullbill->transactionid;
    $options['hideback'] = true;
    echo $renderer->action_form('produce', $options);
    echo $renderer->shop_return_button($theshop);
    echo $OUTPUT->box_end();

} else {
    echo '<center>';
    echo $renderer->progress('PENDING');
    echo '</center>';

    echo $OUTPUT->box_start('shop-notification');
    echo $OUTPUT->box_start('shop-notification-message');
    echo $config->sellername.' ';
    echo shop_compile_mail_template('post_billing_message', array(), '');
    echo $OUTPUT->box_start('shop-message-hidden', 'shop-notification-message-followup');
    echo shop_compile_mail_template('pending_followup_text', array('SUPPORT' => $supportstr), 'shoppaymodes_'.$afullbill->paymode);
    echo $OUTPUT->box_end();
    echo $OUTPUT->box_end();

    echo $OUTPUT->box_end();

    echo '<div id="shop-buttons">';
    echo $renderer->printable_bill_link($afullbill);
    echo $renderer->shop_return_button($theshop);
    echo '</div>';
}

// If testing the shop, provide a manual link to generate the paypal_ipn call.
if ($config->test && $afullbill->paymode == 'paypal') {
    require_once($CFG->dirroot.'/local/shop/paymodes/paypal/ipn_lib.php');
    paypal_print_test_ipn_link($SESSION->shoppingcart->transid, $theshop->id);
}
