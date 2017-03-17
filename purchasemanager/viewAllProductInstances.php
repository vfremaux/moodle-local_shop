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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');

use local_shop\Product;

$action = optional_param('what', '', PARAM_ALPHA);
$order = optional_param('order', 'code', PARAM_ALPHA);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$customerid = optional_param('customer', 0, PARAM_INT);
$shopownerid = optional_param('shopowner', 0, PARAM_INT);

$viewparams = array('view' => $view, 'customer' => $customerid, 'order' => $order, 'dir' => $dir, 'shopowner' => $shopownerid);

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

$select = " hasaccount > 0 ";
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

$sql = "
    SELECT
        c.id,
        c.firstname,
        c.lastname,
        c.city,
        c.country,
        c.hasaccount
    FROM
        {local_shop_customer} c
    $join
    WHERE
        $select
    ORDER BY
        c.lastname,
        c.firstname
";
$customers = $DB->get_records_sql($sql, $params);

if (!$customerid) {
    // Take the first one as default.
    $ckeys = array_keys($customers);
    $customerid = array_pop($ckeys);
}

$customermenu = $shoprenderer->print_customer_menu($url, $customers, $customerid);

$productinstances = Product::get_instances_on_context(array('ci.userid' => $shopownerid, 'p.customerid' => $customerid));

echo $out;

$params = array('view' => 'viewAllProductInstances', 'customerid' => $customerid, 'shopowner' => $shopowner);
$viewurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);

echo $OUTPUT->heading(get_string('productinstances', 'local_shop'));

echo '<div class="form-filter-menus">';
echo '<div class="form-filter-owner">';
echo $ownermenu;
echo '</div>';
echo '<div class="form-filter-customer">';
echo $customermenu;
echo '</div>';
echo '</div>';

if (count(array_keys($productinstances)) == 0) {
    echo $OUTPUT->notification(get_string('noinstances', 'local_shop'));
} else {
    $formurl = new moodle_url('/local/shop/purchasemanager/view.php');
    echo '<form name="selection" action="'.$formurl.'" method="get">';
    echo '<input type="hidden" name="view" value="viewAllProductInstances" />';
    echo '<input type="hidden" name="what" value="" />';
    echo '<input type="hidden" name="customerid" value="'.$customerid.'" />';
    echo '<input type="hidden" name="shopowner" value="'.$shopowner.'" />';
    echo '<table width="100%">';
    $portlet = null;
    echo $renderer->productinstance_admin_line($portlet);

    foreach (array_values($productinstances) as $instance) {
        echo $renderer->productinstance_admin_line($instance, $viewparams);
    }
}
echo '</table>';
echo '</form>';