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

class ResetForm extends moodleform {

    // Define the form
    function definition () {
        global $DB;

        $mform =& $this->_form;
        // Accessibility: "Required" is bad legend text.

        $shopoptions = array(0 => get_string('allshops', 'local_shop'));
        $shops = $DB->get_records_menu('local_shop', array(), 'name', 'id,name');
        if (!empty($shops)) {
            // Add identified shops.
            $shopoptions = array_merge($shopoptions, $shops);
        }

        $mform->addElement('header', 'header1', get_string('resetitems', 'local_shop'));

        $mform->addElement('select', 'shopid', get_string('shop', 'local_shop'), $shopoptions);
        $mform->setType('shopid', PARAM_INT);

        $mform->addElement('checkbox', 'bills', get_string('resetbills', 'local_shop'));

        $mform->addElement('checkbox', 'customers', get_string('resetcustomers', 'local_shop'));

        $mform->addElement('checkbox', 'catalogs', get_string('resetcatalogs', 'local_shop'));

        $mform->disabledIf('bills', 'customers', 'checked');
        $mform->disabledIf('bills', 'catalogs', 'checked');

        $this->add_action_buttons(true, get_string('reset', 'local_shop'));
    }
}