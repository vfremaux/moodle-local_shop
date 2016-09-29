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

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');

use local_shop\Product;

$action = optional_param('what', '', PARAM_ALPHA);
$order = optional_param('order', 'code', PARAM_ALPHA);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$customerid = optional_param('customer', 0, PARAM_INT);

$ownermenu = '';
if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = $USER->id;
} else {
    $shopowner = null;
    $shoprenderer = $PAGE->get_renderer('local_shop');
    $ownermenu = $shoprenderer->print_owner_menu($url);
}

// execute controller
//echo "[$view:$cmd]";

if ($action != '') {
   include_once($CFG->dirroot.'/local/shop/purchasemanager/productinstances.controller.php');
   $controller = new productinstances_controller($thecatalogue);
   $controller->receive($action);
   $controller->process($action);
}

$customermenu = $shoprenderer->print_customer_menu($url);

$productinstances = Product::get_instances_on_context(array('ci.userid' => 0 + $shopowner, 'p.customerid' => $customerid));

echo $out;

$viewurl = new moodle_url('/local/shop/purchasemanager/view.php', array('view' => 'viewAllProductInstances', 'customerid' => $customerid, 'shopowner' => $shopowner));

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

    foreach (array_values($productinstances) as $portlet) {
        echo $renderer->productinstance_admin_line($portlet);
    }
}
echo '</table>';
echo '</form>';
