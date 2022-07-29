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
 * @package    shoppaymodes_stripe_checkout
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// get the context back.

/*
 * this process implements the IPN handler for Stripe Checkout asynchronous returns.
 * It has no reference to shopid.
 */
require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/stripe_checkout/stripe_checkout.class.php');
include_once($CFG->dirroot.'/local/shop/front/lib.php');

// Setup trace.

shop_trace('Success Controller : IPN Stripe Checkout return');

// Answer 500 to invalidate purchase (?).

$shopinstance = null;
$payhandler = new shop_paymode_stripe_checkout($shopinstance);
$payhandler->process_webhook();