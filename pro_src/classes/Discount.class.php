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
 * class for disconunt instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

use StdClass;

abstract class Discount extends ShopObject {

    public const APPLY_ON_FULL_BILL = 'bill';

    public const APPLY_ON_ITEM_LIST = 'itemlist';

    public static $table = 'local_shop_discount';

    /*
     * The current instance final applicable ratio.
     * this local ratio overrides the record magic value.
     */
    public $ratio;

    /**
     * Standard ShopObject model constructor
     * @param mixed $idorrecord
     * @param bool $light lightweight control to get a lightweight object.
     */
    protected function __construct($idorrecord, $lightweight = false) {
        global $CFG;

        parent::__construct($idorrecord, self::$table);
        $this->productiondata = new StdClass();
        $this->ratio = $this->record->ratio;

        if ($lightweight) {
            // This builds a lightweight proxy of the Discount, without bills.
            return;
        }

        $this->bills = self::get_bill_instances($this->id);
    }

    public function set_productiondata($productiondata) {
        $this->productiondata = $productiondata;

        // Restore a discount ratio if there are some productiondata
        if (!empty($this->productiondata)) {
            if (isset($this->productiondata->ratio)) {
                $this->ratio = $this->productiondata->ratio;
            } else {
                $this->ratio = $this->record->ratio;
            }
        }
    }

    /**
     * Static instance retriever. Magically loads the discount class instance if needed.
     */
    public static function instance($discountorid) {
        global $DB, $CFG;

        if (is_object($discountorid)) {
            $type = $discountorid->type;
            if (empty($type)) {
                throw new \coding_exception("Discount type cannot be empty\nsource data is ".print_r($discountorid, true));
            }

            if (empty($discountorid->shopid)) {
                throw new \coding_exception("Discount shopid cannot be empty\nsource data is ".print_r($discountorid, true));
            }

        } else {
            $type = $DB->get_field('local_shop_discount', 'type', ['id' => $discountorid]);
        }

        if (empty($type)) {
            return null;
            // throw new \coding_exception("Discount type cannot be empty\nsource data is ".print_r($discountorid, true));
        }

        $class = $type.'Discount';
        include_once($CFG->dirroot.'/local/shop/pro/classes/'.$class.'.class.php');
        $class = '\\local_shop\\'.$class;
        return new $class($discountorid);
    }

    /**
     * Applies discount rules on the session shoppingcart to preview discount.
     * @param object $shop the current shop context
     */
    public static function preview_discount_in_session($shop, $recalc = false) {
        global $SESSION;
        static $discountpreview; // Some local memory caching.

        if (empty($SESSION->shoppingcart)) {
            // Quick enmpty trap.
            return false;
        }

        if (!empty($discountpreview) && !$recalc) {
            // Give back cached data or recalculate it.
            return $discountpreview;
        }

        $discountpreview = new StdClass;
        $discountpreview->ispartial = 0;
        $discountpreview->reason = '';
        $discountpreview->discount = 0;
        $discountpreview->discounttax = 0;
        $discountpreview->untaxeddiscount = 0;
        if (empty($discountpreview->discounttaxes)) {
            $discountpreview->discounttaxes = [];
        }

        $discounts = self::get_applicable_discounts($shop->id);
        $thecatalog = $shop->get_catalogue();

        $rates = [];
        if (!empty($discounts)) {
            foreach ($discounts as $di) {
                $preview = $di->preview($thecatalog);

                if (!$preview) {
                    // Not applicable in preview.
                    continue;
                }

                if ($di->operator == 'takeover') {
                    // If takeover, reset variables and prepare to go out.
                    $rates = [];
                    $discountpreview->discount = 0;
                    $discountpreview->reason = '';
                }

                $rates[] = $di->ratio;
                $discountpreview->discount += $preview->discount; // All discounts value.
                $discountpreview->discounttax += $preview->discounttax; // Tax sum on discounts.

                foreach ($preview->discounttaxes as $taxid => $taxvalue) {
                    if (!array_key_exists($taxid, $discountpreview->discounttaxes)) {
                        $discountpreview->discounttaxes[$taxid] = $taxvalue;
                    } else {
                        $discountpreview->discounttaxes[$taxid] += $taxvalue;
                    }
                }

                $discountpreview->untaxeddiscount += $preview->untaxeddiscount; // Untaxed value of all discounts.
                if ($preview->ispartial && !$discountpreview->ispartial) {
                    // Catch definitively a partial state. That is, the ratio does not apply on all the order.
                    $discountpreview->ispartial = true;
                }

                $discountpreview->discounts[] = $preview;

                if ($di->operator == 'takeover') {
                    // Go out of foreach. Takeover finishes.
                    break;
                }
            }

            // Give average ratio.
            $ratenums = count($rates);
            $discountpreview->ratio = ($ratenums) ? array_sum($rates) / $ratenums : 0;
        }

        return $discountpreview;
    }

