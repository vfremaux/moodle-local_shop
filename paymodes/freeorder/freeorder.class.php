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
 * @package    shoppaymodes_freeorder
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
 * A class free order payment as a fallback when order is 0
 * amount.
 */
class shop_paymode_freeorder extends shop_paymode {

    /**
     * Constructor
     * @param Shop $theshop
     */
    public function __construct(?Shop $shop) {
        // NOT ENABLED AS NOT SELECTABLE METHOD!
        parent::__construct('freeorder', $shop, false);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param objectref &$shoppingcart
     */
    public function print_payment_portlet(&$shoppingcart) {
        // No portlet.
        assert(true);
    }

    /**
     * Prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {
        // Nothing to print.
        assert(true);
    }

    /**
     * Print something on payment completion.
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
        assert(true);
    }

    /**
     * Has this method configuration ?
     */
    public function has_config() {
        return false;
    }

    /**
     * Is this payment method interactive (needs customer payment action) ? 
     */
    public function is_interactive() {
        // Non interactive.
        return false;
    }

    /**
     * Provides global settings to add to shop settings when installed.
     * @param objectref &$settings
     */
    public function settings(&$settings) {
        // No settings.
        assert(true);
    }
}
