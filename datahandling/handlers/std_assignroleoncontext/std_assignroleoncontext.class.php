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
 * @subpackage shophandlers
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * STD_ASSIGN_ROLE_ON_CONTEXT is a standard shop product action handler that products as result a single
 * role assignation on a context.
 * Typical use of this handler is triggering access to some features after payement, by enabling a set
 * of capabilities allowed by this role. this will probably lead to create specific roles that maps
 * those features authorisation.
 * actiondata is defined as an action customized information for a specific product in the
 * product definition, where one standard handler is choosen.
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use local_shop\Product;
Use local_shop\Shop;

class shop_handler_std_assignroleoncontext extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_assignroleoncontext'; // For unit test reporting.
        parent::__construct($label);
    }

    // Pre pay information always comme from shopping session.
    public function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        // Get customersupportcourse designated by handler internal params.

        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
            if ($data->actionparams['customersupport']) {
                $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Prepay Warning :";
                $message = " Customer support defaults to block settings.";
                shop_trace($message);
            } else {
                $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Prepay Warning :";
                $message = " No customer support aea defined.";
                shop_trace($message);
            }
        }

        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        if (isloggedin()) {
            if ($customer->hasaccount != $USER->id) {
                /*
                 * do it quick in this case. Actual user could authentify, so it is the legitimate account.
                 * We guess if different non null id that the customer is using a new account. This should not really be possible
                 */
                $customer->hasaccount = $USER->id;
                $DB->update_record('local_shop_customer', $customer);
            } else {
                $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
                $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Prepay :";
                $message .= " Known account {$USER->username} at process entry."
                shop_trace($message);
                return $productionfeedback;
            }
        } else {
            /*
             * In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
             * The Customer might match with an existing user...
             * TODO : If a collision is to be detected, a question should be asked to the customer.
             */
            // Create Moodle User but no assignation (this will register in customer support if exists).
            if (!shop_create_customer_user($data, $customer, $newuser)) {
                shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Prepay Error : User could not be created {$newuser->username}.");
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }

            $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_assignroleoncontext');
            $a->username = $newuser->username;
            $a->password = $customer->password;
            $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_assignroleoncontext', $a);
            $productionfeedback->salesadmin = get_string('productiondata_sales', 'shophandlers_std_assignroleoncontext', $newuser->username);
        }

        return $productionfeedback;
    }

    // Post pay information can come from session or from production data stored in delayed bills.
    public function produce_postpay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        // Check for params validity (internals).

        if (!isset($data->actionparams['contextlevel'])) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay :";
            $message .= " failed item {$data->id} no context level";
            shop_trace($message);
            return;
        }

        $contextlevel = $data->actionparams['contextlevel'];
        $instance = $data->actionparams['instance'];

        $classfunc = 'context_'.$contextlevel.'::instance';

        $context = $classfunc($instance);

        if (!$context()) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay :";
            $meesage .= " failed item {$data->id} no valid context ($contextlevel, $instance)";
            shop_trace($message);
            return;
        }

        $rolename = @$data->actionparams['role'];
        if (empty($rolename)) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay :";
            $message .= " failed item {$data->id} no role defined";
            shop_trace($message);
            return;
        }

        $role = $DB->get_record('role', array('shortname' => $rolename));
        if (!$role) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay :";
            $message .= " failed item {$data->id} no valid role ($rolename)"
            shop_trace($message);
            return;
        }

        if (!empty($data->required['foruser'])) {
            $idnumber = $data->required['foruser'];
            if (!$user = $DB->get_record('user', array('idnumber' => $idnumber))) {
                // Second chance.
                if (!$user = $DB->get_record('user', array('username' => $idnumber))) {
                    $fb = get_string('productiondata_failure_public', 'shophandlers_std_assignroleoncontext', 'Code : BAD_USER');
                    $productionfeedback->public = $fb;
                    $fb = get_string('productiondata_failure_private', 'shophandlers_std_assignroleoncontext', $idnumber);
                    $productionfeedback->private = $fb;
                    $fb = get_string('productiondata_failure_sales', 'shophandlers_std_assignroleoncontext', $idnumber);
                    $productionfeedback->salesadmin = $fb;
                    return $productionfeedback;
                }
            }
            $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
            $customeruser = $DB->get_record('user', array('id', $customer->hasaccount));
        } else {
            if ($USER->id) {
                $userid = $USER->id;
                $customeruser = $USER;
            } else {
                $fb = get_string('productiondata_failure_public', 'shophandlers_std_assignroleoncontext', 'Code : NO_USER');
                $productionfeedback->public = $fb;
                $fb = get_string('productiondata_failure_private', 'shophandlers_std_assignroleoncontext', 0);
                $productionfeedback->private = $fb;
                $fb = get_string('productiondata_failure_sales', 'shophandlers_std_assignroleoncontext', 0);
                $productionfeedback->salesadmin = $fb;
                return $productionfeedback;
            }
        }

        $startdate = @$data->actionparams['startdate'];
        if (empty($startdate)) $startdate = time();

        // Computes infinite, relative of fixed enddate.
        $enddate = @$data->actionparams['enddate'];
        if (preg_match('/^+(\d+)$/', $enddate, $matches)) {
            $enddate = $startdate + $matches[1];
        } else if (empty($enddate)) {
            $enddate = 0;
        }

        // Perform operations.

        $now = time();

        try {
            role_assign($role->id, $user->id, $context->id);
        } catch (Exception $exc) {
            shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay : Failed...");
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_assignroleoncontext', 'Code : ROLE ASSIGN');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_assignroleoncontext', $course->id);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_assignroleoncontext', $course->id);
            $productionfeedback->salesadmin = $fb;
            return $productionfeedback;
        }

        // Create product instance in product table.

        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'enrol';
        $product->instanceid = $enrol->id;
        $product->startdate = $starttime;
        $product->enddate = $endtime;
        $product->reference = shop_generate_product_ref($data);
        $product->productiondata = Product::compile_production_data($data->actionparams);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        // Add user to customer support.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        $fb = get_string('productiondata_assign_public', 'shophandlers_std_assignroleoncontext');
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_assign_private', 'shophandlers_std_assignroleoncontext', $course->id);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_assign_sales', 'shophandlers_std_assignroleoncontext', $course->id);
        $productionfeedback->salesadmin = $fb;

        shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay : Completed in $coursename...");
        return $productionfeedback;
    }

    /**
     * unit tests check input conditions from product setup without doing anything,
     * collects input errors and warnings
     */
    public function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->required['foruser'])) {
            if ($data->onlyforloggedin != PROVIDING_LOGGEDIN_ONLY) {
                $errors[$data->code][] = get_string('erroremptyuserrisk', 'shophandlers_std_assignroleoncontext');
            }
            $warnings[$data->code][] = get_string('warningonlyforselfproviding', 'shophandlers_std_assignroleoncontext');
        }

        if (!isset($data->actionparams['contextlevel'])) {
            $errors[$data->code][] = get_string('errormissingcontextlevel', 'shophandlers_std_assignroleoncontext');
        }

        if (!in_array($data->actionparams['contextlevel'], array('course', 'module', 'category'))) {
            $errors[$data->code][] = get_string('errorunsupportedcontextlevel', 'shophandlers_std_assignroleoncontext');
        }

        if (!isset($data->actionparams['instance'])) {
            $errors[$data->code][] = get_string('errormissingcontext', 'shophandlers_std_assignroleoncontext');
        }

        $params = array('contextlevel' => $data->actionparams['contextlevel'], 'instance' => $data->actionparams['instance']);
        if (!$DB->get_record('context', $params)) {
            $errors[$data->code][] = get_string('errorcontext', 'shophandlers_std_assignroleoncontext');
        }

        if (!isset($data->actionparams['role'])) {
            $errors[$data->code][] = get_string('errormissingrole', 'shophandlers_std_assignroleoncontext');
        }

        if (!$role = $DB->get_record('role', array('shortname' => $data->actionparams['role']))) {
            $errors[$data->code][] = get_string('errorrole', 'shophandlers_std_assignroleoncontext', $data->actionparams['role']);
        }
    }
}