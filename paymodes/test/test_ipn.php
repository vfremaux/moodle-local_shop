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
 * @package     shoppaymodes_test
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get all input parms.

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/test/test.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

$config = get_config('local_shop');

// Setup trace.

echo '<pre>';
mtrace('Test Autoresponse (IPN) : This simulates an asynchronous response of a payment terminal');
shop_trace('Test Autoresponse (IPN) : This simulates an asynchronous ');

// Keep out casual intruders.

if (empty($config->test)) {
    die('Test payment plugin cannot be used when shop is in production state');
}

/*
 * we cannot know yet which shop instance plays as information is in the mercanet
 * cryptic answer. Process_ipn() decodes cryptic answer and get this context information to
 * go further.
 */

// systematic answer (tells we are listening test actually).

$shopinstance = null;
$payhandler = new shop_paymode_test($shopinstance);
$payhandler->process_ipn();