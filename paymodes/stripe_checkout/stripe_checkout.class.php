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
 * Main payumode class
 *
 * @package    shoppaymodes_stripe_checkout
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/paymodes/stripe_checkout/extralib/stripe-php/init.php');

use local_shop\Shop;
use local_shop\Bill;

/**
 * A class to pay using a stripe_checkout broker
 */
class shop_paymode_stripe_checkout extends shop_paymode {

    /**
     * Constructor
     * @param Shop $shop
     */
    public function __construct(?Shop $shop) {
        // To enable stripe_checkout in your installation, change second param to "true".
        parent::__construct('stripe_checkout', $shop, true, true);
    }

    /**
     * Is this paymode capable of instant payment ?
     */
    public function is_instant_payment() {
        return true;
    }

    /**
     * prints a payment porlet in an order form
     * @param objectref &$shoppingcart
     */
    public function print_payment_portlet(&$shoppingcart) {
        global $OUTPUT, $PAGE;

        if ($shoppingcart->usedistinctinvoiceinfo) {
            $paymentinfo = $shoppingcart->invoicinginfo;
        } else {
            $paymentinfo = $shoppingcart->customerinfo;
        }

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        if ($this->_config->test) {
            $private = $this->_config->stripe_checkout_testsecret;
            $public = $this->_config->stripe_checkout_testsid;
        } else {
            $private = $this->_config->stripe_checkout_secret;
            $public = $this->_config->stripe_checkout_sid;
        }
        \Stripe\Stripe::setApiKey($private);

        $successurl = new moodle_url('/local/shop/paymodes/stripe_checkout/accept.php', ['transid' => $shoppingcart->transid]);
        $cancelurl = new moodle_url('/local/shop/paymodes/stripe_checkout/cancel.php', ['transid' => $shoppingcart->transid]);

        $shoppingcartlines = $this->convert_lines($shoppingcart);

        $sessiondata = [
          'customer_email' => $shoppingcart->customerinfo['email'],
          'client_reference_id' => $shoppingcart->transid,
          'payment_method_types' => ['card'],
          'line_items' => [$shoppingcartlines],
          'success_url' => $successurl,
          'cancel_url' => $cancelurl,
        ];
        $session = \Stripe\Checkout\Session::create($sessiondata);

        $params = ["{\"pk\":\"{$public}\", \"sid\":\"{$session->id}\"}"];
        $PAGE->requires->js_call_amd('shoppaymodes_stripe_checkout/stripe_checkout', 'init', $params);

        $template = new StdClass();
        if ($session) {
            $template->sid = $session->id;
            $template->info = $paymentinfo;

            // Records payment intent id in session.
            $shoppingcart->stripe = new StdClass();
            $shoppingcart->onlinetransactionid = $session->payment_intent;
        }

        echo $OUTPUT->render_from_template('shoppaymodes_stripe_checkout/portlet', $template);
    }

