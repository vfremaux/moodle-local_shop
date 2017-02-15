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
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

class BillItem_Form extends moodleform {

    protected $editoroptions;
    protected $attributesshort;
    protected $attributesshortjs;
    protected $attributesdescription;

    public function __construct($action, $data) {
        global $COURSE;

        $context = context_system::instance();

        $maxfiles = 99;                // TODO: add some setting.
        $maxbytes = $COURSE->maxbytes; // TODO: add some setting.
        $this->editoroptions = array('trusttext' => true,
                                     'subdirs' => false,
                                     'maxfiles' => $maxfiles,
                                     'maxbytes' => $maxbytes,
                                     'context' => $context);

        $this->attributesshort = 'size="24" maxlength="24"';
        $this->attributesshortjs = array('size' => 24,
                                         'maxlength' => 24,
                                         'onchange' => 'calculate_price()');
        $this->attributesdescription = 'cols="50" rows="8"';

        parent::__construct($action, $data);
    }

    public function definition() {
        global $OUTPUT, $DB;

        $config = get_config('local_shop');
        $bill = $this->_customdata['bill'];

        // Setting variables.
        $mform =& $this->_form;

        $mform->addelement('hidden', 'billid');
        $mform->setType('billid', PARAM_INT);

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->_customdata['what'].'billitem', 'local_shop')));

        $js = "
            <script type=\"text/javascript\">
            function calculate_price() {
                var cost = parseFloat($('#id_unitcost').val());
                var quantity = parseFloat($('#id_quantity').val());
                if (isNaN(cost)) {
                    $('#id_unitcost').val('0.00');
                };
                if (isNaN(quantity)) {
                    $('#id_quantity').val(1);
                };
                var price = new Number(cost * quantity);
                $('#billitem-totalprice').html(price.toFixed(\"2\"));
            }
            </script>
        ";

        $mform->addElement('html', $js);

        // Adding fieldset.
        $label = get_string('order', 'local_shop');
        $billcode = 'ORD-'.date('Ymd', $bill->emissiondate).'-'.$bill->id;
        $mform->addElement('static', 'billtitle', $label, $billcode);

        $lastordering = $DB->get_field_sql('SELECT max(ordering) from {local_shop_bill}');
        $lastordering = $lastordering + 1;
        $mform->addElement('hidden', 'ordering', $lastordering);
        $mform->setType('ordering', PARAM_INT);

        $mform->addElement('text', 'itemcode', get_string('code', 'local_shop'), $this->attributesshort);
        $mform->setType('itemcode', PARAM_INT);
        $mform->addRule('itemcode', get_string('missingcode', 'local_shop'), 'required', '', 'client');

        $mform->addElement('editor', 'abstract', get_string('abstract', 'local_shop'), $this->editoroptions);
        $mform->setType('abstract', PARAM_CLEANHTML);

        $mform->addElement('editor', 'description', get_string('description'), $this->editoroptions);
        $mform->setType('description', PARAM_CLEANHTML);

        $mform->addElement('date_selector', 'delay', get_string('timetodo', 'local_shop'));

        $mform->addElement('text', 'unitcost', get_string('unittex', 'local_shop'), $this->attributesshortjs);
        $mform->setType('unitcost', PARAM_NUMBER);
        $mform->setDefault('unitcost', 0);

        $mform->addElement('text', 'quantity', get_string('biquantity', 'local_shop').':', $this->attributesshortjs);
        $mform->setType('quantity', PARAM_NUMBER);
        $mform->setDefault('quantity', 1);

        $content = '<span id="billitem-totalprice">0.00</span> '. $config->defaultcurrency;
        $mform->addElement('static', 'totalprice', get_string('total'), $content);
        $bill = $DB->get_record('local_shop_bill', array('id' => $bill->id));

        if ($bill->ignoretax == 0) {
            $taxcodeopts = $DB->get_records_menu('local_shop_tax', null, 'title', 'id, title');
            $jsoptions = array('onchange' => 'calculate_price()');
            $mform->addElement('select', 'taxcode', get_string('taxcode', 'local_shop'), $taxcodeopts, $jsoptions);
            $mform->setType('taxcode', PARAM_INT);
        }

        // Adding submit and reset button.
        $this->add_action_buttons();
    }
}