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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

class shop_controller extends front_controller_base {

    public function process($cmd) {
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
        } else if ($cmd == 'clearall') {
            unset($SESSION->shoppingcart);
            $params = array('view' => 'shop', 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id);
            redirect(new \moodle_url('/local/shop/front/view.php', $params));
        } else if ($cmd == 'navigate') {

            $shoppingcart = $SESSION->shoppingcart;

            // Precalculates some sums.
            $shoppingcart->untaxedtotal = 0;
            $shoppingcart->taxedtotal = 0;
            $shoppingcart->taxestotal = 0;

            // Reset all existing taxes counters.
            if (!empty($shoppingcart->taxes)) {
                foreach ($shoppingcart->taxes as $tcode => $amountfoo) {
                    $shoppingcart->taxes[$tcode] = 0;
                }
            }

            foreach ($shoppingcart->order as $shortname => $q) {
                $ci = $this->thecatalog->get_product_by_shortname($shortname);
                $ht = $q * $ci->get_price($q);
                $ttc = $q * $ci->get_taxed_price($q);
                $shoppingcart->untaxedtotal += $ht;
                $shoppingcart->taxedtotal += $ttc;
                $shoppingcart->taxestotal += $ttc - $ht;
                if (!isset($shoppingcart->taxes[$ci->taxcode])) {
                    $shoppingcart->taxes[$ci->taxcode] = 0;
                }
                $shoppingcart->taxes[$ci->taxcode] += $ttc - $ht;
            }

            $reason = '';

            $discountrate = $this->theshop->calculate_discountrate_for_user($shoppingcart->untaxedtotal, $this->context,
                                                                            $reason);
            if ($discountrate) {
                $discountmultiplier = $discountrate / 100;
                $shoppingcart->discount = $shoppingcart->taxedtotal * $discountmultiplier;
                $shoppingcart->untaxeddiscount = $shoppingcart->untaxedtotal * $discountmultiplier;
                $shoppingcart->finaluntaxedtotal = $shoppingcart->untaxedtotal * (1 - $discountmultiplier);
                $shoppingcart->finaltaxedtotal = $shoppingcart->taxedtotal * (1 - $discountmultiplier);
                $shoppingcart->finaltaxestotal = $shoppingcart->taxestotal * (1 - $discountmultiplier);

                // Try one : apply discount to all tax lines.
                if (!empty($shoppingcart->taxes)) {
                    foreach ($shoppingcart->taxes as $tcode => $amountfoo) {
                        $shoppingcart->taxes[$tcode] *= 1 - $discountmultiplier;
                    }
                }
            } else {
                $shoppingcart->discount = 0;
                $shoppingcart->finaluntaxedtotal = $shoppingcart->untaxedtotal;
                $shoppingcart->finaltaxedtotal = $shoppingcart->taxedtotal;
                $shoppingcart->finaltaxestotal = $shoppingcart->taxestotal;
            }

            $next = $this->theshop->get_next_step('shop');
            $params = array('view' => $next, 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id);
            redirect(new \moodle_url('/local/shop/front/view.php', $params));
        }
    }
}