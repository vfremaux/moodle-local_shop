<?php

namespace local_shop;

class AmountThresholdPolicy extends DiscountPolicy {

    protected function get_name() {
        return 'amountthreshold';
    }

    public function calculate_discount(&$bill) {
    }

}