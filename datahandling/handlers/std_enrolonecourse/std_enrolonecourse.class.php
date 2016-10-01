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
 * @package     local_shop
 * @category    local
 * @subpackage  producthandlers
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * STD_ENROL_ONE_COURSE is a standard shop product action handler that enrols in one course setup in
 * actiondata.
 * actiondata is defined as an action customized information for a specific product in the
 * product definition, where one standard handler is choosen.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use local_shop\Product;
Use local_shop\Shop;

class shop_handler_std_enrolonecourse extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_enrolonecourse'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * this product should not be available if the current user (purchaser) is
     * already certified, i.e. has a delivered certificate for the associated certificate.
     * this might be better checked by testing a shop product existance
     */
    public function is_available(&$catalogitem) {
        global $USER, $DB;

        if (!empty($catalogitem->handlerparams['coursename'])) {
            $params =  array('shortname' => $catalogitem->handlerparams['coursename']);
            $course = $DB->get_record('course',$params);
        } else if (!empty($catalogitem->handlerparams['courseid'])) {
            $params =  array('shortname' => $catalogitem->handlerparams['courseid']);
            $course = $DB->get_record('course',$params);
        }

        if (!$course || !$course->visible) {
            // Hide product if course has disappeared or is not visible.
            return false;
        }

        $context = context_course::instance($course->id);

        if (!isloggedin()) {
            // We cannot check the product is purchased or not for unlogged people.
            return true;
        }

        return !is_enrolled($context, $USER);
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
                $message = "[{$data->transactionid}] STD_ENROL_ONE_COURSE Prepay Warning :";
                $message .= " Customer support defaults to shop settings.";
                shop_trace($message);
            } else {
                $message = "[{$data->transactionid}] STD_ENROL_ONE_COURSE Prepay Warning :";
                $message .= " No customer support area defined.";
                shop_trace($message);
            }
        }

        // If Customer already has account in incoming data we have nothing to do.
        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        if (isloggedin()) {
            if ($customer->hasaccount != $USER->id) {
                /*
                 * do it quick in this case. Actual user could authentify, so it is the legitimate account.
                 * We guess if different non null id that the customer is using a new account.
                 * This should not really be possible
                 */
                $customer->hasaccount = $USER->id;
                $DB->update_record('local_shop_customer', $customer);
            } else {
                $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
                $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Prepay :";
                $message .= " Known account {$USER->username} at process entry.";
                shop_trace($message);
                return $productionfeedback;
            }
        } else {
            /*
             * In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
             * The Customer might match with an existing user...
             * TODO : If a collision is to be detected, a question should be asked to the customer.
             */
            if (!shop_create_customer_user($data, $customer, $newuser)) {
                $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Prepay Error :";
                $message .= " User could not be created {$newuser->username}."
                shop_trace($message);
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }

            $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_enrolonecourse');
            $a->username = $newuser->username;
            $a->password = $customer->password;
            $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_enrolonecourse', $a);
            $fb = get_string('productiondata_sales', 'shophandlers_std_enrolonecourse', $newuser->username);
            $productionfeedback->salesadmin = $fb;
        }

        return $productionfeedback;
    }

    /**
     * Post pay information can come from session or from production data stored in delayed bills.
     */
    public function produce_postpay(&$data) {
        global $CFG, $DB, $USER;

        $config = get_config('local_shop');

        $productionfeedback = new StdClass();

        // Check for params validity (internals).

        if (!isset($data->actionparams['coursename'])) {
            print_error('errormissingactiondata', 'local_shop', $this->get_name());
        }

        $coursename = $data->actionparams['coursename'];
        $rolename = @$data->actionparams['role'];
        if (empty($rolename)) {
            $rolename = 'student';
        }

        $startdate = @$data->actionparams['startdate'];
        if (empty($startdate)) $startdate = time();

        // Computes infinite, relative of fixed enddate.
        $enddate = @$data->actionparams['enddate'];
        if (preg_match('/^\+(\d+)$/', $enddate, $matches)) {
            $enddate = $startdate + $matches[1];
        } else if (empty($enddate)) {
            $enddate = 0;
        }

        $enrolname = @$data->actionparams['enrol'];
        if (empty($enrolname)) {
            $enrolname = 'manual';
        }

        // Perform operations.

        // Assign Student role in course for the period.
        if (!$course = $DB->get_record('course', array('shortname' => $coursename))) {
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : failed... Bad course id");
            print_error("Bad target course for product");
        }

        $role = $DB->get_record('role', array('shortname' => $rolename));
        $now = time();

        $params = array('enrol' => $enrolname, 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED), 'sortorder ASC');
        if ($enrols = $DB->get_records('enrol', $params) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin($enrolname); // The enrol object instance.
        }

        try {
            $enrolplugin->enrol_user($enrol, $USER->id, $role->id, $startdate, $enddate, ENROL_USER_ACTIVE);
        } catch (Exception $exc) {
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : Failed...");
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_enrolonecourse', 'Code : ROLE ASSIGN');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_enrolonecourse', $course->id);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_enrolonecourse', $course->id);
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
        $product->startdate = $startdate;
        $product->enddate = $enddate;
        $product->reference = shop_generate_product_ref($data);
        $product->productiondata = Product::compile_production_data($data->actionparams);
        $product->test = $config->test;
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        $fb = get_string('productiondata_assign_public', 'shophandlers_std_enrolonecourse');
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_assign_private', 'shophandlers_std_enrolonecourse', $course->id);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_assign_sales', 'shophandlers_std_enrolonecourse', $course->id);
        $productionfeedback->salesadmin = $fb;

        /*
         * Make a group if needed for the customer
         * the customer is buying for its own so it will get an "own group" to eventually
         * get separated from other learner teams.
         */

        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        $customeruser = $DB->get_record('user', array('id' => $customer->hasaccount));

        $groupname = 'customer_'.$customeruser->username;

        if (!$group = $DB->get_record('groups', array('courseid' => $course->id, 'name' => $groupname))) {
            $group = new StdClass();
            $group->courseid = $course->id;
            $group->idnumber = $data->transactionid;
            $group->name = $groupname;
            $group->description = get_string('providedbymoodleshop', 'local_shop');
            $group->descriptionformat = 1;
            $group->enrolmentkey = 0;
            $group->timecreated = $now;
            $group->timemodified = $now;
            $group->id = $DB->insert_record('groups', $group);
        }

        // Add all created users to group.

        if (!$groupmember = $DB->get_record('groups_members', array('groupid' => $group->id, 'userid' => $USER->id))) {
            $groupmember = new StdClass();
            $groupmember->groupid = $group->id;
            $groupmember->userid = $USER->id;
            $groupmember->timeadded = $now;
            $DB->insert_record('groups_members', $groupmember);
        }

        // Add user to customer support.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $USER, $data->transactionid);
        }

        shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : Completed in $coursename...");
        return $productionfeedback;
    }

    /**
     * unit tests check input conditions from product setup without doing anything,
     * collects input errors and warnings
     *
     */
    public function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['coursename'])) {
            $warnings[$data->code][] = get_string('errornocourse', 'shophandlers_std_enrolonecourse');
        } else {
            if (!$course = $DB->get_record('course', array('shortname' => $data->actionparams['coursename']))) {
                $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecourse', $data->actionparams['coursename']);
                $errors[$data->code][] = $fb;
            }
        }

        if (!isset($data->actionparams['role'])) {
            $warnings[$data->code][] = get_string('warningroledefaultstoteacher', 'shophandlers_std_enrolonecourse');
            $data->actionparams['role'] = 'student';
        }

        if (!$role = $DB->get_record('role', array('shortname' => $data->actionparams['role']))) {
            $errors[$data->code][] = get_string('errorrole', 'shophandlers_std_enrolonecourse', $data->actionparams['role']);
        }

    }
}