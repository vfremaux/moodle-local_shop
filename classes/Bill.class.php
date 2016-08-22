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
require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

class Bill extends ShopObject {

    static $table = 'local_shop_bill';

    var $items;

    protected $finalshippedtaxedtotal;
    protected $finaltaxedtotal;
    protected $finaltaxestotal;
    protected $finaluntaxedtotal;
    protected $discount;

    var $shipping;

    var $taxlines;

    var $thecatalogue;
    var $theshop;
    var $theblock;
    var $context;

    var $customer;

    var $customeruser;

    // build a full bill plus billitems
    function __construct($idorrecord, &$theShop = null, &$theCatalogue = null, &$theBlock = null, $light = false) {
        global $DB;

        $config = get_config('local_shop');

        $this->thecatalogue = $theCatalogue;
        $this->theshop = $theShop;
        $this->theblock = $theBlock;

        $this->context = \context_system::instance();

        parent::__construct($idorrecord, self::$table);

        $this->shipping = 0;
        $this->discount = 0;

        if ($idorrecord) {

            if ($light) return; // this builds a lightweight proxy of the Bill, without items

            if (is_object($idorrecord)) {
                $id = $idorrecord->id;
            } else {
                $id = $idorrecord;
                $idorrecord = $DB->get_record('local_shop_bill', array('id' => $idorrecord));
            }
            $itemrecs = $DB->get_records('local_shop_billitem', array('billid' => $id));

            $this->finaluntaxedtotal = 0;
            $this->finaltaxestotal = 0;
            $this->finaltaxedtotal = 0;
            foreach ($itemrecs as $itemrec) {
                $billitem = new BillItem($itemrec, $this);
                if ($billitem->itemcode == '_SHIPPING_') {
                    $this->shipping = $itemrec->totalprice; // taxed
                    continue;
                }
                if ($billitem->itemcode == '_DISCOUNT_') {
                    $this->discount = $itemrec->totalprice; // taxed, negative value
                    continue;
                }
                $this->finaluntaxedtotal += $itemrec->unitcost * $itemrec->quantity;
                $this->finaltaxedtotal += $billitem->get_taxed_price() * $itemrec->quantity;
                $taxamount = $billitem->get_tax_amount() * $itemrec->quantity;
                $this->finaltaxestotal += $taxamount;
                $this->taxlines[$billitem->taxcode] = $taxamount;
                $this->items[$itemrec->id] = $billitem;
            }

            if ($this->customer = $DB->get_record('local_shop_customer', array('id' => $idorrecord->customerid))) {
                $this->customeruser = $DB->get_record('user', array('id' => $this->customer->hasaccount));
            } else {
                $this->customeruser = null;
            }

            if (round($this->record->untaxedamount, 2) != round($this->record->amount - $this->record->taxes, 2)) {
                mtrace("Precision Untaxed Warning");
                mtrace("global untaxed amount: ".$this->record->untaxedamount);
                mtrace("Taxed total: ".$this->record->amount);
                mtrace("Taxes: ".$this->record->taxes);
            }

            // Get shop from record if not provided
            if (empty($this->theshop)) {
                $this->theshop = new Shop($this->record->shopid);
            }

            if (empty($this->thecatalogue)) {
                $this->thecatalogue = new Catalog($this->theshop->catalogueid);
            }

            if (empty($this->theblock)) {
                if (!empty($this->record->blockid)) {
                    $this->theblock = shop_get_block_instance($this->record->blockid);
                }
            }
        } else {

            if (empty($theShop)) {
                throw new \Exception('Null Shop not allowed when creating bill');
            }

            if (empty($theCatalogue)) {
                throw new \Exception('Null Shop not allowed when creating bill');
            }

            $lastordering = $DB->get_field('local_shop_bill', 'MAX(ordering)', array());
            $lastordering++;

            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->blockid = (0 + @$this->theblock->instance->id);
            $this->record->shopid = $this->theshop->id;
            $this->record->idnumber = '';
            $this->record->ordering = $lastordering;
            $this->record->customerid = 0;
            $this->record->title = '';
            $this->record->worktype = 'PROD';
            $this->record->status = 'WORKING';
            $this->record->remotestatus = '';
            $this->record->emissiondate = time();
            $this->record->lastactiondate = time();
            $this->record->assignedto = 0;
            $this->record->timetodo = 0;
            $this->record->untaxedamount = 0;
            $this->record->taxes = 0;
            $this->record->amount = 0;
            $this->record->currency = '';
            $this->record->convertedamount = 0;
            $this->record->transactionid = '__'.md5(time()); // randomize a temporary TID
            $this->record->onlinetransactionid = '';
            $this->record->expectedpaiement = 0;
            $this->record->paiedamount = 0;
            $this->record->paymode = '';
            $this->record->ignoretax = 0;
            $this->record->paymentfee = 0;
            $this->record->productionfeedback = '';
            $this->record->test = $config->test;

            $this->items = array();

            $this->save(); // We need absolutely a DB Id for all following operations
        }
    }

