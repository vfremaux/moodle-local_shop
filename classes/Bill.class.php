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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

use StdClass;
use context_system;
use Exception;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

class Bill extends ShopObject {

    protected static $table = 'local_shop_bill';

    /**
     * an array of BillItem objects
     */
    public $items;
    public $itemcount;

    /**
     * the original value of the bill summating only ordered item costs
     */
    protected $orderamount;

    /**
     * the original value of taxes the bill summating only ordered item costs
     */
    protected $ordertaxes;

    /**
     * the original untaxed value of the bill summating only ordered item costs
     */
    protected $orderuntaxedamount;

    /**
     * the final bill amount including taxes, discounts AND shipping
     * this is stored into the $bill->record->amount attribute in db
     */
    protected $finalshippedtaxedtotal;

    /**
     * the final bill amount including taxes, discounts
     */
    protected $finaltaxedtotal;

    /**
     * the overal taxes
     * this is stored into the $bill->record->taxes attribute in db
     */
    protected $finaltaxestotal;

    /**
     * the overal untaxed total
     * should always be $finaltaxedtotal - $finaltaxestotal
     * this is stored into the $bill->record->untaxedamount attribute in db
     */
    protected $finaluntaxedtotal;

    /**
     * tells something has changed and recalculation is needed bifore any display or
     * use.
     */
    protected $dirty;

    /**
     * the shipping amount
     */
    protected $shipping;

    /**
     * An array with all tax lines for each taxcode.
     */
    public $taxlines;

    /**
     * External object references
     */
    public $thecatalogue;
    public $theshop;
    public $theblock;
    public $context;
    public $customer;
    public $customeruser;

    public $url;

