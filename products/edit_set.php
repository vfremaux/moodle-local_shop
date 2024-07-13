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
 * Edit a product set
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/products/lib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_set.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Catalog;
use local_shop\CatalogItem;

$PAGE->requires->jquery();
$PAGE->requires->js('/local/shop/extlib/js.js', true);
$PAGE->requires->js('/local/shop/js/shopadmin.js', true);
$PAGE->requires->js('/local/shop/js/shopadmin_late.js', false);

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$setid = optional_param('itemid', '', PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);

// Security.
$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/products/edit_set.php', ['setid' => $setid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($setid) {
    $set = new CatalogItem($setid);
    $itemcatalog = $set->get_catalog();
    $mform = new Set_Form($url, ['what' => 'edit', 'catalog' => $itemcatalog]);
} else {
    $itemcatalog = $thecatalog;
    $mform = new Set_Form($url, ['what' => 'add', 'catalog' => $itemcatalog]);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/products/view.php', ['view' => 'viewAllProducts', 'categoryid' => $categoryid]));
}

if ($data = $mform->get_data()) {

    $data->catalogid = $itemcatalog->id;
    $data->isset = PRODUCT_SET;

    $data->description = $data->description_editor['text'];
    $data->descriptionformat = $data->description_editor['format'];

    if (empty($data->renewable)) {
        $data->renewable = 0;
    }

    if (empty($data->setid)) {
        $data->shortname = CatalogItem::compute_item_shortname($data);

        // We must care that setid deisgnates both local form Setid and Catalogitem setid field.
        $data->setid = 0;
        $data->id = $DB->insert_record('local_shop_catalogitem', $data);

        // We have items in the set. update relevant products.
        $productsinset = optional_param('productsinset', [], PARAM_INT);
        if (is_array($productsinset)) {
            foreach ($productsinset as $productid) {
                if ($productid != $data->id) {
                    // Protect against self referencing.
                    $record = new StdClass;
                    $record->id = $productid;
                    $record->setid = $data->id;
                    $DB->update_record('local_shop_catalogitem', $record);
                }
            }
        }

        // If slave catalogue must insert a master copy.
        if ($thecatalog->isslave) {
            $masterdata = clone($data);
            unset($masterdata->id);
            $masterdata->catalogid = $itemcatalog->groupid;
            $DB->insert_record('local_shop_catalogitem', $masterdata);
        }
    } else {
        $data->itemid = $data->setid;
        $data->id = $data->setid;
        unset($data->setid);
        // We must care that setid designates both local form Setid and Catalogitem setid field.

        // If set code as changed, we'd better recompute a new shortname.
        if (empty($data->shortname) ||
                ($data->code != $DB->get_field('local_shop_catalogitem', 'code', ['id' => $data->id]))) {
            $data->shortname = CatalogItem::compute_item_shortname($data);
        }

        $DB->update_record('local_shop_catalogitem', $data);
    }

    // Process text fields from editors.
    $draftideditor = file_get_submitted_draft_itemid('description_editor');
    $data->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemdescription',
                                                    $data->id, ['subdirs' => true], $data->description);
    $data = file_postupdate_standard_editor($data, 'description', $mform->editoroptions, $context, 'local_shop',
                                            'catalogitemdescription', $data->id);
    // Post update after processing text.
    $DB->update_record('local_shop_catalogitem', $data);

    $usercontext = context_user::instance($USER->id);
    shop_products_process_files($data, $context, $usercontext);

    $params = ['view' => 'viewAllProducts', 'categoryid' => $categoryid];
    redirect(new moodle_url('/local/shop/products/view.php', $params));
}

if ($setid) {
    $set = new CatalogItem($setid);
    $itemrec = $set->record;
    $itemrec->setid = $setid;
    unset($itemrec->id);
    $itemrec->itemid = $setid;
    $mform->set_data($itemrec);
} else {
    $set = new CatalogItem(null);
    $itemrec = $set->record;
    $itemrec->categoryid = optional_param('categoryid', 0, PARAM_INT);
    $mform->set_data($itemrec);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
