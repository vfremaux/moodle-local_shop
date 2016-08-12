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
 *
 * Defines form to add a new customer
 *
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_customer.class.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Customer;

// get the block reference and key context.
list($theShop, $theCatalog, $theBlock) = shop_build_context();

$customerid = optional_param('customerid', '', PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/customers/edit_customer.php', array('customerid' => $customerid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('editcustomer', 'local_shop'));

if ($customerid) {
    $customer = new Customer($customerid);
    $mform = new Customer_Form('', array('what' => 'edit'));
    $mform->set_data($customer);
} else {
    $customer = new Customer(null);
    $mform = new Customer_Form('', array('what' => 'add'));
    $mform->set_data($customer);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/customers/view.php', array('view' => 'viewAllCustomers')));
}

if ($data = $mform->get_data()) {
    if ($DB->record_exists('user', array('email' => $data->email))) {
        $account = $DB->get_record('user', array('email' => $data->email));
        $data->hasaccount = $account->id;
    } else {
        $data->hasaccount = 0;
    }
    $data->timecreated = time();
    if (empty($data->id)) {
        $newid = $DB->insert_record('local_shop_customer', $data);
    } else {
        $updateid = $DB->update_record('local_shop_customer', $data);
    }
    redirect(new moodle_url('/local/shop/customers/view.php', array('view' => 'viewAllCustomers')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();