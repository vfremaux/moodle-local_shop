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

defined('MOODLE_INTERNAL') || die();

/**
 * Defines form to add a new billitem
 *
 * @package    local_shop
 * @category   local
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

class BillItem_Form extends moodleform {

    private $mode;

    private $bill;

    function __construct($mode, $bill, $action) {
        $this->mode = $mode;
        $this->bill = $bill;
        parent::__construct($action);
    }

    function definition() {
        global $CFG, $OUTPUT, $DB;

        $config = get_config('local_shop');

        // Setting variables.
        $mform =& $this->_form;

        // Adding title and description.
        $mform->addElement('html', $OUTPUT->heading(get_string($this->mode.'billitem', 'local_shop')));

        // $error_float = print_string('error_costNotAFloat', 'local_shop');
        // print_string("error_quantityNotAFloat");
        $js = "
            <script type=\"text/javascript\">
            function calculatePrice() {
                var cost = parseFloat(document.billItem.unitCost.value);
                var quantity = parseFloat(document.billItem.quantity.value);
                if (isNaN(cost)) {
                    alert(\"error\");
                    document.billItem.unitCost.value = \"0.00\";
                    exit();
                };
                if (isNaN(quantity)) {
                    alert(\"error2\");
                    document.billItem.quantity.value = \"1\";
                    exit();
                };
                var priceDisplay = document.getElementById('totalPrice'); 
                var price = new Number(cost * quantity);
                priceDisplay.innerHTML = price.toFixed(\"2\");
            }
            </script>
        ";

        $ordering = ($this->mode == 'add') ? $this->bill->maxordering + 1 : 0 ;
        $mform->addElement('html', $js);

        // Adding fieldset.
        $attributes = 'size="50" maxlength="200"';
        $attributesshort = 'size="24" maxlength="24"';
        $attributes_description = 'cols="50" rows="8"';
        $mform->addElement('static', 'billtitle', get_string('order', 'local_shop'), 'ORD-'.date('Ymd', $this->bill->emissiondate).'-'.$this->bill->id);

        $lastordering = $DB->get_field_sql('SELECT max(ordering) from {local_shop_bill}');
        $lastordering = $lastordering + 1;
        $mform->addElement('hidden', 'ordering', $lastordering);
        $mform->setType('ordering', PARAM_INT);

        $mform->addElement('text', 'itemcode', get_string('code', 'local_shop'), $attributesshort);
        $mform->setType('itemcode', PARAM_INT);

        $mform->addElement('editor', 'abstract', get_string('abstract', 'local_shop'), $attributesshort);
        $mform->setType('abstract', PARAM_CLEANHTML);

        $mform->addElement('editor', 'description', get_string('description'));
        $mform->setType('description', PARAM_CLEANHTML);

        $mform->addElement('date_selector', 'delay', get_string('timetodo', 'local_shop'));

        $mform->addElement('text', 'unitcost', get_string('unittex', 'local_shop'), $attributesshort);
        $mform->setType('unitcost', PARAM_NUMBER);

        $mform->addElement('text', 'quantity', get_string('quantity', 'local_shop').':', $attributesshort);
        $mform->setType('quantity', PARAM_NUMBER);

        $mform->addElement('static', 'totalprice', get_string('total'), "<span id=\"totalPrice\">0.00</span> ". $config->defaultcurrency);
        $bill = $DB->get_record('local_shop_bill', array('id' => $this->bill->id));

        if ($bill->ignoretax == 0) {
            $taxcodeopts = $DB->get_records_menu('local_shop_tax', null, 'title', 'id, title');
            $mform->addElement('select', 'taxcode', get_string('taxcode', 'local_shop'), $taxcodeopts);
            $mform->setType('taxcode', PARAM_INT);
        }

        // Adding submit and reset button.
        $this->add_action_buttons();
    }
}