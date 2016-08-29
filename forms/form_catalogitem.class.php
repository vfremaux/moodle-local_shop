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
 * Defines form to add a new project
 *
 * @package    local_shop
 * @category   local
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Tax;

abstract class catalogitemform extends moodleform {

    protected function add_price_group() {

        $attributesprice1 = array('size' => 7, 'maxlength'=> 10, 'onchange' => 'updatetiprice(1)');

        $mform = $this->_form;

        $pricegroup = array();
        $price1 = &$mform->createElement('text', 'price1', '', $attributesprice1);
        $price1->updateAttributes(array('onchange' => 'updatetiprice(1)'));
        $pricegroup[] = $price1;
        $mform->setType('price1', PARAM_NUMBER);
        $from1 = &$mform->createElement('text', 'from1', '');
        $from1->updateAttributes(array('disabled' => 'disabled', 'value' => 0, 'size' => 7));
        $mform->setType('from1', PARAM_INT);
        $pricegroup[] = $from1;
        $to1 = &$mform->createElement('text', 'range1', '', $attributesprice1);
        $to1->updateAttributes(array('onchange' => 'checkprices(1)'));
        $pricegroup[] = $to1;
        $mform->setType('range1', PARAM_INT);
        $ttc1 = &$mform->createElement('static', 'ti1', '', '<span id="id_price1ti">TTC : </span>');
        $pricegroup[] = $ttc1;
        $mform->addGroup($pricegroup, 'priceset1', get_string('unitprice1', 'local_shop'), ' ', false);
        // $mform->addRule('price1', '', 'required');

        $pricegroup = array();
        $price2 = &$mform->createElement('text', 'price2', '', $attributesprice1);
        $price2->updateAttributes(array('onchange' => 'updatetiprice(2)'));
        $pricegroup[] = $price2;
        $mform->setType('price2', PARAM_NUMBER);
        $from2 = $mform->createElement('text', 'from2', '');
        $mform->setType('from2', PARAM_INT);
        $from2->updateAttributes(array('disabled' => 'disabled', 'size' => 7));
        $pricegroup[] = $from2;
        $to2 = &$mform->createElement('text', 'range2', '', $attributesprice1);
        $to2->updateAttributes(array('onchange' => 'checkprices(2)'));
        $pricegroup[] = $to2;
        $mform->setType('range2', PARAM_INT);
        $ttc2 = &$mform->createElement('static', 'ti2', '', '<span id="id_price2ti">TTC : </span>');
        $pricegroup[] = $ttc2;
        $mform->addGroup($pricegroup, 'priceset2', get_string('unitprice2', 'local_shop'), ' ', false);

        $pricegroup = array();
        $price3 = &$mform->createElement('text', 'price3', '', $attributesprice1);
        $price3->updateAttributes(array('onchange' => 'updatetiprice(3)'));
        $pricegroup[] = $price3; 
        $mform->setType('price3', PARAM_NUMBER);
        $from3 = &$mform->createElement('text', 'from3', '');
        $mform->setType('from3', PARAM_INT);
        $from3->updateAttributes(array('disabled' => 'disabled', 'size' => 7));
        $pricegroup[] = $from3;
        $to3 = &$mform->createElement('text', 'range3', '', $attributesprice1);
        $to3->updateAttributes(array('onchange' => 'checkprices(3)'));
        $pricegroup[] = $to3;
        $mform->setType('range3', PARAM_INT);
        $ttc3 = &$mform->createElement('static', 'ti3', '', '<span id="id_price3ti">TTC : </span>');
        $pricegroup[] = $ttc3;
        $mform->addGroup($pricegroup, 'priceset3', get_string('unitprice3', 'local_shop'), ' ', false);

        $pricegroup = array();
        $price4 = &$mform->createElement('text', 'price4', '', $attributesprice1);
        $price4->updateAttributes(array('onchange' => 'updatetiprice(4)'));
        $pricegroup[] = $price4;
        $mform->setType('price4', PARAM_NUMBER);
        $from4 = &$mform->createElement('text', 'from4', '');
        $mform->setType('from4', PARAM_INT);
        $from4->updateAttributes(array('disabled' => 'disabled', 'size' => 7));
        $pricegroup[] = $from4;
        $to4 = &$mform->createElement('text', 'range4', '', $attributesprice1);
        $to4->updateAttributes(array('onchange' => 'checkprices(4)'));
        $pricegroup[] = $to4;
        $mform->setType('range4', PARAM_INT);
        $ttc4 = &$mform->createElement('static', 'ti4', '', '<span id="id_price4ti">TTC : </span>');
        $pricegroup[] = $ttc4;
        $mform->addGroup($pricegroup, 'priceset4', get_string('unitprice4', 'local_shop'), ' ', false);

        $pricegroup = array();
        $price5 = &$mform->createElement('text', 'price5', '', $attributesprice1);
        $price5->updateAttributes(array('onchange' => 'updatetiprice(5)'));
        $pricegroup[] = $price5;
        $mform->setType('price5', PARAM_NUMBER);
        $from5 = &$mform->createElement('text', 'from5', '');
        $from5->updateAttributes(array('disabled' => 'disabled', 'size' => 7));
        $mform->setType('from5', PARAM_INT);
        $pricegroup[] = $from5;
        // $to5 = &$mform->createElement('text', 'range5', '', $attributesprice1);
        $to5 = &$mform->createElement('static', 'range5', '', '');
        $pricegroup[] = $to5;
        $mform->setType('range5', PARAM_INT);
        $ttc5 = &$mform->createElement('static', 'ti5', '', '<span id="id_price5ti">TTC : </span>');
        $pricegroup[] = $ttc5;
        $mform->addGroup($pricegroup, 'priceset5', get_string('unitprice5', 'local_shop'), ' ', false);

    }

