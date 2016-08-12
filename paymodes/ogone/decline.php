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
 * @package    shoppaymodes_ogone
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//Get return parms and redirect to shop

// Return_Context : orderID={$transid}

require('../../../../config.php');
require_once $CFG->dirroot.'/local/shop/paymodes/ogone/ogone.class.php';
require_once $CFG->dirroot.'/local/shop/front/lib.php';

// We cannot know yet the shop instance before having decoded ogone 
// Response content
// Note your ogone account needs explicitely be configured to add transaction
// informations fields to the gateway redirects.
// Check the Ingenico/Ogone documentation on techical setup of your merchant acocunt
// for more information.
$shopinstance = null;
$payhandler = new shop_paymode_ogone($shopinstance);
$payhandler->cancel();