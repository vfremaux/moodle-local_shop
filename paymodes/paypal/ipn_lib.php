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
 * Ipn library
 *
 * @package  shoppaymodes_paypal
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

/**
 * A lib to provide stuff to simulate IPN from the shop itself
 */

/**
 * Prints a link to test IPN
 * @param string $transid
 * @param int $shopid
 */
function paypal_print_test_ipn_link($transid, $shopid) {

    $config = get_config('local_shop');

    $sellerexpectedname = (empty($config->test)) ? $config->paypalsellername : $config->paypalsellertestname;

    $txnid = substr($transid, 0, 10);
    $url = new moodle_url('/local/shop/paymodes/paypal/paypal_ipn.php');

    $custom = $shopid;

    $testipnstr = get_string('ipnfortest', 'shoppaymodes_paypal');

    echo '<form action="'.$url.'" name="ipnsimulate" method="POST" class="shop-inline" >';
    echo '<input type="hidden" name="invoice" value="'.$transid.'" />';
    echo '<input type="hidden" name="custom" value="'.$custom.'" />';
    echo '<input type="hidden" name="txn_id" value="'.$txnid.'" />';
    echo '<input type="hidden" name="business" value="'.$sellerexpectedname.'" />';
    echo '<input type="hidden" name="receiver_id" value="'.$sellerexpectedname.'" />';
    echo '<input type="hidden" name="simulating" value="1" />';
    echo '<input type="hidden" name="payment_status" value="Completed" />';

    // Catch all post values that came back from Paypal.
    foreach ($_POST as $key => $value) {
        $value = stripslashes($value);
        echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
    }

    echo '<input type="submit" value="'.$testipnstr.'" />';
    echo '</form>';
}

/**
 * sends admin a notification
 * @param string $subject
 * @param mixed $data
 */
function shop_email_paypal_error_to_admin($subject, $data) {
    global $DB;

    if ($salesrole = $DB->get_record('role', ['shortname' => 'sales'])) {
        $salesadmins = get_users_from_role_on_context($salesrole, context_system::instance());
    }
    
    if (empty($salesadmins)) {
        $salesadmins[] = get_admin();
    }

    $site = get_site();

    $message = "$site->fullname: Paypal IPN : Transaction failed.\n\n$subject\n\n";

    foreach ($data as $key => $value) {
        $message .= "$key => $value\n";
    }

    if (!empty($salesadmins)) {
        foreach ($salesadmins as $salesadmin) {
            email_to_user($salesadmin, $salesadmin, "Paypal IPN Error : ".$subject, $message);
        }
    }
}