    /**
     * prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_stripe_checkout');
    }

    /**
     * Print when payment is complete
     */
    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', [], 'local_shop');
    }

    /**
     * Processes an interactive payment return.
     *
     * The process() handler will pass the bill from PLACED to PENDING state, unless
     * it has asynchronously already be processed by an IPN return.
     */
    public function process() {
        global $SESSION;

        shop_trace('Stripe Interactive FullFill Controller');
        // There is no handling of interactive action.

        $transid = required_param('transid', PARAM_TEXT);
        $afullbill = Bill::get_by_transaction($transid);

        if ($afullbill->status == SHOP_BILL_PLACED) {
            $mess = "Stripe WebHooks have failed to produce your order. ";
            $mess .= "This may be due to a misconfiguration of the moodle shop Stripe Payment method.";
            throw new moodle_exception($mess);
            die;
        }

        if ($afullbill->status == SHOP_BILL_SOLDOUT) {
            // Usually should not. Webhooks should have produced at this stage.
            if (empty($this->_config->test)) {
                $params = ['view' => 'produce', 'shopid' => $this->theshop->id, 'what' => 'produce', 'transid' => $transid];
                $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
                redirect($redirecturl);
            } else {
                $params = ['view' => 'produce', 'id' => $this->theshop->id, 'what' => 'produce', 'transid' => $transid];
                $continueurl = new moodle_url('/local/shop/front/view.php', $params);
                echo $OUTPUT->continue_button($continueurl, get_string('continueaftersoldout', 'shoppaymodes_mercanet'));
            }
        }
        if ($afullbill->status == SHOP_BILL_COMPLETE) {
            // All is done already. clear everything.
            unset($SESSION->shoppingcart);
            if (empty($this->_config->test)) {
                $params = [
                    'view' => $this->theshop->get_starting_step(),
                    'id' => $this->theshop->id,
                    'what' => 'produce',
                    'transid' => $transid,
                ];
                $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
                redirect($redirecturl);
            } else {
                $params = [
                    'view' => $this->theshop->get_starting_step(),
                    'id' => $this->theshop->id,
                    'what' => 'produce',
                    'transid' => $transid,
                ];
                $continueurl = new moodle_url('/local/shop/front/view.php', $params);
                echo $OUTPUT->continue_button($continueurl, get_string('continueaftersoldout', 'shoppaymodes_mercanet'));
            }
        }
    }

    /**
     * Comply with abstract definition.
     */
    public function process_ipn() {
        $this->process_webhook();
    }

    /**
     * processes a payment asynchronoous confirmation.
     *
     * Note that this function is not interactive and should not care
     * about session handling even when cancelling : it has NO user session
     * in the context.
     */
    public function process_webhook() {
        global $CFG, $DB;

        shop_trace('Stripe WebHook Controller');

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        if ($this->_config->test) {
            \Stripe\Stripe::setApiKey($this->_config->stripe_checkout_testsid);
            $endpoint_secret = $this->_config->stripe_checkout_testsecret;
        } else {
            \Stripe\Stripe::setApiKey($this->_config->stripe_checkout_sid);
            $endpoint_secret = $this->_config->stripe_checkout_secret;
        }

        // You can find your endpoint's secret in your webhook settings

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload.
            shop_trace('[ERROR] Stripe/Checkout Unexpected Value');
            http_response_code(400);
            exit();
        } catch(\Stripe\Error\SignatureVerification $e) {
            // Invalid signature.
            shop_trace('[ERROR] Stripe/Checkout Signature Verification Error');
            http_response_code(400);
            exit();
        }

        // Handle the checkout.session.completed event.
        if (($event->type == 'checkout.session.completed') || ($event->type == 'payment_intent.succeeded')) {

            if ($event->type == 'checkout.session.completed') {
                $session = $event->data->object;
                $transid = $session->client_reference_id;
            } else {
                // payment_intent.succeeded
                $payment = $event->data->object;
                $transid = $DB->get_field('local_shop_bill', 'transactionid', ['onlinetransactionid' => $payment->id]);
            }

            if (!$afullbill = \local_shop\Bill::get_by_transaction($transid)) {
                // Not matched any internal bill.
                shop_trace("[$transid] Stripe/Checkout WebHook ERROR : No such order");
                http_response_code(400);
                exit();
            }

            if ($afullbill->status != SHOP_BILL_SOLDOUT) {
                /*
                 * Bill has not yet been soldout through an IPN notification
                 * sold it out and update both DB and memory record
                 */

                // Stores the back code of Stripe.
                if ($afullbill->onlinetransactionid != $session->payment_intent) {
                    // Bill exists, but does not match the refering session/payment_intent
                    // This is a probable faulty situation.
                    $mess = "[$transid] Stripe/Checkout WebHook ERROR : Mismatched bill with session, keyed by payment_intent ID";
                    shop_trace($mess);
                    http_response_code(400);
                    exit();
                }
                $afullbill->paymode = 'strip_checkout';
                $afullbill->status = SHOP_BILL_SOLDOUT;
                $afullbill->remotestatus = 'checkout.session.completed';
                $afullbill->save(true);

                $message = "[{$afullbill->transactionid}] Stripe/Checkout WebHook Start Production";
                shop_trace($message);

                // Perform final production.
                $action = 'produce';
                include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
                $controller = new \local_shop\front\production_controller($afullbill->theshop, $afullbill->thecatalogue,
                            null, $afullbill, true, false);
                $controller->process($action);
                shop_trace("[{$afullbill->transactionid}] Stripe/Checkout WebHook End Production");
            }

            shop_trace("[{$afullbill->transactionid}] Stripe/Checkout WebHook : End of transaction");
            if (!empty($this->_config->test)) {
                // Verbose it to output in test mode.
                mtrace('Stripe/checkout WebHook : End of transaction');
            }
            http_response_code(200);
            exit();
        }

        if ($event->type == 'payment_intent.payment_failed') {

            $payment = $event->data->object;
            $transid = $DB->get_field('local_shop_bill', 'transactionid', ['onlinetransactionid' => $payment->id]);

            if (!$afullbill = \local_shop\Bill::get_by_transaction($transid)) {
                // Not matched any internal bill.
                shop_trace("[$transid] Stripe/Checkout WebHook ERROR : No such order");
                http_response_code(400);
                exit();
            }

            $afullbill->status = SHOP_BILL_REFUSED;
            $afullbill->save(true);
            shop_trace($tracereport);
            http_response_code(200);
            exit();
        }
    }

    /**
     * Cancels the order and return to shop.
     */
    public function cancel() {

        if ($transid = required_param('transid', PARAM_TEXT)) {
            shop_trace('[$transid] Stripe Checkout Payment Cancelled');

            $afullbill = Bill::get_by_transaction($transid);
            $afullbill->onlinetransactionid = $transid;
            $afullbill->paymode = 'ogone';
            $afullbill->status = SHOP_BILL_CANCELLED;
            $afullbill->save(true);

        } else {
            $error = '[ERROR] Stripe Checkout Data error on cancel. ';
            $error .= 'Cancelling at least the current session.';
            shop_trace($error);
        }

        // Do not cancel shopping cart. User may use another payment.

        $params = ['view' => $this->theshop->get_starting_step(), 'id' => $this->theshop->id];
        redirect(new moodle_url('/local/shop/front/view.php', $params));
    }

    /**
     * Provides global settings to add to shop settings when installed.
     * @param objectref &$settings
     */
    public function settings(&$settings) {

        $label = get_string($this->name.'paymodeparams', 'shoppaymodes_stripe_checkout');
        $settings->add(new admin_setting_heading('local_shop_'.$this->name, $label, ''));

        $key = 'local_shop/stripe_checkout_sid';
        $label = get_string('sid', 'shoppaymodes_stripe_checkout');
        $desc = get_string('configsid', 'shoppaymodes_stripe_checkout');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/stripe_checkout_secret';
        $label = get_string('secret', 'shoppaymodes_stripe_checkout');
        $desc = get_string('configsecret', 'shoppaymodes_stripe_checkout');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/stripe_checkout_testsid';
        $label = get_string('testsid', 'shoppaymodes_stripe_checkout');
        $desc = get_string('configtestsid', 'shoppaymodes_stripe_checkout');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/stripe_checkout_testsecret';
        $label = get_string('testsecret', 'shoppaymodes_stripe_checkout');
        $desc = get_string('configtestsecret', 'shoppaymodes_stripe_checkout');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

    }

    /**
     * convert lines for stripe.
     * @param objectref &$shoppingcart
     */
    protected function convert_lines(&$shoppingcart) {

        $context = shop_build_context();
        $theshop = $context[0];
        $thecatalog = $context[1];

        $lines = [];

        if (!empty($shoppingcart->order)) {
            foreach ($shoppingcart->order as $shortname => $q) {
                $ci = $thecatalog->get_product_by_shortname($shortname);
                $ciimageurl = $ci->get_image_url();
                $line = [
                    'name' => $ci->name,
                    'description' => $ci->description,
                    'images' => [$ciimageurl],
                    'amount' => round($ci->get_price($q) * 100),
                    'currency' => \core_text::strtolower($theshop->get_currency()),
                    'quantity' => $q,
                ];
                $lines[] = $line;
            }
        }

        return $lines;
    }
}
