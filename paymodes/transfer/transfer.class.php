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

defined('MOODLE_INTERNAL') || die();

/**
 * @package    shoppaymodes_transfer
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

class shop_paymode_transfer extends shop_paymode{

    function __construct(&$shopblockinstance) {
        parent::__construct('transfer', $shopblockinstance);
    }
        
    // prints a payment porlet in an order form
    function print_payment_portlet(&$billdata) {
        global $CFG;
        $proc = 1;

        $config = get_config('local_shop');

        echo '<p>' . shop_compile_mail_template('pay_instructions', array(), 'shoppaymodes_transfer');
        echo shop_compile_mail_template('print_procedure_text', array(
            'SELLER' => $config->sellername,
            'ADDRESS' => $config->sellerbillingaddress,
            'ZIP' => $config->sellerbillingzip,
            'CITY' => $config->sellerbillingcity,
            'COUNTRY' => strtoupper($config->sellercountry),
            'BANKING' => $config->banking,
            'BANK_CODE' => $config->bankcode,
            'BANK_OFFICE' => $config->bankoffice,
            'BANK_ACCOUNT' => $config->bankaccount,
            'ACCOUNT_KEY' =>  $config->bankaccountkey,
            'IBAN' =>  $config->iban,
            'BIC' =>  $config->bic,
            'TVA_EUROPE' =>  $config->tvaeurope,
            'PROC_ORDER' => $proc++  ), 'shoppaymodes_transfer');
    }

    // prints a payment porlet in an order form
    function print_invoice_info(&$billdata = null) {
        global $CFG;
        $proc = 1;

        $config = get_config('local_shop');

        echo '<p>'.shop_compile_mail_template('pay_instructions_invoice', array(), 'shoppaymodes_transfer');
        echo shop_compile_mail_template('print_procedure_text_invoice', array(
            'SELLER' => $config->sellername,
            'ADDRESS' => $config->sellerbillingaddress,
            'ZIP' => $config->sellerbillingzip,
            'CITY' => $config->sellerbillingcity,
            'COUNTRY' => strtoupper($config->sellercountry),
            'BANKING' => $config->banking,
            'BANK_CODE' => $config->bankcode,
            'BANK_OFFICE' => $config->bankoffice,
            'BANK_ACCOUNT' => $config->bankaccount,
            'ACCOUNT_KEY' =>  $config->bankaccountkey,
            'IBAN' =>  $config->iban,
            'BIC' =>  $config->bic,
            'TVA_EUROPE' =>  $config->tvaeurope,
            'PROC_ORDER' => $proc++  ), 'shoppaymodes_transfer');
    }

    function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', array());
    }

    // processes a payment return
    function process() {
    }

    // processes a payment asynchronoous confirmation
    function process_ipn() {
        // no IPN for offline payment.
    }
    
    // provides global settings to add to shop settings when installed
    function settings(&$settings) {
    }
}