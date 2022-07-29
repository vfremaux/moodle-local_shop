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

namespace local_shop\output;

class front_order_totals implements \Templatable {

    protected $bill;

    protected $theshop;

    protected $context;

    public function __construct($args) {
        $this->bill = $args[0];
        $this->theshop = $args[1];
    }

    public function export_for_template(\renderer_base $output) {
        global $SESSION, $CFG;

        $config = get_config('local_shop');

        $template = new \StdClass;
        $bill = $this->bill;

        $shoppingcart = @$SESSION->shoppingcart;

        $reason = '';

        if (!is_null($bill)) {
            $bill->recalculate(); // Recalculate from DB with discounts.
            $taxedtotal = $bill->ordertaxed;
            $finaltaxedtotal = $bill->finaltaxedtotal;
            $finaluntaxedtotal = $bill->finaluntaxedtotal;
            $finaltaxestotal = $bill->taxes;
            $shippingtaxedvalue = 0;
        } else {
            $taxedtotal = $shoppingcart->taxedtotal;
            $context = \context_system::instance();

            $finaluntaxedtotal = @$shoppingcart->finaluntaxedtotal;
            $finaltaxedtotal = @$shoppingcart->finaltaxedtotal;
            $finaltaxestotal = @$shoppingcart->finaltaxestotal;
            $shippingtaxedvalue = 0 + @$shoppingcart->shipping->taxedvalue;
            $finalshippedtaxedtotal = $shoppingcart->finalshippedtaxedtotal;

            // Check discounts.
            if (local_shop_supports_feature('shop/discounts')) {
                include_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');
                // Discounts are being applied in shoppingcart.
                $discountpreview = \local_shop\Discount::preview_discount_in_session($this->theshop);
                if ($discountpreview) {
                    $template->hasdiscounts = !empty($discountpreview->discounts);
                    if ($template->hasdiscounts) {
                        $template->discounts = $discountpreview->discounts;
                    }

                    $template->ispartial = $discountpreview->ispartial;
                    $template->discount = sprintf('%0.2f', round($discountpreview->discount, 2));
                }
            }
        }

        $template->taxedtotal = sprintf("%0.2f", round($taxedtotal, 2));
        $template->currency = $this->theshop->get_currency('symbol');

        if (!empty($config->hasshipping)) {
            $finaltaxedtotal = sprintf("%0.2f", round($finaltaxedtotal + $shippingtaxedvalue, 2));
            $template->hasshipping = $config->hasshipping;
            $template->shippingtaxedvalue = sprintf("%0.2f", round($shippingtaxedvalue, 2));
        }
        $template->finalshippedtaxedtotal = sprintf("%0.2f", round($finalshippedtaxedtotal, 2));

        // Finalizing and formatting.
        $template->finaltaxedtotal = sprintf("%0.2f", round($finaltaxedtotal, 2));
        $template->finaluntaxedtotal = sprintf("%0.2f", round($finaluntaxedtotal, 2));

        return $template;
    }
}