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
 * @package    shoppaymodes_publicmandate
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

class shop_paymode_publicmandate extends shop_paymode {

    public function __construct(&$shop) {
        parent::__construct('publicmandate', $shop);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param objectref &$billdata
     */
    public function print_payment_portlet(&$billdata) {
        $proc = 1;

        echo '<p>' . shop_compile_mail_template('pay_instructions', array(), 'shoppaymodes_publicmandate');
        echo shop_compile_mail_template('print_procedure_text', array(
            'PROC_ORDER' => $proc++), 'shoppaymodes_publicmandate');
    }

    /**
     * Prints a payment porlet in an order form.
     * @param objectref &$billdata
     */
    public function print_invoice_info(&$billdata = null) {
        $proc = 1;

        echo '<p>'.shop_compile_mail_template('pay_instructions_invoice', array(), 'shoppaymodes_publicmandate');
        echo shop_compile_mail_template('print_procedure_text_invoice', array(
            'PROC_ORDER' => $proc++), 'shoppaymodes_publicmandate');
    }

    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', array(), 'local_shop');
    }

    /**
     * Processes a payment return.
     */
    public function process() {
    }

    /**
     * Processes a payment asynchronoous confirmation.
     */
    public function process_ipn() {
        // No IPN for offline payment.
    }

    // Provides global settings to add to shop settings when installed.
    public function settings(&$settings) {
    }
}