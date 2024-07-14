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
 * Main paymode method class
 *
 * @package    shoppaymodes_check
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
 * Pay by check
 */
class shop_paymode_check extends shop_paymode {

    /**
     * Constructor
     * @param Shop $shop
     */
    public function __construct(?Shop $shop) {
        parent::__construct('check', $shop);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param objectref &$shoppingcart
     */
    public function print_payment_portlet(&$shoppingcart) {

        $afullbill = Bill::get_by_transaction($shoppingcart->transid);
        $afullbill->status = SHOP_BILL_PENDING;
        $afullbill->save(true);

        $proc = 1;
        echo '<p>' . shop_compile_mail_template('pay_instructions', [], 'shoppaymodes_check');
        echo '<blockquote>';

        $params = ['view' => 'bill', 'id' => $afullbill->theshop->id, 'transid' => $shoppingcart->transid];
        $billurl = new moodle_url('/local/shop/front/view.php', $params);

        $params = [
            'PROC_ORDER' => $proc++,
            'BILL_URL' => $billurl,
        ];
        echo shop_compile_mail_template('print_procedure_text', $params, 'shoppaymodes_check');
        $params = [
            'SELLER' => $this->_config->sellername,
            'ADDRESS' => $this->_config->selleraddress,
            'ZIP' => $this->_config->sellerzip,
            'CITY' => $this->_config->sellercity,
            'COUNTRY' => strtoupper($this->_config->sellercountry),
            'BILL_URL' => $billurl,
            'PROC_ORDER' => $proc++,
        ];
        echo shop_compile_mail_template('procedure_text', $params, 'shoppaymodes_check');
        echo '</blockquote>';
    }

    /**
     * Prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {
        $proc = 1;
        echo '<p>' . shop_compile_mail_template('pay_instructions_invoice', [], 'shoppaymodes_check');
        echo '<blockquote>';
        $params = ['view' => 'bill', 'id' => $afullbill->shop->id, 'transid' =>$billdata->transid];
        $billurl = new moodle_url('/local/shop/front/view.php', $params);
        $params = [
            'PROC_ORDER' => $proc++,
            'BILL_URL' => $billurl,
        ];
        echo shop_compile_mail_template('print_procedure_text_invoice', $params, 'shoppaymodes_check');
        $params = [
            'SELLER' => $this->_config->sellername,
            'ADDRESS' => $this->_config->selleraddress,
            'ZIP' => $this->_config->sellerzip,
            'CITY' => $this->_config->sellercity,
            'COUNTRY' => strtoupper($this->_config->sellercountry),
            'BILL_URL' => $billurl,
            'PROC_ORDER' => $proc++,
        ];
        echo shop_compile_mail_template('procedure_text_invoice', $params, 'shoppaymodes_check');
        echo '</blockquote>';
    }

    /**
     * Print when payment completed
     */
    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', [], 'local_shop');
    }

    /**
     * Processes a payment return.
     */
    public function process() {
        assert(true);
    }

    /**
     * Processes a payment asynchronoous confirmation.
     */
    public function process_ipn() {
        // No IPN for offline payment.
        assert(true);
    }

    /**
     * Provides global settings to add to shop settings when installed.
     */
    public function settings(&$settings) {
        assert(true);
    }
}
