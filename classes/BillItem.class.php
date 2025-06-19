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
 * A bill item is a single order line of a bill/order record.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

use StdClass;

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

/**
 * A Bill Item represents an order line with all the context that was there when
 * it was created. It stores a freezed image of the catalog item (may be even disconnected from
 * deleted catalogs) for reference to a stable price table.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class BillItem extends ShopObject {

    /**
     * DB table (for ShopObject)
     */
    protected static $table = 'local_shop_billitem';

    /**
     * the BillItem usually belongs to a Bill (or order).
     * The $bill variable is populated with a full bill reference, or 
     * a partially rehydrated object.
     */
    public $bill;

    /**
     * the BillItem is usually representing a purchase operation related to an 
     * Item in the shop's Catalog
     */
    public $catalogitem;

    /**
     * Production data is a data array coming from internal dynamic calculation
     * in moodle, before production is performed.
     */
    protected $productiondata;

    /**
     * Customer data comes from customer input when activating the purchase workflow.
     * Some catalogitems may ask the customer to input his own information at purchase time.
     */
    protected $customerdata;

    /**
     *
     */
    protected $nosave;

    /**
     * Action params come from the handler's parameter in the catalogitem definition.
     */
    public $actionparams;

    /**
     * Builds a bill item object
     * @param mixed $idorrec
     * @param bool $light
     * @param array $internalrefs
     * @param $ordering
     * @param bool $nosave
     */
    public function __construct($idorrec, $light = false, $internalrefs = [], $ordering = -1, $nosave = false) {

        if (array_key_exists('bill', $internalrefs)) {
            $this->bill = $internalrefs['bill'];
        }
        $this->nosave = $nosave;

        // Here we make some assertions to check the billitem integrity.
        parent::__construct($idorrec, self::$table);

        if (!empty($this->record->id)) {
            if (empty($this->bill)) {
                $this->bill = new Bill($this->record->billid);
            }

            $message = " ({$this->record->unitcost} * {$this->record->quantity}) == {$this->record->totalprice} ";
            if (!assert(($this->record->unitcost * $this->record->quantity) == $this->record->totalprice, $message)) {
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            }

            /*
             * Reydrates the original catalog item stored when bill was created.
             * This ensures getting exact prices from the moment, event if changed in catalog inbetween.
             */
            if ($catalogitemdata = base64_decode($this->record->catalogitem)) {
                // Very old records from older version. Will deprecate soon.
                $catalogitemdata = str_replace('block_shop_catalogitem', 'local_shop_catalogitem', $catalogitemdata);
                $this->catalogitem = unserialize($catalogitemdata);
            } else {
                $this->catalogitem = '';
            }
            if ($this->record->type != 'DISCOUNT') {
                $this->productiondata = unserialize(base64_decode($this->record->productiondata));
                $this->customerdata = unserialize(base64_decode($this->record->customerdata));
            }

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
            if (empty($this->bill)) {
                throw new \Exception('A bill is expected to build a new BillItem');
            }

            if ($ordering == -1) {
                $this->ordering = self::last_ordering($this->bill->id);
            } else {
                $this->ordering = $ordering;
            }

            /*
             * first creation of a record
             * itemcode is NOT a legacy record field, but comes from shopping front
             */
            $this->record = new StdClass();
            $this->record->type = $idorrec->type;

            if ($idorrec->type != 'BILLING') {
                // These are pseudo products.
                $this->record->billid = $this->bill->id;
                $this->record->itemcode = $idorrec->itemcode;
                if ($idorrec->type == 'DISCOUNT') {
                    $this->catalogitem = $this->bill->thecatalogue->get_product_by_code($idorrec->productiondata);
                }
                if ($this->catalogitem) {
                    $this->record->catalogitem = base64_encode(serialize($this->catalogitem));
                } else {
                    $this->record->catalogitem = '';
                }
                $this->record->unitcost = 0 + $idorrec->unitcost;
                if ($this->catalogitem) {
                    $this->record->taxcode = $this->catalogitem->taxcode;
                } else {
                    $this->record->taxcode = $idorrec->taxcode;
                }
                $this->record->totalprice = $idorrec->unitcost * $idorrec->quantity;
                $this->record->quantity = 0 + $idorrec->quantity;
                $this->record->abstract = ($idorrec->abstract) ? $idorrec->abstract : '';
                $this->record->description = ($idorrec->description) ? $idorrec->description : '';
                $this->record->productiondata = ($idorrec->productiondata) ? $idorrec->productiondata : '';
                $this->record->customerdata = '';
            } else {
                $this->catalogitem = $this->bill->thecatalogue->get_product_by_shortname($idorrec->itemcode);
                $this->record->billid = $this->bill->id;
                $this->record->itemcode = $this->catalogitem->code;
                $this->record->unitcost = 0 + $this->catalogitem->get_price($idorrec->quantity);
                $this->record->taxcode = $this->catalogitem->taxcode;
                $this->record->totalprice = $idorrec->quantity * $this->record->unitcost;
                $this->record->quantity = $idorrec->quantity;
                $this->record->abstract = $this->catalogitem->name;
                $this->record->description = $this->catalogitem->description;
                // This gets production data from shop front end. Essentially user definitions.
                $this->productiondata = $idorrec->productiondata;
                // This adds a freezed copy of original handler params.
                if (empty($this->productiondata)) {
                    $this->productiondata = new \StdClass;
                }
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

    /**
     * List control. Moves a bill item in ordering.
     * @param $dir the ordering shift
     * @param $z the starting point.
     * @TODO seems a bit simplist. Review using treelib.
     */
    public function move($dir, $z) {
        global $DB;

        $sql = "
            UPDATE
                {local_shop_billitem}
            SET
                ordering = ordering + ?
            WHERE
                ordering = ? AND
                billid = ?
        ";
        $DB->execute($sql, [$dir, $z, $this->id]);
    }

    /**
     * Get the associated Catalog Item
     */
    public function get_catalog_item() {
        return $this->catalogitem;
    }

    /**
     * Get the quantity indexed unit price.
     */
    public function get_price() {
        if (!empty($this->catalogitem)) {
            return $this->catalogitem->get_price($this->record->quantity);
        } else {
            return $this->unitcost;
        }
    }

	/**
	 * Get the quantity indexed unit price with tax applied.
	 */
    public function get_taxed_price() {
        global $DB;
        static $taxcache;

        if (!empty($this->catalogitem)) {
            return $this->catalogitem->get_taxed_price($this->record->quantity);
        } else {
            // Calculate with real tax.

            if (empty($taxid)) {
                $taxid = $this->taxcode;
            }

            if ($taxid == 0) {
                return $this->get_price();
            }

            if (!isset($taxcache)) {
                $taxcache = [];
            }
            if (!array_key_exists($taxid, $taxcache)) {
                if ($taxcache[$taxid] = $DB->get_record('local_shop_tax', ['id' => $taxid])) {
                    if (empty($taxcache[$taxid]->formula)) {
                        $taxcache[$taxid]->formula = '$ttc = $ht';
                    }
                } else {
                    return $this->get_price();
                }
            }

            $in['ht'] = $this->get_price();
            $in['tr'] = $taxcache[$taxid]->ratio;
            $result = evaluate(\core_text::strtolower($taxcache[$taxid]->formula).';', $in, 'ttc');
            $this->tax = $result['ttc'] - $in['ht'];

            return $result['ttc'];
        }
    }

	/**
	 * Get tax amount on unit product.
	 */
    public function get_tax_amount() {
        $taxed = $this->get_taxed_price();
        $untaxed = $this->get_price();
        return $taxed - $untaxed;
    }

    /**
     * Get tax amount on order with quantity factor.
     */
    public function get_totaltax() {
        return $this->get_tax_amount() * $this->record->quantity;
    }

    /**
     * Get the taxed total amount to charge to customer.
     */
    public function get_totaltaxed() {
        return $this->get_taxed_price($this->record->quantity) * $this->record->quantity;
    }

    /**
     * Get the customerid from the surrounding Bill context
     */
    public function get_customerid() {
        if (empty($this->bill)) {
            // Rehydrates if necessary.
            $this->bill = new Bill($this->billid);
        }
        return $this->bill->customerid;
    }

    /**
     * Save the bill item in DB
     */
    public function save() {
        if (!empty($this->nosave)) {
            // This can occur when faking bundle parts.
            return;
        }
        // to be really sure !!
        $this->record->unitcost = 0 + $this->record->unitcost;
        $this->record->quantity = 0 + $this->record->quantity;
        parent::save();
        $this->id = $this->record->id;
    }

    /**
     * Delete this BillItem instance
     */
    public function delete(): void {
        // Delete products currently attached to.
        $products = Product::get_instances(['currentbillitemid' => $this->id]);
        if ($products) {
            foreach ($products as $p) {
                $p->delete();
            }
        }

        parent::delete();
    }

    /**
     * Get last ordering in Bill context
     * @param int $billid
     */
    public static function last_ordering($billid) {
        global $DB;

        return $DB->get_field('local_shop_billitem', 'MAX(ordering)', ['billid' => $billid]);
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * àparam int $limitnum
     * @param bool $light
     * @param array $internalrefs
     */
    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0,
                $limitnum = '', $light = false, $internalrefs = []) {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum, $light, $internalrefs);
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     * @param string $chooseopt
     */
    public static function get_instances_menu($filter = [], $order = '', $chooseopt = 'choosedots') {
        $fields = "CONCAT(billid, '-', ordering, '-', itemcode)";
        return parent::_get_instances_menu(self::$table, $filter, $order, $fields, $chooseopt);
    }

    /**
     * Stringifier
     */
    public function toString() {
        $printable = $this->record;
        return $printable;
    }
}
