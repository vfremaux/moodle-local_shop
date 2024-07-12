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
 * class for disccount instances based on partner identity identification.
 * Discount is agreed if the purchase belongs to a certain source and an identified partner code,
 * then the discount applies.
 *
 * Interactive applicability : 
 *    - source matches 'I' : applyes when the identified customer is an configured partner, then applying
 * its associated discount ratio.
 *    - source matches 'E' : applyes when the bill is tagged with a partner id as a result from a partner
 * external routing, then applying the parter's associated ratio
 *
 * Non interactive applicability :
 *    - source matches "A" : applyes when the incomming bill is owned by the partner billing account AND
 * the bill has a "registered_product" (till now, its the known criteria, but wil have to evolve)
 *
 * Ruledata are a set of PARTNERID|RATIO|BILLSOURCE triplets.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');

use StdClass;

class PartnerDiscount extends Discount {

    protected $rules;

    public function check_applicability(&$bill = null) {

        $this->checked = true;

        // Load rules into the instance.
        $this->get_rules();
        if (empty($this->rules)) {
            // Instance has no rules defined.
            return false;
        }

        // check decision steps : get partner identifier, get souce code, then search rule applicability.
        list($source, $partner) = $this->identify_partner_and_source($bill);

        if (!$partner || $source == '') {
            return false;
        }

        // partner and source are identified.

        if (!array_key_exists($partner->parnerkey, $this->rules)) {
            return false;
        }

        // partner is in ruleset and may have application.

        if (!array_key_exists($source, $this->rules[$partner->partnerkey])) {
            // this partner has no discount rules for this purchase source.
            return false;
        }

        $this->ratio = $this->rules[$partner->partnerkey][$s];
        $productiondata = new StdClass;
        $productiondata->source = $source;
        $productiondata->ratio = $this->ratio;
        $this->productiondata = $productiondata;
        return true;
    }

    /** 
     * Check in session shoppingcart for some traces of a partnerinfo, or 
     * some logged in status into a partner associated customer account, using
     * $USER => Customer => Partner search.
     * This allows displaying updated info to the customer on shop front.
     */
    public function preview_applicability() {
        global $SESSION, $USER;

        // TODO : check session for partner key and logged in $USER identity. Gues
        // if these data match our datarules.

        return false;
    }

    /**
     * Checks if the discount has global conditions that allows it's application in context.
     * f.e. : is product restricted and has no allowed product in shopping cart.
     * This is an early check to establish the list of evaluable discounts, before detailed
     * applicability is verified.
     */
    public function is_interactive_eligible() {
        return false;
    }

    /**
     * This method check by Ajax if the Discount conditions could be verified from
     * a front shop interactive process. In PartnerDiscount not effective : a Partner
     * should log in with his account and this is a sufficiant condition to match partner status.
     */
    public function is_interactive_verified() {
        return false;
    }

    /**
     * Loads rules from the instance config.
     * Loads the rules as an array keyed by : [partnerkey][sourcecode] => ratio.
     * @return void
     */
    protected function get_rules() {

        if (!isset($this->rules)) {
            $ruledefs = preg_split('/[\\s,]+/', $this->ruledata);
            $this->rules = [];
            foreach ($ruledefs as $def) {
                list($partnerkey, $sources, $ratio) = explode('|', $def);
                $sourcearr = str_split($sources);
                foreach ($sourcearr as $s) {
                    $this->rules[$partnerkey][$s] = $ratio;
                }
            }
        }
    }

    /**
     * While configuring a discount instance, checks the form return for validity of the ruledata
     * @param object $data data from editing form.
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
                return get_string('discounterror:emptypartnerkey', 'local_shop');
            }

            if (!$DB->record_exists('local_shop_partner', ['partnerkey' => $parts[0]])) {
                return get_string('discounterror:badpartnerkey', 'local_shop');
            }

            if (!empty($parts[1])) {
                return get_string('discounterror:emptysourcecode', 'local_shop');
            }

            if (!$this->check_source_code($parts[1])) {
                return get_string('discounterror:badsourcecode', 'local_shop');
            }

            if (!is_numeric($parts[2])) {
                return get_string('discounterror:badratioformat', 'local_shop');
            }
        }

        return false;
    }

    /**
     * Checks if a source code has only a single I E or A or single combination of each
     */
    protected function check_source_code($sourcecode) {
        $chars = str_split($sourcecode);

        $hasi = 0;
        $hase = 0;
        $hasa = 0;
        foreach ($chars as $c) {
            switch($c) {
                case 'E' : {
                    if ($hase == 1) {
                        return false;
                    }
                    $hase = 1;
                }

                case 'I' : {
                    if ($hasi == 1) {
                        return false;
                    }
                    $hasi = 1;
                }

                case 'A' : {
                    if ($hasa == 1) {
                        return false;
                    }
                    $hasa = 1;
                }

                default: 
                    return false;
            }

        }
        return ($hase || $hasi || $hasa);
    }

    /**
     * Identifies a Partner related to the purchase situation, and qualifies
     * the source of the pruchase.
     * @param object $bill the bill.
     */
    protected function identify_partner_and_source($bill) {
        global $DB;

        // First try : type E
        if (!empty($bill->partnerid)) {
            return ['E', new Partner($bill->partnerid)];
        }

        // Second try : type I
        if ($partnerrec = $DB->get_record('local_shop_partner', ['customerid' => $bill->customerid])) {
            $partner = new Partner($partnerrec);

            if ($bill->has_internal_items()) {
                // Internal available products can only be purchased by an automated chain.
                return ['A', $partner];
            } else {
                return ['I', $partner];
            }
        }

        // no identification.
        return ['', null];
    }
}