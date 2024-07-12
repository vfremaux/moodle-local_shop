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

class Product_Instance_Form extends moodleform {

    public function definition() {
        global $OUTPUT, $DB;

        // Setting variables.

        $mform =& $this->_form;

        $mform->addElement('hidden', 'instanceid');
        $mform->setType('instanceid', PARAM_INT);

        $mform->addElement('hidden', 'initialbillitemid');
        $mform->setType('initialbillitemid', PARAM_INT);

        $attributesspecificdata = 'rows="4" style="width:80%" ';
        $attributeshandlerparams = 'cols="50" rows="8" style="width:80%" ';

        $cmd = $this->_customdata['command'];
        $mform->addElement('header', 'productinstancehdr', get_string($cmd.'productinstance', 'local_shop'));

        // Choosing catalogitem.
        $mform->addElement('select', 'catalogitemid', get_string('catalogitem', 'local_shop'), $this->_customdata['catalogitems']);
        $mform->addRule('catalogitemid', '', 'required');

        $mform->addElement('select', 'customerid', get_string('customer', 'local_shop'), $this->_customdata['customers']);
        $mform->addRule('customerid', '', 'required');

        $mform->addElement('select', 'currentbillid', get_string('currentbill', 'local_shop'), $this->_customdata['bills']);

        $mform->addElement('select', 'currentbillitemid', get_string('currentbillitem', 'local_shop'), $this->_customdata['billitems']);
        $mform->disabledIf('curentbillitemid', 'currentbillid', 'eq', 0);

        $options = array(
            'startyear' => date('Y', time()) - 2,
            'endyear' => date('Y', time()) + 10,
            'step' => 1,
            'optional' => 0
        );
        $mform->addElement('date_time_selector', 'startdate', get_string('startdate', 'local_shop'), $options);

        $options = array(
            'startyear' => date('Y', time()) - 2,
            'endyear' => date('Y', time()) + 10,
            'step' => 1,
            'optional' => 1
        );
        $mform->addElement('date_time_selector', 'enddate', get_string('enddate', 'local_shop'), $options);

        $mform->addElement('text', 'reference', get_string('reference', 'local_shop'));
        $mform->setType('reference', PARAM_TEXT);

        $mform->addElement('checkbox', 'generatereference', get_string('generatereference', 'local_shop'));
        $mform->disabledIf('reference', 'generatereference', 'eq', 1);
        if ($cmd == 'edit') {
            $mform->setAdvanced('generatereference');
        }

        $mform->addElement('select', 'contexttype', get_string('contexttype', 'local_shop'), $this->_customdata['contexttypes']);

        $mform->addElement('text', 'contextinstanceid', get_string('instance', 'local_shop'));
        $mform->setType('contextinstanceid', PARAM_INT);

        $group = [];
        $group[] = & $mform->createElement('textarea', 'productiondata', '', ['cols' => 120, 'rows' => 6, 'data-format' => 'url', 'data-edithandle' => 'id_editproductiondata']);
        $mform->setType('productiondata', PARAM_TEXT);
        $group[] = & $mform->createElement('button', 'editproductiondata', get_string('edit', 'local_shop'));
        $mform->addGroup($group, 'grpproductiondata', get_string('productiondata', 'local_shop'), '', false);
        $mform->setAdvanced('grpproductiondata');

        $group = [];
        $group[] = & $mform->createElement('textarea', 'extradata', '', ['cols' => 120, 'rows' => 6, 'data-format' => 'json', 'data-edithandle' => 'id_editextradata']);
        $mform->setType('extradata', PARAM_TEXT);
        $group[] = & $mform->createElement('button', 'editextradata', get_string('edit', 'local_shop'));
        $mform->addGroup($group, 'grpextradata', get_string('extradata', 'local_shop'), '', false);
        $mform->setAdvanced('grpextradata');

        $mform->addElement('checkbox', 'test', get_string('testproduct', 'local_shop'));
        $mform->setAdvanced('test');

        $this->add_action_buttons();
    }

}