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

require_once($CFG->dirroot.'/auth/ticket/lib.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;

/*
 * perform a transition from state to state for a workflowed object
 */
function bill_transition_pending_soldout($billorid) {
    global $CFG, $SITE, $USER, $DB, $OUTPUT;

    $config = get_config('local_shop');

    /*
     * Scenario :
     * - the order is being payed offline.
     * - the operator needs to sold out manually the bill and realize all billitems production.
     */

    if (is_object($billorid)) {
        $bill = $billorid;
    } else {
        $bill = new Bill($billid);
    }

    if ($bill) {

        // Start marking soldout status. Final may be COMPLETE if production occurs.
        $message = "[{$bill->transactionid}] Bill Controller :";
        $message .= " Transaction Soldout Operation on seller behalf by $USER->username";
        shop_trace($message);
        $bill->status = 'SOLDOUT';
        $bill->save(true);

        include_once($CFG->dirroot.'/local/shop/datahandling/production.php');
        $bill->customer = $DB->get_record('local_shop_customer', array('id' => $bill->customerid));
        $bill->foruser = $bill->customer->hasaccount;
        $bill->user = $DB->get_record('user', array('id' => $bill->customer->hasaccount));

        $productiondata = produce_postpay($bill);

        shop_aggregate_production($bill, $productiondata, true);

        echo $OUTPUT->box_start();
        echo $productiondata->salesadmin;
        echo $OUTPUT->box_end();

        // Now notify user the order and all products have been activated.
        if (!empty($productiondata->private)) {
            $message = "[{$bill->transactionid}] Bill Controller :";
            $message .= " Transaction Autocompletion Operation on seller behalf by $USER->username";
            shop_trace($message);
            // Notify end user.
            // Feedback customer with mail confirmation.
            $vars = array('SERVER' => $SITE->shortname,
                          'SERVER_URL' => $CFG->wwwroot,
                          'SELLER' => $config->sellername,
                          'FIRSTNAME' => $bill->customer->firstname,
                          'LASTNAME' => $bill->customer->lastname,
                          'MAIL' => $bill->customer->email,
                          'CITY' => $bill->customer->city,
                          'COUNTRY' => $bill->customer->country,
                          'ITEMS' => count($bill->itemcount),
                          'PAYMODE' => get_string($bill->paymode, 'local_shop'),
                          'AMOUNT' => $bill->amount);
            $notification  = shop_compile_mail_template('salesFeedback', $vars, 'local_shop');
            $params = array('shopid' => $billid->shopid,
                            'view' => 'bill',
                            'billid' => $bill->id,
                            'transid' => $bill->transactionid);
            $customerbillviewurl = new moodle_url('/local/shop/front/view.php', $params);
            $seller = new StdClass;
            $seller->firstname = $config->sellername;
            $seller->lastname = '';
            $seller->email = $config->sellermail;
            $seller->maildisplay = 1;
            $title = $SITE->shortname.' : '.get_string('yourorder', 'local_shop');
            $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->private, $notification);
            ticket_notify($bill->user, $seller, $title, $sentnotification, $sentnotification, $customerbillviewurl);
        }
    } else {
        shop_trace("[ERROR] Transition error : Bad bill ID $billid");
    }
}

function bill_transition_failure_soldout($billid) {
    bill_transition_pending_soldout($billid);
}

/*
 * perform a transition from state to state for a workflowed object
 * When a bill gets pending, it waits for a payement that accomplishes the SOLDOUT state.
 * a PLACED to PENDING should try to recover pre_payment production if performed
 * manually
 */
function bill_transition_placed_pending($billorid) {
    global $CFG, $SITE, $USER, $DB, $OUTPUT;

    $config = get_config('local_shop');

    /*
     * Scenario :
     * - the order is being payed offline.
     * - the operator needs to sold out manually the bill and realize all billitems production.
     */

    if (is_object($billorid)) {
        $bill = $billorid;
    } else {
        $bill = new Bill($billid);
    }

    if ($bill) {

        include_once($CFG->dirroot.'/local/shop/datahandling/production.php');

        $productiondata = produce_prepay($bill);
        shop_aggregate_production($bill, $productiondata, true);

        echo $OUTPUT->box_start();
        echo $productiondata->salesadmin;
        echo $OUTPUT->box_end();

        // Now notify user the order and all products have been activated.
        if (!empty($productiondata->private)) {
            // Notify end user.
            // Feedback customer with mail confirmation.
            $vars = array('SERVER' => $SITE->shortname,
                          'SERVER_URL' => $CFG->wwwroot,
                          'SELLER' => $config->sellername,
                          'FIRSTNAME' => $bill->customer->firstname,
                          'LASTNAME' => $bill->customer->lastname,
                          'MAIL' => $bill->customer->email,
                          'CITY' => $bill->customer->city,
                          'COUNTRY' => $bill->customer->country,
                          'ITEMS' => count($bill->billItems),
                          'PAYMODE' => get_string($bill->paymode, 'local_shop'),
                          'AMOUNT' => $bill->amount);
            $notification  = shop_compile_mail_template('salesFeedback', $vars, 'local_shop');
            $params = array('shopid' => $bill->shopid, 'view' => 'bill', 'billid' => $bill->id, 'transid' => $bill->transactionid);
            $customerbillviewurl = new moodle_url('/local/shop/front/view.php', $params);
            $seller = new StdClass;
            $seller->firstname = $config->sellername;
            $seller->lastname = '';
            $seller->email = $config->sellermail;
            $seller->maildisplay = 1;
            $title = $SITE->shortname.' : '.get_string('yourorder', 'local_shop');
            $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->private, $notification);
            ticket_notify($bill->customeruser, $seller, $title, $sentnotification, $sentnotification, $customerbillviewurl);
        }

        $message = "[{$bill->transactionid}] Bill Controller :";
        $message .= " Delayed Transaction Activating Operations on seller behalf by $USER->username";
        shop_trace($message);
        $bill->status = 'PENDING';
        $bill->save(true);
    } else {
        shop_trace("[ERROR] Transition error : Bad bill ID $billid");
    }
}

function bill_transition_soldout_complete($billorid) {
    global $CFG, $SITE, $USER, $DB;

    /*
     * Scenario :
     *
     * the order has being payed offline and passed to SOLDOUT, but no automated production
     * pushhed the bill to COMPLETE. Operator marks manually the order COMPLETE after an
     * offline shiping operation has been done.
     * the operator needs to COMPLETE manually the bill and realize all billitems production
     */

    if (is_object($billorid)) {
        $bill = $billorid;
    } else {
        $bill = new Bill($billid);
    }

    if ($bill) {
        // Start marking soldout status. Final may be COMPLETE if production occurs.
        $message = "[{$bill->transactionid}] Bill Controller :";
        $message .= " Transaction Complete Operation on seller behalf by $USER->username";
        shop_trace($message);
        $bill->status = 'COMPLETE';
        $bill->save(true);
    } else {
        shop_trace("[ERROR] Transition error : Bad bill ID $billid");
    }
}