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
 * Paymode implemetation class
 *
 * @package    shoppaymodes_ogone
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/paymodes/ogone/extlibs/extralibs.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Shop;
use local_shop\Bill;

define('OGONE_STATUS_INVALID', 0);
define('OGONE_STATUS_CUSTOMER_CANCELLED', 1);
define('OGONE_STATUS_REFUSED', 2);
define('OGONE_STATUS_AUTHORISED', 5);
define('OGONE_STATUS_AUTHORISED_PENDING', 51);
define('OGONE_STATUS_INCERTAIN', 52);
define('OGONE_STATUS_OFFLINE_CANCEL_PENDING', 61);
define('OGONE_STATUS_OFFLINE_CANCEL_INCERTAIN', 62);
define('OGONE_STATUS_OFFLINE_CANCEL_REFUSED', 63);
define('OGONE_STATUS_CANCELLED', 7);
define('OGONE_STATUS_CANCELLED_PENDING', 71);
define('OGONE_STATUS_PAYBACK', 8);
define('OGONE_STATUS_PAYBACK_PENDING', 81);
define('OGONE_STATUS_PROCEEDED', 9);
define('OGONE_STATUS_PROCEEDING', 91);
define('OGONE_STATUS_PROCEEDING_INCERTAIN', 92);
define('OGONE_STATUS_PROCEEDING_REFUSED', 93);

