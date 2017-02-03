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

require_once($CFG->dirroot.'/local/shop/products/category/viewAllCategories.controller.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');

use local_shop\Category;

$action = optional_param('what', '', PARAM_TEXT);
$order = optional_param('order', 'sortorder', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);

if ($action != '') {
    $controller = new \local_shop\backoffice\category_controller();
    $controller->process($action);
}

$params = array('id' => $thecatalog->id, 'view' => 'viewAllCategories', 'order' => $order, 'dir' => $dir);
$url = new moodle_url('/local/shop/products/category/view.php', $params);

// Eliminate tests.
$select = "
    catalogid = ? AND
    UPPER(name) NOT LIKE 'test%'
";
$categorycount = $DB->count_records_select('local_shop_catalogcategory', $select, array($thecatalog->id));

$categories = Category::get_instances(array('catalogid' => $thecatalog->id, 'parentid' => 0), "$order $dir");

echo $OUTPUT->heading(get_string('category', 'local_shop'), 1);

$addcategorystr = get_string('newcategory', 'local_shop');
$backtocatalogstr = get_string('backtocatalog', 'local_shop');

if (empty($categories)) {
    echo $OUTPUT->box(get_string('nocats', 'local_shop'));
} else {
    echo $renderer->categories($categories, $order, $dir);
}

$editurl = new moodle_url('/local/shop/products/category/edit_category.php', array('id' => $thecatalog->id));
$params = array('shopid' => $theshop->id, 'catalogid' => $thecatalog->id, 'view' => 'viewAllProducts');
$catalogurl = new moodle_url('/local/shop/products/view.php', $params);
echo '<div class="addlink"><a href="'.$editurl.'">'.$addcategorystr.'</a> -';
echo ' <a href="'.$catalogurl.'">'.$backtocatalogstr.'</a></div>';