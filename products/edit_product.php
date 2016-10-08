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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/products/lib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_product.class.php');
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

$itemid = optional_param('itemid', 0, PARAM_INT);

// Security.

$context = context_system::instance();

require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/products/edit_product.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($itemid) {
    $item = new CatalogItem($itemid);
    $mform = new Product_Form($url, array('what' => 'edit', 'catalog' => $thecatalog));
    $itemrec = $item->record;
    $itemrec->itemid = $itemid;
    $mform->set_data($itemrec);
} else {
    $item = new CatalogItem(null);
    $mform = new Product_Form($url, array('what' => 'add', 'catalog' => $thecatalog));
    $itemrec = $item->record;
    $itemrec->categoryid = optional_param('categoryid', 0, PARAM_INT);
    $mform->set_data($itemrec);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
}

if ($data = $mform->get_data()) {

    $data->enablehandler = optional_param('enablehandler', 0, PARAM_TEXT);

    $data->id = $data->itemid;
    $data->catalogid = $thecatalog->id;

    $data->description = $data->description_editor['text'];
    $data->descriptionformat = $data->description_editor['format'];

    $data->notes = $data->notes_editor['text'];
    $data->notesformat = $data->notes_editor['format'];

    $data->eula = $data->eula_editor['text'];
    $data->eulaformat = $data->eula_editor['format'];

    if (empty($data->renewable)) {
        $data->renewable = 0;
    }

    if (empty($data->itemid)) {

        $data->shortname = CatalogItem::compute_item_shortname($data);

        $data->id = $DB->insert_record('local_shop_catalogitem', $data);

        // We have items in the set. update relevant products.
        $productsinset = optional_param('productsinset', array(), PARAM_INT);
        if (is_array($productsinset)) {
            foreach ($productsinset as $productid) {
                $record = new StdClass;
                $record->id = $productid;
                $record->setid = $newid;
                $DB->update_record('local_shop_catalogitem', $record);
            }
        }
        // If slave catalogue must insert a master copy.
        if ($thecatalog->isslave) {
            $data->catalogid = $thecatalog->groupid;
            $DB->insert_record('local_shop_catalogitem', $data);
        }
    } else {
        $data->id = $data->itemid;

        // If product code as changed, we'd better recompute a new shortname.
        if (empty($data->shortname) ||
                ($data->code != $DB->get_field('local_shop_catalogitem', 'code', array('id' => $data->id)))) {
            $data->shortname = CatalogItem::compute_item_shortname($data);
        }

        $DB->update_record('local_shop_catalogitem', $data);
    }

    // Process text fields from editors.
    $draftideditor = file_get_submitted_draft_itemid('description_editor');
    $data->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemdescription',
                                                    $data->id, array('subdirs' => true), $data->description);
    $data = file_postupdate_standard_editor($data, 'description', $mform->editoroptions, $context, 'local_shop',
                                            'catalogitemdescription', $data->id);

    $draftideditor = file_get_submitted_draft_itemid('notes_editor');
    $data->notes = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemnotes',
                                              $data->id, array('subdirs' => true), $data->notes);
    $data = file_postupdate_standard_editor($data, 'notes', $mform->editoroptions, $context, 'local_shop',
                                            'catalogitemnotes', $data->id);

    $draftideditor = file_get_submitted_draft_itemid('eula_editor');
    $data->eula = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemeula',
                                             $data->id, array('subdirs' => true), $data->eula);
    $data = file_postupdate_standard_editor($data, 'eula', $mform->editoroptions, $context, 'local_shop',
                                            'catalogitemeula', $data->id);

    shop_products_process_files($data, $context, $usercontext);

    redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();