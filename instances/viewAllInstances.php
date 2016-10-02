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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/instances/renderer.php');

$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$customerid = required_param('customer', PARAM_INT);

try {
    $customer = new Customer($customerid);
} catch (Exception $e) {
    print_error('objecterror', 'local_shop', $e->get_message());
}

// Execute controller.
$hashandlersstr = get_string('hashandlers', 'local_shop');

$action = optional_param('what', '', PARAM_ALPHA);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/instances/instances.controller.php');
    $controller = new instances_controller();
    $controller->process($action);
}

// Fetch all product instances for the current customer account.
$instances = Product::get_instances(array('customerid' => $customer->id));

echo $out;

$listurl = new moodle_url('/local/shop/instances/view.php', array('view' => 'viewAllInstances'));
echo $mainrenderer->customer_choice($customerid, $listurl);

echo $OUTPUT->heading(get_string('customer', 'local_shop'), 1);

echo $renderer->customer($customer);

echo $OUTPUT->heading(get_string('instances', 'local_shop'), 1);

if (empty($instances)) {
    echo $OUTPUT->notification(get_string('noinstances', 'local_shop'));
} else {
    echo $renderer->instances();
}

echo $renderer->global_commands();