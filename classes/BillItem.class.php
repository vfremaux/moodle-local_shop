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

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');

/**
 * A Bill Item represents an order line with all the context that was there when 
 * it was created. It stores a freezed image of the catalog item (may be even disconnected from
 * deleted catalogs) for reference to a stable price table.
 *
 */
class BillItem extends ShopObject {

    static $table = 'local_shop_billitem';

    protected $bill;

    public $catalogitem;

    var $productiondata;

    var $customerdata;

    var $actionparams; // parameters decoded from handler params

    function __construct($idorrec, &$bill = null, $ordering = -1) {
        global $DB;

        $this->bill = $bill;

        parent::__construct($idorrec, self::$table);

        // here we make some assertions to check the billitem integrity
        parent::__construct($idorrec, self::$table);

        if (!empty($this->record->id)) {

            if (empty($this->bill)) {
                $bill = new Bill($this->record->billid);
            }

            assert(($this->record->unitcost * $this->record->quantity) == $this->record->totalprice);

            // reydrates the original catalog item stored when bill was created.
            // This ensures getting exact prices from the moment, event if changed in catalog inbetween.
            $this->catalogitem = unserialize(base64_decode($this->record->catalogitem));
            $this->productiondata = unserialize(base64_decode($this->record->productiondata));
            $this->customerdata = unserialize(base64_decode($this->record->customerdata));

            if (!empty($this->productiondata->handlerparams)) {
                if (is_string($this->productiondata->handlerparams)) {
                    $pairs = explode('&', $this->productiondata->handlerparams);
                    if (!empty($pairs)) {
                        foreach ($pairs as $p) {
                            list($param,$value) = explode('=', $p);
                            $this->actionparams[$param] = $value;
                        }
                    }
                } else {
                    $this->actionparams = $this->productiondata->handlerparams;
                }
            }
        } else {

            if (empty($bill)) {
                throw new \Exception('A bill is expected to build a new BillItem');
            }

            // Try whenever possible drive ordering from outside without DB calls.... 
            if ($ordering == -1) {
                if ($maxordering = $DB->get_record_select('local_shop_billitem', " billid = ? AND ordering = (SELECT MAX(ordering) FROM {local_shop_billitem} WHERE billid = ?) ", array($bill->id, $bill->id))) {
                    $this->ordering = $maxordering->ordering + 1;
                } else {
                    $this->ordering = 1;
                }
            } else {
                $this->ordering = $ordering;
            }
            // first creation of a record
            // itemcode is NOT a legacy record field, but comes from shopping front
            $this->record->type = $idorrec->type;

            if ($idorrec->type != 'BILLING') {
                // These are pseudo products.
                $this->catalogitem = $bill->thecatalogue->get_product_by_code($idorrec->itemcode);
                $this->record->billid = $bill->id;
                $this->record->itemcode = $idorrec->itemcode;
                $this->record->catalogitem = base64_encode(serialize($this->catalogitem));
                $this->record->unitcost = $idorrec->unitcost;
                $this->record->taxcode = $this->catalogitem->taxcode;
                $this->record->totalprice = $idorrec->totalprice;
                $this->record->quantity = $idorrec->quantity;
                $this->record->abstract = '';
                $this->record->description = '';
                $this->record->productiondata = '';
                $this->record->customerdata = '';
            } else {
                $this->catalogitem = $bill->thecatalogue->get_product_by_shortname($idorrec->itemcode);
                $this->record->billid = $bill->id;
                $this->record->itemcode = $this->catalogitem->code;
                $this->record->unitcost = $this->catalogitem->get_price($idorrec->quantity);
                $this->record->taxcode = $this->catalogitem->taxcode;
                $this->record->totalprice = $idorrec->quantity * $this->record->unitcost;
                $this->record->quantity = $idorrec->quantity;
                $this->record->abstract = $this->catalogitem->name;
                $this->record->description = $this->catalogitem->description;
                $this->productiondata = $idorrec->productiondata; // this gets production data from shop front end. Essentially user definitions;
                $this->productiondata->handlerparams = $this->catalogitem->handlerparams; // this adds a freezed copy of original handler params.
                if (!empty($this->productiondata->handlerparams)) {
                    if (is_array($this->productiondata->handlerparams)) {
                        $this->actionparams = $this->productiondata->handlerparams;
                    } else {
                        $pairs = explode('&', $this->productiondata->handlerparams);
                        if (!empty($pairs)) {
                            foreach ($pairs as $p) {
                                list($param,$value) = explode('=', $p);
                                $this->actionparams[$param] = $value;
                            }
                        }
                    }
                }
    
                $this->productiondata->catalogitemdata = $this->catalogitem->productiondata; // this passes some production params from catalog.
                $this->customerdata = $idorrec->customerdata;
    
                // deshydrates sub structures in record for storage
                $this->record->catalogitem = base64_encode(serialize($this->catalogitem));
                $this->record->productiondata = base64_encode(serialize($this->productiondata));
                $this->record->customerdata = base64_encode(serialize($this->customerdata)); // customer data comes from product requirements
            }
        }
    }

    function move($dir, $z) {
       global $DB;

       $sql = "
          UPDATE 
             {local_shop_billitem} 
          SET
             ordering = ordering + $dir
          WHERE
             ordering = ? AND
             billid = ?
       ";
       $DB->execute($sql, array($z, $this->id));
    }

    function get_price() {
        return $this->catalogitem->get_price($this->record->quantity);
    }
    
    function get_taxed_price() {
        return $this->catalogitem->get_taxed_price($this->record->quantity);
    }

    function get_tax_amount() {
        return $this->catalogitem->get_taxed_price($this->record->quantity) - $this->catalogitem->get_price($this->record->quantity);
    }

    function get_totaltax() {
        return $this->get_tax_amount() * $this->record->quantity;
    }

    function get_totaltaxed() {
        return $this->get_taxed_price($this->record->quantity) * $this->record->quantity;
    }

    function get_customerid() {
        return $this->bill->customerid;
    }

    function save() {
        parent::save();
    }

    function delete() {
        // Delete products currently attached to.
        $products = Product::get_instances(array('currentbillitemid' => $this->id));
        if ($products) {
            foreach ($products as $p) {
                $p->delete();
            }
        }

        parent::delete();
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}