/**
 * Pay using Ogone Ingenico API
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class shop_paymode_ogone extends shop_paymode {

    /**
     * Constructor
     * @param Shop $theshop
     */
    public function __construct(?Shop $shop) {
        // To enable ogone in your installation, change second param to "true".
        parent::__construct('ogone', $shop, true, true);
    }

    /**
     * Is this mode instant payment ?
     */
    public function is_instant_payment() {
        return true;
    }

    /**
     * prints a payment porlet in an order form
     */
    public function print_payment_portlet() {
        global $CFG, $SESSION;

        $shoppingcart = $SESSION->shoppingcart;

        $LANGS = [
            'en' => 'en_US',
            'fr' => 'fr_FR',
            'sp' => 'sp_SP',
            'de' => 'de_DE',
            'it' => 'it_IT',
        ];

        if ($shoppingcart->usedistinctinvoiceinfo) {
            $paymentinfo = $shoppingcart->invoicinginfo;
        } else {
            $paymentinfo = $shoppingcart->customerinfo;
        }

        $mode = ($this->_config->test) ? 'test' : 'prod';

        $accepturl = new moodle_url('/local/shop/paymodes/ogone/accept.php');
        $declineurl = new moodle_url('/local/shop/paymodes/ogone/decline.php');
        $exceptionurl = new moodle_url('/local/shop/paymodes/ogone/exception.php');
        $cancelurl = new moodle_url('/local/shop/paymodes/ogone/cancel.php');

        echo '<form method="post" action="https://secure.ogone.com/ncol/'.$mode.'/orderstandard_utf8.asp" id=form1 name=form1>';

        $commanddata = [
            'PSPID' => $this->_config->ogone_psid,
            'ORDERID' => $shoppingcart->transid,
            'AMOUNT' => $shoppingcart->finalshippedtaxedtotal * 100,
            'CURRENCY' => $this->theshop->get_currency(),
            'LANGUAGE' => $LANGS[current_language()],
            'CN' => $paymentinfo['lastname'].' '.$paymentinfo['firstname'],
            'EMAIL' => $paymentinfo['email'],
            'OWNERZIP' => $paymentinfo['zip'],
            'OWNERADDRESS' => $paymentinfo['address'],
            'OWNERCTY' => $paymentinfo['country'],
            'OWNERTOWN' => $paymentinfo['city'],
            'OWNERTELNO' => '',
        ];

        // General params.
        $signdata = [];
        foreach ($commanddata as $name => $value) {
            echo '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            if (!empty($value)) {
                $signdata[] = strtoupper($name)."=$value";
            }
        }

        ksort($signdata);
        $shabase = implode($this->_config->ogone_secret_in, $signdata).$this->_config->ogone_secret_in;
        $shavalue = sha1($shabase);

        echo '<input type="hidden" name="SHASIGN" value="'.$shavalue.'">';

        echo '<input type="hidden" name="TITLE" value="'.$this->theshop->name.'">';
        echo '<input type="hidden" name="BGCOLOR" value="">';
        echo '<input type="hidden" name="TXTCOLOR" value="">';
        echo '<input type="hidden" name="TBLBGCOLOR" value="">';
        echo '<input type="hidden" name="TBLTXTCOLOR" value="">';
        echo '<input type="hidden" name="BUTTONBGCOLOR" value="">';
        echo '<input type="hidden" name="BUTTONTXTCOLOR" value="">';
        echo '<input type="hidden" name="LOGO" value="'.$this->_config->ogone_logourl.'">';
        echo '<input type="hidden" name="FONTTYPE" value="">';

        echo '<input type="hidden" name="ACCEPTURL" value="'.$accepturl.'">';
        echo '<input type="hidden" name="DECLINEURL" value="'.$declineurl.'">';
        echo '<input type="hidden" name="EXCEPTIONURL" value="'.$exceptionurl.'">';
        // Rely on acceptance process for incertain states.
        echo '<input type="hidden" name="CANCELURL" value="'.$cancelurl.'">';
        echo '<input type="hidden" name="HOMEURL" value="'.$CFG->wwwroot.'">';
        echo '<input type="hidden" name="CATALOGURL" value="'.$this->theshop->url().'">';

        echo '<input type="submit" value="" id=submit2 name=submit2>';
        echo '</form> ';
    }

    /**
     * prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_paybox');
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
     * @todo : check if required_param is possible. Do we have moodle libs here ?
     */
    public function process() {

        shop_trace('Ogone/Ingenico Return Controller');

        if ($this->check_data_out()) {

            $transid = required_param('orderID', PARAM_RAW);

            if ($afullbill = Bill::get_by_transaction($transid)) {

                $this->theshop = $afullbill->theshop;

                /*
                 * bill could already be SOLDOUT by IPN    so do nothing
                 * process it only if needind to process.
                 */
                if ($afullbill->status == SHOP_BILL_PLACED) {
                    // Bill has not yet been soldout nor produced by an IPN notification.
                    $afullbill->status = SHOP_BILL_PENDING;
                    $afullbill->save(true);

                    shop_trace("[$transid] Ogone/Ingenico Return Controller Complete : Redirecting");
                    $params = [
                        'view' => $this->theshop->get_next_step('payment'),
                        'id' => $this->theshop->id,
                        'transid' => $transid,
                    ];
                    redirect(new moodle_url('/local/shop/front/view.php', $params));
                }
            } else {
                shop_trace('[$transid] Ogone/Ingenico Process : No such order');
            }
        } else {
            shop_trace('[ERROR] Ogone/Ingenico Return Data Failure');
        }
    }

    /**
     * processes a payment asynchronoous confirmation.
     *
     * Note that this function is not interactive and should not care
     * about session handling even when cancelling : it has NO user session
     * in the context.
     * @todo : check if required_param is possible. Do we have moodle libs here ?
     */
    public function process_ipn() {
        global $CFG;

        shop_trace('Ogone/Ingenico IPN Controller');

        $acceptancecodes = [OGONE_STATUS_PROCEEDED];
        $rejectcodes = [OGONE_STATUS_REFUSED, OGONE_STATUS_PROCEEDING_REFUSED];
        $cancellationcodes = [OGONE_STATUS_CANCELLED];

        /*
         * Possibly liberalize in the future
         * Add OGONE_STATUS_PROCEEDING tp acceptance codes.
         */

        if ($this->check_data_out()) {

            $transid = required_param('orderID', PARAM_RAW);
            // $transid = $_REQUEST['orderID'];

            // Get the bill from the response.
            if ($afullbill = Bill::get_by_transaction($transid)) {

                $status = required_param('STATUS', PARAM_TEXT);
                if (in_array($status, $acceptancecodes)) {
                    if ($afullbill->status != SHOP_BILL_SOLDOUT) {
                        /*
                         * Bill has not yet been soldout through an IPN notification
                         * sold it out and update both DB and memory record
                         */

                        // Stores the back code of ogone.
                        $afullbill->onlinetransactionid = required_param('PAYDID', PARAM_TEXT);
                        $afullbill->paymode = 'ogone';
                        $afullbill->status = SHOP_BILL_SOLDOUT;
                        $afullbill->remotestatus = $status;
                        $afullbill->save(true);

                        $message = "[{$afullbill->transactionid}] Ogone/Ingenico IPN Start Production";
                        shop_trace($message);

                        // Perform final production.
                        $action = 'produce';
                        include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
                        $controller = new \local_shop\front\production_controller($afullbill->theshop, $afullbill->thecatalogue, null, $afullbill, true, false);
                        $controller->process($action);
                        shop_trace("[{$afullbill->transactionid}] Ogone/Ingenico IPN End Production");
                    }
                } else if (in_array($status, $rejectcodes)) {
                    if ($afullbill->status != SHOP_BILL_REFUSED) {
                        shop_trace("[{$afullbill->transactionid}] Ogone/Ingenico IPN : Payment refused");
                        $afullbill->status = SHOP_BILL_REFUSED;
                        $afullbill->save(true);
                    }
                } else if (in_array($status, $cancellationcodes)) {
                    if ($afullbill->status != SHOP_BILL_CANCELLED) {
                        shop_trace("[{$afullbill->transactionid}] Ogone/Ingenico IPN : Customer cancelled");
                        $afullbill->status = SHOP_BILL_CANCELLED;
                        $afullbill->save(true);
                    }
                }

                // Finishing.
                shop_trace("[{$afullbill->transactionid}] Ogone/Ingenico IPN : End of transaction");
                if (!empty($this->_config->test)) {
                    // Verbose it to output in test mode
                    mtrace('Ogone/Ingenico IPN : End of transaction');
                }
            } else {
                shop_trace("[$transid] Ogone/Ingenico IPN ERROR : No such order");
            }
        } else {
            shop_trace('[ERROR] Ogone/Ingenico IPN Data Failure');
        }
    }

    /**
     * Cancels the order and return to shop.
     */
    public function cancel() {

        if ($transid = $this->check_data_out()) {
            shop_trace('[$transid] Ogone/Ingenico Payment Cancelled');

            $afullbill = Bill::get_by_transaction($transid);
            $afullbill->onlinetransactionid = $transid;
            $afullbill->paymode = 'ogone';
            $afullbill->status = SHOP_BILL_CANCELLED;
            $afullbill->save(true);

        } else {
            $error = '[ERROR] Ogone/Ingenico Data error on cancel. ';
            $error .= 'Cancelling at least the current session.';
            shop_trace($error);
        }

        // Do not cancel shopping cart. User may use another payment.

        $params = ['view' => $this->theshop->get_starting_step(), 'id' => $this->theshop->id];
        redirect(new moodle_url('/local/shop/front/view.php', $params));
    }

    /**
     * check data when outgoing.
     * @todo : check cleaning is available.
     */
    private function check_data_out() {

        $possiblefields = [
            'AAVADDRESS',
            'AAVCHECK',
            'AAVMAIL',
            'AAVNAME',
            'AAVPHONE',
            'AAVZIP',
            'ACCEPTANCE',
            'ALIAS',
            'AMOUNT',
            'BIC',
            'BIN',
            'BRAND',
            'CARDNO',
            'CCCTY',
            'CN',
            'COLLECTOR_BIC',
            'COLLECTOR_IBAN',
            'COMPLUS',
            'CREATION_STATUS',
            'CREDITDEBIT',
            'currency',
            'CVCCHECK',
            'DCC_COMMPERCENTAGE',
            'DCC_CONVAMOUNT',
            'DCC_CONVCCY',
            'DCC_EXCHRATE',
            'DCC_EXCHRATESOURCE',
            'DCC_EXCHRATETS',
            'DCC_INDICATOR',
            'DCC_MARGINPERCENTAGE',
            'DCC_VALIDHOURS',
            'DEVICEID',
            'DIGESTCARDNO',
            'ECI',
            'ED',
            'EMAIL',
            'ENCCARDNO',
            'FXAMOUNT',
            'FXCURRENCY',
            'IP',
            'IPCTY',
            'MANDATEID',
            'MOBILEMODE',
            'NBREMAILUSAGE',
            'NBRIPUSAGE',
            'NBRIPUSAGE_ALLTX',
            'NBRUSAGE',
            'NCERROR',
            'orderID',
            'PAYID',
            'PAYMENT_REFERENCE',
            'PM',
            'SCO_CATEGORY',
            'SCORING',
            'SEQUENCETYPE',
            'SIGNDATE',
            'STATUS',
            'SUBBRAND',
            'SUBSCRIPTION_ID',
            'TRXDATE',
            'VC',
        ];

        $checkdata = [];
        $request = shoppaymodes_ogone_x_get_request();
        foreach ($request as $key => $value) {
            if (in_array($possiblefields, $key) && !empty($value)) {
                $checkdata[] = strtoupper($key)."=".clean_param($value, PARAM_TEXT);
            }
        }

        if (empty($checkdata)) {
            return false;
        }

        ksort($checkdata);

        $shabase = implode($this->_config->ogone_secret_out, $checkdata).$this->_config->ogone_secret_out;
        $shaout = sha1($shabase);

        $shasign = optional_param('SHASIGN', '', PARAM_RAW);
        $transid = required_param('orderID', PARAM_TEXT);
        if (!empty($shasign) && $shasign == $shaout) {
            return $transid;
        }

        return false;
    }

    /**
     * Provides global settings to add to shop settings when installed.
     * @param object $settings
     */
    public function settings($settings) {

        $label = get_string($this->name.'paymodeparams', 'shoppaymodes_ogone');
        $settings->add(new admin_setting_heading('local_shop_'.$this->name, $label, ''));

        $key = 'local_shop/ogone_psid';
        $label = get_string('psid', 'shoppaymodes_ogone');
        $desc = get_string('configpsid', 'shoppaymodes_ogone');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/ogone_secret_in';
        $label = get_string('secretin', 'shoppaymodes_ogone');
        $desc = get_string('configsecretin', 'shoppaymodes_ogone');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/ogone_secret_out';
        $label = get_string('secretout', 'shoppaymodes_ogone');
        $desc = get_string('configsecretout', 'shoppaymodes_ogone');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/ogone_paramvar';
        $label = get_string('paramvar', 'shoppaymodes_ogone');
        $desc = get_string('configparamvar', 'shoppaymodes_ogone');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

        $key = 'local_shop/ogone_logourl';
        $label = get_string('logourl', 'shoppaymodes_ogone');
        $desc = get_string('configlogourl', 'shoppaymodes_ogone');
        $settings->add(new admin_setting_configtext($key, $label, $desc, ''));
    }
}
