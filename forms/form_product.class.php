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
 * @package    local_shop
 * @category   local
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalogitem.class.php');

use local_shop\Tax;

class Product_Form extends catalogitemform {

    public $editoroptions;
    public $attributesshort;

    function definition() {
        global $CFG, $DB, $OUTPUT, $COURSE;

        $config = get_config('local_shop');

        if (!$this->_customdata['catalog']->isslave) {

            $select = " 
                catalogid = ? AND 
                (isset = 1 OR isset = 2) 
            ";
            $sets = $DB->get_records_select('local_shop_catalogitem', $select, array($this->_customdata['catalog']->id), 'id, name');
        }

        // Setting variables

        $mform =& $this->_form;

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        // Adding title and description
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'product', 'local_shop')));

        $mform->addElement('header', 'h0', get_string('general'));

        $context = context_system::instance();

        $maxfiles = 99;                // TODO: add some setting
        $maxbytes = $COURSE->maxbytes; // TODO: add some setting
        $this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $context);

        // Adding fieldset
        $attributes = 'size="50" maxlength="200"';
        $this->attributesshort = 'size="24" maxlength="24"';
        $attributeslong = 'size="60" maxlength="255"';
        $attributes_description = 'cols="50" rows="8"';
        $attributes_specificdata = 'rows="4" style="width:80%" ';
        $attributes_handlerparams = 'cols="50" rows="8" style="width:80%" ';

        $mform->addElement('text', 'code', get_string('code', 'local_shop'), $this->attributesshort);
        $mform->setType('code', PARAM_ALPHANUMEXT);
        $mform->addRule('code', null, 'required');

        $mform->addElement('text', 'name', get_string('name', 'local_shop'), $attributeslong);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('editor', 'description_editor', get_string('description'), null, $this->editoroptions);
        $mform->setType('description_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');

        if (!empty($config->multipleowners)) {
            $potentialowners = $DB->get_records_select('local_shop_customer', " hasaccount > 0 ", array(), 'hasaccount,firstname,lastname');

            $ownersmenu = array('' => get_string('sitelevel', 'local_shop'));
            if ($potentialowners) {
                foreach($potentialowners as $accountid => $owner) {
                    $ownersmenu[$accountid] = $owner->lastname.' '.$owner->firstname;
                }
            }

            $mform->addElement('select', 'userid', get_string('productowner', 'local_shop'), $ownersmenu);
            $mform->setType('userid', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'userid', 0);
            $mform->setType('userid', PARAM_INT);
        }

        $statusopts = shop_get_status();
        $mform->addElement('select', 'status', get_string('status', 'local_shop'), $statusopts);
        $mform->setType('status', PARAM_TEXT);

        $mform->addElement('header', 'h1', get_string('financials', 'local_shop'));

        $this->add_price_group();

        $this->add_tax_select();

        $mform->addElement('header', 'h2', get_string('behaviour', 'local_shop'));

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

        if (!$this->_customdata['catalog']->isslave) {
            $setopts[0] = get_string('outofset', 'local_shop');
            if (!empty($sets)) {
                foreach ($sets as $set) {
                    $setopts[$set->id] = $set->name;
                }
            }
            $mform->addElement('select', 'setid', get_string('set', 'local_shop'), $setopts);
        }
        $group = array();
        $group[] = &$mform->createElement('checkbox', 'showsnameinset', '', get_string('shownameinset', 'local_shop'));
        $mform->setDefault('showsnameinset', 1);
        $group[] = &$mform->createElement('checkbox', 'showsdescriptioninset', '', get_string('showdescriptioninset', 'local_shop'));
        $mform->setDefault('showsdescriptioninset', 1);
        $mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);

        $mform->addElement('header', 'h3', get_string('assets', 'local_shop'));

        $this->add_document_assets();

        $mform->addElement('editor', 'notes_editor', get_string('notes', 'local_shop'), null, $this->editoroptions);
        $mform->setType('notes_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('notes_editor', 'description', 'local_shop');

        $mform->addElement('header', 'h4', get_string('automation', 'local_shop'));

        // This may need to be translated for localised catalogs.
        $mform->addElement('textarea', 'requireddata', get_string('requireddata', 'local_shop'), $attributes_specificdata);
        $mform->setType('requireddata', PARAM_TEXT);
        $mform->addHelpButton('requireddata', 'requireddata', 'local_shop');

        if (!$this->_customdata['catalog']->isslave) {
            $handleropts['0'] = get_string('disabled', 'local_shop');
            $handleropts['1'] = get_string('dedicated', 'local_shop');
            $handleropts = array_merge($handleropts, shop_get_standard_handlers_options());

            $mform->addElement('select', 'enablehandler', get_string('enablehandler', 'local_shop'), $handleropts);
            $mform->setType('enablehandler', PARAM_TEXT);

            $mform->addElement('textarea', 'handlerparams', get_string('handlerparams', 'local_shop'), $attributes_handlerparams);
            $mform->setType('handlerparams', PARAM_TEXT);
            $mform->addHelpButton('handlerparams', 'handlerparams', 'local_shop');

            $seatmodeoptions[SHOP_QUANT_NO_SEATS] = get_string('no');
            $seatmodeoptions[SHOP_QUANT_ONE_SEAT] = get_string('oneseat', 'local_shop');
            $seatmodeoptions[SHOP_QUANT_AS_SEATS] = get_string('yes');
            $mform->addElement('select', 'quantaddressesusers', get_string('quantaddressesusers', 'local_shop'), $seatmodeoptions);
            $mform->setType('quantaddressesusers', PARAM_INT);
            $mform->addHelpButton('quantaddressesusers', 'quantaddressesusers', 'local_shop');

            $mform->addElement('checkbox', 'renewable', get_string('renewable', 'local_shop'));
            $mform->addHelpButton('renewable', 'renewable', 'local_shop');
            $mform->disabledIf('renewable', 'enablehandler', 'eq', 0);
        } else {
            $mform->addelement('hidden', 'enablehandler');
            $mform->setType('enablehandler', PARAM_TEXT);
            $mform->addelement('hidden', 'handlerparams');
            $mform->setType('handlerparams', PARAM_TEXT);
            $mform->addelement('hidden', 'quantaddressesusers');
            $mform->setType('quantaddressesusers', PARAM_INT);
            $mform->addelement('hidden', 'renewable');
            $mform->setType('renewable', PARAM_BOOL);
        }

        // Adding submit and reset button
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        $mform->closeHeaderBefore('buttonar');
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