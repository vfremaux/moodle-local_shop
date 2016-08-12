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

namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

class shop_controller extends front_controller_base {

    function process($cmd) {
        global $SESSION;

        if ($cmd == 'import') {
            unset($SESSION->shoppingcart);
            $SESSION->shoppingcart = new StdClass;
            $SESSION->shoppingcart->order = array();
            foreach (array_keys($_GET) as $inputkey) {
                if ($inputkey == 'shipping') {
                    continue;
                }
                $SESSION->shoppingcart->order[$inputkey] = optional_param($inputkey, 0, PARAM_INT);
            }
        } elseif ($cmd == 'clearall') {
            unset($SESSION->shoppingcart);
            redirect(new \moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id)));
        } elseif ($cmd == 'navigate') {

            // precalculates some sums
            $SESSION->shoppingcart->untaxedtotal = 0;
            $SESSION->shoppingcart->taxedtotal = 0;
            $SESSION->shoppingcart->taxestotal = 0;

            // reset all existing taxes counters
            if (!empty($SESSION->shoppingcart->taxes)) {
                foreach($SESSION->shoppingcart->taxes as $tcode => $amountfoo) {
                    $SESSION->shoppingcart->taxes[$tcode] = 0;
                }
            }

            foreach ($SESSION->shoppingcart->order as $shortname => $q) {
                $ci = $this->thecatalog->get_product_by_shortname($shortname);
                $ht = $q * $ci->get_price($q);
                $ttc = $q * $ci->get_taxed_price($q);
                $SESSION->shoppingcart->untaxedtotal += $ht;
                $SESSION->shoppingcart->taxedtotal += $ttc;
                $SESSION->shoppingcart->taxestotal += $ttc - $ht;
                if (!isset($SESSION->shoppingcart->taxes[$ci->taxcode])) {
                    $SESSION->shoppingcart->taxes[$ci->taxcode] = 0;
                }
                $SESSION->shoppingcart->taxes[$ci->taxcode] += $ttc - $ht;
            }

            $SESSION->shoppingcart->discount = 0;
            $SESSION->shoppingcart->finaluntaxedtotal = $SESSION->shoppingcart->untaxedtotal;
            $SESSION->shoppingcart->finaltaxedtotal = $SESSION->shoppingcart->taxedtotal;
            $SESSION->shoppingcart->finaltaxestotal = $SESSION->shoppingcart->taxestotal;

            $discountrate = shop_calculate_discountrate_for_user($SESSION->shoppingcart->untaxedtotal, $this->context, $reason);
            if ($discountrate) {
                $discountmultiplier = $discountrate / 100;
                $SESSION->shoppingcart->untaxeddiscount = $discountmultiplier * $SESSION->shoppingcart->untaxedtotal;
                $SESSION->shoppingcart->finaluntaxedtotal = $SESSION->shoppingcart->untaxedtotal * (1 - $discountmultiplier);
                $SESSION->shoppingcart->finaltaxedtotal = $SESSION->shoppingcart->taxedtotal * (1 - $discountmultiplier);
                $SESSION->shoppingcart->finaltaxestotal = $SESSION->shoppingcart->taxestotal * (1 - $discountmultiplier);
                $SESSION->shoppingcart->discount = $SESSION->shoppingcart->finaltaxedtotal * $discountmultiplier;

                // try one : apply discount to all tax lines
                if (!empty($SESSION->shoppingcart->taxes)) {
                    foreach($SESSION->shoppingcart->taxes as $tcode => $amountfoo) {
                        $SESSION->shoppingcart->taxes[$tcode] *= 1 - $discountmultiplier;
                    }
                }
            }

            redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_next_step('shop'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id)));
        }
    }
}