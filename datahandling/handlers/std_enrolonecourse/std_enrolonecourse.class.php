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
 * @package  shophandlers_std_enrolonecourse
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
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
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/compatlib.php');
require_once($CFG->dirroot.'/group/lib.php');

use local_shop\Product;
use local_shop\ProductEvent;
use local_shop\Shop;
use local_shop\CatalogItem;

/**
 * STD_ENROL_ONE_COURSE is a standard shop product action handler that enrols in one course setup in
 * actiondata.
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
class shop_handler_std_enrolonecourse extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_enrolonecourse'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * this product should not be available if the current user (purchaser) is
     * already enrolled in course.
     * @param CatalogItem $catalogitem
     */
    public function is_available(CatalogItem $catalogitem) {
        global $USER, $DB;

        if (!empty($catalogitem->handlerparams['coursename'])) {
            $params = ['shortname' => $catalogitem->handlerparams['coursename']];
            $course = $DB->get_record('course', $params);
        } else if (!empty($catalogitem->handlerparams['courseidnumber'])) {
            $params = ['idnumber' => $catalogitem->handlerparams['courseidnumber']];
            $course = $DB->get_record('course', $params);
        } else if (!empty($catalogitem->handlerparams['courseid'])) {
            $params = ['id' => $catalogitem->handlerparams['courseid']];
            $course = $DB->get_record('course', $params);
        }

        if (!$course || !$course->visible) {
            // Hide product if course has disappeared or is not visible.
            return false;
        }

        $context = context_course::instance($course->id);

        if (!isloggedin() || isguestuser()) {
            // We cannot check the product is purchased or not for unlogged people.
            return true;
        }

        return !is_enrolled($context, $USER);
    }

    /**
     * Max quantity allowed with this hanlder
     */
    public function get_max_quantity() {
        return 1;
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public function produce_prepay(&$data, &$errorstatus) {

        $message = "[{$data->transactionid}] STD_ENROL_ONE_COURSE Prepay :";
        $message .= " Start processing.";
        shop_trace($message);

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
        global $DB;

        $message = "[{$data->transactionid}] STD_ENROL_ONE_COURSE Postpay :";
        $message .= " Start processing.";
        shop_trace($message);

        $config = get_config('local_shop');

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        // Check for params validity (internals).

        if (empty($data->actionparams['coursename']) && empty($data->actionparams['courseid'])) {
            throw new moodle_exception(get_string('errormissingactiondata', 'local_shop', $this->get_name()));
        }

        $rolename = $data->actionparams['role'] ?? '';
        if (empty($rolename)) {
            $rolename = 'student';
        }

        // Perform operations.

        // Assign Student role in course for the period.
        if (!empty($data->actionparams['coursename'])) {
            $coursename = $data->actionparams['coursename'];
            if (!$course = $DB->get_record('course', ['shortname' => $coursename])) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : failed... Bad course name");
                // Those hard error cases may stop a full postpay processing.
                // TODO : change reaction by reporting error and let through.
                throw new moodle_exception("Bad target course shortname for product");
            }
        } else if (!empty($data->actionparams['courseidnumber'])) {
            $idnumber = $data->actionparams['courseidnumber'];
            if (!$course = $DB->get_record('course', ['idnumber' => $idnumber])) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : failed... Bad course idnumber");
                // Those hard error cases may stop a full postpay processing.
                // TODO : change reaction by reporting error and let through.
                throw new moodle_exception("Bad target course id for product");
            }
        } else {
            $courseid = $data->actionparams['courseid'];
            if (!$course = $DB->get_record('course', ['shortname' => $courseid])) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : failed... Bad course id");
                // Those hard error cases may stop a full postpay processing.
                // TODO : change reaction by reporting error and let through.
                throw new moodle_exception("Bad target course id for product");
            }
        }

        // Compute start and end time.
        $starttime = shop_compute_enrol_time($data, 'starttime', $course);
        $endtime = shop_compute_enrol_time($data, 'endtime', $course);

        $enrolname = $data->actionparams['enrol'] ?? '';
        if (empty($enrolname)) {
            $enrolname = 'manual';
        }

        $role = $DB->get_record('role', ['shortname' => $rolename]);
        $now = time();

        $params = ['enrol' => $enrolname, 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED];
        if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin($enrolname); // The enrol object instance.
        }

        try {
            $userid = $DB->get_field('local_shop_customer', 'hasaccount', ['id' => $data->get_customerid()]);
            $enrolplugin->enrol_user($enrol, $userid, $role->id, $starttime, $endtime, ENROL_USER_ACTIVE);
            $message = "User {$userid} Enrolled in course {$course->shortname} ";
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : ".$message);
        } catch (Exception $exc) {
            $e = new StdClass;
            $e->code = $data->itemcode;
            $e->errorcode = 'Code : ROLE ASSIGN ISSUE';
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : Failed enrol... ".$exc->getMessage());
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_enrolonecourse', $e);
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_enrolonecourse', $course->id);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_enrolonecourse', $course);
            $productionfeedback->salesadmin = $fb;
            return $productionfeedback;
        }

        // Get the user enrolment record as instance for product record.
        $ue = $DB->get_record('user_enrolments', ['enrolid' => $enrol->id, 'userid' => $userid]);

        // Create product instance in product table.

        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'userenrol';
        $product->instanceid = $ue->id;
        $product->startdate = $starttime;
        $product->enddate = $endtime;
        $product->extradata = '';
        $product->reference = shop_generate_product_ref($data);
        $extra = ['handler' => 'std_enrolonecourse'];
        $product->productiondata = Product::compile_production_data($data->actionparams, $extra);
        $product->test = $config->test;
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->save();

        $fb = get_string('productiondata_assign_public', 'shophandlers_std_enrolonecourse');
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_assign_private', 'shophandlers_std_enrolonecourse', $course->id);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_assign_sales', 'shophandlers_std_enrolonecourse', $course);
        $productionfeedback->salesadmin = $fb;

        /*
         * Make a group if needed for the customer
         * the customer is buying for its own so it will get an "own group" to eventually
         * get separated from other learner teams.
         */

        if (!$customerid = $data->customer->id) {
            $customerid = $data->get_customerid();
        }

        $customer = $DB->get_record('local_shop_customer', ['id' => $customerid]);
        $customeruser = $DB->get_record('user', ['id' => $customer->hasaccount]);

        // Create customer self group. (ordering related group)
        $groupname = 'customer_'.$customeruser->username;

        if (!$group = $DB->get_record('groups', ['courseid' => $course->id, 'name' => $groupname])) {
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

        groups_add_member($group->id, $userid);

        // Manage named group request.

        if (!empty($data->actionparams['groupname'])) {
            // Check if group exists and add it elsewhere.
            $params = ['courseid' => $course->id, 'name' => $data->actionparams['groupname']];
            if (!$group = $DB->get_record('groups', $params)) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE Postpay : Creating Origin Shop Group");
                $group = new StdClass();
                $group->courseid = $course->id;
                $group->idnumber = '';
                $group->name = $data->actionparams['groupname'];
                $group->description = get_string('providedbymoodleshop', 'local_shop');
                $group->descriptionformat = 1;
                $group->enrolmentkey = 0;
                $group->timecreated = $now;
                $group->timemodified = $now;
                $group->id = $DB->insert_record('groups', $group);
            }

            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE Postpay : Registering in Origin Shop Group");
            groups_add_member($group->id, $userid);
        }

        // Add user to customer support.

        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : Completed for $coursename...");
        return $productionfeedback;
    }

    /*
     * Gets a thumbnail from course overview files as thumb.
     * @param object local_shop\CatalogItem $catalogitem
     */
    public function get_alternative_thumbnail_url(local_shop\CatalogItem $catalogitem) {
        global $DB, $CFG;

        $shouldexist = false;
        if (!empty($catalogitem->handlerparams['coursename'])) {
            $params = ['shortname' => $catalogitem->handlerparams['coursename']];
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        } else if (!empty($catalogitem->handlerparams['courseidnumber'])) {
            $params = ['idnumber' => $catalogitem->handlerparams['courseidnumber']];
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        } else if (!empty($catalogitem->handlerparams['courseid'])) {
            $params = ['id' => $catalogitem->handlerparams['courseid']];
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        }

        if (!$course) {
            global $OUTPUT;
            $context = context_system::instance();
            if ($shouldexist && has_capability('local/shop:salesadmin', $context)) {
                echo $OUTPUT->notification(get_string('potentialhandlererror', 'local_shop', $catalogitem->code), 'error');
            }
            return null;
        }

        // Thumb or viewable image.
        // Take first available image NOT TOO LARGE (800px)
        $courseinlist = \local_shop\compat::get_course_list($course);
        foreach ($courseinlist->get_course_overviewfiles() as $file) {
            if ($isimage = $file->is_valid_image()) {
                $imageinfo = $file->get_imageinfo();
                if ($imageinfo['width'] < 800) {
                    $path = '/'. $file->get_contextid(). '/'. $file->get_component().'/';
                    $path .= $file->get_filearea().$file->get_filepath().$file->get_filename();
                    $return = ''.file_encode_url("$CFG->wwwroot/pluginfile.php", $path, !$isimage);
                    return $return;
                }
            }
        }

        return null;
    }

    /**
     * Dismounts all effects of the handler production when a product is deleted.
     *
     * In enrolonecourse plugin, unenrols the target user from course using the user enrolment record
     * assigned to the product. Other enrol sources remain unchanged.
     *
     * @param Product $product
     */
    public function delete(Product $product) {
        global $DB;

        if ($product->contexttype == 'userenrol') {
            if ($ue = $DB->get_record('user_enrolments', ['id' => $product->instanceid])) {
                $enrol = $DB->get_record('enrol', ['id' => $ue->enrolid]);
                $enrolplugin = enrol_get_plugin($enrol->enrol);
                shop_trace('[] Deleting user enrolment on {$ue->enrolid} for user {$ue->userid}');
                $enrolplugin->unenrol_user($enrol, $ue->userid);
            }
        }
    }

    /**
     * Disables the product effect in a way it can be restored
     * @param Product $product
     */
    public function soft_delete(Product $product) {
        global $DB;

        if ($product->contexttype == 'userenrol') {
            if ($ue = $DB->get_record('user_enrolments', ['id' => $product->instanceid])) {
                $ue->status = 1;
                $DB->update_record('user_enrolments', $ue);
            }
        }
    }

    /**
     * Update essentially updates enrolment period against product date changes.
     * @param Product $product
     */
    public function update(Product $product) {
        global $DB;

        if ($product->contexttype == 'userenrol') {
            if ($ue = $DB->get_record('user_enrolments', ['id' => $product->instanceid])) {
                $ue->timestart = $product->startdate;
                if (!empty($product->enddate)) {
                    $ue->timeend = $product->enddate;
                }
                $DB->update_record('user_enrolments', $ue);
            }
        }
    }

    /** 
     * Restores the effect of the product instance
     * @param Product $product
     */
    public function soft_restore(Product $product) {
        global $DB;

        if ($product->contexttype == 'userenrol') {
            if ($ue = $DB->get_record('user_enrolments', ['id' => $product->instanceid])) {
                $ue->status = 0;
                $DB->update_record('user_enrolments', $ue);
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

        $mess = get_string('usinghandler', 'local_shop', $this->name);
        $messages[$data->code][] = $mess;

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['coursename']) &&
                !isset($data->actionparams['courseid']) &&
                        !isset($data->actionparams['courseidnumber'])) {
            $warnings[$data->code][] = get_string('errornocourse', 'shophandlers_std_enrolonecourse');
        } else {
            if (!empty($data->actionparams['coursename'])) {
                if (!$course = $DB->get_record('course', ['shortname' => $data->actionparams['coursename']])) {
                    $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecourse', $data->actionparams['coursename']);
                    $errors[$data->code][] = $fb;
                }
            }
            if (!empty($data->actionparams['courseid'])) {
                if (!$course = $DB->get_record('course', ['id' => $data->actionparams['courseid']])) {
                    $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecourse', $data->actionparams['courseid']);
                    $errors[$data->code][] = $fb;
                }
            }
            if (!empty($data->actionparams['courseidnumber'])) {
                if (!$course = $DB->get_record('course', ['idnumber' => $data->actionparams['courseidnumber']])) {
                    $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecourse', $data->actionparams['courseidnumber']);
                    $errors[$data->code][] = $fb;
                }
            }

            // If we have course, and an explicit groupname given, check groupname
            if (!empty($course)) {
                if (!empty($data->actionparams['groupname'])) {
                    $params = ['courseid' => $course->id, 'name' => $data->actionparams['groupname']];
                    if (!$DB->get_record('groups', $params)) {
                        $fb = get_string('warninggrouptobecreated', 'shophandlers_std_enrolonecourse', $data->actionparams['groupname']);
                        $warnings[$data->code][] = $fb;
                    } else {
                        // In unit tests, do not create anything.
                    }
                }
            }
        }

        if (!isset($data->actionparams['role'])) {
            $warnings[$data->code][] = get_string('warningroledefaultstoteacher', 'shophandlers_std_enrolonecourse');
            $data->actionparams['role'] = 'student';
        }

        if (!$DB->get_record('role', ['shortname' => $data->actionparams['role']])) {
            $errors[$data->code][] = get_string('errorrole', 'shophandlers_std_enrolonecourse', $data->actionparams['role']);
        }
    }
}
