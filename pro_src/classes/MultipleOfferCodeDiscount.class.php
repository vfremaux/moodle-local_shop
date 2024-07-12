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

require_once($CFG->dirroot.'/local/shop/pro/classes/OfferCodeDiscount.class.php');

use StdClass;
use context_system;

class MultipleOfferCodeDiscount extends OfferCodeDiscount {

    /**
     * Offer code checks if a special code has been entered in SESSION's sharing cart, that
     * applies to some or all elements of the order.
     *
     * @param object $bill is unused in this class
     */
    public function check_applicability(&$bill = null) {
        global $SESSION;

        $codes = $this->get_codes();
        if (!empty($codes)) {
            if (!empty($SESSION->shoppingcart->discountcodes[$this->id])) {
                $thecode = $SESSION->shoppingcart->discountcodes[$this->id];
                if (in_array($thecode, array_keys($codes))) {
                    // One of is true and the discount is agreed.
                    // Dynamically applies the ratio to the instance, conrresponding to the code.
                    $this->ratio = $codes[$thecode];

                    $productiondata = new StdClass;
                    $productiondata->code = $thecode;
                    $productiondata->ratio = $this->ratio;
                    $this->productiondata = $productiondata;
                    $this->checked = true;
                    return true;
                }
            }
        }

        $this->checked = true;
        return false;
    }

    public function preview_applicability() {
        $null = null;
        return $this->check_applicability($null);
    }

    /**
     * Provides eligibility info of the discount for interactive display
     * of customer GUI (block shop_discounts).
     */
    public function is_interactive_eligible() {
        global $SESSION;

        if ($this->applyon == 'itemlist') {
            if (!empty($this->applyondata)) {
                $items = preg_split('/[\s,]+/', $this->applyondata);
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

    public function has_multiple_ratios() {
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

        $codes = $this->get_codes();

        if (empty($SESSION->shoppingcart->discountcodes)) {
            if (empty($SESSION->shoppingcart)) {
                $SESSION->shoppingcart = new StdClass;
            }
            $SESSION->shoppingcart->discountcodes = [];
        }

        if (!empty($SESSION->shoppingcart->discountcodes[$this->id])) {
            $thecode = $SESSION->shoppingcart->discountcodes[$this->id];
            if (in_array($thecode, array_keys($codes))) {
                $this->ratio = $codes[$thecode];
                return true;
            }
        }
        return false;
    }

    /**
     * Decode codes to ratio mapping from ruledata.
     */
    protected function get_codes() {
        $codedefs = preg_split('/[\s,]+/', $this->ruledata); // Can accept several codes to trigger the discount.
        $codes = [];
        foreach ($codedefs as $def) {
            list($code, $ratio) = explode('|', $def);
            $codes[$code] = $ratio;
        }

        return $codes;
    }

    /**
     * Checks setting from editing form and gives back an error
     * statement if needed.
     * @param object $data Data from instance edition form.
     * @return false if eveything is ok.
     */
    protected function check_ruledata($data) {
        global $DB;

        $codedefs = preg_split("/[\s,]+/", $data->ruledata); // Can accept several codes to trigger the discount.
        $codes = [];
        foreach ($codedefs as $def) {
            if (empty(trim($def))) {
                // blank lines.
                continue;
            }

            $parts = explode('|', $def);
            if (count($parts) != 2) {
                return get_string('errordiscount:notenougharguments', 'local_shop');
            }

            if (!empty($parts[0])) {
                return get_string('errordiscount:emptycode', 'local_shop');
            }

            if (!is_numeric($parts[1])) {
                return get_string('errordiscount:badratioformat', 'local_shop');
            }
        }

        return false;
    }
}