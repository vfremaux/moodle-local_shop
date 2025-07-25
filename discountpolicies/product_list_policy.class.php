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
 * Discount policy based on a product list
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
 * A class to apply a discount policy for products in a reference list
 */
class ProductListPolicy extends DiscountPolicy {

    /**
     * Returns the policy name
     */
    protected function get_name() {
        return 'productlist';
    }

    /**
     * How to calculate discount
     * @param Bill $bill
     */
    public function calculate_discount(Bill $bill) {
        assert(true);
    }
}
