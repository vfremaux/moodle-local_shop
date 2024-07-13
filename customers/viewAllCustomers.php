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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Customer;

$sortorder = optional_param('sortorder', 'name', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);
$action = optional_param('what', '', PARAM_TEXT);
$shopid = optional_param('shopid', 0, PARAM_INT);
$nopaging = optional_param('nopaging', 0, PARAM_BOOL);
$pagesize = 10;
$customerpage = optional_param('customerpage', 0, PARAM_INT);
$offset = $customerpage * $pagesize;

ini_set('memory_limit', '512M');

list($filter, $filterclause, $urlfilter) = shop_get_customer_filtering();

if (!empty($action)) {
    include_once($CFG->dirroot.'/local/shop/customers/customers.controller.php');
    $controller = new \local_shop\backoffice\customers_controller();
    $controller->receive($action);
    $controller->process($action);
}

$params = array('view' => 'viewAllCustomers', 'sortorder' => $sortorder, 'dir' => $dir);
$url = new moodle_url('/local/shop/customers/view.php', $params);

$config = get_config('local_shop');

// $customers = Customer::get_instances_for_admin($theshop);

echo $out;

echo $OUTPUT->heading(get_string('customeraccounts', 'local_shop'), 1);

echo $renderer->customers_options($mainrenderer);

$total = Customer::count_instances_by_shop($filter);
if ($nopaging) {
    $customers = Customer::get_instances_by_shop($filter, $sortorder, $dir);
} else {
    $customers = Customer::get_instances_by_shop($filter, $sortorder, $dir, $offset, $pagesize);
}

if (empty($customers)) {
    echo $OUTPUT->notification(get_string('nocustomers', 'local_shop'));
} else {

    // Print pager.
    $urlpagingfilter = str_replace('nopaging=1', 'nopaging=0', $urlfilter);
    $pagingbar = $OUTPUT->paging_bar($total, $customerpage, $pagesize, $url.'&'.$urlpagingfilter, 'customerpage');
    if ($pagingbar) {
        echo $pagingbar;
        echo $renderer->no_paging_switch($url, $urlfilter);
    }

    echo $renderer->customers($customers, $url);

    if ($total > 20) {
        if ($pagingbar) {
            echo $pagingbar;
            echo $renderer->no_paging_switch($url, $urlfilter);
        }
    }
}

echo $renderer->customer_view_links();