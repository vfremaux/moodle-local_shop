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
 * Defines form to add a new billitem
 *
 * @package    local_shop
 * @category   local
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Customer;

class Bill_Form extends moodleform {

    public function definition() {
        global $OUTPUT, $DB;

        $strrequired = get_string('required', 'local_shop');

        $config = get_config('local_shop');

        // Setting variables.
        $mform =& $this->_form;

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'bill', 'local_shop')));

        $attributes = 'size="47" maxlength="200"';
        $attributesshort = 'size="30" maxlength="200"';
        $attributesjscustomer = 'onchange = listClear(document.getElementById(\'id_useraccountid\'))';
        $attributesjsuser = 'onchange = listClear(document.getElementById(\'id_userid\'))';

        // Adding fieldset.
        $mform->addElement('hidden', 'billid');
        $mform->setType('billid', PARAM_INT);

        $mform->addElement('text', 'title', get_string('billtitle', 'local_shop'), $attributesshort);
        $mform->setType('title', PARAM_TEXT);

        $customers = Customer::get_instances();

        // Getting the full name of customers.
        $fullnamecustoselect = array();
        $fullnamecustoselect['0'] = get_string('choosecustomer', 'local_shop');

        foreach ($customers as $customer) {
            $fullnamecustoselect[$customer->id] = $customer->lastname.' '.$customer->firstname;
        }

        // Select user whithout customer account.
        $sqluser = "
            SELECT
                u.id,
                ".get_all_user_name_fields(true, 'u')."
            FROM
                {user} AS u
            WHERE
                u.id NOT IN (SELECT hasaccount FROM {local_shop_customer} )
        ";
        $users = $DB->get_records_sql($sqluser);

        // Getting the full names.
        $fullnameuserselect = array();
        $fullnameuserselect['0'] = get_string('chooseuser', 'local_shop');
        foreach ($users as $user) {
            $fullnameuserselect[$user->id] = fullname($user);
        }

        // Set default for user select.
        $userarray = array();
        $label = get_string('customers', 'local_shop');
        $userarray[] = &$mform->createElement('select', 'userid', $label, $fullnamecustoselect, $attributesjscustomer);
        $label = get_string('users');
        $userarray[] = &$mform->createElement('select', 'useraccountid', $label, $fullnameuserselect, $attributesjsuser);
        $orstr = get_string('or', 'local_shop');
        $mform->addGroup($userarray, 'selectar', get_string('pickuser', 'local_shop'), '&nbsp;'. $orstr.'&nbsp;', false);
        $mform->addHelpButton('selectar', 'customer_account', 'local_shop');

        $mform->addElement('editor', 'abstract', get_string('abstract', 'local_shop').':');
        $mform->setType('abstract', PARAM_CLEANHTML);

        $radioarray = array();
        $radioarray[] = &$mform->createElement('radio', 'ignoretax', '', get_string('yes'), 0, $attributes);
        $radioarray[] = &$mform->createElement('radio', 'ignoretax', '', get_string('no'), 1, $attributes);
        $mform->addGroup($radioarray, 'radioar', get_string('allowtax', 'local_shop').':', array(' '), false);
        $mform->addHelpButton('radioar', 'allowtax', 'local_shop');

        $context = context_system::instance();
        $billeditors = get_users_by_capability($context, 'block/shop:beassigned', 'u.id,'.get_all_user_name_fields(true, 'u'));
        $editoropt = array();
        if ($billeditors) {
            foreach ($billeditors as $billeditor) {
                $editoropt[$billeditor->id] = fullname($billeditor);
            }
        }
        $mform->addElement('select', 'assignedto', get_string('assignedto', 'local_shop').':', $editoropt);
        $mform->addHelpButton('assignedto', 'bill_assignation', 'local_shop');

        if ($this->_customdata['what'] == 'add') {
            $mform->addElement('hidden', 'status', 'WORKING');
        } else {
            $status = shop_get_bill_states();
            $mform->addElement('select', 'status', get_string('status', 'local_shop').':', $status);
        }
        $mform->setType('status', PARAM_TEXT);

        $worktypes = shop_get_bill_worktypes();
        $mform->addElement('select', 'worktype', get_string('worktype', 'local_shop').':', $worktypes);

        $currencies = shop_get_supported_currencies();
        $mform->addElement('select', 'currency', get_string('currency', 'local_shop').':', $currencies);
        $mform->addRule('currency', $strrequired, 'required', null, 'client');
        $mform->setDefault('currency', $config->defaultcurrency);

        $paymodes = shop_paymode::get_list();
        $mform->addElement('select', 'paymode', get_string('paymodes', 'local_shop').':', $paymodes);
        $mform->addRule('paymode', $strrequired, 'required', null, 'client');

        $mform->addElement('date_selector', 'timetodo', get_string('timetodo', 'local_shop'));

        $mform->addElement('date_selector', 'expectedpaiement', get_string('expectedpaiement', 'local_shop').':');

        // Adding submit and reset button.
        $this->add_action_buttons();
    }
}