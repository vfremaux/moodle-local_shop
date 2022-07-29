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
 * @package    shoppaymodes_paypal
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get the context back.

/*
 * this process implements the IPN handler for Paypal asynchronous returns.
 * It has no reference to block id.
 */
require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/paypal/paypal.class.php');
require_once($CFG->dirroot.'/local/shop/paymodes/paypal/ipn_lib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

// Setup trace.

shop_trace('Success Controller : IPN Paypal return');

$PAGE->set_context(context_system::instance());

// Keep eventual intruders out.

if (empty($_POST) or !empty($_GET)) {
    die("Sorry, you can not use the script that way.");
}

$simulate = optional_param('simulate', false, PARAM_BOOL);

$shopinstance = null;
$payhandler = new shop_paymode_paypal($shopinstance);
$payhandler->process_ipn();

if ($simulate) {
    echo $OUTPUT->notification("Paypal IPN Simulation: done.", 'success');
}