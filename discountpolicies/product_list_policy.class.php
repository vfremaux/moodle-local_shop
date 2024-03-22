<?php

namespace local_shop;

class ProductListPolicy extends DiscountPolicy {

    protected function get_name() {
        return 'productlist';
    }

    public function calculate_discount(&$bill) {
    }

}