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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * STD_SETUP_ONE_COURSE_SESSION is a standard shop product action handler that allows the shop operator to
 * prepare and setup a training session for other stakeholders
 * actiondata is defined as an action customized information for a specific product in the
 * product definition, where one standard handler is choosen.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use local_shop\Product;
Use local_shop\Shop;

class shop_handler_std_setuponecoursesession extends shop_handler {

    var $requiredroles;

    function __construct($label) {
        $this->name = 'std_setuponecoursesession'; // for unit test reporting
        parent::__construct($label);
        $this->requiredroles = array('student', 'teacher', 'supervisor', 'owner');
    }

    function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        // Get customersupportcourse designated by handler internal params

        if (!isset($data->actionparams['customersupport'])) {
            $theShop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theShop->defaultcustomersupportcourse;
        }

        // Check customer state and create account if necessary

        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        if (isloggedin()) {
            if ($customer->hasaccount != $USER->id) {
                // do it quick in this case. Actual user could authentify, so it is the legitimate account.
                // We guess if different non null id that the customer is using a new account. This should not really be possible
                $customer->hasaccount = $USER->id;
                $productionfeedback->public = get_string('fixaccount', 'local_shop', $USER->username);
                $productionfeedback->private = get_string('fixnaccount', 'local_shop', $USER->username);
                $productionfeedback->salesadmin = get_string('fixaccount', 'local_shop', $USER->username);
                $DB->update_record('local_shop_customer', $customer);
            } else {
                $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
                shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Prepay : Known account {$USER->username} at process entry.");
                return $productionfeedback;
            }
        } else {
            // In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
            // The Customer cannot match with an existing user (this has been checked in customer.controller.php)
            // TODO : If a collision is to be detected, a question should be asked to the customer.

            if (!shop_create_customer_user($data, $customer, $newuser)) {
                shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Prepay Error : User could not be created {$newuser->username}.");
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }

            $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_setuponecoursesession', '');
            $a = new StdClass();
            $a->username = $newuser->username;
            $a->password = $customer->password;
            $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_setuponecoursesession', $a);
            $productionfeedback->salesadmin = get_string('productiondata_sales', 'shophandlers_std_setuponecoursesession', $newuser->username);
        }

