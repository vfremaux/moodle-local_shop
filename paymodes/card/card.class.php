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
 * @package    shoppaymodes_card
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

/**
 * A generic sample class for credic card payment
 * not enabled in reality.
 */
class shop_paymode_card extends shop_paymode {

    public function __construct(&$shop) {
        // GENERIC PLUGIN. DO NOT ENABLE!
        parent::__construct('card', $shop, false);
    }

    // Prints a payment porlet in an order form.
    public function print_payment_portlet(&$billdata) {
        return;
    }

    // Prints a payment porlet in an order form.
    public function print_invoice_info(&$billdata = null) {
        return;
    }

    public function print_complete() {
        echo compile_mail_template('bill_complete_text', array(), 'local_shop');
    }

    // Processes a payment return.
    public function process() {
        return;
    }

    // Processes a payment asynchronoous confirmation.
    public function process_ipn() {
        return;
    }

    // Provides global settings to add to shop settings when installed.
    public function settings(&$settings) {
        return;
    }
}