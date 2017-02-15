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

$setid = optional_param('setid', '', PARAM_INT);

// Security.
$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/products/edit_set.php', array('setid' => $setid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($setid) {
    $set = new CatalogItem($setid);
    $mform = new Set_Form('', array('what' => 'edit', 'catalog' => $thecatalog));
    $set->record->setid = $setid;
    unset($set->record->id);
    $mform->set_data($set->record);
} else {
    $set = new CatalogItem(null);
    $mform = new Set_Form('', array('what' => 'add', 'catalog' => $thecatalog));
    $setrec = $set->record;
    $setrec->categoryid = optional_param('categoryid', 0, PARAM_INT);
    $mform->set_data($setrec);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
}

if ($data = $mform->get_data()) {

    $data->catalogid = $thecatalog->id;
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
        $productsinset = optional_param('productsinset', array(), PARAM_INT);
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
            $data->catalogid = $thecatalog->groupid;
            $DB->insert_record('local_shop_catalogitem', $data);
        }
    } else {
        $data->id = $data->setid;
        // We must care that setid designates both local form Setid and Catalogitem setid field.
        $data->setid = 0;

        // If set code as changed, we'd better recompute a new shortname.
        if (empty($data->shortname) ||
                ($data->code != $DB->get_field('local_shop_catalogitem', 'code', array('id' => $data->id)))) {
            $data->shortname = CatalogItem::compute_item_shortname($data);
        }

        if (!$data->id = $DB->update_record('local_shop_catalogitem', $data)) {
            print_error('errorupdateset', 'local_shop');
        }
    }

    // Process text fields from editors.
    $draftideditor = file_get_submitted_draft_itemid('description_editor');
    $data->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogitemdescription',
                                                    $data->id, array('subdirs' => true), $data->description);
    $data = file_postupdate_standard_editor($data, 'description', $mform->editoroptions, $context, 'local_shop',
                                            'catalogitemdescription', $data->id);

    $usercontext = context_user::instance($USER->id);
    shop_products_process_files($data, $context, $usercontext);

    redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();