    protected function add_tax_select() {

        $mform = $this->_form;

        $taxcodeopts = Tax::get_instances_menu(array(), 'title');
        $mform->addElement('select', 'taxcode', get_string('taxcode', 'local_shop'), $taxcodeopts, array('onchange' => 'updatetiprice(1)'));
        $mform->setDefault('taxcode', null);
        $mform->setType('taxcode', PARAM_INT);
        $mform->addHelpButton('taxcode', 'taxhelp', 'local_shop');
        $mform->addRule('taxcode', null, 'required');
    }

    protected function add_sales_params() {

        $mform = $this->_form;

        if (!$this->_customdata['catalog']->isslave) {
            $mform->addElement('text', 'stock', get_string('stock', 'local_shop'), $this->attributesshort);
            $mform->setType('stock', PARAM_NUMBER);

            $mform->addElement('text', 'sold', get_string('sold', 'local_shop'), $this->attributesshort);
            $mform->setType('sold', PARAM_NUMBER);

            $maxquantopts = array('0' => get_string('unlimited'),
                                  '1' => '1',
                                  '2' => '2',
                                  '3' => '3',
                                  '4' => '4',
                                  '5' => '5',
                                  '10' => '10',
                                  '20' => '20',
                                  '50' => '50'
                                  );
            $mform->addElement('select', 'maxdeliveryquant', get_string('maxdeliveryquant', 'local_shop'), $maxquantopts);
            $mform->setType('maxdeliveryquant', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'stock');
            $mform->setType('stock', PARAM_NUMBER);
            $mform->addElement('hidden', 'sold');
            $mform->setType('sold', PARAM_NUMBER);
            $mform->addElement('hidden', 'maxdeliveryquant');
            $mform->setType('maxdeliveryquant', PARAM_INT);
        }
    }

    protected function add_target_market() {

        $mform = $this->_form;

        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedin', 'local_shop'), 1);
        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('both', 'local_shop'), 0);
        $radiogroup[] = &$mform->createElement('radio', 'onlyforloggedin', '', get_string('loggedout', 'local_shop'), -1);
        $mform->addGroup($radiogroup, 'loggedingroup', get_string('onlyfor', 'local_shop'), array(' '), false);
        $mform->setDefault('onlyforloggedin', 0);
    }

    protected function add_document_assets() {
        global $COURSE;

        $fpickerattributes = array('maxbytes' => $COURSE->maxbytes, 'accepted_types' => array('.jpg', '.gif', '.png'));
        $leafletfpickerattributes = array('maxbytes' => $COURSE->maxbytes, 'accepted_types' => array('.pdf'));

        $mform = $this->_form;

        $group = array();
        $group[0] = & $mform->createElement('filepicker', 'leaflet', get_string('leaflet', 'local_shop'), $leafletfpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearleaflet', get_string('clear', 'local_shop'));

        $mform->addGroup($group, 'grleaflet', get_string('leaflet', 'local_shop'), array(get_string('clear', 'local_shop').'&nbsp;:&nbsp;'), false);

        $group = array();
        $group[0] = & $mform->createElement('filepicker', 'image', get_string('image', 'local_shop'), $fpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearimage', get_string('clear', 'local_shop'));

        $mform->addGroup($group, 'grimage', get_string('image', 'local_shop'), array(get_string('clear', 'local_shop').'&nbsp;:&nbsp;'), false);

        $group = array();
        $group[0] = & $mform->createElement('filepicker', 'thumb', get_string('thumbnail', 'local_shop'), $fpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearthumb', get_string('clear', 'local_shop'));

        $mform->addGroup($group, 'grthumb', get_string('thumbnail', 'local_shop'), array(get_string('clear', 'local_shop').'&nbsp;:&nbsp;'), false);

        $group = array();
        $group[0] = & $mform->createElement('filepicker', 'unit', get_string('unitpix', 'local_shop'), $fpickerattributes);
        $group[1] = & $mform->createElement('checkbox', 'clearunit', get_string('clear', 'local_shop'));
        $mform->addGroup($group, 'grunit', get_string('unitpix', 'local_shop'), array(get_string('clear', 'local_shop').'&nbsp;:&nbsp;'), false);
    }
}