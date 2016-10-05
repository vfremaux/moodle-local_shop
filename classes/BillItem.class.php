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
 * A bill item is a single order line.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

/**
 * A Bill Item represents an order line with all the context that was there when
 * it was created. It stores a freezed image of the catalog item (may be even disconnected from
 * deleted catalogs) for reference to a stable price table.
 *
 */
class BillItem extends ShopObject {

    protected static $table = 'local_shop_billitem';

    public $bill;

    public $catalogitem;

    protected $productiondata;

    protected $customerdata;

    public $actionparams; // Parameters decoded from handler params.

    public function __construct($idorrec, &$bill = null, $ordering = -1) {
        global $DB;

        $this->bill = $bill;

        // Here we make some assertions to check the billitem integrity.
        parent::__construct($idorrec, self::$table);

        if (!empty($this->record->id)) {
            if (empty($this->bill)) {
                $bill = new Bill($this->record->billid);
            }

            $message = " ({$this->record->unitcost} * {$this->record->quantity}) == {$this->record->totalprice} ";
            if (!assert(($this->record->unitcost * $this->record->quantity) == $this->record->totalprice, $message)) {
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            }

            /*
             * Reydrates the original catalog item stored when bill was created.
             * This ensures getting exact prices from the moment, event if changed in catalog inbetween.
             */
            $catalogitemdata = base64_decode($this->record->catalogitem);
            $catalogitemdata = str_replace('block_shop_catalogitem', 'local_shop_catalogitem', $catalogitemdata);
            $this->catalogitem = unserialize($catalogitemdata);
            $this->productiondata = unserialize(base64_decode($this->record->productiondata));
            $this->customerdata = unserialize(base64_decode($this->record->customerdata));

            if (!empty($this->productiondata->handlerparams)) {
                if (is_string($this->productiondata->handlerparams)) {
                    $pairs = explode('&', $this->productiondata->handlerparams);
                    if (!empty($pairs)) {
                        foreach ($pairs as $p) {
                            list($param, $value) = explode('=', $p);
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

            if ($ordering == -1) {
                $this->ordering = BillItem::last_ordering($bill->id);
            } else {
                $this->ordering = $ordering;
            }

            /*
             * first creation of a record
             * itemcode is NOT a legacy record field, but comes from shopping front
             */
            $this->record = new \StdClass;
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
                // This gets production data from shop front end. Essentially user definitions.
                $this->productiondata = $idorrec->productiondata;
                // This adds a freezed copy of original handler params.
                $this->productiondata->handlerparams = $this->catalogitem->handlerparams;
                if (!empty($this->productiondata->handlerparams)) {
                    if (is_array($this->productiondata->handlerparams)) {
                        $this->actionparams = $this->productiondata->handlerparams;
                    } else {
                        $pairs = explode('&', $this->productiondata->handlerparams);
                        if (!empty($pairs)) {
                            foreach ($pairs as $p) {
                                list($param, $value) = explode('=', $p);
                                $this->actionparams[$param] = $value;
                            }
                        }
                    }
                }

                // This passes some production params from catalog.
                $this->productiondata->catalogitemdata = $this->catalogitem->productiondata;
                $this->customerdata = $idorrec->customerdata;

                // Deshydrates sub structures in record for storage.
                $this->record->catalogitem = base64_encode(serialize($this->catalogitem));
                $this->record->productiondata = base64_encode(serialize($this->productiondata));

                // Customer data comes from product requirements.
                $this->record->customerdata = base64_encode(serialize($this->customerdata));
            }
        }
    }

    public function move($dir, $z) {
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

    public function get_price() {
        return $this->catalogitem->get_price($this->record->quantity);
    }

    public function get_taxed_price() {
        return $this->catalogitem->get_taxed_price($this->record->quantity);
    }

    public function get_tax_amount() {
        $taxed = $this->catalogitem->get_taxed_price($this->record->quantity);
        $untaxed = $this->catalogitem->get_price($this->record->quantity);
        return $taxed - $untaxed;
    }

    public function get_totaltax() {
        return $this->get_tax_amount() * $this->record->quantity;
    }

    public function get_totaltaxed() {
        return $this->get_taxed_price($this->record->quantity) * $this->record->quantity;
    }

    public function get_customerid() {
        if (empty($this->bill)) {
            // Rehydrates if necessary.
            $this->bill = new Bill($this->billid);
        }
        return $this->bill->customerid;
    }

    public function delete() {
        // Delete products currently attached to.
        $products = Product::get_instances(array('currentbillitemid' => $this->id));
        if ($products) {
            foreach ($products as $p) {
                $p->delete();
            }
        }

        parent::delete();
    }

    public static function last_ordering($billid) {
        global $DB;

        return $DB->get_field('local_shop_billitem', 'MAX(ordering)', array('billid' => $billid));
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}