    // Build a full bill plus billitems.
    public function __construct($idorrecord, $light = false, &$theshop = null, &$thecatalogue = null, &$theblock = null) {
        global $DB;

        $config = get_config('local_shop');

        $this->thecatalogue = $thecatalogue;
        $this->theshop = $theshop;
        $this->theblock = $theblock;

        $this->context = context_system::instance();

        parent::__construct($idorrecord, self::$table);

        $this->shipping = 0;
        $this->discount = 0;
        $this->taxlines = [];

        if ($idorrecord) {

            if ($light) {
                // This builds a lightweight proxy of the Bill, without items.
                return;
            }

            // Get shop from record if not provided.
            if (empty($this->theshop)) {
                $this->theshop = new Shop($this->record->shopid);
            }

            if (empty($this->thecatalogue)) {
                $this->thecatalogue = new Catalog($this->theshop->catalogid);
                $this->theshop->thecatalogue = $this->thecatalogue;
            }

            if (empty($this->theblock)) {
                if (!empty($this->record->blockid)) {
                    $this->theblock = \shop_get_block_instance($this->record->blockid);
                }
            }

            $refs['bill'] = $this;
            $this->items = BillItem::get_instances(['billid' => $this->record->id], 'ordering', '*', 0, '', true, $refs);

            $this->recalculate();

            if ($this->customer = $DB->get_record('local_shop_customer', array('id' => $this->record->customerid))) {
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

            $this->url = new moodle_url('/local/shop/bills/view.php', ['view' => 'viewBill', 'id' => $this->id]);

        } else {
            if (empty($theshop)) {
                throw new Exception('Null Shop not allowed when creating bill');
            }

            if (empty($thecatalogue)) {
                throw new Exception('Null Shop not allowed when creating bill');
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
            $this->record->status = SHOP_BILL_WORKING;
            $this->record->remotestatus = '';
            $this->record->invoiceinfo = '';
            $this->record->emissiondate = time();
            $this->record->lastactiondate = time();
            $this->record->assignedto = 0;
            $this->record->timetodo = 0;
            $this->record->untaxedamount = 0;
            $this->record->taxes = 0;
            $this->record->amount = 0;
            $this->record->currency = '';
            $this->record->convertedamount = 0;
            $this->record->transactionid = $this->generate_unique_transaction(); // Randomize a temporary TID.
            $this->record->onlinetransactionid = '';
            $this->record->expectedpaiement = 0;
            $this->record->paiedamount = 0;
            $this->record->paymode = '';
            $this->record->ignoretax = 0;
            $this->record->paymentfee = 0;
            $this->record->productionfeedback = '';
            $this->record->test = $config->test;
            $this->record->partnerid = 0;
            $this->record->partnertag = '';

            $this->items = [];

            $this->dirty = true;

        }
    }

    public static function last_ordering($shopid) {
        global $DB;

        return $DB->get_field('local_shop_bill', 'MAX(ordering)', array('shopid' => $shopid));
    }

    public function generate_unique_transaction() {
        global $DB, $CFG, $SITE;

        $transid = shop_get_transid();
        $this->transactionid = $transid;
    }

    /**
     * Adds a BillItem object to item list
     * Order amounts are updated in order for the discount check to
     * have accurate amount of the original order
     */
    public function add_item(BillItem $bi) {
        shop_trace("[{$this->transactionid}] Add item. ".$bi->itemcode.' * '.$bi->quantity);
        $this->items[$bi->id] = $bi;
        $this->orderuntaxedamount += $bi->totalprice;
        $this->ordertaxes += $bi->get_totaltax();
        $this->orderamount += $bi->get_totaltaxed();
        $this->dirty = true;
    }

    /**
     * Adds an item from a DB record making a BillItem instance
     * Order amounts are updated in order for the discount check to
     * have accurate amount of the original order
     * @param object $birec a record with bill item attributes.
     * @param int $ordering
     * @return a BillItem object;
     */
    public function add_item_data($birec, $ordering = -1) {
        static $statictempid = 999999000;

        shop_trace("[{$this->transactionid}] Bill.Add item data. ".$birec->itemcode.' * '.$birec->quantity);
        $billitem = new BillItem($birec, false, ['bill' => $this], $ordering);
        if (empty($billitem->id)) {
            // For new items never recorded in DB.
            $billitem->id = $statictempid;
            $statictempid++;
        }
        $this->items[$billitem->id] = $billitem;
        $this->orderuntaxedamount += $billitem->totalprice;
        $this->ordertaxes += $billitem->get_totaltax();
        $this->orderamount += $billitem->get_totaltaxed();
        $this->itemcount++;
        $this->dirty = true;
        return $billitem;
    }

    /**
     * delete an item by code
     */
    public function delete_item($itemcode) {

        foreach ($this->items as $id => $item) {
            if ($item->itemcode == $itemcode) {
                unset($this->items[$id]);
            }
        }
        $this->dirty = true;
    }

    /**
     * delete all items from memory and DB for complete reconstruction
     */
    public function delete_items() {
        global $DB;

        if ($this->record->id) {
            $DB->delete_records('local_shop_billitem', array('billid' => $this->record->id));
            $this->items = array();
            $this->orderuntaxedamount = 0;
            $this->ordertaxes = 0;
            $this->orderamount = 0;
        }
    }

    /**
     * Checks discount conditions and setup discount as a special bill item.
     * OBDSOLETE: should be removed
     */
    /*
    public function check_discount() {
        global $DB;

        if (empty($this->items)) {
            return;
        }

        $reason = '';
        $discountrate = $this->theshop->calculate_discountrate_for_user($this->orderamount, $this->context, $reason);

        // Trigger adding a DISCOUNT billitem per product if threshold is reached OR if any loggedin user condition matches.
        if ($discountrate) {

            // Reset all discount data.
            $this->discount = 0;
            $DB->delete_records('local_shop_billitem', array('billid' => $this->id, 'type' => 'DISCOUNT'));

            foreach ($this->items as $biid => $bi) {
                $birec = new StdClass;
                $birec->type = 'DISCOUNT';
                $birec->itemcode = $bi->itemcode;
                $birec->catalogitem = $bi->catalogitem;
                $birec->unitcost = - $bi->unitcost * $discountrate / 100;
                $birec->quantity = $bi->quantity;
                $birec->abstract = 'Product discount';
                $birec->totalprice = - $bi->unitcost * $discountrate / 100 * $bi->quantity;
                $taxamount = - $bi->get_tax_amount() * $discountrate / 100;
                $birec->productiondata = '';
                $birec->customerdata = '';
                $billitem = new BillItem($birec, false, ['bill' => $this]);
                $this->items[] = $billitem;

                /*
                 * echo 'DUuc '.($birec->unitcost).'<br/>';
                 * echo 'DUut '.$bi->get_tax_amount().'<br/>';
                 * echo 'DUU '.($birec->totalprice - $taxamount).'<br/>';
                 * echo 'DUt '.$taxamount.'<br/>';
                 * echo 'DUT '.$birec->totalprice.'<br/><br/>';
                 */
                /*
                $this->discount += $birec->totalprice;
                $this->discounttaxes += $taxamount;
                if (array_key_exists($billitem->taxcode, $this->taxlines)) {
                    $this->taxlines[$billitem->taxcode] += $taxamount;
                } else {
                    $this->taxlines[$billitem->taxcode] = $taxamount;
                }
                $this->untaxeddiscount = $this->discount - $this->discounttaxes;
            }
        }
    }
    */

    public function save($stateonly = false) {
        static $pass = 0;

        if ($this->dirty) {
            shop_trace("[{$this->transactionid}] Bill.save : Dirty state. Pass ".$pass);
            // Recalculate Bill record totalizers from internal items.
            $this->recalculate();
        }

        $pass++.

        shop_trace("[{$this->transactionid}] Bill.save Saving state and record. Pass ".$pass);
        $billid = parent::save(); // Parent has recorded id into our record.

        // Performance optimisation when no change in Bill construction.
        if ($stateonly) {
            return $billid;
        }

        if (!empty($this->items)) {
            foreach ($this->items as $biid => $bi) {
                shop_trace("[{$this->transactionid}] Bill.save Saving Items {$bi->type}/{$bi->itemcode}");
                $bi->save();
                // Reindex in new saved ID.
                $this->items[$bi->record->id] = $bi;
                unset($this->items[$biid]);
            }
        }
        return $billid;
    }

    /**
     * delete all taxlines
     */
    public function reset_taxlines() {

        if (empty($this->items)) {
            $this->taxlines = [];
        }
    }

    /**
     * get bill content (elements) and calculate in-memory
     * bill totalizers. This will update Bill states and sumators
     * for recording in DB.
     */
    public function recalculate() {
        global $DB, $CFG;

        $this->orderuntaxed = 0;
        $this->ordertaxes = 0;
        $this->ordertaxed = 0;
        $this->finaluntaxedtotal = 0;
        $this->finaltaxestotal = 0;
        $this->finaltaxedtotal = 0;
        $this->finalshippedtaxedtotal = 0;
        $this->discount = 0;
        $this->discounttaxes = 0;
        $this->untaxeddiscount = 0;
        $this->itemcount = 0;
        $this->taxlines = array();

        $discounts = [];

        if (!empty($this->items)) {
            foreach ($this->items as $bi) {

                // Deroute some special types.
                if ($bi->type == 'SHIPPING') {
                    $this->shipping = $bi->totalprice; // Taxed.
                    continue;
                }

                if ($bi->type == 'DISCOUNT') {
                    // Collect discount items for discount recalculation.
                    $discounts[] = $bi;
                    continue;
                }

                // If standard BILLING line, aggregate to ordetotals.
                $this->orderuntaxed += $bi->unitcost * $bi->quantity; // Not stored in record.
                $this->ordertaxed += $bi->get_taxed_price() * $bi->quantity; // Not stored in record.
                $taxamount = $bi->get_tax_amount() * $bi->quantity;
                $this->ordertaxes += $taxamount; // Not stored in record.
                $this->itemcount += $bi->quantity; // Not stored in record.

                // Register tax by taxcode.
                if (array_key_exists($bi->taxcode, $this->taxlines)) {
                    $this->taxlines[$bi->taxcode] += $taxamount;
                } else {
                    $this->taxlines[$bi->taxcode] = $taxamount;
                }

                if (($CFG->debug == DEBUG_DEVELOPER) && optional_param('control', false, PARAM_BOOL)) {
                     echo 'UC '.($bi->unitcost * $bi->quantity).'<br/>';
                     echo 'Ut '.$taxamount.'<br/>';
                     echo 'UT '.($bi->get_taxed_price() * $bi->quantity).'<br/>';
                     echo 'OTTC '.($this->ordertaxed).'<br/>';
                     echo 'OHT '.($this->orderuntaxed).'<br/>';
                     echo 'OT '.($this->ordertaxes).'<br/><br/>';
                }
            }
        }

        if (local_shop_supports_feature('shop/discounts')) {
            if (!empty($discounts)) {
                include_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');
                // This will reset all discount counters and recalculate all applied discounts.
                Discount::recalculate_discounts($this);
            }
        }

        $this->finaluntaxedtotal = $this->orderuntaxed + $this->untaxeddiscount;
        $this->finaltaxestotal = $this->ordertaxes + $this->discounttaxes;
        $this->finaltaxedtotal = $this->ordertaxed + $this->discount;

        if ($CFG->debug == DEBUG_DEVELOPER && optional_param('control', false, PARAM_BOOL)) {
             echo 'Bill summary<br/>';
             echo 'OU '.$this->orderuntaxed.'<br/>';
             echo 'Ot '.$this->ordertaxes.'<br/>';
             echo 'OT '.$this->ordertaxed.'<br/><br/>';

             echo 'DU '.$this->untaxeddiscount.'<br/>';
             echo 'Dt '.$this->discounttaxes.'<br/>';
             echo 'DT '.$this->discount.'<br/><br/>';

             echo 'FU '.$this->finaluntaxedtotal.'<br/>';
             echo 'Ft '.$this->finaltaxestotal.'<br/>';
             echo 'FT '.$this->finaltaxedtotal.'<br/>';
            }

        // Transfer to effective DB record.
        $this->record->amount = $this->finaltaxedtotal;
        $this->record->taxes = $this->finaltaxestotal;
        $this->record->untaxedamount = $this->finaluntaxedtotal;

        // Discounts are applied in 'finalshippedtaxedtotal'
        $this->finalshippedtaxedtotal = $this->finaltaxedtotal + $this->shipping; // Not in record.
        $this->dirty = false;
        shop_trace("[{$this->transactionid}] Bill.recalculate : Bill recalculated to final amount : {$this->finalshippedtaxedtotal}");
    }

    public function delete() {

        // Delete all bill items.
        $billitems = BillItem::get_instances(array('billid' => $this->id));
        if ($billitems) {
            foreach ($billitems as $bi) {
                $bi->delete();
            }
        }

        parent::delete();
    }

    public function work($tostatus) {
        global $CFG;

        // Call a transition handler.
        $result = 1;

        include_once($CFG->dirroot.'/local/shop/transitions.php');
        // Lower case because Moodle validation forces all functions to be lowercase.

        $transitionhandler = \core_text::strtolower("bill_transition_{$this->record->status}_{$tostatus}");
        shop_trace('['.$this->transactionid.'] Internal transaction: '.$transitionhandler);
        if (function_exists($transitionhandler)) {
            $result = $transitionhandler($this);
        } else {
            // Just pass to final status.
            $this->status = $tostatus;
            $this->save();
        }
    }

    public static function get_by_transaction($transid) {
        global $DB;

        if (empty($transid)) {
            throw new \Exception('Empty transaction');
        }

        $record = $DB->get_record('local_shop_bill', array('transactionid' => $transid));
        if (!$record) {
            throw new \Exception('Invalid Transaction Identifier');
        }

        $theshop = new Shop($record->shopid);

        $thecatalogue = new Catalog($theshop->catalogid);
        $bill = new Bill($record, false, $theshop, $thecatalogue);
        return $bill;
    }

    public static function count_by_states($fullview, $filterclause) {
        global $DB;

        $total = new StdClass;
        $total->WORKING = $DB->count_records_select('local_shop_bill', " status = 'WORKING' $filterclause");

        if ($fullview) {
            $total->PLACED = $DB->count_records_select('local_shop_bill', "status = 'PLACED' $filterclause");
            $total->PENDING = $DB->count_records_select('local_shop_bill', " status = 'PENDING' $filterclause");
        }

        $total->SOLDOUT = $DB->count_records_select('local_shop_bill', "status = 'SOLDOUT' $filterclause");
        $total->COMPLETE = $DB->count_records_select('local_shop_bill', "status = 'COMPLETE' $filterclause");

        if ($fullview) {
            $total->CANCELLED = $DB->count_records_select('local_shop_bill', " status = 'CANCELLED' $filterclause");
            $total->FAILED = $DB->count_records_select('local_shop_bill', "status = 'FAILED' $filterclause");
        }

        $total->PAYBACK = $DB->count_records_select('local_shop_bill', "status = 'PAYBACK' $filterclause");

        if ($fullview) {
            $total->ALL = $DB->count_records_select('local_shop_bill', " 1 $filterclause ");
        }

        return $total;
    }

    public static function count(array $filter = array()) {
        return parent::_count(self::$table, $filter);
    }

    public static function sum($field, array $filter = array()) {
        return parent::_sum(self::$table, $field, $filter);
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function get_instances_menu($filter = array(), $order = '', $chooseopt = 'choosedots') {
        return parent::_get_instances_menu(self::$table, $filter, $order, "CONCAT(emissiondate, '-', ordering, '-', idnumber)", $chooseopt);
    }

    public function toString() {
        $printable = new StdClass;
        $printable->record = $this->record;
        foreach ($this->items as $bi) {
            $printable->items[] = $bi->toString();
        }
        return $printable;
    }
}