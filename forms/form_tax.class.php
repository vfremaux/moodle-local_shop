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
 * @author      Valery Fremaux <valery.fremaux@club-internet.fr>
 * @copyright   (C) 2016 Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/country.php');

class Tax_Form extends moodleform {

    public function definition() {
        global $CFG, $OUTPUT;

        $attributes = 'size="47" maxlength="200"';

        $mform =& $this->_form;

        // Title and description.
        $mform->addElement('hidden', 'taxid'); // Tax ID.
        $mform->setType('taxid', PARAM_INT);

        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'tax', 'local_shop')), $attributes);

        $mform->addElement('text', 'title', get_string('taxname', 'local_shop'), $attributes); // Tax name field.
        $mform->addRule('title', null, 'required');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('text', 'ratio', get_string('taxratio', 'local_shop')); // Tax ratio field.
        $mform->addRule('ratio', null, 'required');
        $mform->setType('ratio', PARAM_TEXT);

        $country = 'FR';
        $choices = get_string_manager()->get_list_of_countries();
        $choices = array('' => get_string('selectacountry').'...') + $choices;
        $mform->addElement('select', 'country', get_string('taxcountry', 'local_shop'), $choices);
        $mform->setType('country', PARAM_TEXT);

        $mform->addElement('text', 'formula', get_string('taxformula', 'local_shop'), $attributes); // Tax formula field.
        $mform->addHelpButton('formula', 'formula_creation', 'local_shop');
        $mform->setType('formula', PARAM_TEXT);

        // Adding submit and reset button.
        $mform->addElement('static', get_string('formulaexample', 'local_shop'));

        $this->add_action_buttons();
    }

    public function validation($data, $files = array()) {

        $errors = array();
        if (empty($data['title'])) {
            $errors['title'] = get_string('erroremptytaxtitle', 'local_shop');
        }

        if (empty($data['formula'])) {
            $errors['title'] = get_string('erroremptytaxformula', 'local_shop');
        }
    }
}