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
 * @package    shoppaymodes_paybox
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once $CFG->dirroot.'/local/shop/paymodes/paymode.class.php';

class shop_paymode_paybox extends shop_paymode{

    function __construct(&$shop) {
        // to enable paybox in your installation, change second param to "true"
        parent::__construct('paybox', $shop, false, true);
    }
        
    // prints a payment porlet in an order form
    function print_payment_portlet(&$billdata) {
        echo '<p>Not implemeted Yet!</p> ';
    }

    /**
     * prints a payment porlet in an order form
     */
    function print_invoice_info(&$billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_paybox', '');
    }

    function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', array('SUPPORT' => $this->_config->sellermailsupport), 'local_shop');
    }

    // processes a payment return
    function process() {
    }

    // processes a payment asynchronoous confirmation
    function process_ipn() {
    }
    
    // provides global settings to add to shop settings when installed
    function settings(&$settings) {
        $settings->add(new admin_setting_heading('local_shop_'.$this->name, get_string($this->name.'paymodeparams', 'local_shop'), ''));

    }
}