    /**
     * Get in session the amount of discounted tax fot the given taxcode
     */
    public function get_discount_tax($taxid) {
        global $SESSION;

        return @$SESSION->shoppingcart->discounttaxes[$taxid];
    }

    /**
     * Get all instances of bills that apply the discount.
     * @param int $discountid
     */
    public static function get_bill_instances($discountid) {
        global $DB;

        $discountitems = $DB->get_records('local_shop_billitem', ['type' => 'DISCOUNT', 'itemcode' => 'D'.$discountid]);
        $billids = [];
        foreach ($discountitems as $di) {
            $billids[$di->billid] = true;
        }
        $billids = array_keys($billids);

        return null;
    }

    public static function get_applicable_discounts($shopid) {
        global $DB, $CFG;

        $discountrecs = $DB->get_records('local_shop_discount', ['shopid' => $shopid, 'enabled' => 1], 'ordering');

        $discounts = [];
        if (!empty($discountrecs)) {
            foreach ($discountrecs as $drec) {
                $class = $drec->type.'Discount';
                include_once($CFG->dirroot.'/local/shop/pro/classes/'.$class.'.class.php');
                $class = '\\local_shop\\'.$class;
                $discounts[$drec->id] = new $class($drec);
            }
        }

        return $discounts;
    }

    /**
     * Get all administrable instances.
     */
    public static function get_instances_for_admin($shopid) {
        global $DB, $CFG;

        $discountrecs = $DB->get_records('local_shop_discount', ['shopid' => $shopid], 'ordering');

        $discounts = [];
        if (!empty($discountrecs)) {
            foreach ($discountrecs as $drec) {
                $class = $drec->type.'Discount';
                include_once($CFG->dirroot.'/local/shop/pro/classes/'.$class.'.class.php');
                $class = '\\local_shop\\'.$class;
                $discounts[$drec->id] = new $class($drec);
            }
        }

        return $discounts;
    }

    /**
     * Each Discount subclass will implement its own applicability algorithm.
     * Check applicability conditions when creating the order. Usually, we can chack applicability
     * from a Bill object, once it has been fully assembled.
     */
    public abstract function check_applicability(&$bill = null);

    /**
     * Each Discount subclass will implement its own applicability algorithm
     * Checks applicability in early stages of the purchase process, when data is only available
     * from Ajax queries or from shoppingcart in session. Maybe all context capable to make decisions
     * is not available. Preview_applicability will try to do the best it can.
     * The pupose of this function is to send back applicability status for early stages signals to the customer.
     */
    public abstract function preview_applicability();

    /**
     * Gives a discount preview calculation of the discount instance using the in-session Shopping cart
     * Default is to give the discount calculation from session in the instance scope.
     * Preview scans all the order and calculates the application of the current Discount instance on each
     * prduct which has been ordered. Tax, dicount amount (untaxed and taxed) are summarized. Taxes are 
     * globalized and also stored "per tax code" summators.
     * @return A preview object.
     */
    public function preview($thecatalog) {
        global $SESSION, $CFG;

        $debug = optional_param('debug', false, PARAM_BOOL);

        if (!$this->preview_applicability()) {
            if ($debug) {
                echo "Debug : $this->name is Not applicable<br/>";
            }
            return false;
        }

        if ($this->applyon == 'itemlist') {
            $includes = [];
            $applydata = $this->applydata; // Take care : magic getter with empty().
            if (!empty($applydata)) {
                $items = explode(',', $applydata);
                foreach ($items as $it) {
                    $includes[] = trim($it);
                }
            } else {
                // No item listed. Nothing passes thru.
                if ($debug) {
                    echo "Debug : $this->name Has no items listed<br/>";
                }
                return false;
            }
        }

        $preview = new StdClass;
        $preview->ispartial = false;
        $preview->discounttaxes = [];

        if (empty($SESSION->shoppingcart->order)) {
            // Empty order, discounts do not apply. Quick trap.
            return;
        }

        $totalobjects = 0;
        $amount = 0;
        $untaxed = 0;
        $taxes = 0;
        foreach ($SESSION->shoppingcart->order as $shortname => $q) {
            $totalobjects += $q;
            $ci = $thecatalog->get_product_by_shortname($shortname);
            if (!empty($includes) && !in_array($ci->code, $includes)) {
                $preview->ispartial = true;
                continue;
            }
            // Summ only on discounted items.
            $ttc = $ci->get_taxed_price($q) * $q;
            $ht = $ci->get_price($q) * $q;
            $amount += $ttc;
            $untaxed += $ht;
            $tax = $ci->get_tax($q) * $q;

            // Tax globalizer on all order.
            $taxes += $tax;
            $taxcode = $ci->taxcode; // Magic function !
            if (!empty($taxcode)) {
                // Dispatch taxes accross tax instances.
                if (!array_key_exists($ci->taxcode, $preview->discounttaxes)) {
                    $preview->discounttaxes[$ci->taxcode] = 0;
                }
                $preview->discounttaxes[$ci->taxcode] += $tax * $this->ratio / 100;
            }
        }

        $preview->discountrate = $this->ratio;
        $preview->discount = $amount * $this->ratio / 100;
        $preview->untaxeddiscount = $untaxed * $this->ratio / 100;
        $preview->discounttax = $taxes * $this->ratio / 100;
        $preview->reason = $this->name;

        return $preview;
    }

