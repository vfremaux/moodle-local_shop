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
 * STD_PROROGATE_PRODUCT is a standard generic shop product action handler that prorogates an existing product
 * instance lifetime.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\Customer;

/**
 * The Prorogate handler is convenient to push further the ending date of a product. 
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

    public function __construct($label) {
        $this->name = 'std_prorogate'; // For unit test reporting.
        parent::__construct($label);
    }

    public function supports() {
        return PROVIDING_CUSTOMER_ONLY;
    }

    /*
     * Pre pay information always comme from shopping session.
     * Nothing to do here.
     */
    function produce_prepay(&$data, &$errorstatus) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        return $productionfeedback;
    }

    /*
     * Postpay prorogates
     * $data (full bill data) must contain a productiondata->reference product reference.
     */
    public function produce_postpay(&$data) {
        global $CFG, $DB;

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
            shop_trace("[{$data->transactionid}] STD_PROROGATE Postpay Error : Product could not be identified with reference {$data->productiondata->reference}.");
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_prorogate', $e);
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_prorogate', $e);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_prorogate', $e);
            $productionfeedback->salesadmin = $fb;
            return $productionfeedback;
        }

        // Push enddate
        $timeshift = $data->actionparams['timeshift'] ?? 0;
        $product->enddate = $product->enddate + $timeshift * DAYSECS;
        $product->save();

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->eventtype = 'updated';
        $productevent->eventdata = json_encode($data);
        $productevent->datecreated = $now = time();
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
     */
    public function is_available(&$catalogitem) {
        global $USER, $DB;
        return true;
    }

    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $erors, $warnings, $messages);

        if (empty($data->actionparams['timeshift'])) {
            $warnings[$data->code][] = get_string('warningnotimeshift', 'shophandlers_std_prorogate');
        }
    }
}