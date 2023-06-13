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
 * @package   local_shop
 * @category  blocks
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/lib/formslib.php');

class AssignSeatForm extends moodleform {

    function definition() {
        global $USER, $OUTPUT;

        $mycontext = context_user::instance($USER->id);
        $mform = $this->_form;

        // Get users that i am behalfed on.
        $usermenu = [];
		$fields = \local_shop\compat::get_fields_for_get_cap();
        if ($myusers = get_users_by_capability($mycontext, 'block/user_delegation:hasasbehalf', $fields)) {
            foreach ($myusers as $uid => $u) {
                $usermenu[$uid] = fullname($u);
            }
        }

        // The course id
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'assignseatheader', get_string('assignavailableseat', 'shophandlers_std_generateseats'));

        $mform->addElement('static', 'assignseatinstr', '', get_string('assigninstructions', 'shophandlers_std_generateseats'));

        if (empty($usermenu)) {
            $msg = $OUTPUT->notification(get_string('enrolinstructions', 'shophandlers_std_generateseats'));
            $mform->addElement('static', 'nolearnersstr', '', $msg);
        } else {
            $mform->addElement('select', 'userid', get_string('user'), $usermenu);
            $mform->setType('userid', PARAM_INT);
        }

        $coursemenu = [];

        foreach ($this->_customdata['allowedcourses'] as $cid => $c) {
            $coursemenu[$cid] = $c->shortname.' '.$c->fullname;
        }
        $mform->addElement('select', 'courseid', get_string('course'), $coursemenu);
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();

        if (empty($usermenu)) {
            $mform->disabledIf('submitbutton', 'userid', 'noitemselected');
        }
    }

    function validation($data, $files = array()) {
    }
}