    function last_ordering() {
        global $DB;

        return $DB->get_field('local_shop_bill', 'MAX(ordering)', array());
    }

    function generate_unique_transaction() {
        global $DB;

        // Seek for a unique transaction ID.
        $transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 32));
        while ($DB->record_exists('local_shop_bill', array('transactionid' => $transid))) {
            $transid = strtoupper(substr(mysql_escape_string(base64_encode(crypt(microtime() + rand(0,32)))), 0, 40));
        }
        $this->transactionid = $transid;
    }

    function add_item(BillItem $bi) {
        global $USER;

        $this->items[] = $bi;
        $this->untaxedamount += $bi->totalprice;
        $this->taxes += $bi->get_totaltax();
        $this->amount += $bi->get_totaltaxed();
    }

    function add_item_data($birec, $ordering = -1) {
        $billitem = new BillItem($birec, $this, $ordering);
        $this->items[] = $billitem;
        $this->untaxedamount += $billitem->totalprice;
        $this->taxes += $billitem->get_totaltax();
        $this->amount += $billitem->get_totaltaxed();
    }

    function check_discount() {
        global $CFG, $USER;

        $discountrate = shop_calculate_discountrate_for_user($this->amount, $this->context, $reason);

        // trigger adding a DISCOUNT billitem per product if threshold is reached OR if any loggedin user condition matches
        if ($discountrate) {
            foreach ($this->items as $bi) {
                $birec = new StdClass();
                $birec->type = 'DISCOUNT';
                $birec->itemcode = $bi->itemcode;
                $birec->unitcost = - $bi->unitcost * $discountrate / 100;
                $birec->quantity = $bi->quantity;
                $birec->description = 'Product discount';
                $birec->totalprice = - $bi->unitcost * $discountrate / 100 * $bi->quantity;
                $birec->productiondata = '';
                $birec->customerdata = '';
                $billitem = new BillItem($birec, $this);
                $this->items[] = $billitem;

                $this->untaxedamount += $billitem->totalprice;
                $this->taxes += $billitem->get_totaltax();
                $this->amount += $billitem->get_totaltaxed();
            }
        }
    }

    function save($stateonly = false) {
        $billid = parent::save(); // parent has recorded id into our record.

        // Performance optimisation when no change in Bill construction.
        if ($stateonly) {
            return $billid;
        }

        if (!$this->discount) {
            $this->check_discount();
        }

        if (!empty($this->items)) {
            foreach ($this->items as $bi) {
                $bi->save();
            }
        }
        return $billid;
    }

    /**
     * delete all items from memory and DB for complete reconstruction
     */
    function delete_items() {
        global $DB;

        if ($this->record->id) {
            $DB->delete_records('local_shop_billitem', array('billid' => $this->record->id));
            $this->items = array();
        }
    }

    /**
    * delete all taxlines
    */
    function reset_taxlines() {

        if (empty($this->items)) {
            $this->taxlines = array();
        }
    }

    /**
     * should be obsoleted by full Bill object handling
     */
    function recalculate() {
        global $CFG, $DB;

        $sql = "
            SELECT
                SUM(totalprice) as untaxedamount,
                SUM(totalprice * (IF(t.ratio IS NOT NULL,t.ratio,0) / 100)) as taxes,
                SUM(totalprice * (1 + (IF(t.ratio IS NOT NULL,t.ratio,0) / 100))) as amount
            FROM
                {local_shop_billitem} as bi 
            LEFT JOIN
                {local_shop_tax} as t
            ON
                bi.taxcode = t.id
            WHERE
                billid = ?
            GROUP BY 
                billid
        ";
    
        if ($billtotals = $DB->get_record_sql($sql, array($this->id))) {
            $this->untaxedamount = $billtotals->untaxedamount;
            $this->taxes = $billtotals->taxes;
            $this->amount = $billtotals->amount;
            $this->save();
        }
    }

    function delete() {

        // Delete all bill items.
        $billitems = BillItem::get_instances(array('billid' => $this->id));
        if ($billitems) {
            foreach ($billitems as $bi) {
                $bi->delete();
            }
        }

        parent::delete();
    }

    static function get_by_transaction($transid) {
        global $DB;

        if (empty($transid)) {
            throw new \Exception('Empty transaction');
        }

        $record = $DB->get_record('local_shop_bill', array('transactionid' => $transid));
        if (!$record) {
            throw new \Exception('Invalid Transaction Identifier');
        }

        $theShop = new Shop($record->shopid);

        $theCatalogue = new Catalog($theShop->catalogid);
        $bill = new Bill($record, $theShop, $theCatalogue);
        return $bill;
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}