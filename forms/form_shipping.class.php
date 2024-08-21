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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * A form to associate products to shipping zones.
 */
class ProductShipping_Form extends moodleform {

    /**
     * Standard definition
     */
    public function definition() {
        global $OUTPUT;

        $codeattributes = 'size="10" maxlength="10"';

        // Setting variables.
        $mform =& $this->_form;

        // Title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'shipping', 'local_shop')));

        $mform->addElement('select', 'productcode', get_string('productcode', 'local_shop'), $this->_customdata['products']);
        $mform->addRule('productcode', null, 'required');

        $mform->addElement('select', 'zoneid', get_string('shippingzone', 'local_shop'), $this->_customdata['shippingzones']);
        $mform->addRule('zoneid', null, 'required');

        $mform->addElement('text', 'value', get_string('shippingfixedvalue', 'local_shop'));
        $mform->setType('value', PARAM_TEXT);
        $mform->addHelpButton('value', 'shippingfixedvalue', 'local_shop');

        $mform->addElement('text', 'formula', get_string('formula', 'local_shop'));
        $mform->setAdvanced('formula');
        $mform->setType('formula', PARAM_TEXT);
        $mform->addHelpButton('formula', 'formula', 'local_shop');

        $mform->addElement('text', 'a', get_string('param_a', 'local_shop'), $codeattributes);
        $mform->setAdvanced('a');
        $mform->setType('a', PARAM_NUMBER);

        $mform->addElement('text', 'b', get_string('param_b', 'local_shop'), $codeattributes);
        $mform->setAdvanced('b');
        $mform->setType('b', PARAM_NUMBER);

        $mform->addElement('text', 'c', get_string('param_c', 'local_shop'), $codeattributes);
        $mform->setAdvanced('c');
        $mform->setType('c', PARAM_NUMBER);

        // Adding submit and reset button.

        $this->add_action_buttons(true);
    }

    /**
     * Quickform Freeze wrapper
     * @param string $field
     */
    public function freeze($field) {
        $mform = $this->_form;
        $mform->freeze($field);
    }
}
