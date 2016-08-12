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

use \local_shop\Shop;
use \local_shop\Catalog;

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_shop.class.php');
require_once($CFG->dirroot."/local/shop/classes/Shop.class.php");

$context = context_system::instance();
$PAGE->set_context($context);

$config = get_config('local_shop');

$id = optional_param('id', 0, PARAM_INT); // Shop current shop id 
$shopid = optional_param('shopid', 0, PARAM_INT); // Shop current shop id 
$url = new moodle_url('/local/shop/shop/edit_shop.php', array('id' => $id));

// Security.
require_login();
require_capability('local/shop:salesadmin', $context);

$shopid = optional_param('id', '', PARAM_INT);

// Make page header and navigation.

$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($shop = new Shop($shopid)) {
    $mform = new Shop_Form($url, array('what' => 'edit'));
    $shop->record->shopid = $shopid;
    $shop->record->id = $id;
    $mform->set_data($shop->record);
} else {
    $shop = new Shop();
    $mform = new Shop_Form($url, array('what' => 'add'));
    $shop->record->id = $id;// the current shopid
    $mform->set_data($shop->record);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/shop/view.php', array('view' => 'viewAllShops')));
}
if ($shoprec = $mform->get_data()) {

    Shop::compact_paymodes($shoprec);

    $shoprec->descriptionformat = $shoprec->description_editor['format'];
    $shoprec->description = $shoprec->description_editor['text'];

    $shoprec->eulaformat = $shoprec->eula_editor['format'];
    $shoprec->eula = $shoprec->eula_editor['text'];

    if (empty($shoprec->shopid)) {
        $DB->insert_record('local_shop', $shoprec);
    } else {
        $shoprec->id = $shoprec->shopid;
        $DB->update_record('local_shop', $shoprec);
    }

    // Process text fields from editors.
    $draftid_editor = file_get_submitted_draft_itemid('description_editor');
    $shoprec->description = file_save_draft_area_files($draftid_editor, $context->id, 'local_shop', 'description', $shoprec->id, array('subdirs' => true), $shoprec->description);
    $shoprec = file_postupdate_standard_editor($shoprec, 'description', $mform->editoroptions, $context, 'local_shop', 'description', $shoprec->id);

    $draftid_editor = file_get_submitted_draft_itemid('eula_editor');
    $shoprec->eula = file_save_draft_area_files($draftid_editor, $context->id, 'local_shop', 'eula', $shoprec->id, array('subdirs' => true), $shoprec->eula);
    $shoprec = file_postupdate_standard_editor($shoprec, 'eula', $mform->editoroptions, $context, 'local_shop', 'eula', $shoprec->id);

    // When we need to hace a complete developped view of shops in the future
    // redirect(new moodle_url('/local/shop/shop/view.php', array('view' => 'viewShop', 'shopid' => $shopid)));
    redirect(new moodle_url('/local/shop/shop/view.php', array('view' => 'viewAllShops')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();