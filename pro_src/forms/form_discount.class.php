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
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Shop;
use local_shop\CatalogItem;
use local_shop\Catalog;

class Discount_Form extends moodleform {

    protected $editoroptions;

    public function __construct($url, $customdata) {
        global $CFG;

        parent::__construct($url, $customdata);

        $maxfiles = 99;                // TODO: add some settings.
        $maxbytes = $CFG->maxbytes; // TODO: add some settings.
        $context = context_system::instance();

        $this->editoroptions = array('trusttext' => true,
                                     'subdirs' => false,
                                     'maxfiles' => $maxfiles,
                                     'maxbytes' => $maxbytes,
                                     'context' => $context);
    }

    public function definition() {
        global $CFG, $OUTPUT;

        // Setting variables.
        $mform =& $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'shopid');
        $mform->setType('shopid', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'discount', 'local_shop')));

        // Adding fieldset.
        $attributes = ' size="80" maxlength="200" ';
        $editorattributes = ' size="80" maxlength="200" ';
        $areaattributes = ' cols="80" rows="5" ';

        $mform->addElement('advcheckbox', 'enabled', get_string('discountenabled', 'local_shop'));
        $mform->setType('enabled', PARAM_BOOL);
        $mform->setDefault('enabled', true);

        $mform->addElement('text', 'name', get_string('discountname', 'local_shop'), $attributes);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addElement('editor', 'argument_editor', get_string('argument', 'local_shop'), null, $this->editoroptions);
        $mform->setType('argument', PARAM_CLEANHTML);

        $mform->addElement('text', 'ratio', get_string('ratio', 'local_shop'));
        $mform->setType('ratio', PARAM_NUMBER);
        $mform->addRule('ratio', null, 'required');
        $mform->addHelpButton('ratio', 'ratio', 'local_shop');

        $discounttypeoptions = [
            'Unconditional' => get_string('discountunconditional', 'local_shop'),
            'UserInstitution' => get_string('discountinstitutionmatch', 'local_shop'),
            'OrderNum' => get_string('discountsuccessfullordernum', 'local_shop'),
            'OrderAmount' => get_string('discountorderamount', 'local_shop'),
            'LongTimeCustomer' => get_string('discountlongtimecustomer', 'local_shop'),
            'UserCapability' => get_string('discountusercapability', 'local_shop'),
            'OfferCode' => get_string('discountoffercode', 'local_shop'),
            'MultipleOfferCode' => get_string('multiplediscountoffercode', 'local_shop'),
            'PartnerMultipleOfferCode' => get_string('partnermultiplediscountoffercode', 'local_shop'),
        ];
        $mform->addElement('select', 'type', get_string('discounttype', 'local_shop'), $discounttypeoptions);
        $mform->setType('type', PARAM_TEXT);
        $mform->addRule('type', null, 'required');
        $mform->addHelpButton('type', 'type', 'local_shop');

        $mform->addElement('textarea', 'ruledata', get_string('discountruledata', 'local_shop'), $areaattributes);
        $mform->setType('ruledata', PARAM_TEXT);
        $mform->disabledif('ruledata', 'type', 'eq', 'Unconditional');

        $applyonoptions = [
            'bill' => get_string('fullbill', 'local_shop'),
            'itemlist' => get_string('itemlist', 'local_shop'),
        ];
        $mform->addElement('select', 'applyon', get_string('discountapplieson', 'local_shop'), $applyonoptions);
        $mform->setType('applyon', PARAM_TEXT);
        $mform->addRule('applyon', null, 'required');

        $thecatalog = $this->_customdata['thecatalog'];
        $itemoptions = CatalogItem::get_instances_menu(['catalogid' => $thecatalog->id]);
        $attrs = ['size' => 15];
        $select = &$mform->addElement('select', 'applydata', get_string('discountapplydata', 'local_shop'), $itemoptions, $attrs);
        $mform->setType('applydata', PARAM_SEQUENCE);
        $select->setMultiple(true);
        $mform->disabledif('applydata', 'applyon', 'eq', 'bill');
        $mform->addHelpButton('applydata', 'applydata', 'local_shop');

        $operatoroptions = [
            'accumulate' => get_string('accumulate', 'local_shop'),
            'takeover' => get_string('takeover', 'local_shop'),
            'stopchainifapplies' => get_string('stopchainifapplies', 'local_shop'),
            'stopchainifnotapplies' => get_string('stopchainifnotapplies', 'local_shop'),
        ];
        $mform->addElement('select', 'operator', get_string('operator', 'local_shop'), $operatoroptions);
        $mform->addRule('operator', null, 'required');
        $mform->addHelpButton('operator', 'operator', 'local_shop');

        // Adding submit and reset button.
        $this->add_action_buttons();
    }

    /*
     *
     */
    public function validation($data, $files = array()) {
        global $DB;

        $errors = parent::validation($data, $files);

        $params = ['name' => $data['name'], 'shopid' => $data['shopid'], 'id' => $data['id']];
        $select = ' name = :name AND shopid = :shopid AND id <> :id ';
        if ($DB->get_record_select('local_shop_discount', $select, $params)) {
            $errors['name'] = get_string('errordiscountnameexistsinshop', 'local_shop');
        }

        if ($data['applyon'] == 'itemlist' && empty($data['applydata'])) {
            $errors['applydate'] = get_string('erroremptydiscountitemlist', 'local_shop');
        }

        $type = $data['type'];
        $class = 'local_shop\\'.$type;
        if (method_exists($class, 'check_ruledata')) {
            $func =  "{$class}::check_ruledata";
            if ($error = $func($data)) {
                $errors['ruledata'] = $error;
            }
        }

        // print_object($errors);

        return $errors;
    }

    public function set_data($defaults) {

        $context = context_system::instance();
        $defaults = file_prepare_standard_editor($defaults, 'argument', $this->editoroptions, $context, 'local_shop',
                                                 'discountargument', @$defaults->id);

        parent::set_data($defaults);
    }
}