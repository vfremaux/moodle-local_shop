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

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalogitem.class.php');

use local_shop\Tax;

class Product_Form extends catalogitemform {

    public function definition() {
        global $OUTPUT, $DB;

        if (!$this->_customdata['catalog']->isslave) {

            $select = "
                catalogid = ? AND
                (isset = 1 OR isset = 2)
            ";
            $params = array($this->_customdata['catalog']->id);
            $sets = $DB->get_records_select('local_shop_catalogitem', $select, $params, 'id, name');
        }

        // Setting variables.

        $mform =& $this->_form;

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        $attributesspecificdata = 'rows="4" style="width:80%" ';
        $attributeshandlerparams = 'cols="50" rows="8" style="width:80%" ';

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'product', 'local_shop')));

        $mform->addElement('header', 'h0', get_string('general'));

        $this->add_standard_name_elements();

        $mform->addElement('header', 'h1', get_string('financials', 'local_shop'));

        $this->add_price_group();

        $this->add_tax_select();

        $mform->addElement('header', 'h2', get_string('behaviour', 'local_shop'));

        $this->add_sales_params();

        $this->add_target_market();

        if ($cats = $this->_customdata['catalog']->get_categories()) {
            foreach ($cats as $cat) {
                $sectionopts[$cat->id] = $cat->name;
            }
            $mform->addElement('select', 'categoryid', get_string('section', 'local_shop'), $sectionopts);
            $mform->setType('categoryid', PARAM_INT);
            $mform->addRule('categoryid', null, 'required');
        } else {
            $mform->addElement('static', 'nocats', get_string('nocats', 'local_shop'));
        }

        if (!$this->_customdata['catalog']->isslave) {
            $setopts[0] = get_string('outofset', 'local_shop');
            if (!empty($sets)) {
                foreach ($sets as $set) {
                    $setopts[$set->id] = $set->name;
                }
            }
            $mform->addElement('select', 'setid', get_string('set', 'local_shop'), $setopts);
        }
        $group = array();
        $label = get_string('shownameinset', 'local_shop');
        $group[] = &$mform->createElement('checkbox', 'showsnameinset', '', $label);
        $mform->setDefault('showsnameinset', 1);
        $label = get_string('showdescriptioninset', 'local_shop');
        $group[] = &$mform->createElement('checkbox', 'showsdescriptioninset', '', $label);
        $mform->setDefault('showsdescriptioninset', 1);
        $mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);

        $mform->addElement('header', 'h3', get_string('assets', 'local_shop'));

        $this->add_document_assets();

        $mform->addElement('header', 'h4', get_string('automation', 'local_shop'));

        // This may need to be translated for localised catalogs.
        $label = get_string('requireddata', 'local_shop');
        $mform->addElement('textarea', 'requireddata', $label, $attributesspecificdata);
        $mform->setType('requireddata', PARAM_TEXT);
        $mform->addHelpButton('requireddata', 'requireddata', 'local_shop');

        if (!$this->_customdata['catalog']->isslave) {
            $handleropts['0'] = get_string('disabled', 'local_shop');
            $handleropts['1'] = get_string('dedicated', 'local_shop');
            $handleropts = array_merge($handleropts, shop_get_standard_handlers_options());

            $mform->addElement('select', 'enablehandler', get_string('enablehandler', 'local_shop'), $handleropts);
            $mform->setType('enablehandler', PARAM_TEXT);

            $label = get_string('handlerparams', 'local_shop');
            $mform->addElement('textarea', 'handlerparams', $label, $attributeshandlerparams);
            $mform->setType('handlerparams', PARAM_TEXT);
            $mform->addHelpButton('handlerparams', 'handlerparams', 'local_shop');

            $seatmodeoptions[SHOP_QUANT_NO_SEATS] = get_string('no');
            $seatmodeoptions[SHOP_QUANT_ONE_SEAT] = get_string('oneseat', 'local_shop');
            $seatmodeoptions[SHOP_QUANT_AS_SEATS] = get_string('yes');
            $label = get_string('quantaddressesusers', 'local_shop');
            $mform->addElement('select', 'quantaddressesusers', $label, $seatmodeoptions);
            $mform->setType('quantaddressesusers', PARAM_INT);
            $mform->addHelpButton('quantaddressesusers', 'quantaddressesusers', 'local_shop');

            $mform->addElement('checkbox', 'renewable', get_string('renewable', 'local_shop'));
            $mform->addHelpButton('renewable', 'renewable', 'local_shop');
            $mform->disabledIf('renewable', 'enablehandler', 'eq', 0);
        } else {
            $mform->addelement('hidden', 'enablehandler');
            $mform->setType('enablehandler', PARAM_TEXT);
            $mform->addelement('hidden', 'handlerparams');
            $mform->setType('handlerparams', PARAM_TEXT);
            $mform->addelement('hidden', 'quantaddressesusers');
            $mform->setType('quantaddressesusers', PARAM_INT);
            $mform->addelement('hidden', 'renewable');
            $mform->setType('renewable', PARAM_BOOL);
        }

        // Adding submit and reset button.
        $this->add_action_buttons();

        $mform->closeHeaderBefore('buttonar');
    }

    public function set_data($defaults) {
        $context = context_system::instance();
        $this->set_name_data($defaults, $context);
        $this->set_document_asset_data($defaults, $context);
        parent::set_data($defaults);
    }
}