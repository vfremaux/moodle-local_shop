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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class Partner_Form extends moodleform {

    public function definition() {
        global $OUTPUT, $DB;

        $attributes = 'size="47" maxlength="200"';

        $mform =& $this->_form;

        // Title and description.
        $mform->addElement('hidden', 'id'); // Current shop ID.
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'partnerid'); // Partner ID.
        $mform->setType('partnerid', PARAM_INT);

        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'partner', 'local_shop')), $attributes);

        $mform->addElement('text', 'name', get_string('partnername', 'local_shop'), $attributes);
        $mform->addRule('name', null, 'required');
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'partnerkey', get_string('partnerkey', 'local_shop'), array('size' => 16, 'maxlength' => 16));
        $mform->addRule('partnerkey', null, 'required');
        $mform->setType('partnerkey', PARAM_TEXT);

        $mform->addElement('text', 'referer', get_string('referer', 'local_shop'), array('size' => 40, 'maxlength' => 255));
        $mform->addHelpButton('referer', 'referer', 'local_shop');
        $mform->setType('referer', PARAM_TEXT);

        $mform->addElement('text', 'partnersecretkey', get_string('partnersecretkey', 'local_shop'), array('size' => 32, 'maxlength' => 32));
        $mform->setType('partnersecretkey', PARAM_TEXT);

        $customers = $DB->get_records('local_shop_customer');
        if ($customers) {
            $customersmenu = ['' => get_string('unset', 'local_shop')];
            foreach ($customers as $cid => $c) {
                $customersmenu[$cid] = $c->lastname.' '.$c->firstname;
            }
            $mform->addElement('select', 'customerid', get_string('customerid', 'local_shop'), $customersmenu);
            $mform->setType('customerid', PARAM_TEXT);
            $mform->addHelpButton('customerid', 'partnercustomerid', 'local_shop');
        } else {
            $mform->addElement('hidden', 'customerid', 0);
        }

        $users = $DB->get_records('user', ['deleted' => 0, 'suspended' => 0], 'lastname, firstname', 'id, firstname, lastname');
        $usernames = array();
        foreach ($users as $uid => $u) {
            $usernames[$uid] = $u->lastname.' '.$u->firstname;
        }
        $options = array(
            'multiple' => false,
            'noselectionstring' => get_string('unset', 'local_shop'),
        );
        $mform->addElement('autocomplete', 'moodleuser', get_string('moodleuser', 'local_shop'), $usernames, $options);
        $mform->addHelpButton('moodleuser', 'moodleuser', 'local_shop');
        $mform->setType('moodleuser', PARAM_INT);

        $mform->addElement('checkbox', 'enabled', get_string('partnerenabled', 'local_shop'));
        $mform->setType('enabled', PARAM_BOOL);

        $this->add_action_buttons();
    }

    public function validation($data, $files = array()) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (empty($data['partnerid'])) {
            if (empty($data['name'])) {
                $errors['name'] = get_string('erroremptypartnername', 'local_shop');
            }

            if ($DB->record_exists('local_shop_partner', array('name' => $data['name']))) {
                $errors['name'] = get_string('partnernameexists', 'local_shop');
            }
        }

        return $errors;
    }
}