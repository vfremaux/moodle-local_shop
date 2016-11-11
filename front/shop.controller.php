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

    /**
     * In this case, all the data resides already in session.
     * there is nothing to get from a query.
     */
    public function receive($cmd, $data = array()) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'import':
                foreach (array_keys($_GET) as $inputkey) {
                    if ($inputkey == 'shipping') {
                        continue;
                    }
                    $this->data->$inputkey = optional_param($inputkey, 0, PARAM_INT);
                }
                break;
            case 'clearall':
                break;

            case 'addunit':
                $this->data->shortname = required_param('productname', PARAM_TEXT);
                break;

            case 'setunits':
                $this->data->quant = required_param('quant', PARAM_INT);
                $this->data->shortname = required_param('productname', PARAM_TEXT);
                break;

            case 'deleteunit':
                $this->data->clearall = optional_param('clearall', false, PARAM_BOOL);
                $this->data->shortname = required_param('productname', PARAM_TEXT);
                break;

            case 'checkpasscode':
                $this->data->shortname = required_param('productname', PARAM_TEXT);
                $this->data->passcode = required_param('passcode', PARAM_TEXT);
                break;

            case 'navigate':
                break;
        }
    }

    public function process($cmd) {
        global $SESSION;

        if ($cmd == 'import') {
            unset($SESSION->shoppingcart);
            $SESSION->shoppingcart = new \StdClass;
            $SESSION->shoppingcart->order = array();
            foreach (array_keys((array)$this->data) as $inputkey) {
                if ($inputkey == 'shipping') {
                    continue;
                }
                $SESSION->shoppingcart->order[$inputkey] = $this->data[$inputkey];
            }
        } else if ($cmd == 'clearall') {
            unset($SESSION->shoppingcart);
            $params = array('view' => 'shop', 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id);
            return new \moodle_url('/local/shop/front/view.php', $params);
        } else if ($cmd == 'addunit') {
            @$SESSION->shoppingcart->order[$this->data->shortname]++;
            $product = $this->thecatalog->get_product_by_shortname($this->data->shortname);
            $output = new \StdClass();
            $output->html = $this->renderer->units($product);
            $output->quant = $SESSION->shoppingcart->order[$this->data->shortname];
            $output = json_encode($output);
        } else if ($cmd == 'setunits') {
            $product = $this->thecatalog->get_product_by_shortname($this->data->shortname);

            if ($product->maxdeliveryquant) {
                if ($this->data->quant > $product->maxdeliveryquant) {
                    $this->data->quant = $product->maxdeliveryquant;
                }
            }
            @$SESSION->shoppingcart->order[$this->data->shortname] = $this->data->quant;

            // $theblock->view = 'shop'; // We are necessarily in shop.
            $output = new \StdClass();
            $output->html = $this->renderer->units($product);
            $output->quant = $SESSION->shoppingcart->order[$this->data->shortname];
            $output = json_encode($output);
        } else if ($cmd == 'deleteunit') {
            if ($this->data->clearall) {
                unset($SESSION->shoppingcart->order[$this->data->shortname]);
            } else {
                @$SESSION->shoppingcart->order[$this->data->shortname]--;
            }
            if (@$SESSION->shoppingcart->order[$this->data->shortname] == 0) {
                unset($SESSION->shoppingcart->order[$this->data->shortname]);
            }

            $catalogitem = $this->thecatalog->get_product_by_shortname($this->data->shortname);

            $requiredroles = $this->thecatalog->check_required_roles();

            if ($catalogitem->quantaddressesusers) {
                // If seat based, remove last assign per unit removed.
                foreach ($requiredroles as $role) {
                    if (isset($SESSION->shoppingcart->{$role})) {
                        array_pop($SESSION->shoppingcart->{$role});
                    }
                    if (empty($SESSION->shoppingcart->{$role})) {
                        unset($SESSION->shoppingcart->{$role});
                    }
                }
                if (!empty($SESSION->shoppingcart->assigns) &&
                        array_key_exists($this->data->shortname, $SESSION->shoppingcart->assigns)) {
                    $SESSION->shoppingcart->assigns[$this->data->shortname]--;
                    if ($SESSION->shoppingcart->assigns[$this->data->shortname] == 0) {
                        unset($SESSION->shoppingcart->assigns[$this->data->shortname]);
                    }
                }
            } else {
                // If non seat based, remove assign only when last unit is removed.
                foreach ($requiredroles as $role) {
                    if (isset($SESSION->shoppingcart->{$role})) {
                        unset($SESSION->shoppingcart->{$role});
                    }
                }
                if (!isset($SESSION->shoppingcart->order[$this->data->shortname])) {
                    unset($SESSION->shoppingcart->assigns[$this->data->shortname]);
                }
            }

            $output = new \StdClass();
            $output->html = $this->renderer->units($catalogitem);
            $output->quant = 0 + @$SESSION->shoppingcart->order[$this->data->shortname];
            $output = json_encode($output);
        } else if ($cmd == 'orderdetails') {
            $categories = $this->thecatalog->get_all_products($fooproducts); // Loads categories with products.
            $output = new \StdClass;
            $output->html = $this->renderer->order_detail($categories);
            $output = json_encode($output);
        } else if ($cmd == 'ordertotals') {
            $this->thecatalog->get_all_products($fooproducts); // Loads categories with products.
            $output = new \StdClass;
            $output->html = $this->renderer->order_totals($this->thecatalog);
            $output = json_encode($output);
        } else if ($cmd == 'checkpasscode') {
            $output = new \StdClass;
            if ($product = $this->data->thecatalog->get_product_by_shortname($this->data->shortname)) {
                if ($this->data->passcode == $product->password) {
                    $output->status = 'passed';
                } else {
                    $output->status = 'failed';
                }
            } else {
                $output->status = 'product error';
            }
            $output = json_encode($output);


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
            return new \moodle_url('/local/shop/front/view.php', $params);
        }

        // Other controller output cases.
        return $output;
    }
}