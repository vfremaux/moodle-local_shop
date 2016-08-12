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

use local_shop\Catalog;

// get the block reference and key context.
list($theShop, $theCatalog, $theBlock) = shop_build_context();

// get the block reference and key context

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
$PAGE->navbar->add(format_string($theCatalog->name), new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
$PAGE->navbar->add(get_string('addcategory', 'local_shop'));

if ($categoryid) {
    $category = $DB->get_record('local_shop_catalogcategory', array('id' => $categoryid));
    $mform = new Category_Form('', array('what' => 'edit'));
    $category->categoryid = $category->id;
    $category->id = $theShop->id;
    $mform->set_data($category);
} else {
    $mform = new Category_Form('', array('what' => 'add'));
    $formdata = new StdClass();
    $formdata->id = $theShop->id;
    $formdata->description = '';
    $formdata->descriptionformat = FORMAT_MOODLE;
    $mform->set_data($formdata);
}
if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/products/view.php', array('id' => $theShop->id, 'view' => 'viewAllProducts')));
}
if ($data = $mform->get_data()) {

    if (!isset($data->visible)) {
        $data->visible = 0;
    }

    $data->catalogid = $theCatalog->id;

    $maxorder = $DB->get_field('local_shop_catalogcategory', 'MAX(sortorder)', array('catalogid' => $theCatalog->id));

    $data->description = $data->description_editor['text'];
    $data->descriptionformat = 0 + $data->description_editor['format'];

    if (empty($data->categoryid)) {
        $data->sortorder = $maxorder + 1;
        if (!$data->id = $DB->insert_record('local_shop_catalogcategory', $data)) {
            print_error('erroraddcategory', 'local_shop');
        }
        // We have items in the set. update relevant products.
        $productsinset = optional_param('productsinset', array(), PARAM_INT);
        if (is_array($productsinset)) {
            foreach ($productsinset as $productid) {
                $record = new StdClass;
                $record->id = $productid;
                $record->setid = $data->id;
                $DB->update_record('local_shop_catalogitem', $record);
            }
        }
        // If slave catalogue must insert a master copy.
        if ($theCatalog->isslave) {
            $data->catalogid = $theCatalog->groupid;
            $DB->insert_record('local_shop_catalogcategory', $data);
        }
    } else {
        $data->id = $data->categoryid;
        if (!$data->id = $DB->update_record('local_shop_catalogcategory', $data)) {
            print_error('errorupdatecategory', 'local_shop');
        }
    }

    // Process text fields from editors.
    $draftid_editor = file_get_submitted_draft_itemid('description_editor');
    $data->description = file_save_draft_area_files($draftid_editor, $context->id, 'local_shop', 'categorydescription', $data->id, array('subdirs' => true), $data->description);
    $data = file_postupdate_standard_editor($data, 'description', $mform->editoroptions, $context, 'local_shop', 'categorydescription', $data->id);

    redirect(new moodle_url('/local/shop/products/category/view.php', array('id' => $theShop->id, 'view' => 'viewAllCategories')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();