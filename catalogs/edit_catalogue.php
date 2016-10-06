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
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');
    $controller = new catalog_controller();
    $controller->process($action);
}

$catalogid = optional_param('catalogid', 0, PARAM_INT);

// Make page header and navigation.

$url = new moodle_url('/local/shop/catalogs/edit_catalog.php', array('catalogid' => $catalogid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('catalogs', 'local_shop'));

if ($catalogid) {
    $catalog = $DB->get_record('local_shop_catalog', array('id' => $catalogid));
    $mform = new Catalog_Form('', array('what' => 'edit'));
} else {
    $mform = new Catalog_Form('', array('what' => 'add'));
}

$catalog = new Catalog($catalogid);
$formdata = $catalog->record;
$formdata->catalogid = $catalog->id;
$formdata->id = $theshop->id;
$formdata->blockid = 0 + @$theblock->instance->id;
$mform->set_data($formdata);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/index.php'));
}

if ($data = $mform->get_data()) {

    unset($data->id); // Shop reference cannot be record id.

    $data->descriptionformat = $data->description_editor['format'];
    $data->description = $data->description_editor['text'];
    $data->salesconditionsformat = $data->salesconditions_editor['format'];
    $data->salesconditions = $data->salesconditions_editor['text'];

    if (empty($data->catalogid)) {
        // Creating new.
        $data->groupid = 0;
        $data->id = $DB->insert_record('local_shop_catalog', $data);
        if ($data->linked == 'master') {
            $DB->set_field('local_shop_catalog', 'groupid', $data->id, array('id' => $data->id));
        } else if ($data->linked == 'slave') {
            $DB->set_field('local_shop_catalog', 'groupid', $data->id, array('id' => $data->groupid));
        }
    } else {
        // Updating.
        $data->id = $data->catalogid;
        // We need to release all old slaves if this catalog changes from master to standalone.
        if ($oldcatalog = $DB->get_record('local_shop_catalog', array('id' => $data->id))) {
            if (($oldcatalog->id == $oldcatalog->groupid) && $data->linked != 'master') {
                /*
                 * We are dismitting as master catalog. All slaves should be released.
                 * get all slaves but not me
                 * TODO : may have further side effects, but we'll see later.
                 */
                $select = "
                    groupid = ? AND
                    groupid != id
                ";
                if ($oldslaves = $DB->get_records_select('local_shop_catalog', $select, array($oldcatalog->id))) {
                    foreach ($oldslaves as $oldslave) {
                        $oldslave->groupid = 0;
                        $DB->update_record('local_shop_catalog', $oldslave);
                    }
                }
            }
        }
        $updateid = $DB->update_record('local_shop_catalog', $data);

        if ($data->linked == 'master') {
            $DB->set_field('local_shop_catalog', 'groupid', $updateid, array('id' => $updateid));
        }

    }

    // Process text fields from editors.
    $draftideditor = file_get_submitted_draft_itemid('description_editor');
    $data->description = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogdescription',
                                                    $data->id, array('subdirs' => true), $data->description);
    $data = file_postupdate_standard_editor($data, 'description', $mform->editoroptions, $context, 'local_shop',
                                            'requirementdescription', $data->id);

    $draftideditor = file_get_submitted_draft_itemid('salesconditions_editor');
    $data->salesconditions = file_save_draft_area_files($draftideditor, $context->id, 'local_shop', 'catalogsalesconditions',
                                                        $data->id, array('subdirs' => true), $data->salesconditions);
    $data = file_postupdate_standard_editor($data, 'description', $mform->editoroptions, $context, 'local_shop',
                                            'requirementsalesconditions', $data->id);

    redirect(new moodle_url('/local/shop/index.php'));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();