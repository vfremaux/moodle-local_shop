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
 * @reviewer   Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class Category_Form extends moodleform {

    public $editoroptions;

    public function definition() {
        global $COURSE, $OUTPUT;

        $attributes = array('size' => 47,  'maxlength' => 200);

        $context = context_system::instance();

        $maxfiles = 99;                // TODO: add some settings.
        $maxbytes = $COURSE->maxbytes; // TODO: add some settings.
        $this->editoroptions = array('trusttext' => true,
                                     'subdirs' => false,
                                     'maxfiles' => $maxfiles,
                                     'maxbytes' => $maxbytes,
                                     'context' => $context);

        $mform =& $this->_form;

        $mform->addElement('hidden', 'catalogid');
        $mform->setType('catalogid', PARAM_INT);

        $mform->addElement('hidden', 'categoryid');
        $mform->setType('categoryid', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'category', 'local_shop')));

        $mform->addElement('text', 'name', get_string('name'), '', $attributes);
        $mform->setType('name', PARAM_TEXT);

        if (!empty($this->_customdata['parents'])) {
            $mform->addElement('select', 'parentid', get_string('parentcategory', 'local_shop'), $this->_customdata['parents']);
        }

        // Title and description.
        $mform->addElement('editor', 'description_editor', get_string('description'), null, $this->editoroptions);
        $mform->addHelpButton('description_editor', 'description', 'local_shop');

        $yesnooptions = array('0' => get_string('no'), '1' => get_string('yes'));
        $mform->addElement('select', 'visible', get_string('visible'), $yesnooptions);
        $mform->setDefault('visible', 1);

        // Informations required.
        $mform->addRule('name', null, 'required');

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    public function set_data($defaults) {

        $context = context_system::instance();

        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop',
                                                 'categorydescription', @$defaults->categoryid);

        parent::set_data($defaults);
    }
}