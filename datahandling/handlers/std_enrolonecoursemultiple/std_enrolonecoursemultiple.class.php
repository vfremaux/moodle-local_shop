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
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\ProductEvent;
use local_shop\Shop;

class shop_handler_std_enrolonecoursemultiple extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_enrolonecourse'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * this product should always be available, oppositely to the 
     * enrolonecourse original product.
     */
    public function is_available(&$catalogitem) {
        return true;
    }

    /**
     * Pre pay information always comme from shopping session.
     */
    public function produce_prepay(&$data, &$errorstatus) {

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
     * Post pay information can come from session or from production data stored in delayed bills.
     * @param objectref &$data a full filled billitem object.
     */
    public function produce_postpay(&$data) {
        global $DB, $USER;

        $config = get_config('local_shop');

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        // Check for params validity (internals).

        if (empty($data->actionparams['coursename']) && empty($data->actionparams['courseid'])) {
            print_error('errormissingactiondata', 'local_shop', $this->get_name());
        }

        $rolename = @$data->actionparams['role'];
        if (empty($rolename)) {
            $rolename = 'student';
        }

        // Perform operations.

        // Assign Student role in course for the period.
        if (!empty($data->actionparams['coursename'])) {
            $coursename = $data->actionparams['coursename'];
            if (!$course = $DB->get_record('course', array('shortname' => $coursename))) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE_MULTIPLE PostPay : failed... Bad course name");
                print_error("Bad target course shortname for product");
            }
        } else if (!empty($data->actionparams['courseidnumber'])) {
            $idnumber = $data->actionparams['courseidnumber'];
            if (!$course = $DB->get_record('course', array('idnumber' => $idnumber))) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE_MULTIPLE PostPay : failed... Bad course idnumber");
                print_error("Bad target course id for product");
            }
        } else {
            $courseid = $data->actionparams['courseid'];
            if (!$course = $DB->get_record('course', array('shortname' => $courseid))) {
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE_MULTIPLE PostPay : failed... Bad course id");
                print_error("Bad target course id for product");
            }
        }

        // Compute start and end time.
        $starttime = shop_compute_enrol_time($data, 'starttime', $course);
        $endtime = shop_compute_enrol_time($data, 'endtime', $course);

        $enrolname = @$data->actionparams['enrol'];
        if (empty($enrolname)) {
            $enrolname = 'manual';
        }

        $role = $DB->get_record('role', array('shortname' => $rolename));
        $now = time();

        $context = context_course::instance($course->id);

        $params = array('enrol' => $enrolname, 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED);
        if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin($enrolname); // The enrol object instance.
        }

        if (!$customerid = $data->customer->id) {
            $customerid = $data->get_customerid();
        }

        $customer = $DB->get_record('local_shop_customer', array('id' => $customerid));
        $customeruser = $DB->get_record('user', array('id' => $customer->hasaccount));

        if (!$oldue = $DB->get_record('user_enrolments', array('userid' => $customeruser->id, 'enrolid' => $enrol->id))) {
            try {
                $userid = $DB->get_field('local_shop_customer', 'hasaccount', array('id' => $data->get_customerid()));
                $enrolplugin->enrol_user($enrol, $userid, $role->id, $starttime, $endtime, ENROL_USER_ACTIVE);
                $message = "User {$userid} Enrolled in course {$course->shortname} ";
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE_MULTIPLE PostPay : ".$message);
            } catch (Exception $exc) {
                $e = new StdClass;
                $e->code = $data->code;
                $e->errorcode = 'Code : ROLE ASSIGN ISSUE';
                $e->txid = $data->transactionid;
                shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE PostPay : Failed enrol...");
                $fb = get_string('productiondata_failure_public', 'shophandlers_std_enrolonecoursemultiple', $e);
                $productionfeedback->public = $fb;
                $fb = get_string('productiondata_failure_private', 'shophandlers_std_enrolonecoursemultiple', $course->id);
                $productionfeedback->private = $fb;
                $fb = get_string('productiondata_failure_sales', 'shophandlers_std_enrolonecoursemultiple', $course->id);
                $productionfeedback->salesadmin = $fb;
                return $productionfeedback;
            }

            // Get the user enrolment record as instance for product record.
            $ue = $DB->get_record('user_enrolments', array('enrolid' => $enrol->id, 'userid' => $userid));
        } else {
            // If already enrolled, get the user_enrolment record and push the date forward with this purchase.
            // Ensure the enrolment is active.
            $oldue->status = 0;
            $oldue->timeend = $endtime;
            $DB->update_record('user_enrolments', $oldue);

            $ue = $oldue;
        }

        // Create product instance in product table.
        for ($i = 0; $i < $data->quantity; $i++) {
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
            $extra = array('handler' => 'std_enrolonecoursemultiple');
            $product->productiondata = Product::compile_production_data($data->actionparams, $extra);
            $product->test = $config->test;
            $product->id = $DB->insert_record('local_shop_product', $product);

            // Record a productevent.
            $productevent = new ProductEvent(null);
            $productevent->productid = $product->id;
            $productevent->billitemid = $data->id;
            $productevent->datecreated = $now = time();
            $productevent->save();
        }

        $e = new StdClass;
        $e->txid = $data->transactionid;
        $e->courseid = $course->id;
        $e->username = $customeruser->username;

        $fb = get_string('productiondata_post_public', 'shophandlers_std_enrolonecoursemultiple', $e);
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_post_private', 'shophandlers_std_enrolonecoursemultiple', $e);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_post_sales', 'shophandlers_std_enrolonecoursemultiple', $e);
        $productionfeedback->salesadmin = $fb;

        /*
         * Make a group if needed for the customer
         * the customer is buying for its own so it will get an "own group" to eventually
         * get separated from other learner teams.
         */

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
            shop_trace("[{$data->transactionid}] Creating missing group {$groupname} as {$group->id}");
        }

        // Add all created users to group.

        if (!$groupmember = $DB->get_record('groups_members', array('groupid' => $group->id, 'userid' => $USER->id))) {
            $groupmember = new StdClass();
            $groupmember->groupid = $group->id;
            $groupmember->userid = $USER->id;
            $groupmember->timeadded = $now;
            shop_trace("[{$data->transactionid}] Adding user $USER->id to group {$groupname}");
            $DB->insert_record('groups_members', $groupmember);
        }

        // Add user to customer support.

        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE MULTIPLE Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $USER, $data->transactionid);
        }

        shop_trace("[{$data->transactionid}] STD_ENROL_ONE_COURSE MULTIPLE PostPay : Completed for $coursename...");
        return $productionfeedback;
    }

    /*
     * Gets a thumbnail from course overview files as thumb.
     */
    public function get_alternative_thumbnail_url($catalogitem) {
        global $DB, $CFG;

        $shouldexist = false;
        if (!empty($catalogitem->handlerparams['coursename'])) {
            $params = array('shortname' => $catalogitem->handlerparams['coursename']);
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        } else if (!empty($catalogitem->handlerparams['courseidnumber'])) {
            $params = array('idnumber' => $catalogitem->handlerparams['courseidnumber']);
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        } else if (!empty($catalogitem->handlerparams['courseid'])) {
            $params = array('id' => $catalogitem->handlerparams['courseid']);
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
     * The contexttype will denote the type of Moodle object that was created. some
     * hanlders may deal with several contexttypes if they have a complex production
     * operation. the instanceid is moslty a moodle table id that points the concerned instance
     * within the context type scope.
     *
     * In enrolonecoursemultiple plugin, will search for the latest product instance of same type 
     * in same context. It there are some, will adjust the endtime period to the last available
     * remaining product. It none, will unenrol completely the user from that context.
     *
     * @param string $contexttype type of context to dismount
     * @param integer/string $instanceid identifier of the instance
     */
    public function delete(&$product) {
        global $DB;

        // Get all products of type userenrol in the same instanceid context.
        $params = array('contexttype' => 'userenrol', 'instanceid' => $product->instanceid);
        $remainings = Product::get_instances($params, 'enddate');

        if (!$remainings || (count($remainings) == 1)) {
            // Am i the only one ? Usually should never be false.
            if ($product->contexttype == 'userenrol') {
                if ($ue = $DB->get_record('user_enrolments', array('id' => $product->instanceid))) {
                    $enrol = $DB->get_record('enrol', array('id' => $ue->enrolid));
                    $enrolplugin = enrol_get_plugin($enrol->enrol);
                    shop_trace('[] Deleting user enrolment on {$ue->enrolid} for user {$ue->userid}');
                    $enrolplugin->unenrol_user($enrol, $ue->userid);
                }
            }
        }

        // Adjust enrol end date to latest.
        $remainingsvalues = array_values($remainings);
        $lastdateproduct = array_pop($remainingsvalues);
        if ($ue = $DB->get_record('user_enrolments', array('id' => $product->instanceid))) {
            $ue->timeend = $lastdateproduct->enddate;
            shop_trace('[] Adjusting user enrolment enddate to {$lastdateproduct->enddate} for {$ue->enrolid} for user {$ue->userid}');
            $DB->update_record('user_enrolments', $ue);
        }
    }

    public function soft_delete(&$product) {
        global $DB;

        // Get all products of type userenrol in the same instanceid context.
        $params = array('contexttype' => 'userenrol', 'instanceid' => $product->instanceid);
        $remainings = Product::get_instances($params, 'enddate');

        if (!$remaining || (count($remainings) == 1)) {
            // Am i the only one ? Usually should never be false.
            if ($product->contexttype == 'userenrol') {
                if ($ue = $DB->get_record('user_enrolments', array('id' => $product->instanceid))) {
                    $ue->status = 1;
                    $DB->update_record('user_enrolments', $ue);
                }
            }
        }

        // Adjust enrol end date to latest.
        $lastdateproduct = array_pop(array_values($remainings));
        $ue = $DB->get_record('user_enrolments', array('id' => $product->instanceid));
        $ue->timeend = $lastdateproduct->enddate;
        shop_trace('[] Adjusting user enrolment enddate to {$lastdateproduct->enddate} for {$ue->enrolid} for user {$ue->userid}');
        $DB->update-record('user_enrolments', $ue);

    }

    public function soft_restore(&$product) {
        global $DB;

        if ($product->contexttype == 'userenrol') {
            if ($ue = $DB->get_record('user_enrolments', array('id' => $product->instanceid))) {
                $ue->status = 0;
                $DB->update_record('user_enrolments', $ue);
            }
        }
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

        if (!isset($data->actionparams['coursename']) &&
                !isset($data->actionparams['courseid']) &&
                        !isset($data->actionparams['courseidnumber'])) {
            $warnings[$data->code][] = get_string('errornocourse', 'shophandlers_std_enrolonecoursemultiple');
        } else {
            if (!empty($data->actionparams['coursename'])) {
                if (!$DB->get_record('course', array('shortname' => $data->actionparams['coursename']))) {
                    $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecoursemultiple', $data->actionparams['coursename']);
                    $errors[$data->code][] = $fb;
                }
            }
            if (!empty($data->actionparams['courseid'])) {
                if (!$DB->get_record('course', array('id' => $data->actionparams['courseid']))) {
                    $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecoursemultiple', $data->actionparams['courseid']);
                    $errors[$data->code][] = $fb;
                }
            }
            if (!empty($data->actionparams['courseidnumber'])) {
                if (!$DB->get_record('course', array('idnumber' => $data->actionparams['courseidnumber']))) {
                    $fb = get_string('errorcoursenotexists', 'shophandlers_std_enrolonecoursemultiple', $data->actionparams['courseidnumber']);
                    $errors[$data->code][] = $fb;
                }
            }
        }

        if (!isset($data->actionparams['role'])) {
            $warnings[$data->code][] = get_string('warningroledefaultstoteacher', 'shophandlers_std_enrolonecoursemultiple');
            $data->actionparams['role'] = 'student';
        }

        if (!$DB->get_record('role', array('shortname' => $data->actionparams['role']))) {
            $errors[$data->code][] = get_string('errorrole', 'shophandlers_std_enrolonecoursemultiple', $data->actionparams['role']);
        }

    }

    /**
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     */
    public function display_product_infos($pid, $pinfo, $extradata = null) {
        global $DB, $OUTPUT;

        // Aggregate here the extradata info that may content an exploitation customer side reference.
        $str = '';

        if (!empty($extradata)) {
            $str .= $OUTPUT->render_from_template('shophandlers_std_enrolonecoursemultiple/extradata', (object)$extradata);
        }

        return $str;
    }
}