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
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

use local_shop\Category;

$PAGE->requires->js('/local/shop/front/js/front.js.php?id='.$theshop->id);

// Check see all mode in session.
if (isloggedin() && is_siteadmin()) {
    $SESSION->shopseeall = optional_param('seeall', @$SESSION->shopseeall, PARAM_BOOL);
}

// Pre feed SESSION shoppingcart if required.
$action = optional_param('what', '', PARAM_TEXT);
if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/shop.controller.php');
    $controller = new \local_shop\front\shop_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $resulturl = $controller->process($action);
    if ($resulturl) {
        redirect($resulturl);
    }
}

$categories = $thecatalog->get_categories();
// Now we browse categories for making the catalog.

// Choose a category.
$category = optional_param('category', null, PARAM_INT);
if (empty($category)) {
    // Explicit the category.
    $catids = array_keys($categories);
    $firstcategory = array_shift($catids);
    $category = new Category($firstcategory);
    if ($category->is_empty()) {
        $firstcategory = $category->get_first_non_empty_child();
    }

    $params = array('view' => $view, 'category' => $firstcategory, 'shopid' => $theshop->id);
    redirect(new moodle_url('/local/shop/front/view.php', $params));
}

$categories = $thecatalog->get_all_products($shopproducts);

echo $out;

$units = 0;
if (isset($SESSION->shoppingcart->order)) {
    foreach ($SESSION->shoppingcart->order as $shortname => $q) {
        $units += $q;
    }
}

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

echo $OUTPUT->box(format_text($theshop->description, $theshop->descriptionformat), 'shop-description');

echo $renderer->admin_options();
echo $renderer->progress('CHOOSE');

echo '<form name="caddie" action="">';
echo '<table width="100%" cellspacing="10"><tr valign="top"><td width="*">';

echo $renderer->catalog($categories);

echo '</tr></table>';
echo '</form>';

echo $renderer->my_total_link();
