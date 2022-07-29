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

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalogitem.class.php');

use local_shop\Tax;

class Set_Form extends CatalogItem_Form {

    public function __construct($action, $data) {
        parent::__construct($action, $data);
    }

    public function definition() {
        global $OUTPUT;

        // Setting variables.
        $mform =& $this->_form;

        $mform->addElement('hidden', 'setid');
        $mform->setType('setid', PARAM_INT);

        $mform->addElement('hidden', 'isset', 1);
        $mform->setType('isset', PARAM_INT);

        $mform->addElement('hidden', 'price1', 0);
        $mform->setType('price1', PARAM_NUMBER);

        $mform->addElement('hidden', 'price2', 0);
        $mform->setType('price2', PARAM_NUMBER);

        $mform->addElement('hidden', 'price3', 0);
        $mform->setType('price3', PARAM_NUMBER);

        $mform->addElement('hidden', 'taxcode', 0);
        $mform->setType('taxcode', PARAM_TEXT);

        $mform->addElement('hidden', 'stock', 0);
        $mform->setType('stock', PARAM_INT);

        $mform->addElement('hidden', 'sold', 0);
        $mform->setType('sold', PARAM_INT);

        $mform->addElement('hidden', 'maxdeliveryquant', 0);
        $mform->setType('maxdeliveryquant', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'set', 'local_shop')));

        $mform->addElement('header', 'h0', get_string('general'));

        $this->add_standard_name_elements();

        $mform->addElement('header', 'h2', get_string('behaviour', 'local_shop'));

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

        $mform->addElement('header', 'h3', get_string('assets', 'local_shop'));

        $this->add_document_assets();

        $mform->addElement('text', 'requireddata', get_string('requireddata', 'local_shop'), $this->attributesshort);
        $mform->setType('requireddata', PARAM_TEXT);

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