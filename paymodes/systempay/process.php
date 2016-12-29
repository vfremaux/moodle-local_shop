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
 * @package    shoppaymodes_systempay
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Get DATA param string from SystemPay API and redirect to shop.

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/systempay/systempay.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

$shopinstance = null;
$payhandler = new shop_paymode_systempay($shopinstance);

if ($_REQUEST['vads_result'] != SP_PURCHASE_CANCELLED) {
    /*
     * process all cases, including payment failure with this credit card,
     * so we can keep the order alive to be payed by another card.
     */
    $payhandler->process();
} else {
    // Explicit purchase cancellation on payment front end.
    $payhandler->cancel();
}
