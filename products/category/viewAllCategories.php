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
require_once($CFG->dirroot.'/local/shop/products/category/viewAllCategories.controller.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');

use local_shop\products\category_controller;
use local_shop\Category;

$action = optional_param('what','',PARAM_TEXT);
$order = optional_param('order', 'name', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);

if ($action != '') {
   $controller = new \local_shop\products\category_controller();
   $controller->process($action);
}

$url = new moodle_url('/local/shop/products/category/view.php', array('id' => $theCatalog->id, 'view' => 'viewAllCategory', 'order' => $order, 'dir' => $dir));

$categoryCount = $DB->count_records_select('local_shop_catalogcategory', " catalogid = ? AND UPPER(name) NOT LIKE 'test%' ", array($theCatalog->id)); // eliminate tests

$categories = Category::get_instances(array('catalogid' => $theCatalog->id), "$order $dir");

echo $OUTPUT->heading(get_string('category', 'local_shop'), 1);

$addcategorystr = get_string('newcategory', 'local_shop');
$backtocatalogstr = get_string('backtocatalog', 'local_shop');

if (empty($categories)) {
    echo $OUTPUT->box(get_string('nocats', 'local_shop'));
} else {
    echo $renderer->categories($categories);
}

$editurl = new moodle_url('/local/shop/products/category/edit_category.php', array('id' => $theCatalog->id));
$catalogurl = new moodle_url('/local/shop/products/view.php', array('id' => $theShop->id, 'catalogid' => $theCatalog->id, 'view' => 'viewAllProducts'));
echo '<div class="addlink"><a href="'.$editurl.'">'.$addcategorystr.'</a> - <a href="'.$catalogurl.'">'.$backtocatalogstr.'</a></div>';