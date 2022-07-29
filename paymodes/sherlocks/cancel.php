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
 * @package    shoppaymodes_sherlocks
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get DATA param string from sherlocks API and redirect to shop.

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/sherlocks/sherlocks.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/shop/paymodes/systempay/cancel.php'));

/*
 * we cannot know yet which block instanceplays as infomation is in the sherlocks
 * cryptic answer. Cancel() decodes crytpic answer and get this context information to
 * go further.
 */
$shopinstance = null;
$payhandler = new shop_paymode_sherlocks($shopinstance);
$payhandler->cancel();