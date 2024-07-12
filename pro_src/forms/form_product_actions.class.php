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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/lib/formslib.php');

class Purchase_Action_Date_Form extends moodleform {

    public function definition() {

        // Setting variables.

        $mform =& $this->_form;

        $mform->addElement('static', 'instances', get_string('instances', 'local_shop'), implode(', ', $this->_customdata['instancenames']));

        $options = [
            'startyear' => date('Y', time()) - 2,
            'endyear' => date('Y', time()) + 10,
            'step' => 1,
            'optional' => 0
        ];
        $mform->addElement('date_time_selector', $this->_customdata['date'], get_string($this->_customdata['date'], 'local_shop'), $options);

        $this->add_action_buttons();
    }

}

class Purchase_Action_Text_Form extends moodleform {

    public function definition() {

        // Setting variables.

        $mform =& $this->_form;

        $mform->addElement('static', 'instances', get_string('instances', 'local_shop'), implode(', ', $this->_customdata['instancenames']));

        $options = [
            'size' => 180
        ];
        $mform->addElement('text', $this->_customdata['text'], get_string($this->_customdata['text'], 'local_shop'), $options);
        $mform->setType($this->_customdata['text'], PARAM_TEXT);

        $this->add_action_buttons();
    }

}

class Purchase_Action_Metadata_Form extends moodleform {

    public function definition() {

        // Setting variables.

        $mform =& $this->_form;

        $mform->addElement('static', 'instances', get_string('instances', 'local_shop'), implode(', ', $this->_customdata['instancenames']));

        $options = [
            'extradata' => get_string('extradata', 'local_shop'),
            'productiondata' => get_string('extradata', 'local_shop')
        ];
        $mform->addElement('select', 'type', get_string('type', 'local_shop'), $options);

        $options = [
            'size' => 180
        ];
        $mform->addElement('text', 'name', get_string('name', 'local_shop'), $options);
        $mform->setType('name', PARAM_TEXT);

        $options = [
            'size' => 180
        ];
        $mform->addElement('text', 'value', get_string('value', 'local_shop'), $options);
        $mform->setType('value', PARAM_TEXT);

        $this->add_action_buttons();
    }

}