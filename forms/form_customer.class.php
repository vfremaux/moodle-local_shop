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
 * A form to edt customer accounts
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Shop;
use local_shop\Catalog;

/**
 * A form to edt customer accounts
 */
class Customer_Form extends moodleform {

    public function definition() {
        global $CFG, $OUTPUT;

        include($CFG->dirroot.'/local/shop/country.php');

        // Setting variables.
        $mform =& $this->_form;

        $mform->addElement('hidden', 'customerid');
        $mform->setType('customerid', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'customer', 'local_shop')));

        // Adding fieldset.
        $attributes = 'size="45" maxlength="200"';

        $mform->addElement('text', 'firstname', get_string('customerfirstname', 'local_shop'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', null, 'required');

        $mform->addElement('text', 'lastname', get_string('customerlastname', 'local_shop'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', null, 'required');

        $mform->addElement('text', 'address', get_string('address', 'local_shop'), $attributes);
        $mform->setType('address', PARAM_TEXT);
        $mform->addRule('address', null, 'required');

        $mform->addElement('text', 'zip', get_string('zip', 'local_shop'));
        $mform->setType('zip', PARAM_TEXT);
        $mform->addRule('zip', null, 'required');

        $mform->addElement('text', 'city', get_string('city', 'local_shop'));
        $mform->setType('city', PARAM_TEXT);
        $mform->addRule('city', null, 'required');

        $mform->addElement('text', 'email', get_string('email', 'local_shop'), $attributes);
        $mform->setType('email', PARAM_TEXT);
        $mform->addRule('email', null, 'required');

        $choices = get_string_manager()->get_list_of_countries();
        Catalog::process_merged_country_restrictions($choices);
        $choices = array_merge(array('' => get_string('selectacountry').'...'), $choices);

        $mform->addElement('select', 'country', get_string('taxcountry', 'local_shop'), $choices);
        $mform->addRule('country', null, 'required');

        $mform->addElement('text', 'organisation', get_string('organisation', 'local_shop'));
        $mform->setType('organisation', PARAM_TEXT);

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    /**
     * Standard Validation
     */
    public function validation($data, $files = []) {
        global $DB;

        $errors = parent::validation($data, $files);

        $select = ' email = ? AND id != ? ';
        if ($DB->record_exists_select('local_shop_customer', $select, ['id' => $data->customerid ,'email' => $data['email']])) {
            $errors['email'] = get_string('erroremailexists', 'local_shop');
        }

        /**
         * @todo : Build a stronger mail format validation pattern.
         */
        if (!preg_match('/.+@.+/', $data['email'])) {
            $errors['email'] = get_string('errornotanemail', 'local_shop');
        }

        return $errors;
    }
}
