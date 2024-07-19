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
 *
 * Defines form to add a new shop
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

/**
 * A form class to edit shop intances.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class Shop_Form extends moodleform {

    /** @var array edition options for file managers */
    public $editoroptions;

    /**
     * Standard definition
     */
    public function definition() {
        global $DB;

        // Setting variables.
        $mform =& $this->_form;

        $config = get_config('local_shop');

        $this->editoroptions = ['maxfiles' => 10, 'context' => context_system::instance(), 'subdirs' => true];

        // Adding title and description.
        $mform->addElement('header', 'general', get_string($this->_customdata['what'].'shop', 'local_shop'));

        $attributes = 'size="47" maxlength="200"';
        $attributesshort = 'size="30" maxlength="200"';
        $attributeslong = 'size="80" maxlength="255"';

        // Adding fieldset.
        $mform->addElement('hidden', 'what', $this->_customdata['what']);
        $mform->setType('what', PARAM_TEXT);

        // The current shopid.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // The edited shopid.
        $mform->addElement('hidden', 'shopid');
        $mform->setType('shopid', PARAM_INT);

        $mform->addElement('text', 'name', get_string('name'), $attributesshort);
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('hidden', 'sortorder');
        $mform->setType('sortorder', PARAM_INT);

        $mform->addElement('editor', 'description_editor', get_string('description').':', null, $this->editoroptions);
        $mform->setType('description', PARAM_CLEANHTML);

        // Catalog choice.
        if ($cats = $DB->get_records('local_shop_catalog')) {
            foreach ($cats as $cat) {
                $catoptions[$cat->id] = format_string($cat->name);
            }
            $mform->addElement('select', 'catalogid', get_string('configcatalog', 'local_shop'), $catoptions);
            $mform->setType('catalogid', PARAM_INT);
        } else {
            $context = context_system::instance();
            $str = get_string('nocatalogs', 'local_shop');
            if (has_capability('local/shop:salesadmin', $context)) {
                $gotoadminstr = get_string('gotoadminlink', 'local_shop');
                $catalogadminurl = new moodle_url('/local/shop/index.php');
                $str .= '. <a href="'.$catalogadminurl.'">'.$gotoadminstr.'</a>';
            }
            $mform->addElement('static', 'catalogidlabel', get_string('configcatalog', 'local_shop'), $str);
        }

        $mform->addElement('text', 'navsteps', get_string('navsteps', 'local_shop'), $attributeslong);
        $mform->setType('navsteps', PARAM_TEXT);
        $mform->setDefault('navsteps', $config->defaultnavsteps);
        $mform->setAdvanced('navsteps');

        if (!empty($cats)) {

            // Tax application.
            $radioarray = [];
            $radioarray[] = &$mform->createElement('radio', 'allowtax', '', get_string('yes'), 1, $attributes);
            $radioarray[] = &$mform->createElement('radio', 'allowtax', '', get_string('no'), 0, $attributes);
            $mform->addGroup($radioarray, 'radioar', get_string('allowtax', 'local_shop').':', [' '], false);
            $mform->addHelpButton('radioar', 'allowtax', 'local_shop');

            // Shop Currency.
            $currencies = shop_get_supported_currencies();
            $mform->addElement('select', 'currency', get_string('currency', 'local_shop').':', $currencies);
            $mform->addRule('currency', '', 'required', null, 'client');
            $mform->setDefault('currency', $config->defaultcurrency);

            // Choosing valid paymodes for this shop instance.
            $mform->addElement('header', 'heading_paymodes', get_string('paymentmethods', 'local_shop'));
            $mform->addElement('html', get_string('carefullchoice', 'local_shop'));
            $paymodes = shop_paymode::get_plugins($this);
            foreach ($paymodes as $pm) {
                if ($pm->enabled) {
                    $pm->add_instance_config($mform);
                }
            }

            $mform->addElement('header', 'heading_misc', get_string('miscellaneous', 'local_shop'));

            $mform->addElement('advcheckbox', 'forcedownloadleaflet', get_string('configforcedownloadleaflet', 'local_shop'));
            $mform->setType('forcedownloadleaflet', PARAM_BOOL);

            $yesnochoices = ['0' => get_string('no'), '1' => get_string('yes')];
            $label = get_string('configcustomerorganisationrequired', 'local_shop');
            $mform->addElement('select', 'customerorganisationrequired', $label, $yesnochoices);
            $mform->setDefault('customerorganisationrequired', 1);

            $label = get_string('configenduserorganisationrequired', 'local_shop');
            $mform->addElement('select', 'enduserorganisationrequired', $label, $yesnochoices);
            $mform->setDefault('enduserorganisationrequired', 0);

            $label = get_string('configendusermobilephonerequired', 'local_shop');
            $mform->addElement('select', 'endusermobilephonerequired', $label, $yesnochoices);
            $mform->setDefault('endusermobilephonerequired', 0);

            $label = get_string('configprinttabbedcategories', 'local_shop');
            $mform->addElement('select', 'printtabbedcategories', $label, $yesnochoices);
            $mform->setDefault('customerorganisationrequired', 0);

            // Default customer support course if.
            $courseoptions = $DB->get_records_menu('course', ['visible' => 1], 'fullname', 'id,fullname');
            $courseoptions[0] = get_string('none', 'local_shop');
            $label = get_string('configdefaultcustomersupportcourse', 'local_shop');
            $mform->addElement('select', 'defaultcustomersupportcourse', $label, $courseoptions);
            $mform->setDefault('defaultcustomersupportcourse', 0);

            $mform->addElement('editor', 'eula_editor', get_string('configeula', 'local_shop'), null, $this->editoroptions);
            $mform->setType('eula', PARAM_CLEANHTML); // XSS is prevented when printing the block contents and serving files.

            // Adding submit and reset button.
            $this->add_action_buttons();
        } else {
            // We cannot submit.
            $mform->addElement('cancel');
        }
    }

    /**
     * Feeding form with previous data
     * @param array $defaults
     */
    public function set_data($defaults) {
        $context = context_system::instance();

        $defaults = file_prepare_standard_editor($defaults, 'description', $this->editoroptions, $context, 'local_shop',
                                                 'description', $defaults->shopid);

        $defaults = file_prepare_standard_editor($defaults, 'eula', $this->editoroptions, $context, 'local_shop',
                                                 'eula', $defaults->shopid);

        parent::set_data($defaults);
    }

    /**
     * Validates data on submission
     * @param array $data
     * @param array $files embedded files
     */
    public function validation($data, $files = []) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (empty($data['shopid'])) {
            // Only if creating new.
            if ($DB->record_exists('local_shop', ['name' => $data['name']])) {
                $errors['name'] = get_string('errorshopexists', 'local_shop');
            }
        }

        return $errors;
    }
}
