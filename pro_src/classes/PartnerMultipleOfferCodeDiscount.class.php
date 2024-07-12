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
 * class for disccount instances based on multiple code vs. discount ratio associations.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/pro/classes/MultipleOfferCodeDiscount.class.php');
require_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');

use StdClass;
use context_system;
use local_shop\Partner;

class PartnerMultipleOfferCodeDiscount extends MultipleOfferCodeDiscount {

    /**
     * Offer code checks if a special code has been entered in SESSION's sharing cart, that
     * applies to some or all elements of the order.
     *
     * @param object $bill the bill.
     */
    public function check_applicability(&$bill = null) {
        global $SESSION;

        $this->checked = true;

        $this->get_codes();
        if (empty($this->codes)) {
            // Instance has no codes defined.
            return false;
        }

        if (empty($SESSION->shoppingcart->discountcodes[$this->id])) {
            // We have no codes.
            return false;
        }

        $this->thecode = $SESSION->shoppingcart->discountcodes[$this->id];

        if (!in_array($this->thecode, array_keys($this->codes))) {
            // We have no matching code.
            return false;
        }

        // One of is true and the discount is agreed.
        // Dynamically applies the ratio to the instance, conrresponding to the code.
        $this->ratio = $this->codes[$this->thecode]->ratio;
        // Plug partner code into bill
        $data = new StdClass;
        $data->partnerkey = $this->codes[$this->thecode]->partnerkey;
        $data->partnertag = $this->thecode;
        if (!is_null($bill)) {
            Partner::register_in_bill($bill, $data);
            shop_debug_trace("Partner registered in bill {$bill->id}", SHOP_TRACE_DEBUG_FINE);
        }

        $productiondata = new StdClass;
        $productiondata->code = $this->thecode;
        $productiondata->ratio = $this->ratio;
        $this->productiondata = $productiondata;

        $this->checked = true;
        return true;
    }

    public function preview_applicability() {
        return $this->check_applicability();
    }

    /**
     * Provides eligibility info of the discount for interactive display
     * of customer GUI (block shop_discounts).
     */
    public function is_interactive_eligible() {
        global $SESSION;

        if ($this->applyon == 'itemlist') {
            if (!empty($this->applyondata)) {
                $items = explode(',', $this->applyondata);
                foreach ($items as $it) {
                    if (in_array(trim($it), array_keys($SESSION->shoppingcart->order))) {
                        return true;
                    }
                }
                // Not eligible : item restricted and no item matches.
                return false;
            }
        }
        return true;
    }

    /**
     * checks if the session stored data that triggers the discount is
     * verified.
     */
    public function is_interactive_verified() {
        global $SESSION;

        if (!$this->is_interactive_eligible()) {
            return false;
        }

        $this->get_codes();

        if (empty($SESSION->shoppingcart)) {
            $SESSION->shoppingcart = new StdClass;
        }

        if (empty($SESSION->shoppingcart->discountcodes)) {
            $SESSION->shoppingcart->discountcodes = [];
        }

        if (!empty($SESSION->shoppingcart->discountcodes[$this->id])) {
            $thecode = $SESSION->shoppingcart->discountcodes[$this->id];
            if (in_array($thecode, array_keys($this->codes))) {
                $this->ratio = $this->codes[$thecode]->ratio;
                return true;
            }
        }
        return false;
    }

    public function has_multiple_ratios() {
        return true;
    }

    /**
     * Decode codes to ratio mapping from ruledata.
     */
    protected function get_codes() {

        if (!isset($this->codes)) {

            $codedefs = preg_split('/[\\s,]+/', $this->ruledata); // Can accept several codes to trigger the discount.
            $this->codes = [];
            foreach ($codedefs as $def) {
                list($code, $ratio, $partnerkey) = explode('|', $def);
                $partnerdiscount = new StdClass;
                $partnerdiscount->code = $code;
                $partnerdiscount->ratio = $ratio;
                $partnerdiscount->partnerkey = $partnerkey;
                $this->codes[$code] = $partnerdiscount;
            }
        }
    }

    /**
     * Checks setting from editing form and gives back an error
     * statement if needed.
     * @param object $data Data from instance edition form.
     * @return false if eveything is ok.
     */
    protected function check_ruledata($data) {
        global $DB;

        $codedefs = explode(',', $data->ruledata); // Can accept several codes to trigger the discount.
        $codes = [];
        foreach ($codedefs as $def) {
            if (empty(trim($def))) {
                // blank lines.
                continue;
            }

            $parts = explode('|', $def);
            if (count($parts) != 3) {
                return get_string('discounterror:notenougharguments', 'local_shop');
            }

            if (!empty($parts[0])) {
                return get_string('discounterror:emptycode', 'local_shop');
            }

            if (!is_numeric($parts[1])) {
                return get_string('discounterror:badratioformat', 'local_shop');
            }

            if (!$DB->record_exists('local_shop_partner', ['partnerkey' => $parts[2]])) {
                return get_string('discounterror:badpartnerkey', 'local_shop');
            }
        }

        return false;
    }
}