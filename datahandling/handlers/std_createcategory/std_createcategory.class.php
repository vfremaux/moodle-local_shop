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

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @subpackage  shophandlers
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * STD_CREATE_CATEGORY is a standard shop product action handler that creates a category for the customer
 * and enrols the customer as course creator (category manager) inside.
 *
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use local_shop\Product;
Use local_shop\Shop;

class shop_handler_std_createcategory extends shop_handler{

    function __construct($label) {
        $this->name = 'STD_CREATE_CATEGORY'; // for unit test reporting
        parent::__construct($label);
    }

    function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        // Get customersupportcourse designated by handler internal params

        if (!isset($data->actionparams['customersupport'])) {
            $theShop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theShop->defaultcustomersupportcourse;
        }

        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        if (isloggedin()) {
            if ($customer->hasaccount != $USER->id) {
                // do it quick in this case. Actual user could authentify, so it is the legitimate account.
                // We guess if different non null id that the customer is using a new account. This should not really be possible
                $customer->hasaccount = $USER->id;
                $DB->update_record('local_shop_customer', $customer);
            } else {
                $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
                shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Prepay : Known account {$USER->username} at process entry.");
                return $productionfeedback;
            }
        } else {
            // In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
            // The Customer might match with an existing user... 
            // TODO : If a collision is to be detected, a question should be asked to the customer.
    
            // Create Moodle User but no assignation
            if (!shop_create_customer_user($data, $customer, $newuser)) {
                shop_trace("[{$data->transactionid}] {$this->name} Prepay Error : User could not be created {$newuser->username}.");
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }

            $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_createcategory');
            $a->username = $newuser->username;
            $a->password = $customer->password;
            $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_createcategory', $a);
            $productionfeedback->salesadmin = get_string('productiondata_sales', 'shophandlers_std_createcategory', $newuser->username);
        }

        return $productionfeedback;
    }

    function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();

        if (!isset($data->actionparams['parentcategory'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay Error : Missing action data (parentcategory)");
            return array();
        }
        $catparent = $data->actionparams['parentcategory'];

        $now = time();
        $secsduration = @$data->actionparams['duration'] * DAYSECS;
        $upto = ($secsduration) ? $now + $secsduration : 0 ;

        $cat->name = generate_catname($data->user);

        if ($catid = shop_fast_make_category($catname, $description, $catparent)) {

            if (!$role = $DB->get_record('role', array('shortname' => 'categoryowner'))) { // non standard specific role when selling parts of managmeent delegation
                $role = $DB->get_record('role', array('shortname' => 'coursecreator')); // fall back for standard implementations
            }

            if (!role_assign($role->id, $data->user->id, 0, $context->id, $now, $upto, false, 'manual', time())) {
                $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_createcategory', 'Code : COURSECREATOR ROLE ASSIGN');
                $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_createcategory', $data);
                $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_createcategory', $data);
                shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay : Failed to assign course creator...");
                return $productionfeedback;
            }

        } else {
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_createcategory', 'Code : CATEGORY CREATION');
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_createcategory', $data);
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_createcategory', $data);
            shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay Error : Failed to create catgory.");
            return $productionfeedback;
        }

        // Register product
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem
        $product->currentbillitemid = $data->id; // Data is a billitem
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'category';
        $product->instanceid = $catid;
        $product->startdate = $starttime;
        $product->enddate = $endtime;
        $product->reference = shop_generate_product_ref($data);
        $product->productiondata = '';
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record an event.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        // Add user to customer support
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $data->user, $data->transactionid);
        }

        $productionfeedback->public = get_string('productiondata_assign_public', 'shophandlers_'.$this->name);
        $productionfeedback->private = get_string('productiondata_assign_private', 'shophandlers_std_createcategory', $catid);
        $productionfeedback->salesadmin = get_string('productiondata_assign_sales', 'shophandlers_std_createcategory', $catid);
        shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay : Complete.");

        return $productionfeedback;
    } 

    static function get_required_default() {
        return '';
    }

    static function get_actionparams_default() {
        return 'parentcategory={&duration=&customersupport=}';
    }

    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['parentcategory'])) {
            $errors[$data->code][] = get_string('errormissingparentcategory', 'shophandlers_std_createcategory');
        }

    }
}