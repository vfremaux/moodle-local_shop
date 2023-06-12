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
 * @reviewer   Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalogitem.class.php');

use local_shop\Tax;

class Product_Form extends CatalogItem_Form {

    public function __construct($action, $data) {
        parent::__construct($action, $data);
    }

    public function definition() {
        global $OUTPUT, $DB;

        $config = get_config('local_shop');

        if (!$this->is_slave()) {

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
        $attributeshandlerparams = ['cols' => 50, 'rows' => 8, 'style' => "width:80%", 'data-format' => "url", 'data-edithandle' => 'id_edithandlerparams'];

        // Adding title and description.
        $variant = '';
        if ($this->is_slave()) {
            $variant = 'variant';
        }
        $formcaption = get_string($this->_customdata['what'].'product'.$variant, 'local_shop');
        $mform->addElement('html', $OUTPUT->heading($formcaption));

        $mform->addElement('header', 'h0', get_string('general'));

        $this->add_standard_name_elements();

        $mform->addElement('header', 'h1', get_string('financials', 'local_shop'));

        $this->add_price_group();
        $this->add_tax_select();

        $mform->addElement('header', 'h2', get_string('behaviour', 'local_shop'));

        $this->add_sales_params();
        $this->add_target_market();
        $this->add_category();

        if (!$this->is_slave()) {
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
        $group[] = &$mform->createElement('advcheckbox', 'showsnameinset', '', $label);
        $mform->setDefault('showsnameinset', 1);

        $label = get_string('showdescriptioninset', 'local_shop');
        $group[] = &$mform->createElement('advcheckbox', 'showsdescriptioninset', '', $label);
        $mform->setDefault('showsdescriptioninset', 1);

        $mform->addGroup($group, 'setvisibilityarray', '', array(' '), false);

        $mform->addElement('header', 'h3', get_string('assets', 'local_shop'));

        $this->add_document_assets();

        $mform->addElement('header', 'h4', get_string('automation', 'local_shop'));

        // This may need to be translated for localised catalogs.
        $label = get_string('requireddata', 'local_shop').':';
        $mform->addElement('textarea', 'requireddata', $label, $attributesspecificdata);
        $mform->setType('requireddata', PARAM_TEXT);
        $mform->addHelpButton('requireddata', 'requireddata', 'local_shop');

        // This may need to be translated for localised catalogs.
        $label = get_string('productiondata', 'local_shop').':';
        $mform->addElement('textarea', 'productiondata', $label, $attributesspecificdata);
        $mform->setType('productiondata', PARAM_TEXT);
        $mform->addHelpButton('productiondata', 'productiondata', 'local_shop');
        $mform->setAdvanced('productiondata');

        if (!$this->is_slave()) {
            $handleropts['0'] = get_string('disabled', 'local_shop');
            $handleropts['1'] = get_string('dedicated', 'local_shop');
            $handleropts = array_merge($handleropts, shop_get_standard_handlers_options());

            $label = get_string('enablehandler', 'local_shop').':';
            $mform->addElement('select', 'enablehandler', $label, $handleropts);
            $mform->setType('enablehandler', PARAM_TEXT);

            $group = [];
            $group[] = & $mform->createElement('textarea', 'handlerparams', '', $attributeshandlerparams);
            $mform->setType('handlerparams', PARAM_TEXT);
            $group[] = & $mform->createElement('button', 'edithandlerparams', get_string('edit', 'local_shop'));
            $mform->addGroup($group, 'grphandlerparams', get_string('handlerparams', 'local_shop'), '', false);
            $mform->addHelpButton('grphandlerparams', 'handlerparams', 'local_shop');

            $seatmodeoptions[SHOP_QUANT_NO_SEATS] = get_string('no');
            $seatmodeoptions[SHOP_QUANT_ONE_SEAT] = get_string('oneseat', 'local_shop');
            $seatmodeoptions[SHOP_QUANT_AS_SEATS] = get_string('yes');
            $label = get_string('quantaddressesusers', 'local_shop').':';
            $mform->addElement('select', 'quantaddressesusers', $label, $seatmodeoptions);
            $mform->setType('quantaddressesusers', PARAM_INT);
            $mform->addHelpButton('quantaddressesusers', 'quantaddressesusers', 'local_shop');

            if (!empty($config->userenewableproducts)) {
                $mform->addElement('advcheckbox', 'renewable', get_string('renewable', 'local_shop').':');
                $mform->addHelpButton('renewable', 'renewable', 'local_shop');
                $mform->disabledIf('renewable', 'enablehandler', 'eq', 0);
            } else {
                $mform->addElement('hidden', 'renewable', 0);
                $mform->setType('renewable', PARAM_BOOL);
            }
        } else {
            $mform->addelement('static', 'enablehandlershadow', get_string('enablehandler', 'local_shop').':');
            $mform->addelement('hidden', 'enablehandler');
            $mform->setType('enablehandler', PARAM_TEXT);

            $mform->addelement('static', 'handlerparamsshadow', get_string('handlerparams', 'local_shop').':');
            $mform->addelement('hidden', 'handlerparams');
            $mform->setType('handlerparams', PARAM_TEXT);

            $mform->addelement('static', 'quantaddressesusersshadow', get_string('quantaddressesusers', 'local_shop').':');
            $mform->addelement('hidden', 'quantaddressesusers');
            $mform->setType('quantaddressesusers', PARAM_INT);

            $mform->addelement('static', 'renewableshadow', get_string('renewable', 'local_shop').':');
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

    public function validation($data, $files = []) {
        return parent::validation($data, $files);
    }
}