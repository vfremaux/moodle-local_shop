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
 * Paymode main class
 *
 * @package  shoppaymodes_paypal
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Bill;
use local_shop\Shop;

/**
 * A class to use when paying by Paypal accounts.
 */
class shop_paymode_paypal extends shop_paymode {

    /**
     * Constructor
     * @param Shop $shop
     */
    public function __construct(?Shop $shop) {
        parent::__construct('paypal', $shop, true, true);
    }

    /**
     * Is this mode instant payment ?
     */
    public function is_instant_payment() {
        return true;
    }

    /**
     * Prints a payment porlet in an order form.
     *
     * @param objectref &$shoppingcart a data stub that contains required information for the portlet raster
     */
    public function print_payment_portlet(&$shoppingcart) {
        global $CFG, $OUTPUT;

        $config = get_config('local_shop');

        $template = new StdClass;

        if ($shoppingcart->usedistinctinvoiceinfo) {
            $paymentinfo = $shoppingcart->invoicinginfo;
        } else {
            $paymentinfo = $shoppingcart->customerinfo;
        }

        if (empty($config->test)) {
            $paypalsupportedlangs = [
                'AU', 'AT', 'BE', 'BR', 'CA', 'CH', 'CN', 'DE', 'ES', 'GB',
                'FR', 'IT', 'NL', 'PL', 'PT', 'RU', 'US',
            ];
        } else {
            $paypalsupportedlangs = ['US'];
        }

        $template->istesting = $config->test;
        if ($template->istesting) {
            $template->sellername = $config->paypalsellertestname;
            $template->selleritemname = $config->paypalsellertestitemname;
        } else {
            $template->sellername = $config->paypalsellername;
            $template->selleritemname = $config->paypalselleritemname;
        }
        $template->paypalacceptedstr = get_string('paypalaccepted', 'shoppaymodes_paypal');
        $template->shopname = $this->theshop->name;
        $template->shopid = $this->theshop->id;
        $template->testmodestr = get_string('testmode', 'local_shop');
        $template->amount = sprintf('%.2F', $shoppingcart->finalshippedtaxedtotal);
        $template->firstname = $paymentinfo['firstname'];
        $template->lastname = $paymentinfo['lastname'];
        $template->userstr = get_string('user');
        $template->customername = $paymentinfo['lastname'].' '.$paymentinfo['firstname'];
        $template->address = $paymentinfo['address'];
        $template->city = $paymentinfo['city'];
        $template->country = (!empty($paymentinfo['country'])) ? $paymentinfo['country'] : $CFG->country;
        $template->zip = $paymentinfo['zip'];
        $template->email = $shoppingcart->customerinfo['email']; // Invoicing info has no mail.
        $template->transid = $shoppingcart->transid; // No need special format for online transaction id here.
        $template->shipping = @$shoppingcart->shipping;
        if ($template->istesting) {
            $template->currency = 'USD';
        } else {
            $template->currency = $this->theshop->get_currency();
        }
        $params = ['shopid' => $this->theshop->id, 'transid' => $shoppingcart->transid];
        $template->returnurl = new moodle_url('/local/shop/paymodes/paypal/process.php', $params);
        $template->notifyurl = new moodle_url('/local/shop/paymodes/paypal/paypal_ipn.php');
        $template->cancelurl = new moodle_url('/local/shop/paymodes/paypal/cancel.php', $params);

        if ($template->istesting) {
            if (!empty($config->htaccesscred)) {
                $template->returnurl = preg_replace('#^(https?//\\:)#', '$1'.$config->htaccesscred.'@', $template->returnurl);
                $template->notifyurl = preg_replace('#^(https?//\\:)#', '$1'.$config->htaccesscred.'@', $template->notifyurl);
                $template->cancelurl = preg_replace('#^(https?//\\:)#', '$1'.$config->htaccesscred.'@', $template->cancelurl);
            }
        }

        $template->paypallogourl = new moodle_url('/local/shop/paymodes/paypal/pix/logo_paypal_106x29.png');
        $template->lang = strtoupper(current_language());
        if (!in_array($template->lang, $paypalsupportedlangs)) {
            $template->lang = 'US';
        }
        $template->paypalmsg = get_string('paypalmsg', 'shoppaymodes_paypal');

        $template->cancelstr = get_string('cancel');
        $params = ['step' => 'shop', 'id' => $this->theshop->id];
        $template->cancelurl = new moodle_url('/local/shop/front/view.php', $params);

        echo $OUTPUT->render_from_template('shoppaymodes_paypal/paypalbutton', $template);
    }

