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
 * @package    shoppaymodes_test
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;
use local_shop\Shop;

/**
 * A generic class for making payment tests
 * not enabled in production.
 */
class shop_paymode_test extends shop_paymode {

    /**
     * Constructor
     * @param Shop $theshop
     */
    public function __construct(?Shop $theshop) {
        parent::__construct('test', $theshop, true, true);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param objectref &$shoppingcart
     * @tot : turn into template
     */
    public function print_payment_portlet(&$shoppingcart) {

        $shopurl = new moodle_url('/local/shop/front/view.php');
        $ipnurl = new moodle_url('/local/shop/paymodes/test/test_ipn.php');

        echo '<table cellspacing="30">';
        echo '<tr><td colspan="4" align="left">';
        echo get_string('testadvice', 'shoppaymodes_test');
        echo '</td></tr>';
        echo '<tr>';

        // This is interactive payment triggering immediately successfull payment.
        echo '<td>';
        echo '<form name="paymentform" action="'.$shopurl.'" >';
        echo '<input type="hidden" name="shopid" value="'.$this->theshop->id.'">';
        echo '<input type="hidden" name="transid" value="'.$shoppingcart->transid.'" />';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        echo '<input type="hidden" name="view" value="payment" />';
        echo '<input type="hidden" name="what" value="navigate" />';
        echo '<input type="submit" name="pay" value="'.get_string('interactivepay', 'shoppaymodes_test').'">';
        echo '</form>';
        echo '</td>';

        /*
         * this stands for delayed payment, as check or bank wired transfer, needing backoffice
         * post check to activate production.
         */
        echo '<td>';
        echo '<form name="paymentform" action="'.$shopurl.'" target="_blank">';
        echo '<input type="hidden" name="shopid" value="'.$this->theshop->id.'">';
        echo '<input type="hidden" name="transid" value="'.$shoppingcart->transid.'" />';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        echo '<input type="hidden" name="what" value="navigate" />';
        echo '<input type="hidden" name="view" value="payment" />';
        echo '<input type="hidden" name="delayed" value="1" />';
        echo '<input type="submit" name="pay" value="'.get_string('paydelayedforipn', 'shoppaymodes_test').'">';
        echo '</form>';
        echo '</td>';

        // In IPN Payemnt (delayed return from payment peer) we may have no track of shopid.
        echo '<td>';
        echo '<form name="paymentform" action="'.$ipnurl.'" target="_blank" >';
        echo '<input type="hidden" name="transid" value="'.$shoppingcart->transid.'" />';
        echo '<input type="submit" name="pay" value="'.get_string('ipnpay', 'shoppaymodes_test').'" />';
        echo '</form>';
        echo '</td>';

        echo '<td>';
        echo '<form name="paymentform" action="'.$ipnurl.'" target="_blank" >';
        echo '<input type="hidden" name="transid" value="'.$shoppingcart->transid.'" />';
        echo '<input type="hidden" name="finish" value="1" />';
        echo '<input type="submit" name="pay" value="'.get_string('ipnpayandclose', 'shoppaymodes_test').'" />';
        echo '</form>';
        echo '</td>';

        echo '</tr>';
        echo '</table>';
    }

    /**
     * Prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(Bill $billdata = null) {
        assert(true);
    }

    /**
     * Print when payment is completed
     */
    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', [], 'local_shop');
    }

    /**
     * Processes a payment return. this is a special case that admits being triggered
     * by a pre-existing Bill.
     * @param Bill ref &$billdata the processed bill. Bill attributes may change
     */
    public function process(?Bill $billdata = null) {
        global $OUTPUT;

        if (!$billdata) {
            $delayed = optional_param('delayed', 0, PARAM_BOOL);
            $transid = required_param('transid', PARAM_TEXT);
        } else {
            $transid = $billdata->transactionid;
            $delayed = @$afullbill->delayed;
        }
        shop_trace("[$transid]  Test Processing : paying");

        try {
            $billdata = Bill::get_by_transaction($transid);

            if ($delayed) {
                $billdata->status = 'PENDING';
                $billdata->save(true);
                shop_trace("[$transid]  Test Interactive : Payment Success but waiting IPN for processing");
                return false; // Has not yet payed.
            } else {
                $billdata->status = 'SOLDOUT';
                $billdata->save(true);
                shop_trace("[$transid]  Test Interactive : Payment Success");
                return true; // Has payed.
            }
        } catch (Exception $e) {
            shop_trace("[$transid]  Test Interactive : Transaction ID Error");
            echo $OUTPUT->notification(get_string('ipnerror', 'shoppaymodes_test'), 'error');
        }
    }

    /**
     * Is this method capable of instant payment ?
     */
    public function is_instant_payment() {
        return true;
    }

    /**
     * Processes a payment asynchronous confirmation.
     */
    public function process_ipn() {
        global $CFG, $OUTPUT;

        $transid = required_param('transid', PARAM_TEXT);
        $close = optional_param('finish', false, PARAM_BOOL);

        shop_trace("[$transid]  Test IPN : examinating");
        mtrace("[$transid]  Test IPN : examinating");

        try {
            mtrace("[$transid]  Testing IPN production ");
            $afullbill = Bill::get_by_transaction($transid);

            $ipncall = true;
            $cmd = 'produce';
            include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
            $nullblock = null;
            $controller = new \local_shop\front\production_controller($afullbill->theshop, $afullbill->thecatalogue, $nullblock,
                        $afullbill, $ipncall, true);
            $controller->process($cmd);

            mtrace("[$transid]  Test IPN : Payment Success, transferring to production controller");
            shop_trace("[$transid]  Test IPN : Payment Success, transferring to production controller");

            $afullbill->status = 'SOLDOUT';
            $afullbill->save(true);

            // Lauch production from a SOLDOUT state.
            $controller->process('produce', !$close);

            die;
        } catch (Exception $e) {
            shop_trace("[$transid]  Test IPN : Transaction ID Error");
            mtrace("[$transid]  ".$OUTPUT->notification(get_string('ipnerror', 'shoppaymodes_test'), 'error'));
        }
    }

    /**
     * Provides global settings to add to shop settings when installed.
     * @param objectref &$settings
     */
    public function settings(&$settings) {
        return false;
    }
}
