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
 * Main paymode class
 *
 * @package    shoppaymodes_delegated
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

use local_shop\Shop;

/**
 * A Delegated pay mode delegates payment to the shop administrator. Payment is done
 * and controlled via an external procedure. This is a special backoffice paymode for
 * Web Service based transactions.
 * It has NO need to be enabled and will be activated automatically when WS transactions
 * are used. (e.g. : product activation for third party distributors)
 */
class shop_paymode_delegated extends shop_paymode {

    /**
     * Constructor
     * @param Shop $theshop
     */
    public function __construct(?Shop $theshop) {
        parent::__construct('transfer', $theshop);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param objectref &$shoppingcart
     */
    public function print_payment_portlet(&$shoppingcart) {
        assert(true);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(Bill $billdata = null) {
        assert(true);
    }

    /**
     * Print when payment completed
     */
    public function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', []);
    }

    /**
     * Processes a payment return.
     */
    public function process() {
        // Void.
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
     * @params objectref &$settings
     */
    public function settings(&$settings) {
        assert(true);
    }
}
