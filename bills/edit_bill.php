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
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_bill.class.php');
require_once($CFG->dirroot."/local/shop/classes/Catalog.class.php");
require_once($CFG->dirroot."/local/shop/classes/Bill.class.php");

use local_shop\Bill;
use local_shop\Catalog;

$PAGE->requires->js('/local/shop/js/bills.js');

// get all the shop session context objects
list($theShop, $theCatalog, $theBlock) = shop_build_context();

$config = get_config('local_shop');

// Security.
$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/shop:salesadmin', $context);

$billid = optional_param('billid', 0, PARAM_INT);

// Make page header and navigation.

$PAGE->set_url(new moodle_url('/local/shop/bills/edit_bill.php'));
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($billid) {
    $bill = new Bill($billid, $theShop, $theCatalog, $theBlock);
    $mform = new Bill_Form('', array('what' => 'edit'));
    $mform->set_data($bill);
} else {
    $bill = new Bill(null, $theShop, $theCatalog, $theBlock);
    $mform = new Bill_Form('', array('what' => 'add'));
    $bill->autobill = 0;
    $mform->set_data($bill);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/bills/view.php', array('view' => 'viewAllBills')));
}
if ($bill = $mform->get_data()) {

    $now = time();

    if (!empty($bill->billid)) {
        $bill->id = $bill->billid;
    } else {
        $bill->id = 0;
        $bill->generate_unique_transaction();
        $bill->emissiondate = $now;
    }
    unset($bill->billid);

    $bill->lastactiondate = $now;

    if (empty($bill->currency)) {
        $bill->currency = $theShop->defaultcurrency;
    }

    $shipping = new StdClass;
    if (!empty($config->useshipping)) {
        $shipping = shop_calculate_shipping($catalogid, $country, $order);
    } else {
        $shipping->value = 0;
    }

    // Creating a customer account for a user.
    if ($bill->useraccountid != 0) {
        $user = $DB->get_record('user', array('id' => $bill->useraccountid));
        $customer->firstname = $user->firstname;
        $customer->lastname = $user->lastname;
        $customer->email = $user->email;
        $customer->address = $user->address;
        $customer->city = $user->city;
        $customer->zip = '';
        $customer->country = $user->country;
        $customer->hasaccount = $user->id;
        if (!$newcustomerid = $DB->insert_record('local_shop_customer', $customer)) {
            print_error('erroraddnewcustomer', 'local_shop');
        }
        $bill->customerid = $newcustomerid;
    } else {
        $bill->customerid = $bill->userid;
    }
    unset($bill->userid);
    unset($bill->useraccountid);

    if (empty($bill->id)) {

        $lastordering = Bill::last_ordering();
        $bill->lastordering = $lastordering + 1;

        $bill->id = $DB->insert_record('local_shop_bill', $bill);
    } else {
        $DB->update_record('local_shop_bill', $bill);
    }

    redirect(new moodle_url('/local/shop/bills/view.php', array('view' => 'viewBill', 'billid' => $bill->id)));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();