    /**
     * prints a payment porlet in an order form
     * @param Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_paypal', '');
    }

    /**
     * Print when payment is complete.
     */
    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', [], 'local_shop');
    }

    /**
     * Cancels the order and return to shop
     */
    public function cancel() {

        $transid = required_param('transid', PARAM_RAW);

        $afullbill = Bill::get_by_transaction($transid);
        $afullbill->onlinetransactionid = $transid;
        $afullbill->paymode = 'paypal';
        $afullbill->status = SHOP_BILL_CANCELLED;
        $afullbill->save(true);
        shop_trace('Paypal Interactive Cancellation');

        // Do not cancel shopping cart. User may need another payment method.

        $params = ['view' => 'shop', 'shopid' => $this->theshop->id];
        redirect(new moodle_url('/local/shop/front/view.php', $params));
    }

    /**
     * Processes interactively an order payment request.
     * In thje Paypal process, the payment processing only can be performed
     * waiting for an IPN call that needs answer back to Paypal and acknowledge (VERIFIED)
     */
    public function process() {
        shop_trace('Paypal Return Controller');

        $transid = required_param('transid', PARAM_RAW);

        $afullbill = Bill::get_by_transaction($transid);

        $this->theshop = $afullbill->theshop;

        /*
         * bill could already be SOLDOUT by IPN    so do nothing
         * process it only if needind to process.
         */
        if ($afullbill->status == SHOP_BILL_PLACED) {
            // Bill has not yet been soldout nor produced by an IPN notification.
            $afullbill->status = SHOP_BILL_PENDING;
            $afullbill->save(true);

            shop_trace("[$transid] Paypal Return Controller Complete : Redirecting");
            $params = ['view' => 'produce', 'shopid' => $this->theshop->id, 'transid' => $transid];
            redirect(new moodle_url('/local/shop/front/view.php', $params));
        }
    }

    /**
     * Processes a payment asynchronous confirmation.
     */
    public function process_ipn() {
        global $CFG, $DB, $SESSION, $OUTPUT, $PAGE;

        $simulating = optional_param('simulating', '', PARAM_BOOL);

        // Get all input parms.
        $transid = required_param('invoice', PARAM_TEXT);
        // Get the shopid. Not sure its needed any more.
        $custom = required_param('custom', PARAM_TEXT);

        if (is_scalar($custom)) {
            $shopid = $custom;
        } else {
            list($shopid) = $custom;
        }

        if ($simulating) {
            $PAGE->set_url(new moodle_url('/local/shop/paymodes/paypal/paypal_ipn.php'));
            $PAGE->set_title(get_string('paypalipnsimulation', 'shoppaymodes_paypal'));
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string('paypalipnsimulation', 'shoppaymodes_paypal'), 2);
            echo '<pre>';
        }

        if (empty($transid)) {
            if ($simulating) {
                mtrace("[ERROR] Paypal IPN : Empty Transaction ID");
            }
            shop_trace("[ERROR] Paypal IPN : Empty Transaction ID");
            die;
        }

        if (!$afullbill = Bill::get_by_transaction($transid)) {
            if ($simulating) {
                mtrace("[$transid] Paypal IPN ERROR : No such order");
            }
            shop_trace("[$transid] Paypal IPN ERROR : No such order");
            die;
        }

        // Integrity check : the bill must belong to the shop wich is returned as info in custom Paypal data.
        if ($afullbill->shopid != $shopid) {
            if ($simulating) {
                mtrace("[$transid] Paypal IPN ERROR : Paypal returned info ($shopid) do not match the bill's shop ({$afullbill->shopid}).");
            }
            shop_trace("[$transid] Paypal IPN ERROR : Paypal returned info ($shopid) do not match the bill's shop ({$afullbill->shopid}).");
            die;
        }

        // Pass reference from bill.
        $this->theshop = $afullbill->theshop;

        $txnid = required_param('txn_id', PARAM_TEXT);
        $data = new StdClass;
        $validationquery = 'cmd=_notify-validate';
        $querystring = '';

        shop_trace("[$transid] Paypal IPN : paypal txn : $txnid");
        shop_trace("[$transid] Paypal IPN : paypal trans : $transid");
        if ($simulating) {
            mtrace("[$transid] Paypal IPN : paypal txn : $txnid");
            mtrace("[$transid] Paypal IPN : paypal trans : $transid");
        }

        foreach ($_POST as $key => $value) {
            $value = stripslashes($value);
            $querystring .= "&$key=".urlencode($value);
            $value = clean_param($value, PARAM_TEXT);
            $data->$key = $value;
            if ($simulating) {
                mtrace("[$transid] Paypal IPN : paypal $key : ".$value);
            }
            shop_trace("[$transid] Paypal IPN : paypal $key : ".$value);
        }

        $validationquery .= $querystring;
        // Control for replicated notifications (normal operations).
        if (empty($this->_config->test) && $DB->record_exists('local_shop_paypal_ipn', ['txnid' => $txnid])) {
            if ($simulating) {
                mtrace("[$transid] Paypal IPN : paypal event collision on $txnid");
            }
            shop_trace("[$transid] Paypal IPN : paypal event collision on $txnid");
            shop_email_paypal_error_to_admin("Paypal IPN : Transaction $txnid is being repeated.", $data);
            die;
        } else {
            $paypalipn = new Stdclass;
            $paypalipn->txnid = $txnid;
            $paypalipn->transid = $transid;
            $paypalipn->paypalinfo = $querystring;
            $paypalipn->result = '';
            $paypalipn->timecreated = time();
            if ($simulating) {
                mtrace("[$transid] Paypal IPN : Recording paypal event");
            }
            shop_trace("[$transid] Paypal IPN : Recording paypal event");
            try {
                $DB->insert_record('local_shop_paypal_ipn', $paypalipn);
            } catch (Exception $e) {
                if ($simulating) {
                    mtrace("[$transid] Paypal IPN : Recording paypal event error ".$DB->get_last_error());
                }
                shop_trace("[$transid] Paypal IPN : Recording paypal event error ".$DB->get_last_error());
            }
        }

        /*
         * Warning : Paypal Sandbox may NOT activate any IPN back call.
         * See further faking answer solution for testing paypal.
         */
        if (empty($this->_config->test)) {
            $paypalurl = 'https://www.paypal.com/cgi-bin/webscr';
        } else {
            $paypalurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }

        // Fetch the file on the consumer side and store it here through a CURL call.
        // If simulating, we are confirming a purchase for test. No need to validate again.
        if (!$data->simulating) {
            $ch = curl_init("{$paypalurl}?$validationquery");
            shop_trace("[$transid] Paypal IPN : sending validation request: "."{$paypalurl}?$validationquery");
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, false);
            if (!empty($this->_config->test)) {
                curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-IPN-Moodle-Shop-Test-Agent');
            } else {
                curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-IPN-Moodle-Shop');
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: text/xml charset=UTF-8"]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $rawresponse = curl_exec($ch);

            if (empty($rawresponse)) {
                shop_trace('[$transid] Paypal IPN ERROR : VERIFICATION RESPONSE ERROR');
                die;
            }

            shop_trace("[$transid] Paypal IPN : Raw Response : {$rawresponse}.");
            if ($rawresponse != 'VERIFIED') {
                shop_trace('[$transid] Paypal IPN ERROR : VERIFICATION ERROR');
                die;
            }

            // At this point the IPN is a true return from PAYPAL AND is VERIFIED.

            $DB->set_field('local_shop_paypal_ipn', 'result', 'VERIFIED', ['txnid' => $txnid]);
            shop_trace("[$transid] Paypal IPN : Recording VERIFIED STATE on ".$txnid);

            if ($data->payment_status != "Completed" && $data->payment_status != "Pending") {
                $error = "Paypal IPN : Status not completed nor pending. Check transaction with customer.";
                shop_email_paypal_error_to_admin($error, $data);
                shop_trace("[$transid] Paypal IPN : Status not completed nor pending. Check transaction with customer.");
                die;
            }
        }

        if ($data->simulating && empty($this->_config->test)) {
            mtrace("[$transid] Paypal IPN : Payment simulation only possible in test mode.");
            shop_trace("[$transid] Paypal IPN : Payment simulation only possible in test mode.");
            die;
        }

        if ($data->payment_status == "Pending") {
            $error = "[$transid] Paypal IPN : Status is pending. Check transaction state in a while.";
            if ($simulating) {
                mtrace($error);
            }
            shop_trace($error);
            die;
        }

        $prod = $this->_config->paypalsellername;
        $test = $this->_config->paypalsellertestname;
        $sellerexpectedname = (empty($this->_config->test)) ? $prod : $test;
        if ($data->receiver_id != $sellerexpectedname) {   // Check that the business account is the one we want it to be.
            $error = "[$transid] Paypal IPN : Business email is {$data->receiver_id} (not {$this->_config->paypalsellername})";
            shop_email_paypal_error_to_admin($error, $data);
            if ($simulating) {
                mtrace($error);
            }
            shop_trace($error);
            unset($SESSION->shoppingcart);
            die;
        }

        // At this point, we have a payment confirmation.

        /*
         * Bill has not yet been soldout through an IPN notification
         * sold it out and update both DB and memory record
         */
        if ($afullbill->status != SHOP_BILL_SOLDOUT) {
            // Stores the back code of paypal.
            $tx = required_param('invoice', PARAM_TEXT);
            $afullbill->onlinetransactionid = $tx;
            $afullbill->paymode = 'paypal';
            $afullbill->status = SHOP_BILL_SOLDOUT;
            $afullbill->paymentfee = 0 + @$data->mc_fee;
            $afullbill->save(true);

            shop_trace("[$transid] Paypal IPN Start Production");
            if ($simulating) {
                mtrace("[$transid] Paypal IPN Start Production");
            }
            // Perform final production.
            $action = 'produce';
            include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
            $theblock = null;
            $controller = new \local_shop\front\production_controller($afullbill->theshop, $afullbill->thecatalogue, $theblock, $afullbill, true, $simulating);
            $controller->process($action);
            if ($simulating) {
                mtrace("[{$transid}] Paypal IPN End Production");
            }
            shop_trace("[{$transid}] Paypal IPN End Production");
        }

        if ($simulating) {
            mtrace("[$transid] Paypal IPN : End of transaction");
        }
        shop_trace("[$transid] Paypal IPN : End of transaction");
    }

    /**
     * provides global settings to add to shop settings when installed
     * @param objectref &$settings
     */
    public function settings(&$settings) {

        $label = get_string($this->name.'paymodeparams', 'shoppaymodes_paypal', $this->name);
        $info = get_string('paypaltest_desc', 'shoppaymodes_paypal');
        $settings->add(new admin_setting_heading('local_shop_'.$this->name, $label, $info));

        $label = get_string('paypalsellertestname', 'shoppaymodes_paypal');
        $settings->add(new admin_setting_configtext('local_shop/paypalsellertestname', $label,
                           get_string('configpaypalsellername', 'shoppaymodes_paypal'), '', PARAM_TEXT));

        $label = get_string('sellertestitemname', 'shoppaymodes_paypal');
        $settings->add(new admin_setting_configtext('local_shop/paypalsellertestitemname', $label,
                           get_string('configselleritemname', 'shoppaymodes_paypal'), '', PARAM_TEXT));

        $label = get_string('paypalsellername', 'shoppaymodes_paypal');
        $settings->add(new admin_setting_configtext('local_shop/paypalsellername', $label,
                           get_string('configpaypalsellername', 'shoppaymodes_paypal'), '', PARAM_TEXT));

        $label = get_string('selleritemname', 'shoppaymodes_paypal');
        $settings->add(new admin_setting_configtext('local_shop/paypalselleritemname', $label,
                           get_string('configselleritemname', 'shoppaymodes_paypal'), '', PARAM_TEXT));
    }
}
