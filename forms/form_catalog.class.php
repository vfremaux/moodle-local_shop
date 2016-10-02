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
 * Defines form to add or edit a catalog
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @caopyright  (C) 2016 Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

// Security.

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class Catalog_Form extends moodleform {

    protected $editoroptions;

    public function definition() {
        global $OUTPUT, $DB, $COURSE;

        $context = context_system::instance();

        $maxfiles = 99;                // TODO: add some setting.
        $maxbytes = $COURSE->maxbytes; // TODO: add some setting.
        $this->editoroptions = array('trusttext' => true,
                                     'subdirs' => false,
                                     'maxfiles' => $maxfiles,
                                     'maxbytes' => $maxbytes,
                                     'context' => $context);

        $mform =& $this->_form;

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'catalog', 'local_shop')));

        // Adding fieldset.
        $attributes = 'size="50" maxlength="255"';

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'catalogid');
        $mform->setType('catalogid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name', 'local_shop'), $attributes);
        $mform->addRule('name', null, 'required');
        $mform->setType('name', PARAM_TEXT);

        $label = get_string('description', 'local_shop');
        $mform->addElement('editor', 'description_editor', $label, $this->editoroptions);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');
        $mform->addRule('description_editor', null, 'required');

        $label = get_string('salesconditions', 'local_shop');
        $mform->addElement('editor', 'salesconditions_editor', $label, $this->editoroptions);

        $label = get_string('countrycodelist', 'local_shop');
        $mform->addElement('text', 'countryrestrictions', $label, $attributes);
        $mform->addHelpButton('countryrestrictions', 'countryrestrictions', 'local_shop');
        $mform->setType('countryrestrictions', PARAM_TEXT);

        // Add catalog mode settings.

        $sql = "
           SELECT DISTINCT
              ci.*
           FROM
              {local_shop_catalog} as ci
           WHERE
             ci.id = ci.groupid
        ";
        $mastercatalogoptions = array();
        if ($mastercatalogs = $DB->get_records_sql($sql)) {
            foreach ($mastercatalogs as $acat) {
                $mastercatalogoptions[$acat->id] = $acat->name;
            }
        }

        $linkedarray = array();
        $linkedarray[] = &$mform->createElement('radio', 'linked', '', get_string('standalone', 'local_shop'), 'free');
        $linkedarray[] = &$mform->createElement('radio', 'linked', '', get_string('master', 'local_shop'), 'master');
        if (!empty($mastercatalogoptions)) {
            $linkedarray[] = &$mform->createElement('radio', 'linked', '', get_string('slaveto', 'local_shop'), 'slave');
            $linkedarray[] = &$mform->createElement('select', 'groupid', '', $mastercatalogoptions);
        }
        $mform->addGroup($linkedarray, 'linkedarray', '', array(' '), false);
        $mform->setDefault('linked', 'free');

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    public function set_data($defaults) {

        $context = context_system::instance();

        $draftideditor = file_get_submitted_draft_itemid('description_editor');
        $currenttext = file_prepare_draft_area($draftideditor, $context->id, 'local_shop', 'description_editor', $defaults->id,
                                               array('subdirs' => true), $defaults->description);
        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop',
                                                 'catalogdescription', $defaults->id);
        $defaults->description_editor = array('text' => $currenttext,
                                              'format' => $defaults->descriptionformat,
                                              'itemid' => $draftideditor);

        $draftideditor = file_get_submitted_draft_itemid('salesconditions_editor');
        $currenttext = file_prepare_draft_area($draftideditor, $context->id, 'local_shop', 'salesconditions_editor', $defaults->id,
                                               array('subdirs' => true), $defaults->salesconditions);
        $defaults = file_prepare_standard_editor($defaults, 'salesconditions', $this->editoroptions, $context, 'local_shop',
                                                 'catalogsalesconditions', $defaults->id);
        $defaults->salesconditions_editor = array('text' => $currenttext,
                                                  'format' => $defaults->salesconditionsformat,
                                                  'itemid' => $draftideditor);

        parent::set_data($defaults);
    }
}