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
 * Shop handler main class
 *
 * @package shophandlers_std_assignroleoncontext
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
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
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\ProductEvent;
use local_shop\Shop;

/**
 * STD_ASSIGN_ROLE_ON_CONTEXT is a standard shop product action handler that products as result a single
 * role assignation on a context.
 */
class shop_handler_std_assignroleoncontext extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_assignroleoncontext'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public function produce_prepay(&$data, &$errorstatus) {

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

        $productionfeedback = shop_register_customer($data, $errorstatus);

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
        global $DB, $USER, $SITE;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

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
            $message .= " failed item {$data->id} no valid context ($contextlevel, $instance)";
            shop_trace($message);
            return;
        }

        if ($contextlevel == CONTEXT_BLOCK) {
            $instancename = 'Course Module '.$context->instanceid; // TODO : Clarify in future.
        } else if ($contextlevel == CONTEXT_COURSE) {
            $instancename = 'Block Instance '.$context->instanceid; // TODO : Clarify in future.
        } else if ($contextlevel == CONTEXT_COURSE) {
            $instancename = $DB->get_field('course', 'shortname', ['id' => $context->instanceid]);
        } else if ($contextlevel == CONTEXT_COURSECAT) {
            $instancename = $DB->get_field('course_categories', 'name', ['id' => $context->instanceid]);
        } else if ($contextlevel == CONTEXT_USER) {
            $instancename = $DB->get_field('user', 'username', ['id' => $context->instanceid]);
        } else if ($contextlevel == CONTEXT_SYSTEM) {
            $instancename = $SITE->shortname;
        }

        $rolename = @$data->actionparams['role'];
        if (empty($rolename)) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay :";
            $message .= " failed item {$data->id} no role defined";
            shop_trace($message);
            return;
        }

        $role = $DB->get_record('role', ['shortname' => $rolename]);
        if (!$role) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay :";
            $message .= " failed item {$data->id} no valid role ($rolename)";
            shop_trace($message);
            return;
        }

        if (!empty($data->required['foruser'])) {
            $idnumber = $data->required['foruser'];
            if (!$user = $DB->get_record('user', ['idnumber' => $idnumber])) {
                // Second chance.
                if (!$user = $DB->get_record('user', ['username' => $idnumber])) {
                    $fb = get_string('productiondata_failure_public', 'shophandlers_std_assignroleoncontext', 'Code : BAD_USER');
                    $productionfeedback->public = $fb;
                    $fb = get_string('productiondata_failure_private', 'shophandlers_std_assignroleoncontext', $idnumber);
                    $productionfeedback->private = $fb;
                    $fb = get_string('productiondata_failure_sales', 'shophandlers_std_assignroleoncontext', $idnumber);
                    $productionfeedback->salesadmin = $fb;
                    return $productionfeedback;
                }
            }
            $customer = $DB->get_record('local_shop_customer', ['id' => $data->get_customerid()]);
            $customeruser = $DB->get_record('user', ['id' => $customer->hasaccount]);
        } else {
            if ($USER->id) {
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

        // Compute start and end time.
        $starttime = shop_compute_enrol_time($data, 'starttime', $course);
        $endtime = shop_compute_enrol_time($data, 'endtime', $course);

        // Perform operations.

        try {
            $raid = role_assign($role->id, $user->id, $context->id);
        } catch (Exception $exc) {
            shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay : Failed...");
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_assignroleoncontext', 'Code : ROLE ASSIGN');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_assignroleoncontext', $instancename);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_assignroleoncontext', $instancename);
            $productionfeedback->salesadmin = $fb;
            return $productionfeedback;
        }

        // Create product instance in product table.

        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'roleassign';
        $product->instanceid = $raid; // Register role assign instance.
        $product->startdate = $starttime;
        $product->enddate = $endtime;
        $product->extradata = '';
        $product->reference = shop_generate_product_ref($data);
        $extra = ['handler' => 'std_assignroleoncontext'];
        $product->productiondata = Product::compile_production_data($data->actionparams, $extra);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = time();
        $productevent->save();

        // Add user to customer support.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        $e = new StdClass;
        $e->txid = $data->transactionid;
        $e->username = $user->username;
        $e->role = $role->shortname;
        $e->instancename = $instancename;

        $fb = get_string('productiondata_post_public', 'shophandlers_std_assignroleoncontext', $e);
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_post_private', 'shophandlers_std_assignroleoncontext', $e);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_post_sales', 'shophandlers_std_assignroleoncontext', $e);
        $productionfeedback->salesadmin = $fb;

        shop_trace("[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT PostPay : Completed in $instancename...");
        return $productionfeedback;
    }

    /**
     * Dismounts all effects of the handler production when a product is deleted.
     * The contexttype will denote the type of Moodle object that was created. some
     * hanlders may deal with several contexttypes if they have a complex production
     * operation. the instanceid is moslty a moodle table id that points the concerned instance
     * within the context type scope.
     *
     * In assignroleoncontext plugin, removes the role assignation
     * assigned to the product. Other role assignations will remain unchanged.
     *
     * @param string $contexttype type of context to dismount
     * @param integer/string $instanceid identifier of the instance
     */
    public function delete(&$product) {
        global $DB;

        if ($product->contexttype == 'roleassign') {
            if ($ra = $DB->get_record('role_assignments', ['id' => $product->instanceid])) {
                shop_trace('[] Deleting roleassignement on {$ra->contextid} for user {$ra->userid}');
                role_unassign($ra->roleid, $ra->userid, $ra->contextid);
            }
        }
    }

    /**
     * Tests a product handler
     * @param object $data
     * @param arrayref &$errors
     * @param arrayref &$warnings
     * @param arrayref &$messages
     */
    public function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->required['foruser'])) {
            if ($data->onlyforloggedin < PROVIDING_LOGGEDIN_ONLY) {
                $errors[$data->code][] = get_string('erroremptyuserrisk', 'shophandlers_std_assignroleoncontext');
            }
            $warnings[$data->code][] = get_string('warningonlyforselfproviding', 'shophandlers_std_assignroleoncontext');
        }

        if (!isset($data->actionparams['contextlevel'])) {
            $errors[$data->code][] = get_string('errormissingcontextlevel', 'shophandlers_std_assignroleoncontext');
        }

        if (!in_array($data->actionparams['contextlevel'], ['course', 'module', 'category'])) {
            $errors[$data->code][] = get_string('errorunsupportedcontextlevel', 'shophandlers_std_assignroleoncontext');
        }

        if (!isset($data->actionparams['instance'])) {
            $errors[$data->code][] = get_string('errormissingcontext', 'shophandlers_std_assignroleoncontext');
        }

        $params = ['contextlevel' => $data->actionparams['contextlevel'], 'instance' => $data->actionparams['instance']];
        if (!$DB->get_record('context', $params)) {
            $errors[$data->code][] = get_string('errorcontext', 'shophandlers_std_assignroleoncontext');
        }

        if (!isset($data->actionparams['role'])) {
            $errors[$data->code][] = get_string('errormissingrole', 'shophandlers_std_assignroleoncontext');
        }

        if (!$DB->get_record('role', ['shortname' => $data->actionparams['role']])) {
            $errors[$data->code][] = get_string('errorrole', 'shophandlers_std_assignroleoncontext', $data->actionparams['role']);
        }
    }
}
