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
 * @package    shoppaymodes_freeorder
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

/**
 * A class free order payment as a fallback when order is 0
 * amount.
 */
class shop_paymode_freeorder extends shop_paymode {

    function __construct(&$shop) {
        // NOT ENABLED AS NOT SELECTABLE METHOD!
        parent::__construct('card', $shop, false);
    }

    // Prints a payment porlet in an order form.
    function print_payment_portlet(&$billdata) {
        // No portlet.
    }

    // Prints a payment porlet in an order form.
    function print_invoice_info(&$billdata = null) {
    }

    function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', array(), 'local_shop');
    }

    // Processes a payment return.
    function process() {
    }

    // Processes a payment asynchronoous confirmation.
    function process_ipn() {
    }

    // Provides global settings to add to shop settings when installed.
    function has_config() {
        return false;
    }

    // Provides global settings to add to shop settings when installed.
    function is_interactive() {
        return false;
    }

    // Provides global settings to add to shop settings when installed.
    function settings(&$settings) {
    }
}