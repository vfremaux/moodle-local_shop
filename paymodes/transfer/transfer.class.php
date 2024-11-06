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
 * @package    shoppaymodes_transfer
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Shop;
use local_shop\Bill;

/**
 * Pay by bank transfer. This is a non instant payment method that will
 * need operator check before agreeing production.
 */
class shop_paymode_transfer extends shop_paymode {

    /**
     * Constructor
     * @param Shop $theshop
     */
    public function __construct(?Shop $theshop) {
        parent::__construct('transfer', $theshop);
    }

    /**
     * Prints a payment portlet in an order form.
     */
    public function print_payment_portlet() {

        $proc = 1;

        $config = get_config('local_shop');

        echo '<p>' . shop_compile_mail_template('pay_instructions', [], 'shoppaymodes_transfer');
        $vars = [
            'SELLER' => $config->sellername,
            'ADDRESS' => $config->sellerbillingaddress,
            'ZIP' => $config->sellerbillingzip,
            'CITY' => $config->sellerbillingcity,
            'COUNTRY' => strtoupper($config->sellercountry),
            'BANKING' => $config->banking,
            'BANK_CODE' => $config->bankcode,
            'BANK_OFFICE' => $config->bankoffice,
            'BANK_ACCOUNT' => $config->bankaccount,
            'ACCOUNT_KEY' => $config->bankaccountkey,
            'IBAN' => $config->iban,
            'BIC' => $config->bic,
            'TVA_EUROPE' => $config->tvaeurope,
            'PROC_ORDER' => $proc++,
        ];
        echo shop_compile_mail_template('print_procedure_text', $vars, 'shoppaymodes_transfer');
    }

    /**
     * Prints a payment portlet in an invoice.
     * @param ?Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {

        $proc = 1;

        $config = get_config('local_shop');

        echo '<p>'.shop_compile_mail_template('pay_instructions_invoice', [], 'shoppaymodes_transfer');
        // We are swapping fields to avoid code duplicate trigger.
        $vars = [
            'SELLER' => $config->sellername,
            'ADDRESS' => $config->sellerbillingaddress,
            'CITY' => $config->sellerbillingcity,
            'ZIP' => $config->sellerbillingzip,
            'COUNTRY' => strtoupper($config->sellercountry),
            'BANKING' => $config->banking,
            'BANK_CODE' => $config->bankcode,
            'BANK_ACCOUNT' => $config->bankaccount,
            'BANK_OFFICE' => $config->bankoffice,
            'ACCOUNT_KEY' => $config->bankaccountkey,
            'BIC' => $config->bic,
            'IBAN' => $config->iban,
            'TVA_EUROPE' => $config->tvaeurope,
            'PROC_ORDER' => $proc++,
        ];
        echo shop_compile_mail_template('print_procedure_text_invoice', $vars, 'shoppaymodes_transfer');
    }

    /**
     * Print when payment is completed.
     */
    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', []);
    }

    /**
     * In transfer mode, there is no immediate payment return.
     * So it can be processed as payed immediately. Process action will need
     * an operator action in the backoffice.
     */
    public function process() {
        // Void.
        assert(true);
    }

    /**
     * Processes a payment asynchronoous confirmation.
     * Not relevant for transfer payments.
     */
    public function process_ipn() {
        // No IPN for offline payment.
        assert(true);
    }

    /**
     * Provides global settings to add to shop settings when installed.
     * @param object $settings
     */
    public function settings($settings) {
        return false;
    }
}
