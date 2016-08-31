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
 * Defines form to add a new project
 *
 * @package    local_shop
 * @category   local
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalogitem.class.php');

use local_shop\Tax;

class Bundle_Form extends catalogitemform {

    public $editoroptions;
    public $attributesshort;

    function definition() {
        global $CFG, $OUTPUT, $COURSE, $DB;

        // Setting variables
        $mform =& $this->_form;

        $context = context_system::instance();

        $maxfiles = 99;                // TODO: add some setting
        $maxbytes = $COURSE->maxbytes; // TODO: add some setting
        $this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $context);

        // Adding title and description
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'bundle', 'local_shop')));

        $mform->addElement('hidden', 'bundleid');
        $mform->setType('bundleid', PARAM_INT);

        // Adding fieldset
        $attributes = 'size="50" maxlength="200"';
        $this->attributesshort = 'size="24" maxlength="24"';
        $attributes_description = 'cols="50" rows="8"';
        $fpickerattributes = array('maxbytes' => $COURSE->maxbytes, 'accepted_types' => array('.jpg', '.gif', '.png'));

        $mform->addElement('text', 'code', get_string('code', 'local_shop'), $attributes);
        $mform->setType('code', PARAM_ALPHANUMEXT);
        $mform->addRule('code', null, 'required');

        $mform->addElement('text', 'name', get_string('name', 'local_shop'), $attributes);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('editor', 'description_editor', get_string('description'), null, $this->editoroptions);
        $mform->setType('description_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');

        $this->add_price_group();

        $this->add_tax_select();

        $this->add_sales_params();

        $this->add_target_market();

        //$group[] = &$mform->createElement('checkbox', 'showdescriptioninset', '', get_string('showdescriptioninset', 'local_shop'), 1);
        // $mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);


        if ($cats = $this->_customdata['catalog']->get_categories()) {
            foreach ($cats as $cat) {
                $sectionopts[$cat->id] = $cat->name;
            }
            $mform->addElement('select', 'categoryid', get_string('section', 'local_shop'), $sectionopts);
            $mform->setType('categoryid', PARAM_INT);
            $mform->addRule('categoryid', null, 'required');
        } else {
            $mform->addElement('static', 'nocats', get_string('nocats', 'local_shop'));
        }

        $this->add_document_assets();

        $mform->addElement('text', 'requireddata', get_string('requireddata', 'local_shop'), $attributes);
        $mform->setType('requireddata', PARAM_TEXT);

        $statusopts = shop_get_status();
        $mform->addElement('select', 'status', get_string('status', 'local_shop'), $statusopts);
        $mform->setType('status', PARAM_TEXT);

        $mform->addElement('editor', 'notes_editor', get_string('notes', 'local_shop'), null, $this->editoroptions);
        $mform->setType('notes_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('notes_editor', 'description', 'local_shop');

        /**
        if (!$this->catalog->isslave) {
            $setopts[0] = get_string('outofset', 'local_shop');
            if (!empty($sets)) {
                foreach ($sets as $set) {
                    $setopts[$set->id] = $set->name;
                }
            }

            $mform->addElement('select', 'setid', get_string('set', 'local_shop'), $setopts);
        }
        */

        $group = array();
        $group[] = &$mform->createElement('checkbox', 'shownameinset', '', get_string('shownameinset', 'local_shop'), 1);
        $group[] = &$mform->createElement('checkbox', 'showdescriptioninset', '', get_string('showdescriptioninset', 'local_shop'), 1);
        $mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);

        // Adding submit and reset button
        $this->add_action_buttons();
    }

    function set_data($defaults) {
        global $COURSE;

        $context = context_system::instance();

        $draftid_editor = file_get_submitted_draft_itemid('description_editor');
        $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'local_shop', 'description_editor', @$defaults->id, array('subdirs' => true), $defaults->description);
        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop', 'catalogdescription', @$defaults->id);
        $defaults->description_editor = array('text' => $currenttext, 'format' => $defaults->descriptionformat, 'itemid' => $draftid_editor);

        $draftid_editor = file_get_submitted_draft_itemid('notes_editor');
        $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'local_shop', 'notes_editor', @$defaults->id, array('subdirs' => true), $defaults->notes);
        $defaults = file_prepare_standard_editor($defaults, 'notes', $this->editoroptions, $context, 'local_shop', 'catalogitemnotes', @$defaults->id);
        $defaults->notes_editor = array('text' => $currenttext, 'format' => $defaults->notesformat, 'itemid' => $draftid_editor);

        $this->set_document_asset_data($defaults, $context);

        parent::set_data($defaults);
    }
}