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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/*
 * STD_REGISTERED_PRODUCT is a standard generic shop product action handler that creates instances of a catalogitem
 * as a product record. It has NO counterpart in moodle insternal data and should be used when the product definition
 * has external existance, but still need its lifecycle to be managed, such as validity period, product effective existance,
 * regarding an existance check service.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\Customer;

/**
 * The Registered product handler is convenient to register an external (or abstract) product that has no direct
 * tracks in moodle DB records. This is suitable for any extraneous product type we just want the shop to remind
 * the existance and manage the lifecycle. Therefore, the products will have effective trace in the local_shop_product
 * table, but nowhere else. Remote query on product validity may still be perfomed using purchasemanager service API.
 *
 * Prepay action : as for other products that need some enrolment, a Registered Product may admit a user account is prepared
 * for the purchaser.
 * 
 * PostPay action : When effectively purchasing the product, will an enrolment to a customer support workspace (as a moodle course)
 * be activated and the product record be registered.
 */
class shop_handler_std_registeredproduct extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_registeredproduct'; // For unit test reporting.
        parent::__construct($label);
    }

    public function supports() {
        return PROVIDING_BOTH;
    }

    // Pre pay information always comme from shopping session.
    function produce_prepay(&$data, &$errorstatus) {
        global $CFG, $DB, $USER;

        // Get customersupportcourse designated by handler internal params and prepare customer support action.
        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
        }

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        return $productionfeedback;
    }

    public function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        $starttime = shop_compute_enrol_time($data, 'starttime', null);
        $endtime = shop_compute_enrol_time($data, 'endtime', null);

        // Register product.
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'registered_product';
        $product->instanceid = 0;
        $product->startdate = $starttime;
        $product->enddate = $endtime;
        $product->extradata = '';
        $product->reference = shop_generate_product_ref($data);
        $extra = array('handler' => 'std_registeredproduct');
        $product->productiondata = Product::compile_production_data($data->actionparams, $data->customerdata, $extra);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        // Add user to customer support on real purchase.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_REGISTERED_PRODUCT Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $data->customeruser, $data->transactionid);
        }

        $e = new StdClass;
        $e->username = $data->customeruser->username;
        $e->fullname = $course->fullname;
        $e->name = $data->name;
        $e->txid = $data->transactionid;
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_registeredproduct', $e);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_registeredproduct', $e);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_registeredproduct', $e);
        shop_trace("[{$data->transactionid}] STD_REGISTERED_PRODUCT Postpay : Product {$e->name} registered for {$e->username}.");

        return $productionfeedback;
    }

    /**
     * Registered product always available as new references. Each handler creates a new instance of the product.
     */
    public function is_available(&$catalogitem) {
        global $USER, $DB;
        return true;
    }

    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $erors, $warnings, $messages);
    }
}