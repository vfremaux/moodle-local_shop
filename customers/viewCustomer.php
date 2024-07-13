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
 * View a single customer.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Customer;

$customerid = required_param('customer', PARAM_INT);

$action = optional_param('what', '', PARAM_TEXT);
if (!empty($action)) {
    include_once($CFG->dirroot.'/local/shop/customers/customers.controller.php');
    $controller = new customers_controller();
    $controller->receive($action);
    $controller->process($action);
}

try {
    $customer = new Customer($customerid);
} catch (Exception $e) {
    throw new moodle_exception(get_string('objecterror', 'local_shop', $e->message));
}

// Dispatch bills into status boxes.
$bills = [];
if (is_array($customer->bills)) {
    foreach ($customer->bills as $abill) {
        if (!isset($bills[$abill->status])) {
            $bills[$abill->status] = [];
            $bills[$abill->status][] = $abill;
        }
    }
}

$renderer = shop_get_renderer('customers');
$config = get_config('local_shop');

echo $out;

echo $OUTPUT->heading(get_string('customeraccount', 'local_shop'));

echo $renderer->customer_detail($customer);

if (count($bills) == 0) {
    echo $OUTPUT->notification(get_string('nobillsinaccount', 'local_shop'));
} else {
    foreach (array_keys($bills) as $astatus) {
        echo $renderer->customer_bills($bills[$astatus], $astatus);
    }
}
