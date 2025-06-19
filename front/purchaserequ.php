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
 * @package   local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

$categories = $thecatalog->get_categories();

// In case session is lost, go to the public entrance of the shop.
if (!isset($SESSION->shoppingcart) || !isset($SESSION->shoppingcart->order)) {
    $params = ['id' => $theshop->id, 'blockid' => $theblock->id, 'view' => 'shop'];
    redirect(new moodle_url('/local/shop/front/view.php', $params));
}

// Now we browse categories for making the catalog.

$shopproducts = $thecatalog->get_all_products($categories);

// Pre feed SESSION shoppingcart if required.
$action = optional_param('what', '', PARAM_TEXT);

// Either we are navigating here from elsewhere.

if (!empty($SESSION->shoppingcart->order)) {
    $productkeys = array_keys($SESSION->shoppingcart->order);
} else {
    $productkeys = [];
}

$datarequired = false;

foreach ($productkeys as $shortname) {
    $catalogentry = $thecatalog->get_product_by_shortname($shortname);
    $requireddata = $catalogentry->requireddata;
    if (!empty($requireddata)) {
        $datarequired = true;
    }
}

if (!$datarequired) {
    $SESSION->shoppingcart->norequs = 1;
    $action = 'navigate';
}

$errors = [];
if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/purchaserequ.controller.php');
    $controller = new \local_shop\front\purchaserequ_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $returnurl = $controller->process($action);
    if (!empty($returnurl)) {
        redirect($returnurl);
    }
}

echo $out;

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

echo $renderer->progress('CONFIGURE');

echo $renderer->admin_options();

echo $renderer->customer_requirements($errors);

// Order item counting block.

$options['nextstyle'] = 'shop-next-button';
$options['overtext'] = get_string('saverequirements', 'local_shop');

if (empty($SESSION->shoppingcart->customerdata['completed'])) {
    $options['nextdisabled'] = 'disabled="disabled"';
} else {
    $options['nextstyle'] = 'background-color:green;background-image:none;color:white';
}

echo '<center>';
echo $renderer->action_form('purchaserequ', $options);
echo '</center>';
