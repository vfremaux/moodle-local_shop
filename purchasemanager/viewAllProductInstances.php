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
$customerid = optional_param('customerid', 0, PARAM_INT);
$contexttype = optional_param('contexttype', '', PARAM_TEXT);
$shopid = optional_param('shopid', 0, PARAM_INT);
$shopownerid = optional_param('shopowner', 0, PARAM_INT);
$productstate = optional_param('productstate', '*', PARAM_TEXT);
$producttext = optional_param('producttext', '', PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 50;

$viewparams = array('view' => $view, 'customerid' => $customerid, 'order' => $order, 'dir' => $dir, 'shopowner' => $shopownerid, 'shopid' => $shopid);

$ownermenu = '';

if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = 0;
} else {
    $shopowner = null;
    $shoprenderer = $PAGE->get_renderer('local_shop');
    $ownermenu = $shoprenderer->print_owner_menu($url, $shopownerid);
}

// Execute controller.

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/purchasemanager/productinstances.controller.php');
    $controller = new \local_shop\backoffice\productinstances_controller();
    $controller->receive($action);
    $controller->process($action);
    redirect(new moodle_url('/local/shop/purchasemanager/view.php', $viewparams));
}

// $select = " hasaccount > 0 ";
$select = " 1 = 1 ";
$join = '';
$params = array();
if ($shopownerid) {
    $select .= " AND co.userid = ? ";
    $params[] = $shopownerid;
    $join = "
        LEFT JOIN
            {local_shop_customer_owner} co
        ON
            co.customerid = c.id
    ";
}

$filter = [];
if (!empty($shopid)) {
    $filter['s.id'] = $shopid;
}
if (!empty($customerid)) {
    $filter['p.customerid'] = $customerid;
}
if (!empty($contexttype)) {
    $filter['p.contexttype'] = $contexttype;
}

$productinstancesnum = Product::count_instances_on_context($filter);
$productinstances = Product::get_instances_on_context($filter, 'ci.shortname', $page * $perpage, $perpage);
Product::filter_by_state($productinstances, $productstate);

echo $out;

$params = ['view' => 'viewAllProductInstances', 'customerid' => $customerid, 'shopowner' => $shopowner];
$viewurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);

echo $OUTPUT->heading(get_string('productinstances', 'local_shop'));

echo $renderer->productinstances_options($mainrenderer);

if (count(array_keys($productinstances)) == 0) {
    echo $OUTPUT->notification(get_string('noinstances', 'local_shop'));
} else {
    $pageurl = clone($url);
    $params = [
        'quicksearchfilter' => optional_param('quicksearchfilter', '', PARAM_TEXT),
        'productstate' => $productstate,
        'customerid' => $customerid,
        'shopowner' => $shopownerid,
        'dir' => $dir,
        'contexttype' => $contexttype,
        'order' => $order,
        'shop' => $shopid
    ];
    $pageurl->params($params);
    echo $OUTPUT->paging_bar($productinstancesnum, $page, $perpage, $pageurl);
    echo $renderer->productinstance_admin_form($productinstances, $viewparams, $customerid, $shopowner);
    echo get_string('withselection', 'local_shop');
    echo $renderer->selection_tools($customerid);
}

echo '<br/>';

if (local_shop_supports_feature('products/editable') && has_capability('local/shop:salesadmin', $context)) {
    echo $renderer->add_instance_button($theshop, $shopowner, $customerid);
}
