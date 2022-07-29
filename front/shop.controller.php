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

use \StdClass;
use \moodle_url;
use \core_text;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

class shop_controller extends front_controller_base {

    /**
     * In this case, all the data resides already in session.
     * there is nothing to get from a query.
     *
     * Note : always ensure the productname is passed to lowercase to comply the
     * JS product shortname.
     */
    public function receive($cmd, $data = array()) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new StdClass;
        }

        // Redirection to specific category.
        $this->data->redirect = optional_param('redirect', false, PARAM_BOOL);

        // Autodrive let shop go forward by redirects as far as it cans, tha tis not needing inputing data.
        // This may be used for partner driven purchases.
        $this->data->autodrive = optional_param('autodrive', false, PARAM_BOOL);

        switch ($cmd) {
            case 'import':
                foreach (array_keys($_GET) as $inputkey) {
                    if ($inputkey == 'shipping') {
                        continue;
                    }
                    if ($inputkey == 'partner') {
                        $this->data->partner = optional_param($inputkey, '', PARAM_TEXT);
                        continue;
                    }
                    if (in_array($inputkey, ['view', 'origin', 'what', 'autodrive', 'shopid'])) {
                        continue;
                    }
                    $this->data->$inputkey = optional_param($inputkey, 0, PARAM_INT);
                }
                break;
            case 'clearall':
                break;

            case 'addunit':
                $this->data->shortname = core_text::strtolower(required_param('productname', PARAM_TEXT));
                break;

            case 'setunits':
                $this->data->quant = required_param('quant', PARAM_INT);
                $this->data->shortname = core_text::strtolower(required_param('productname', PARAM_TEXT));
                break;

            case 'deleteunit':
                $this->data->clearall = optional_param('clearall', false, PARAM_BOOL);
                $this->data->shortname = core_text::strtolower(required_param('productname', PARAM_TEXT));
                break;

            case 'checkpasscode':
                $this->data->shortname = core_text::strtolower(required_param('productname', PARAM_TEXT));
                $this->data->passcode = required_param('passcode', PARAM_TEXT);
                break;

            case 'navigate':
                break;
        }
        $this->received = true;
    }

    public function process($cmd) {
        global $SESSION, $CFG, $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $output = '';

        if ($cmd == 'import') {

            unset($SESSION->shoppingcart);
            $SESSION->shoppingcart = new StdClass;
            $SESSION->shoppingcart->order = array();
            foreach (array_keys((array)$this->data) as $inputkey) {
                if ($inputkey == 'shipping') {
                    continue;
                }
                if (local_shop_supports_feature('shop/partners')) {
                    include_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');
                    if ($inputkey == 'partner') {
                        // Save partner info in session.
                        $SESSION->shoppingcart->partner = new StdClass;
                        $parts = explode('_', $this->data->partner);
                        $SESSION->shoppingcart->partner->partnerkey = array_shift($parts);
                        if (!empty($parts)) {
                            // This is a non functionnal entry, just for tagging in backoffice.
                            $SESSION->shoppingcart->partner->partnertag = array_shift($parts); // May be empty.
                        }
                        if (!empty($parts)) {
                            /*
                             * The customer email can serve for preauth when partner is validated and a moodle user
                             * with such mail exists.
                             */
                            $SESSION->shoppingcart->partner->customeremail = array_shift($parts); // May be empty.
                        }
                        $SESSION->shoppingcart->partner->validated = \local_shop\Partner::validate($SESSION->shoppingcart->partner->partnerkey);

                        // Generate precocely a transaction id.
                        $transid = shop_get_transid();
                        $SESSION->shoppingcart->transid = $transid;
                        shop_trace("$transid - Partner call. Partner data : {$this->data->partner}");
                        continue;
                    }
                }
                if (in_array($inputkey, array('shopid', 'origin', 'partner', 'autodrive', 'blockid', 'category', 'view', 'what'))) {
                    continue;
                }

                if ($ci = $DB->get_record('local_shop_catalogitem', array('code' => $inputkey))) {
                    // Only if registered product.
                    $SESSION->shoppingcart->order[$ci->shortname] = $this->data->$inputkey;  // Gives quantity to shortname.
                }
            }
            $category = optional_param('category', '', PARAM_INT);
            $shopid = required_param('shopid', PARAM_INT);
            $blockid = optional_param('blockid', 0, PARAM_INT);
            $params = array('view' => 'shop', 'shopid' => $shopid, 'category' => $category, 'blockid' => $blockid);

            if ($this->data->autodrive) {
                $params['what'] = 'navigate';
                $SESSION->shoppingcart->autodrive = true;
            }
            redirect(new moodle_url('/local/shop/front/view.php', $params));

        } else if ($cmd == 'clearall') {

            unset($SESSION->shoppingcart);
            $params = array('view' => 'shop', 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id);
            return new \moodle_url('/local/shop/front/view.php', $params);

        } else if ($cmd == 'addunit') {

            @$SESSION->shoppingcart->order[$this->data->shortname]++;
            $product = $this->thecatalog->get_product_by_shortname($this->data->shortname);
            $output = new StdClass();
            if (empty($this->data->redirect)) {
                $output->html = $this->renderer->units($product);
                $output->quant = $SESSION->shoppingcart->order[$this->data->shortname];
                $output = json_encode($output);
            } else {
                $category = optional_param('category', '', PARAM_INT);
                $shopid = required_param('shopid', PARAM_INT);
                redirect(new moodle_url('/local/shop/front/view.php?view=shop&shopid='.$shopid.'&category='.$category));
            }

        } else if ($cmd == 'setunits') {
            $product = $this->thecatalog->get_product_by_shortname($this->data->shortname);

            if ($product->maxdeliveryquant) {
                if ($this->data->quant > $product->maxdeliveryquant) {
                    $this->data->quant = $product->maxdeliveryquant;
                }
            }
            @$SESSION->shoppingcart->order[$this->data->shortname] = $this->data->quant;
            if (empty($this->data->redirect)) {
                $output = new StdClass();
                $output->html = $this->renderer->units($product);
                $output->quant = $SESSION->shoppingcart->order[$this->data->shortname];
                $output = json_encode($output);
            } else {
                $category = optional_param('category', '', PARAM_INT);
                $shop = required_param('shopid', PARAM_INT);
                redirect(new moodle_url('/local/shop/front/view.php?view=shop&shopid='.$shopid.'&category='.$category));
            }

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

            if (empty($this->data->redirect)) {
                $outputobj = new StdClass();
                $outputobj->html = $this->renderer->units($catalogitem);
                $outputobj->quant = 0 + @$SESSION->shoppingcart->order[$this->data->shortname];
                $output = json_encode($outputobj);
            } else {
                $category = optional_param('category', '', PARAM_INT);
                $shop = required_param('shopid', PARAM_INT);
                redirect(new moodle_url('/local/shop/front/view.php?view=shop&shopid='.$shopid.'&category='.$category));
            }

        } else if ($cmd == 'orderdetails') {

            $categories = $this->thecatalog->get_all_products($fooproducts); // Loads categories with products.
            $output = new StdClass;
            $output->html = $this->renderer->order_detail($categories);
            $output = json_encode($output);

        } else if ($cmd == 'ordertotals') {

            $this->thecatalog->get_all_products($fooproducts); // Loads categories with products.
            $output = new StdClass;
            $output->html = $this->renderer->order_totals($this->thecatalog);
            $output = json_encode($output);

        } else if ($cmd == 'checkpasscode') {

            $output = new StdClass;
            if ($product = $this->thecatalog->get_product_by_shortname($this->data->shortname)) {
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

            $shoppingcart = &$SESSION->shoppingcart;

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

            if (local_shop_supports_feature('shop/discounts')) {
                include_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');

                $shoppingcart->discounts = \local_shop\Discount::get_applicable_discounts($this->theshop->id);

                if ($shoppingcart->discounts) {
                    $shoppingcart->finaltaxes = [];
                    // Recalculate each time it passes thru if order has changed.
                    $discountpreview = \local_shop\Discount::preview_discount_in_session($this->theshop, true);

                    // Apply all taxes reductions.
                    $shoppingcart->finaltaxestotal = $shoppingcart->taxestotal - $discountpreview->discounttax;
                    if (!empty($shoppingcart->taxes)) {
                        foreach ($shoppingcart->taxes as $tcode => $amountfoo) {
                            if (array_key_exists($tcode, $shoppingcart->taxes) && array_key_exists($tcode, $discountpreview->discounttaxes)) {
                                $shoppingcart->finaltaxes[$tcode] = $shoppingcart->taxes[$tcode] - $discountpreview->discounttaxes[$tcode];
                            } else {
                                $shoppingcart->finaltaxes[$tcode] = 0;
                            }
                        }
                    }
                }
            }

            $shoppingcart->finaluntaxedtotal = $shoppingcart->untaxedtotal - $discountpreview->untaxeddiscount;
            $shoppingcart->finaltaxedtotal = $shoppingcart->taxedtotal - $discountpreview->discount;

            // Consistency check.
            assert($shoppingcart->taxedtotal == $shoppingcart->untaxedtotal + $shoppingcart->taxestotal);
            assert($shoppingcart->finaltaxedtotal == $shoppingcart->finaluntaxedtotal + $shoppingcart->finaltaxestotal);

            $next = $this->theshop->get_next_step('shop');
            $params = array('view' => $next, 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id);

            return new \moodle_url('/local/shop/front/view.php', $params);
        }

        // Other controller output cases.
        return $output;
    }
}