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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');

use local_shop\Product;

raise_memory_limit(MEMORY_HUGE);

$action = optional_param('what', '', PARAM_ALPHA);
$order = optional_param('order', 'code', PARAM_ALPHA);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$search =new StdClass;
$search->customerid = optional_param('customerid', $SESSION->productsearch->customerid ?? 0, PARAM_INT);
$search->type = optional_param('contexttype', $SESSION->productsearch->type ?? '', PARAM_TEXT);
$search->shopid = optional_param('shopid', $SESSION->productsearch->shopid ?? 0, PARAM_INT);
$search->ownerid = optional_param('shopowner', $SESSION->productsearch->ownerid ?? 0, PARAM_INT);
$search->state = optional_param('productstate', $SESSION->productsearch->state ?? '*', PARAM_TEXT);
$search->text = optional_param('quicksearchfilter', $SESSION->productsearch->text ?? '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

$viewparams = [
    'view' => $view,
    'customerid' => $search->customerid,
    'order' => $order,
    'dir' => $dir,
    'shopowner' => $search->ownerid,
    'productstate' => $search->state,
    'quicksearchfilter' => $search->text,
    'contexttype' => $search->type,
    'shopid' => $search->shopid,
];
$viewurl = new moodle_url($url, $viewparams);

$ownermenu = '';

if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = 0;
} else {
    $shopowner = null;
    $shoprenderer = $PAGE->get_renderer('local_shop');
    $ownermenu = $shoprenderer->print_owner_menu($url, $search->shopownerid);
}

// Execute controller.

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/purchasemanager/productinstances.controller.php');
    $controller = new \local_shop\backoffice\productinstances_controller();
    $controller->receive($action);
    $controller->process($action);
    redirect($viewurl);
}

// $select = " hasaccount > 0 ";
$select = " 1 = 1 ";
$join = '';
$params = [];
if ($search->ownerid) {
    $select .= " AND co.userid = ? ";
    $params[] = $search->ownerid;
    $join = "
        LEFT JOIN
            {local_shop_customer_owner} co
        ON
            co.customerid = c.id
    ";
}

$filter = [];
if (!empty($search->shopid)) {
    $filter['s.id'] = $search->shopid;
}
if (!empty($search->customerid)) {
    $filter['p.customerid'] = $search->customerid;
}
if (!empty($search->type)) {
    $filter['p.contexttype'] = $search->type;
}

$productinstancesnum = Product::count_instances_on_context($filter);
$productinstances = Product::get_instances_on_context($filter, 'ci.shortname', $page * $perpage, $perpage);
Product::filter_by_state($productinstances, $search->state);

echo $out;

echo $OUTPUT->heading(get_string('productinstances', 'local_shop'));

echo $renderer->productinstances_options($mainrenderer);

if (count(array_keys($productinstances)) == 0) {
    echo $OUTPUT->notification(get_string('noinstances', 'local_shop'));
} else {
    $params = [
        'dir' => $dir,
        'order' => $order,
    ];
    $pageurl = new moodle_url($viewurl, $params);
    echo $OUTPUT->paging_bar($productinstancesnum, $page, $perpage, $pageurl);
    echo $renderer->productinstance_admin_form($productinstances, $viewparams, $customerid, $shopowner);
    echo get_string('withselection', 'local_shop');
    echo $renderer->selection_tools($customerid);
}

echo '<br/>';

if (local_shop_supports_feature('products/editable') && has_capability('local/shop:salesadmin', $context)) {
    echo $renderer->add_instance_button($theshop, $shopowner, $customerid);
}
