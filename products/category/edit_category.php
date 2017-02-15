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

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_category.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');

use local_shop\Catalog;
use local_shop\Category;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// Get the block reference and key context.

$categoryid = optional_param('categoryid', 0, PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/products/category/edit_category.php', array('categoryid' => $categoryid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('catalogue', 'local_shop'));
$viewurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts'));
$PAGE->navbar->add(format_string($thecatalog->name), $viewurl);
$PAGE->navbar->add(get_string('addcategory', 'local_shop'));

$allcats = Category::get_instances(array('catalogid' => $thecatalog->id));
Category::filter_parentable($allcats, $categoryid);

$allcatsmenu = array();
if (!empty($allcats)) {
    $allcatsmenu[0] = get_string('rootcategory', 'local_shop');
    foreach ($allcats as $cid => $c) {
        $allcatsmenu[$cid] = format_string($c->name);
    }
}

if ($categoryid) {
    $category = $DB->get_record('local_shop_catalogcategory', array('id' => $categoryid));
    $mform = new Category_Form('', array('what' => 'edit', 'parents' => $allcatsmenu));
    $category->categoryid = $category->id;
    $category->id = $theshop->id;

    $mform->set_data($category);
} else {
    $mform = new Category_Form('', array('what' => 'add', 'parents' => $allcatsmenu));
    $formdata = new StdClass();
    $formdata->id = $theshop->id;
    $formdata->description = '';
    $formdata->descriptionformat = FORMAT_MOODLE;
    $mform->set_data($formdata);
}
if ($mform->is_cancelled()) {
    $params = array('id' => $theshop->id, 'view' => 'viewAllProducts');
    redirect(new moodle_url('/local/shop/products/view.php', $params));
}
if ($data = $mform->get_data()) {

    $data->catalog = $thecatalog;

    include_once($CFG->dirroot.'/local/shop/products/category/viewAllCategories.controller.php');
    $processor = new \local_shop\backoffice\category_controller($thecatalog);
    $processor->receive('edit', $data, $mform);
    $processor->process('edit');

    redirect(new moodle_url('/local/shop/products/category/view.php', array('id' => $theshop->id, 'view' => 'viewAllCategories')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();