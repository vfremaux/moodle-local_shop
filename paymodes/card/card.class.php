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
 * @package    shoppaymodes_card
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
 * A generic sample class for credic card payment
 * not enabled in reality.
 */
class shop_paymode_card extends shop_paymode {

    /**
     * Constructor
     * @param Shop $shop
     */
    public function __construct(?Shop $shop) {
        // GENERIC PLUGIN. DO NOT ENABLE!
        parent::__construct('card', $shop, false);
    }

    /**
     * Is this plugin capable of instant payment ?
     */
    public function is_instant_payment() {
        return true;
    }

    /**
     * Prints a payment porlet in an order form.
     */
    public function print_payment_portlet() {
        return;
    }

    /**
     * Prints a payment porlet in an order form.
     * @param Bill $billdata
     */
    public function print_invoice_info(?Bill $billdata = null) {
        return;
    }

    /**
     * Print when payment completed
     */
    public function print_complete() {
        echo compile_mail_template('bill_complete_text', [], 'local_shop');
    }

    /**
     * Processes a payment return.
     */
    public function process() {
        return;
    }

    /**
     * Processes a payment asynchronoous confirmation.
     */
    public function process_ipn() {
        return;
    }

    /**
     * Provides global settings to add to shop settings when installed.
     * @param StdClass $settings
     */
    public function settings($settings) {
        return;
    }
}
