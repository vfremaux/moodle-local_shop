<?php

namespace local_shop;

/**
 *
 */
abstract class DiscountPolicy {

    abstract function get_name();

    public function discount_shop_elements() {

            $policyname = $this->get_name();

            $mform->addElement('checkbox', 'policy'.$policyname.'enabled', get_string($policyname.'local_shop'), 0);
            $mform->setType('policy'.$policyname.'enabled', PARAM_BOOL);
    }

    abstract function calculate_discount(&$bill);

}