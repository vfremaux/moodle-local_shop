<?php

namespace local_shop;

class AmountThresholdPolicy extends DiscountPolicy {

    protected function get_name() {
        return amountthreshold;
    }

    public calculate_discount(&$bill) {
        
    }

}