    /**
     * Provides eligibility info of the discount for interactive display
     * of customer GUI (block shop_discounts).
     * This method tells the discount block that this discount instance has something to
     * ask or disply to the customer on the shop interface.
     */
    public function is_interactive_eligible() {
        if (!empty($this->argument)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Provides a mustache template dataset for dsplaying
     * an interactive form in the discount block content.
     * @return object or empty string.
     */
    public function interactive_form() {
        if (!empty($this->argument)) {
            $formtpl = new Stdclass;
            $formtpl->label = $this->argument;
            return $formtpl;
        } else {
            return '';
        }
    }

    /**
     * A function to process interactive form returns when the discount block
     * form is submitted.
     * @param object $data the submitted data
     * @param array $files file descriptors if files are uploded.
     */
    public function interactive_form_return($data, $files = null) {
    }

    /**
     * Default template data content for the disocunt block content.
     */
    public function export_for_template() {
        $export = clone($this->record);
        if (empty($this->argument)) {
            return $export;
        }

        $export->label = format_text($this->argument, $this->argumentformat);
        $export->hasform = false;
        return $export;
    }

    /**
     * Add properly configured discount BillItems into a Bill for each
     * ordered BillItem where this Discount instance has effect.
     * Recalculates bill discount sumators.
     * @param objectref &$bill the bill.
     */
    public function apply_to_bill(&$bill) {
        global $CFG;

        if ($this->enabled == false) {
            return;
        }

        if ($this->applyon == self::APPLY_ON_FULL_BILL) {
            $catchall = true;
        } else {
            $catchall = false;
            $includelist = explode(',', $this->applydata);
            foreach ($includelist as &$itemcode) {
                $itemcode = trim($itemcode); // Deal with spaces.
            }
        }

        if (!empty($bill->items)) {
            foreach ($bill->items as $biid => $bi) {

                if ($bi->type == 'SHIPPING' || $bi->type == 'DISCOUNT') {
                    // Exclude all shipping records. Do not even think discount on discounts. This should NOT happen, but...
                    continue;
                }

                if (!$catchall && !in_array(trim($bi->itemcode), $includelist)) {
                    // Exclude out of scope items if partial discount.
                    continue;
                }

                assert(!empty($this->checked));

                $birec = new StdClass;
                $birec->type = 'DISCOUNT';
                $birec->itemcode = 'D'.$this->id.'_'.$bi->itemcode;
                $birec->catalogitem = $bi->catalogitem;
                $birec->unitcost = - $bi->unitcost * $this->ratio / 100;
                $birec->quantity = $bi->record->quantity;
                $birec->taxcode = $bi->record->taxcode;
                $birec->abstract = $this->name.' '.$this->ratio.'%';
                $birec->description = '';
                $birec->totalprice = $birec->unitcost * $bi->quantity;
                $taxamount = - $bi->get_tax_amount() * $this->ratio / 100;
                $this->productiondata->itemcode = $bi->itemcode;
                $birec->productiondata = json_encode($this->productiondata);
                $birec->customerdata = '';
                $billitem = new BillItem($birec, false, ['bill' => $bill]);
                $billitem->save();
                $bill->items[$birec->itemcode] = $billitem;

                if ($CFG->debug == DEBUG_DEVELOPER && optional_param('control', false, PARAM_BOOL)) {
                     echo 'Applying discount<br/>';
                     echo 'DUucht '.($birec->unitcost).'<br/>';
                     echo 'DUut '.$bi->get_tax_amount().'<br/>';
                     echo 'DUUht '.($birec->totalprice + $taxamount).'<br/>';
                     echo 'DUt '.$taxamount.'<br/>';
                     echo 'DUTtc '.$birec->totalprice.'<br/><br/>';
                }

                $bill->untaxeddiscount += $birec->totalprice;
                $bill->discounttaxes += $taxamount;
                if (array_key_exists($billitem->taxcode, $bill->taxlines)) {
                    $bill->taxlines[$billitem->taxcode] += $taxamount;
                } else {
                    $bill->taxlines[$billitem->taxcode] = $taxamount;
                }
                $bill->discount = $bill->untaxeddiscount + $bill->discounttaxes;
            }
        }
    }

    /**
     * Recalculates all discount application if the bill has changed, f.e. quantities have changed
     * or some items where removed.
     * Note that the discounts will be reapplied with actualized ratios or parameters, but keeping
     * orginal produciton data.
     * @param object $bill the bill.
     * @param array $discountbillitems array of BillItems representing the previous discounts
     * in the actualized bill.
     */
    public static function recalculate_discounts($bill) {
        global $DB;

        $olddiscountbillitems = [];

        // Calculate on items.
        if (!empty($bill->items)) {
            // Remove all discount items from the bill, but keep track of those that were 
            // before recalculation.
            foreach ($bill->items as $itemid => $bi) {
                if ($bi->type == 'DISCOUNT') {
                    $olddiscountbillitems[] = clone($bi);
                    unset($bill->items[$itemid]);
                    // Also remove in DB.
                    // echo "Deleting $bi->itemcode ";
                    $bi->delete();
                }
            }
        }

        // Reset all discount counters in bill.
        $bill->discount = 0;
        $bill->untaxeddiscount = 0;
        $bill->discounttaxes = 0;

        // We still have them in discountbillitems, so we can recreate them on the updated bill.
        // For every discount instance that was applied originally, reapply those items.
        // Bill recalculation SHOULD NOT apply new discounts that have become available because
        // a bill is supposed to be stalled at the date it was created.
        $discounts = [];
        foreach ($olddiscountbillitems as $bi) {
            // Find the discountid.
            preg_match('/D([\d+]?)_/', $bi->itemcode, $matches);
            $discountid = $matches[1];

            $discount = Discount::instance($discountid);
            if (is_null($discount)) {
                // Deleted dicount.
                continue;
            }
            $discount->set_productiondata(json_decode($bi->productiondata));

            /*
             * Just check to refresh info. But do NOT reconsider applicability.
             * The discount may "have been" applicable when the order was created and
             * this should not be nulled.
             */
             // Resgister discount into activated discounts.
            if (!in_array($discountid, $discounts)) {
                $discounts[$discountid] = $discount;
            }
        }

        // Apply all activated discounts to the bill.
        foreach ($discounts as $discount) {
            $discount->apply_to_bill($bill);
        }
    }

    /**
     * discounts should not be deleted if having bills using the instance.
     */
    public function can_delete() {
        return empty($this->bills);
    }

    /**
     * Tells that this instance has dynamic ratio from the internal configuration
     * that depends on runtime.
     */
    public function has_multiple_ratios() {
        return false;
    }

    /**
     * Extract info about activated discounts and get summary
     * for bill display in backoffice.
     * @param object ref &$template the bill_merchant_line
     * @param object $bill the bill
     */
    public static function export_to_bill_template(&$template, $bill) {

        if (empty($bill->items)) {
            return;
        }

        $template->data1 = $bill->untaxeddiscount;
        $template->source1 = 'untaxeddiscount';

        $template->data2 = $bill->discount;
        $template->source2 = 'discount';

        $template->hasdata2 = false;
        if ($bill->untaxeddiscount != $bill->discount) {
            $template->hasdata2 = true;
        }

        $template->discounttaxes = $bill->discounttaxes;

        $discountcodes = [];
        $template->billhasdiscounts = false;
        foreach ($bill->items as $bi) {
            if ($bi->type != 'DISCOUNT') {
                continue;
            }

            $template->billhasdiscounts = true;
            // Extract codes from production metadata.
            if (!empty($productiondata = json_decode($bi->productiondata))) {
                if (!empty($productiondata->code)) {
                    $discountcodes[] = $productiondata->code;
                }
            }
        }

        $template->discountcodes = implode(', ', $discountcodes);
    }

    /**
     * discounts should not be deleted if having bills using the instance.
     */
    public function delete(): void {
        if (empty($this->bills)) {
            parent::delete();
        }
    }

    public static function count($filter) {
        return parent::_count(self::$table, $filter);
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '', $light = false) {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum, $light);
    }

    public static function get_instances_menu($filter = array(), $order = 'name') {
        return parent::_get_instances_menu(self::$table, $filter, $order, 'name');
    }
}