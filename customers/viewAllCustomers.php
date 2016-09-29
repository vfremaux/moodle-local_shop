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
 * List view of customers.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Customer;

$action = optional_param('action', '', PARAM_TEXT);

if (!empty($action)) {
   include_once($CFG->dirroot.'/local/shop/customers/customers.controller.php');
   $controller = new customer_controller();
   $controller->process($action);
}

$order = optional_param('order', 'lastname', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);
$offset = optional_param('offset', 0, PARAM_INT);

$params = array('view' => 'viewAllCustomers', 'order' => $order, 'dir' => $dir);
$url = new moodle_url('/local/shop/customers/view.php', $params);

$customersCount = $DB->count_records_select('local_shop_customer', " UPPER(email) NOT LIKE 'test%' "); // Eliminate tests.
$config = get_config('local_shop');

$customers = Customer::get_instances_for_admin($theshop);

echo $out;

echo $mainrenderer->shop_choice($url, true);

echo $OUTPUT->heading(get_string('customeraccounts', 'local_shop'), 1);

if (empty($customers)) {
    echo $OUTPUT->notification(get_string('nocustomers', 'local_shop'));
} else {
    echo $renderer->customers($customers);
}

$portlet = new StdClass();
$portlet->url = $url;
$portlet->total = $customersCount;
$portlet->pagesize = $config->maxitemsperpage;
echo $mainrenderer->paging_results($portlet);

echo '<br/>';
echo '<div class="pull-right">';
$newaccounturl = new moodle_url('/local/shop/customers/edit_customer.php');
echo '<a href="'.$newaccounturl.'">'.get_string('newcustomeraccount', 'local_shop').'</a>';
echo '</div>';