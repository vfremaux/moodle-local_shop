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
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/country.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Tax;

class ShippingZone_Form extends moodleform {

    public function definition() {
        global $OUTPUT;

        $codeattributes = 'size="10" maxlength="10"';
        $applicattributes = 'size="50"';

        // Setting variables.
        $mform =& $this->_form;

        $mform->addElement('hidden', 'zoneid'); // Shipzone ID.
        $mform->setType('zoneid', PARAM_INT);

        // Title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'shippingzone', 'local_shop')));

        // Shipzone code.
        $mform->addElement('text', 'zonecode', get_string('zonecode', 'local_shop'), $codeattributes);
        $mform->addRule('zonecode', null, 'required');
        $mform->setType('zonecode', PARAM_TEXT);

        // Shipzone description.
        $mform->addElement('text', 'description', get_string('description', 'local_shop'));
        $mform->addRule('description', null, 'required');
        $mform->setType('description', PARAM_TEXT);

        // Bill scope amount when bill applied.
        $mform->addElement('text', 'billscopeamount', get_string('billscopeamount', 'local_shop')); 
        $mform->setType('billscopeamount', PARAM_TEXT);

        // Bill scope amount when bill applied.
        $taxes = Tax::get_instances();
        $taxoptions = array();
        foreach ($taxes as $t) {
            $taxoptions[$t->id] = $t->title;
        }
        $mform->addElement('select', 'taxid', get_string('tax', 'local_shop'), $taxoptions);

        // The formula used to check application of shipping zone.
        $mform->addElement('text', 'applicability', get_string('applicability', 'local_shop'), $applicattributes);
        $mform->setType('applicability', PARAM_TEXT);

        // Adding submit and reset button.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}