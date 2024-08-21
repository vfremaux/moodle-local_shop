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
 * Defines form to add a new bundle.
 * Bundle are product sets that are sold as one pack.
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalogitem.class.php');

use local_shop\Tax;

/**
 * Form to edit a Bundle
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
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
class Bundle_Form extends CatalogItem_Form {

    /**
     * Constructor
     * @param string $action
     * @param array $data
     */
    public function __construct($action, $data) {
        parent::__construct($action, $data);
    }

    /**
     * Standard definition
     */
    public function definition() {
        global $OUTPUT;

        // Setting variables.

        $mform =& $this->_form;

        $mform->addElement('hidden', 'bundleid');
        $mform->setType('bundleid', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'bundle', 'local_shop')));

        $mform->addElement('header', 'h0', get_string('general'));

        $this->add_standard_name_elements();

        $mform->addElement('header', 'h1', get_string('financials', 'local_shop'));

        $this->add_price_group();
        $this->add_tax_select();

        $mform->addElement('header', 'h2', get_string('behaviour', 'local_shop'));

        $this->add_sales_params();
        $this->add_target_market();
        $this->add_category();

        $group = [];
        $group[] = &$mform->createElement('checkbox', 'shownameinset', '', get_string('shownameinset', 'local_shop'), 1);
        $label = get_string('showdescriptioninset', 'local_shop');
        $group[] = &$mform->createElement('checkbox', 'showdescriptioninset', '', $label, 1);
        $mform->addGroup($group, 'setvisibilityarray', '', [' '], false);

        $mform->addElement('header', 'h3', get_string('assets', 'local_shop'));

        $this->add_document_assets();

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    public function set_data($defaults) {
        $context = context_system::instance();
        $this->set_name_data($defaults, $context);
        $this->set_document_asset_data($defaults, $context);
        parent::set_data($defaults);
    }
}
