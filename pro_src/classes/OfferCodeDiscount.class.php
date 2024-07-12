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
 * class for discount instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

use StdClass;
use context_system;

class OfferCodeDiscount extends Discount {

    /**
     * Offer code checks if a special code has been entered in SESSION's sharing cart, that
     * applies to some or all elements of the order.
     *
     * @param object $bill is unused in this class
     */
    public function check_applicability(&$bill = null) {
        global $SESSION;

        if (!empty($SESSION->shoppingcart->discountcodes[$this->id])) {
            $thecode = $SESSION->shoppingcart->discountcodes[$this->id];
            if ($thecode == trim($this->ruledata)) {
                $productiondata = new StdClass;
                $productiondata->code = $thecode;
                $productiondata->ratio = $this->ratio;
                $this->productiondata = $productiondata;
                return true;
            }
        }

        return false;
    }

    public function preview_applicability() {
        return $this->check_applicability(null);
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

        if (!isset($SESSION->shoppingcart)) {
            $SESSION->shoppingcart = new StdClass;
        }

        if (empty($SESSION->shoppingcart->discountcodes)) {
            $SESSION->shoppingcart->discountcodes = [];
        }

        if (!empty($SESSION->shoppingcart->discountcodes[$this->id])) {
            $thecode = $SESSION->shoppingcart->discountcodes[$this->id];
            if ($thecode == trim($this->ruledata)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Provides info about interactive form elements
     * related to this dicount mode.
     * @return a template fragment with info about a 
     * form widget element.
     */
    public function interactive_form() {

        $itemform = new StdClass;

        $context = context_system::instance();

        $itemform->id = $this->id;
        $itemform->itemelm = 'discountcode'.$this->id;
        $itemform->type = 'text';
        $argument = file_rewrite_pluginfile_urls($this->argument, 'pluginfile.php', $context->id,
                'local_shop', 'discountargument', $this->id);
        $itemform->label = $argument;
        $itemform->datatype = PARAM_ALPHANUM;
        $itemform->istext = true;
        $itemform->hasform = true;
        if ($this->is_interactive_eligible()) {
            $itemform->eligible = 'eligible';
        } else {
            $itemform->eligible = '';
        }

        if ($this->is_interactive_verified()) {
            $itemform->verified = 'verified';
            $itemform->verifiedtext = get_string('codeverified', 'local_shop');
        } else {
            $itemform->verified = 'failed';
            $itemform->verifiedtext = get_string('codefailed', 'local_shop');
        }

        $itemform->placeholder = get_string('entercode', 'local_shop');

        return $itemform;
    }

    /**
     * Receives form data and captures what is relevant for the discount instance.
     * the form is displayed in the local_shop_discount block.
     * @param object $data the discount form data
     * @param object $files eventually files collected from customer
     * @return void
     */
    public function interactive_form_return($data, $files = null) {
        global $SESSION;

        if (is_array($data)) {
            // Ensure we have a data object.
            $data = (object) $data;
        }

        if (empty($SESSION->shoppingcart->discountcodes)) {
            $SESSION->shoppingcart->discountcodes = [];
        }

        $itemform = $this->interactive_form();
        $datakey = $itemform->itemelm;
        if (isset($data->{$datakey})) {
            $value = clean_param($data->{$datakey}, $itemform->datatype);

            if ($value) {
                // Explicit set in session.
                // Add the offercode to collected codes.
                if (!array_key_exists($this->id, $SESSION->shoppingcart->discountcodes)) {
                    $SESSION->shoppingcart->discountcodes[$this->id] = $value;
                }
            } else {
                // Explicit unset.
                if (array_key_exists($this->id, $SESSION->shoppingcart->discountcodes)) {
                    unset($SESSION->shoppingcart->discountcodes[$this->id]);
                }
            }
        }

        if (array_key_exists($this->id, $SESSION->shoppingcart->discountcodes)) {
            return $SESSION->shoppingcart->discountcodes[$this->id];
        }
    }

    public function export_for_template() {
        $export = parent::export_for_template();
        $export->hasform = method_exists($this, 'interactive_form');
        $export->istext = true;
        $export->label = format_text($this->argument, $this->argumentformat);
        return $export;
    }
}