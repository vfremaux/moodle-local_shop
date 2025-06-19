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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @usecase deletecustomer
 * @usecase addcustomer
 */
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Customer;
use local_shop\Bill;
use coding_exception;

/**
 * An MVC controller for managing customer accounts
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class customers_controller {

    /** @var controller input data */
    protected $data;

    /** @var Marks a received state */
    protected $received;

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function should get them from request
     */
    public function receive($cmd, $data = null) {

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'deletecustomer':
                $this->data->customerids = required_param_array('customerid', PARAM_INT);
                break;

            case 'edit':
                // Let data come from $data attribute.
                break;

            case 'sellout':
            case 'unmark':
                $this->data->billid = required_param('billid', PARAM_INT);
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        if ($cmd == 'deletecustomer') {
            if ($this->data->customerids) {
                foreach ($this->data->customerids as $id) {
                    $customer = new Customer($id);
                    $customer->delete();
                }
            }
        }

        if ($cmd == 'edit') {

            if ($DB->record_exists('user', ['email' => $this->data->email])) {
                $account = $DB->get_record('user', ['email' => $this->data->email]);
                $this->data->hasaccount = $account->id;
            } else {
                $this->data->hasaccount = 0;
            }
            $this->data->timecreated = time();
            $this->data->id = $this->data->customerid;
            unset($this->data->customerid);
            if (empty($this->data->id)) {
                $this->data->id = $DB->insert_record('local_shop_customer', $this->data);
            } else {
                $this->data->id = $DB->update_record('local_shop_customer', $this->data);
            }

            return new Customer($this->data);
        }

        // Customer account view : Process a pending order to pass it complete *****************.

        if ($cmd == 'sellout') {
            $bill = new Bill($this->data->billid);
            if ($bill) {
                $bill->work(SHOP_BILL_SOLDOUT);
            }
        }

        // Unmark a bill, revert back to pending *****************.

        if ($cmd == "unmark") {
            $DB->set_field('local_shop_bill', 'status', 'PENDING', ['id' => $bill->id]);
        }
    }
}