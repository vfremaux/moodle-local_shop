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
 * Base Discount policy class
 *
 * @package   local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Experimental design
 */

namespace local_shop;

/**
 * Base class for all discount policies
 */
abstract class DiscountPolicy {

    /**
     * Returns the policy name
     */
    abstract function get_name();

    /**
     * A form element to enable the policy
     * @param object $mform
     */
    public function discount_shop_elements($mform) {

        $policyname = $this->get_name();

        $mform->addElement('checkbox', 'policy'.$policyname.'enabled', get_string($policyname.'local_shop'), 0);
        $mform->setType('policy'.$policyname.'enabled', PARAM_BOOL);
    }

    /**
     * How to calculate discount
     * @param Bill $bill
     */
    abstract function calculate_discount(Bill $bill);
}
