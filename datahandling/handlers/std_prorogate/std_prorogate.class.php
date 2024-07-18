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
 * Main handler class
 *
 * @package  shophandler_std_prorogate
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/*
 * STD_PROROGATE_PRODUCT is a standard generic shop product action handler that prorogates an existing product
 * instance lifetime.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\Customer;
use local_shop\CatalogItem;

/**
 * The STD_PROROGATE handler is convenient to push further the ending date of a product. 
 *
 * Prepay action : A prorogate product is available only regarding a preceding purchase and a valid
 * product instance id to work. There is no prepay action.
 * 
 * PostPay action : When effectively purchasing the product, will prorogate the provided exiting product
 * with the prorogate configured time. This purchase WILL NOT generate a new product instance, but will add
 * an event to the refered product.
 * 
 * The product reference is given as productiondata->reference value.
 */
class shop_handler_std_prorogate extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_prorogate'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * Who can use this hanlder ?
     */
    public function supports() {
        return PROVIDING_CUSTOMER_ONLY;
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    function produce_prepay(&$data, &$errorstatus) {

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        return $productionfeedback;
    }

    /**
     * What is happening after it has been actually paied out, interactively
     * or as result of a delayed sales administration action.
     * @param objectref &$data a bill item (real or simulated).
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public function produce_postpay(&$data) {
        global $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        // Register product.
        $product = \local_shop\Product::instance_by_reference($data->productiondata->reference);
        if (!$product) {
            $e = new StdClass;
            $e->txid = $data->transactionid;
            $e->reference = $data->productiondata->reference;
            $e->errorcode = 'Code : NO PRODUCT REF';
            $e->username = $data->bill->customeruser->username;
            $mess = "[{$data->transactionid}] STD_PROROGATE Postpay Error : ";
            $mess .= "Product could not be identified with reference {$data->productiondata->reference}.";
            shop_trace($mess);
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_prorogate', $e);
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_prorogate', $e);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_prorogate', $e);
            $productionfeedback->salesadmin = $fb;
            return $productionfeedback;
        }

        // Push enddate.
        $timeshift = $data->actionparams['timeshift'] ?? 0;
        $product->enddate = $product->enddate + $timeshift * DAYSECS;
        $product->save();

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->eventtype = 'updated';
        $productevent->eventdata = json_encode($data);
        $productevent->datecreated = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        $e = new StdClass;
        $e->username = $data->bill->customeruser->username;
        $e->reference = $product->reference;
        $e->txid = $data->transactionid;
        $e->enddatestr = userdate($product->enddate);
        $e->billid = $data->bill->id;
        $e->productid = $product->id;
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_prorogate', $e);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_prorogate', $e);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_prorogate', $e);
        shop_trace("[{$data->transactionid}] STD_REGISTERED_PRODUCT Postpay : Product {$e->name} prorogated for {$e->username}.");

        return $productionfeedback;
    }

    /**
     * Prorogate always available as new references. Each handler updates an existing product.
     * @param CatalogItem $catalogitem
     */
    public function is_available(CatalogItem $catalogitem) {
        return true;
    }

    /**
     * Tests a product handler
     * @param object $data
     * @param arrayref &$errors
     * @param arrayref &$warnings
     * @param arrayref &$messages
     */
    function unit_test($data, &$errors, &$warnings, &$messages) {

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (empty($data->actionparams['timeshift'])) {
            $warnings[$data->code][] = get_string('warningnotimeshift', 'shophandlers_std_prorogate');
        }
    }
}
