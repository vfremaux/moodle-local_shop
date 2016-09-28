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
 * Controller for the customer screen responses.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @usecase deletecustomer
 * @usecase addcustomer
 */
defined('MOODLE_INTERNAL') || die();

class customers_controller {

    function process($cmd) {

        // Delete customers ******************************.

        if ($cmd == 'deletecustomer') {
            $customerids = required_param_array('customerid', PARAM_INT);
            if ($customerids) {
                foreach($customerids as $id) {
                    $customer = new Customer();
                    $customer->delete();
                }
            }
        }

        // Adding manually a customer record ***********.

        if ($cmd == 'addcustomer') {
            $customer = new StdClass();
            $customer->firstname = required_param('firstname', PARAM_TEXT);
            $customer->lastname = required_param('lastname', PARAM_TEXT);
            $customer->address = required_param('address', PARAM_TEXT);
            $customer->email = required_param('email', PARAM_TEXT);
            $customer->zip = required_param('zip', PARAM_TEXT);
            $customer->city = required_param('city', PARAM_TEXT);
            $customer->country = optional_param('country', 'FR', PARAM_ALPHA);
            $newid = $DB->insert_record('local_shop_customer', $customer);
        }

        if ($cmd == "sellout") {
            $billid = required_param('billid', PARAM_INT);
            $DB->set_field('local_shop_bill', 'status', 'SOLDOUT', array('id' => $billid));
        }

        // Unmark a product *****************.

        if ($cmd == "unmark") {
            $billid = required_param('billid', PARAM_INT);
            $DB->set_field('local_shop_bill', 'status', 'PENDING', array('id' => $billid));
        }
    }
}