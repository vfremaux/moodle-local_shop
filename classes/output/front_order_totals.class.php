<?php

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
        global $SESSION;

        $config = get_config('local_shop');

        $bill = $this->bill;

        $shoppingcart = @$SESSION->shoppingcart;

        $reason = '';

        if (!is_null($bill)) {
            $bill->recalculate();
            $taxedtotal = $bill->ordertaxed;
            $finaltaxedtotal = $bill->finaltaxedtotal;
            $finaluntaxedtotal = $bill->finaluntaxedtotal;
            $finaltaxestotal = $bill->taxes;
            $discount = $bill->discount;
            $shippingtaxedvalue = 0;
            $discountrate = $this->theshop->calculate_discountrate_for_user($taxedtotal, $bill->context, $reason);
        } else {
            $taxedtotal = $shoppingcart->taxedtotal;
            $context = \context_system::instance();
            $discountrate = $this->theshop->calculate_discountrate_for_user($taxedtotal, $context, $reason);
            $discount = $shoppingcart->discount;

            if ($discountrate) {
                $finaltaxedtotal = $taxedtotal * (1 - ($discountrate / 100));
                $finaluntaxedtotal = $shoppingcart->untaxedtotal * (1 - ($discountrate / 100));
            } else {
                $finaltaxedtotal = $shoppingcart->finaltaxedtotal;
                $finaluntaxedtotal = $shoppingcart->finaluntaxedtotal;
            }

            $finaltaxestotal = @$shoppingcart->finaltaxestotal;
            $shippingtaxedvalue = 0 + @$shoppingcart->shipping->taxedvalue;
            $finalshippedtaxedtotal = $shoppingcart->finalshippedtaxedtotal;
        }

        $template = new \StdClass;

        $template->taxedtotal = sprintf("%0.2f", round($taxedtotal, 2));
        $template->currency = $this->theshop->get_currency('symbol');

        $template->discountrate = $discountrate;
        $template->discount = $discount;

        $template->finaluntaxedtotal = sprintf("%0.2f", round($finaluntaxedtotal, 2));

        if (!empty($config->hasshipping)) {
            $template->finaltaxedtotal = sprintf("%0.2f", round($finaltaxedtotal + $shippingtaxedvalue, 2));
            $template->hasshipping = $config->hasshipping;
            $template->shippingtaxedvalue = sprintf("%0.2f", round($shippingtaxedvalue, 2));

            $template->finalshippedtaxedtotal = sprintf("%0.2f", round($finalshippedtaxedtotal, 2));
        } else {
            $template->finaltaxedtotal = sprintf("%0.2f", round($finaltaxedtotal, 2));
        }

        return $template;
    }
}