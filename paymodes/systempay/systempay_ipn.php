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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// get all input parms
require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/systempay/systempay.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

// Setup trace

shop_trace('SystemPay Autoresponse (IPN) : Open systempaybacksession');

// Keep out casual intruders 

if (empty($_POST) or !empty($_GET)) {
    die("Sorry, you can not use the script that way.");
}

// we cannot know yet which shop instance plays as infomation is in the systempay
// cryptic answer. Process_ipn() decodes cryptic answer and get this context information to 
// go further.

// systematic answer (tells we are listening systempay actually)
echo "spcheckok";

$shopinstance = null;
$payhandler = new shop_paymode_systempay($shopinstance);
$payhandler->process_ipn();

// check request validity

$config = get_config('local_shop');

$certificate = ($config->test) ? @$config->systempay_test_certificate : @$config->systempay_prod_certificate ;
$expected = $payhandler->generate_sign($_POST, $certificate);
if ($expected == $_POST['signature']) {
    $payhandler->process_ipn();
} else {
    shop_trace("SystemPay IPN : Invalid request signature");
    die;
}