        return $productionfeedback;
    }

    /**
     * Scenario :
     * One course, a list of accounts to enrol or create and enrol.
     * List of accounts comme from production data, previously from $SESSION->shoppingcart->$roles
     */
    function produce_postpay(&$data) {
        global $CFG, $DB;

        $config = get_config('local_shop');

        $productionfeedback = new StdClass();

        if (!isset($data->actionparams['coursename'])) {
            print_error('errormissingactiondata', 'local_shop', $this->get_name());
        }

        // Get course designated by handler internal params
        $coursename = $data->actionparams['coursename'];

        if ($course = $DB->get_record('course', array('shortname' => $coursename))) {
            $context = context_course::instance($course->id);
        } else {
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Internal Failure : Target Course Error [{$coursename}].");
            print_error(get_string('errorbadtarget', 'shophandlers_std_setuponecoursesession'));
        }

        if (!isset($data->actionparams['supervisor'])) {
            $data->actionparams['supervisor'] = 'teacher';
        }

        if (!$supervisorrole = $DB->get_record('role', array('shortname' => $data->actionparams['supervisor']))) {
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Internal Failure : Supervisor Role Do Not Exist [{$data->actionparams['supervisor']}].");
            print_error(get_string('errorsupervisorrole', 'shophandlers_std_setuponecoursesession'));
        }

        // Compute start and end time.
        $starttime = shop_compute_enrol_time($data, 'starttime', $course);
        $endtime = shop_compute_enrol_time($data, 'endtime', $course);

        // Get manual enrol plugin to that course for user enrolments.

        if ($enrols = $DB->get_records('enrol', array('enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED), 'sortorder ASC')) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin('manual'); // the enrol object instance
        }

        if (empty($enrol)) {
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Failure : Not enrollable instance");
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_setuponecoursesession', 'Code : ENROL MISSING');
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_setuponecoursesession', $course->id);
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_setuponecoursesession', $course->id);
            return $productionfeedback;
        }

        $now = time();
        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        $customeruser = $DB->get_record('user', array('id' => $customer->hasaccount));

        if (!empty($data->productiondata->users)) {
            foreach ($data->productiondata->users as $roleshort => $participants) {
                foreach ($participants as $p) {
                    if (!$user = $DB->get_record('user', array('email' => $p->email))) {
                        $courseusers[$roleshort][] = shop_create_moodle_user($p, $data, $supervisorrole);
                        shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Creating user [{$p->username}].");
                    } else {
                        $courseusers[$roleshort][] = $user;
                        shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Registering existing user [{$user->username}].");
                    }
                }
            }

            if (!empty($courseusers)) {
                try {
                    foreach ($courseusers as $roleshort => $users) {
                        if ($roleshort == '_supervisor') {
                            $role = $supervisorrole;
                        } else {
                            $role = $DB->get_record('role', array('shortname' => $roleshort));
                        }

                        if (empty($role)) {
                            $role = $studentrole;
                        }

                        shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Enrolling $roleshort.");

                        foreach ($users as $u) {
                            $enrolplugin->enrol_user($enrol, $u->id, $role->id, $starttime, $endtime, ENROL_USER_ACTIVE);
                            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : $u->lastname $u->firstname ($u->username) enrolled.");

                            $enrolmentid = $DB->get_field('user_enrolments', 'id', array('userid' => $u->id, 'enrolid' => $enrol->id));
                            // Register a product (userenrol instance) for each
                            $product = new StdClass();
                            $product->catalogitemid = $data->catalogitem->id;
                            $product->initialbillitemid = $data->id; // Data is a billitem
                            $product->currentbillitemid = $data->id; // Data is a billitem
                            $product->customerid = $data->bill->customerid;
                            $product->contexttype = 'enrol';
                            $product->instanceid = $enrolmentid;
                            $product->startdate = $starttime;
                            $product->enddate = $endtime;
                            $product->reference = shop_generate_product_ref($data);
                            $product->test = $config->test;
                            $itemproductiondata = array();
                            $itemproductiondata['courseid'] = $course->id;
                            $itemproductiondata['handler'] = 'std_setuponecoursesession';
                            $itemproductiondata['coursename'] = $coursename;
                            $itemproductiondata['userid'] = $u->id;
                            $itemproductiondata['starttime'] = $starttime;
                            $itemproductiondata['endtime'] = $endtime;
                            $itemproductiondata['supervisor'] = $data->actionparams['supervisor'];
                            $product->productiondata = Product::compile_production_data($itemproductiondata); // This data is the data required to produce again this product
                            unset($itemproductiondata); // clean some mem
                            $product->id = $DB->insert_record('local_shop_product', $product);

                            // Should we record a productevent
                            $productevent = new StdClass();
                            $productevent->productid = $product->id;
                            $productevent->billitemid = $data->id;
                            $productevent->datecreated = $now = time();
                            $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

                            // GC a bit intermediate stuctures
                            unset($product);
                            unset($productevent);
                        }
                    }
                } catch (Exception $exc) {
                    shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Failure : enrolled failed with exception ({$exc->getMessage()}...");
                    $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_setuponecoursesession', 'Code : ROLE ASSIGN');
                    $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_setuponecoursesession', $course->id);
                    $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_setuponecoursesession', $course->id);
                    return $productionfeedback;
                }
            }
        }

        // Make a group if needed for the customer.
        if (!$group = $DB->get_record('groups', array('courseid' => $course->id, 'name' => 'customer_'.$customeruser->username))) {
            $group = new StdClass();
            $group->courseid = $course->id;
            $group->idnumber = $data->transactionid;
            $group->name = 'customer_'.$customeruser->username;
            $group->description = get_string('providedbymoodleshop', 'local_shop');
            $group->descriptionformat = 1;
            $group->enrolmentkey = 0;
            $group->timecreated = $now;
            $group->timemodified = $now;
            $group->id = $DB->insert_record('groups', $group);
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Creating group ['customer_{$customeruser->username}].");
        }

        // Add all created users to group.

        foreach ($courseusers as $roleshort => $users) {
            foreach ($users as $u) {
                if (!$DB->record_exists('groups_members', array('groupid' => $group->id, 'userid' => $u->id))) {
                    $groupmember = new StdClass();
                    $groupmember->groupid = $group->id;
                    $groupmember->userid = $u->id;
                    $groupmember->timeadded = time();
                    $DB->insert_record('groups_members', $groupmember);
                    shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Binding ({$u->username} in group ['customer_{$customeruser->username}].");
                }
            }
        }

        // Enrol customer in course.
        $enrolplugin->enrol_user($enrol, $customeruser->id, $supervisorrole->id, $starttime, $endtime, ENROL_USER_ACTIVE);

        // Add customer to group.
        if (!$DB->record_exists('groups_members', array('groupid' => $group->id, 'userid' => $customeruser->id))) {
            $groupmember = new StdClass();
            $groupmember->groupid = $group->id;
            $groupmember->userid = $customeruser->id;
            $groupmember->timeadded = time();
            $DB->insert_record('groups_members', $groupmember);
        }

        // Enrol customer in support course if needed and possible. silently fail if not possible.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        // Finished.
        shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : All complete in $coursename.");

        $productionfeedback->public = get_string('productiondata_assign_public', 'shophandlers_std_setuponecoursesession');
        $productionfeedback->private = get_string('productiondata_assign_private', 'shophandlers_std_setuponecoursesession', $course->id);
        $productionfeedback->salesadmin = get_string('productiondata_assign_sales', 'shophandlers_std_setuponecoursesession', $course->id);

        return $productionfeedback;
    }

    /**
     * unit tests check input conditions from product setup without doing anything, collects input errors and warnings
     *
     */
    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['coursename'])) {
            $errors[$data->code][] = get_string('errornotarget', 'shophandlers_std_setuponecoursesession');
        } else {
            if (!$course = $DB->get_record('course', array('shortname' => $data->actionparams['coursename']))) {
                $errors[$data->code][] = get_string('errorcoursenotexists', 'shophandlers_std_setuponecoursesession', $data->actionparams['coursename']);
            }

            // check enrollability
            if ($enrols = $DB->get_records('enrol', array('enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED), 'sortorder ASC')) {
                $enrol = reset($enrols);
            }

            if (empty($enrol)) {
                $errors[$data->code][] = get_string('errorcoursenotenrollable', 'shophandlers_std_setuponecoursesession', $data->actionparams['coursename']);
            }
        }

        if (!isset($data->actionparams['supervisor'])) {
            $warnings[$data->code][] = get_string('warningsupervisordefaultstoteacher', 'shophandlers_std_setuponecoursesession');
            $data->actionparams['supervisor'] = 'teacher';
        }

        if (!$role = $DB->get_record('role', array('shortname' => $data->actionparams['supervisor']))) {
            $errors[$data->code][] = get_string('errorsupervisorrole', 'shophandlers_std_setuponecoursesession', $data->actionparams['supervisor']);
        }
    }

    /**
     * this method renders part of the product post purchase management GUI
     * for products generated with this handler
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     *
     * // TODO : Generalize to all logstores
     */
    function display_product_actions($pid, $params) {
        global $CFG, $COURSE, $DB;

        // here we can unassign a product if it has not been used.
        // check this in logs
        $params = (array)$params; // just to be sure

        $sqlparams = array($params['courseid'], $params['userid'], $params['starttime']);

        $select = " courseid = ?  AND userid = ? AND timecreated > ? ";

        if ($params['endtime']) {
            $sqlparams[] = $params['endtime'];
            $select .= " AND timecreated < ? ";
        }

        $hasentered = $DB->record_exists_select('logstore_standard_log', $select, $sqlparams);

        if (!$hasentered) {
            $str = '';
            $freeassignstr = get_string('freeassign', 'shophandlers_std_setuponecoursesession');
            $postprodurl = new moodle_url('/local/shop/datahandling/postproduction.php', array('id' => $params['courseid'], 'pid' => $pid, 'method' => 'freeassign'));
            $str .= '<a href="'.$postprodurl.'">'.$freeassignstr.'</a>';
        } else {
            $str = get_string('nonmutable', 'local_shop');
        }
        return $str;
    }

    /**
     * this frees assignation on this product, and reverts it to a reassignable generated seat product
     */
    function postprod_freeassign(&$product, &$productioninfo) {
        global $COURSE, $CFG, $OUTPUT, $DB;

        // first unenrol user from course

        $product->contexttype = 'user_enrolment';

        // TODO : Finish processing
    }

    /**
     * this method renders user formated information about production information (contextually to handler)
     * for products generated with this handler
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     */
    function display_product_infos($pid, $pinfo) {
        global $CFG, $COURSE, $DB;

        $str = '';

        $str .= '<div><div class="cs-product-key">'.get_string('coursename', 'shophandlers_std_setuponecoursesession').'</div>';
        $str .= '<div class="cs-product-value">'.$pinfo->coursename.'</div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('beneficiary', 'shophandlers_std_setuponecoursesession').'</div>';
        $u = $DB->get_record('user', array('id' => $pinfo->userid));
        $userurl = new moodle_url('/user/view.php', array('id' => $u->id));
        $str .= '<div class="cs-product-value"><a href="'.$userurl.'">'.fullname($u).'</a></div></div>';

        $str .= '<div><div class="cs-product-key">'.get_string('role').'</div>';
        if ($pinfo->supervisor) {
            $str .= '<div class="cs-product-value">MANAGER</div></div>';
        } else {
            $str .= '<div class="cs-product-value">LEARNER</div></div>';
        }

        return $str;
    }
}