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
 * @package   local_shop
 * @subpackage shophandler_std_generateseats
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * STD_GENERATE_SEATS is a standard shop product action handler that create product instances standing
 * for unassigned seats (defered to future choice enrolments). These products belong to the customer and
 * he will be able to "burn" those products later assigning people he has on his behalf.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\Shop;
use local_shop\Bill;
use local_shop\Customer;

/**
 * STD_GENERATE_SEATS is a standard shop product action handler that create product instances standing
 * for unassigned seats (defered to future choice enrolments). These products belong to the customer and
 * he will be able to "burn" those products later assigning people he has on his behalf.
 */
class shop_handler_std_generateseats extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_generateseats'; // For unit test reporting.
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

        // Get customersupportcourse designated by handler internal params and prepare customer support action.
        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
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

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        $enabledcourses = [];

        if (!empty($data->actionparams['supervisor'])) {
            if (!$DB->get_record('role', ['shortname' => $data->actionparams['supervisor']])) {
                $mess = "[{$data->transactionid}] STD_GENERATE_SEATS Postpay Warning : ";
                $mess .= "Supervisor role defined but not in database. Using teacher as default.";
                shop_trace($mess);
                $data->actionparams['supervisor'] = 'teacher';
            }
        } else {
            $mess = "[{$data->transactionid}] STD_GENERATE_SEATS Postpay Warning : ";
            $mess .= "Supervisor role not defined. Using teacher as default.";
            shop_trace($mess);
            $data->actionparams['supervisor'] = 'teacher';
        }

        if (empty($data->actionparams['courselist'])) {
            shop_trace("[{$data->transactionid}] STD_GENERATE_SEATS Postpay Warning : No courses restriction");
        } else {
            // Prepare course list for productiondata
            $coursepatterns = explode(',', $data->actionparams['courselist']);

            foreach ($coursepatterns as $cn) {
                $params = ['shortname' => $cn];
                $select = $DB->sql_like('shortname', ':shortname');
                if ($allowedcourses = $DB->get_records_select('course', $select, $params, 'shortname', 'id,shortname')) {
                    foreach ($allowedcourses as $c) {
                        $enabledcourses[$c->shortname] = 1;
                    }
                }
            }
        }

        if (!$customerid = $data->customer->id) {
            $customerid = $data->get_customerid();
        }

        if ($customer = new Customer($customerid)) {
            $customeruser = $DB->get_record('user', ['id' => $customer->hasaccount]);
            if (!$customeruser) {
                throw new moodle_exception("Customer user with id {$customer->hasaccount} not found on postpay ");
            }
        } else {
            throw new moodle_exception("Customer record not found on postpay ");
        }

        /*
        // Empty course list should be accepted
        if (empty($enabledcourses)) {
            shop_trace("[{$data->transactionid}] STD_GENERATE_SEATS Postpay Error : No courses in course list. Possible product misconfiguration.");
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_generateseats', $shortname);
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_generateseats', $shortname);
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_generateseats', $shortname);
            return $productionfeedback;
        }
        */

        if (!isset($data->actionparams['packsize'])) {
            shop_trace("[{$data->transactionid}] STD_GENERATE_SEATS Postpay Warning : Defaults to 1 unit pack");
            $data->actionparams['packsize'] = 1;
        }

        shop_trace("[{$data->transactionid}] STD_GENERATE_SEATS Postpay : Complete.");
        for ($i = 0 ; $i < $data->quantity * $data->actionparams['packsize'] ; $i++) {
            $product = new StdClass();
            $product->catalogitemid = $data->catalogitem->id;
            $product->initialbillitemid = $data->id; // Data is a billitem.
            $product->currentbillitemid = $data->id; // Data is a billitem.
            $product->customerid = $data->bill->customerid;
            $product->contexttype = 'userenrol';
            $product->instanceid = ''; // Will match a user_enrolment record when attributed.
            $product->startdate = time();
            $product->enddate = '';
            $product->extradata = '';
            $product->reference = shop_generate_product_ref($data);

            $proddata = [];
            $proddata['handler'] = 'std_generateseats';
            $proddata['enabledcourses'] = implode(',', array_keys($enabledcourses));
            $proddata['supervisor'] = $data->actionparams['supervisor'];
            $product->productiondata = Product::compile_production_data($proddata);

            $product->id = $DB->insert_record('local_shop_product', $product);

            // Should we record a productevent.
            $productevent = new StdClass();
            $productevent->productid = $product->id;
            $productevent->billitemid = $data->id;
            $productevent->datecreated = time();
            $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);
        }

        $e = new Stdclass;
        $e->txid = $data->transactionid;
        $e->username = $customeruser->username;
        $e->seats = $data->quantity * $data->actionparams['packsize'];

        // Add user to customer support on real purchase.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_GENERATE_SEATS Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);

            $supportid = $DB->get_field('course', 'id', ['shortname' => $data->actionparams['customersupport']]);
            $e->customersupporturl = new moodle_url('/course/view.php', ['id' => $supportid]);

            $productionfeedback->public = get_string('productiondata_created_public', 'shophandlers_std_generateseats', $e);
            $productionfeedback->private = get_string('productiondata_created_private', 'shophandlers_std_generateseats', $e);
        } else {
            $e->customersupporturl = '';
            $productionfeedback->public = get_string('productiondata_created_public_no_support', 'shophandlers_std_generateseats', $e);
            $productionfeedback->private = get_string('productiondata_created_private_no_support', 'shophandlers_std_generateseats', $e);
        }

        unset($enabledcourses);

        $productionfeedback->salesadmin = get_string('productiondata_created_sales', 'shophandlers_std_generateseats', $e);

        shop_trace("[{$data->transactionid}] STD_GENERATE_SEATS Postpay : Complete.");

        return $productionfeedback;
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

        if (!isset($data->actionparams['courselist'])) {
            $warnings[$data->code][] = get_string('warningemptycourselist', 'shophandlers_std_generateseats');
        } else {
            $courselist = explode(',', $data->actionparams['courselist']);
            $hascourses = false;
            foreach ($courselist as $cn) {
                $select = $DB->sql_like('shortname', ':shortname');
                if ($DB->get_records_select('course', $select, ['shortname' => $cn])) {
                    $hascourses = true;
                }
            }
            if (!$hascourses) {
                $warnings[$data->code][] = get_string('warningonecoursenotexists', 'shophandlers_std_generateseats', $cn);
            }
        }

        if (!isset($data->actionparams['supervisor'])) {
            $warnings[$data->code][] = get_string('warningsupervisordefaultstoteacher', 'shophandlers_std_generateseats');
            $data->actionparams['supervisor'] = 'teacher';
        }

        if (!$DB->get_record('role', ['shortname' => $data->actionparams['supervisor']])) {
            $errors[$data->code][] = get_string('errorsupervisorrole', 'shophandlers_std_generateseats');
        }

        if (!isset($data->actionparams['packsize'])) {
            $warnings[$data->code][] = get_string('warningpacksizedefaultstoone', 'shophandlers_std_generateseats');
        }
    }

    /**
     * this method renders part of the product post purchase management GUI
     * for products generated with this handler
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     */
    public function display_product_actions($pid, $params) {
        global $COURSE, $DB, $OUTPUT;

        $str = '';
        $options = ['class' => 'form-submit'];
        if ($assignedenrol = $DB->get_field('local_shop_product', 'instanceid', ['id' => $pid])) {

            $ue = $DB->get_record('user_enrolments', ['id' => $assignedenrol]);
            $enrol = $DB->get_record('enrol', ['id' => $ue->enrolid]);
            $userenrolled = $DB->get_record('user', ['id' => $ue->userid]);
            $courseenrolled = $DB->get_record('course', ['id' => $enrol->courseid]);

            $str .= $OUTPUT->box_start();
            $str .= get_string('assignedto', 'shophandlers_std_generateseats', fullname($userenrolled));
            $str .= '<br/>';
            $str .= get_string('incourse', 'shophandlers_std_generateseats', $courseenrolled);
            $str .= $OUTPUT->box_end();

            if ($DB->count_records('log', ['userid' => $ue->userid, 'course' => $enrol->courseid])) {
                $str .= get_string('assignseatlocked', 'shophandlers_std_generateseats');
            } else {
                $params = ['id' => $COURSE->id, 'pid' => $pid, 'method' => 'unassignseat'];
                $url = new moodle_url('/local/shop/datahandling/postproduction.php', $params);
                $str .= $OUTPUT->single_button($url, get_string('unassignseat', 'shophandlers_std_generateseats'), 'post', $options);
            }
        } else {
            $params = ['id' => $COURSE->id, 'pid' => $pid, 'method' => 'assignseat'];
            $url = new moodle_url('/local/shop/datahandling/postproduction.php', $params);
            $str .= $OUTPUT->single_button($url, get_string('assignseat', 'shophandlers_std_generateseats'), 'post', $options);
        }
        return $str;
    }

    /**
     * Post produces assigned seats
     *
     * @param object $product a Product instance
     * @param object $productioninfo a data aggregate with production contextual data
     */
    public function postprod_assignseat(Product &$product, &$productioninfo) {
        global $COURSE, $CFG, $OUTPUT, $DB;

        include_once($CFG->dirroot.'/local/shop/datahandling/handlers/std_generateseats/assign_seat_form.php');

        $coursenames = explode(',', urldecode($productioninfo->enabledcourses));
        $supervisorrole = $DB->get_record('role', ['shortname' => $productioninfo->supervisor]);
        $allowedcourses = [];
        if (!empty($coursenames)) {
            foreach ($coursenames as $cn) {
                $select = $DB->sql_like('shortname', ':shortname');
                if ($valid_courses = $DB->get_records_select('course', $select, ['shortname' => $cn])) {
                    foreach ($valid_courses as $c) {
                        $allowedcourses[$c->id] = $c;
                    }
                }
            }
        } else {
            $allowedcourses = $DB->get_records('course', [], 'fullname');
        }

        $mform = new AssignSeatForm($productioninfo->url, ['allowedcourses' => $allowedcourses]);

        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/course/view.php', ['id' => $COURSE->id]));
        }

        if ($data = $mform->get_data()) {

            $data->supervisorrole = $supervisorrole;
            $ret = $this->postprod_assignseat_worker($data, $product);

            echo $OUTPUT->header();
            echo $ret;
            $label = get_string('backtocourse', 'shophandlers_std_generateseats');
            echo $OUTPUT->single_button(new moodle_url('/course/view.php?id='.$COURSE->id), $label);
            echo $OUTPUT->footer();
            die;
        }

        $data = new StdClass();
        $data->id = $COURSE->id;
        $data->pid = $product->id;
        $data->method = 'assignseat';

        $mform->set_data($data);

        // echo $OUTPUT->header();
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

    /**
     * Postproduction worker for each seat.
     * @param objet $data
     * @param Product ref &$product
     */
    public function postprod_assignseat_worker($data, Product &$product) {
        global $OUTPUT, $DB, $SITE, $USER;

        // Get role record
        // TODO : Generalize with _supervisor
        $role = $DB->get_record('role', ['shortname' => 'student']);
        $supervisorrole = $data->supervisor;

        // Get user to enrol record
        $usertoenrol = $DB->get_record('user', ['id' => $data->userid]);
        $starttime = time();
        $endtime = 0;

        // Get target course
        $course = $DB->get_record('course', ['id' => $data->courseid]);
        $coursecontext = context_course::instance($data->courseid);

        // get bill information
        $billid = $DB->get_field('local_shop_billitem', 'billid', ['id' => $product->currentbillitemid]);
        $bill = new Bill($billid);
        $billnumber = 'B'.sprintf('%010d', $bill->ordering);

        $enrolname = 'manual';

        $params = ['enrol' => $enrolname, 'courseid' => $data->courseid, 'status' => ENROL_INSTANCE_ENABLED];
        if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin($enrolname); // the enrol object instance
        }

        $a = new StdClass();
        $a->user = fullname($usertoenrol);
        $a->course = $course->fullname;

        try {
            $ret = '';
            $ret .= $OUTPUT->heading(get_string('productpostprocess', 'local_shop'));
            if (is_enrolled($coursecontext, $usertoenrol)) {
                $ret .= $OUTPUT->notification(get_string('seatalreadyassigned', 'shophandlers_std_generateseats', $a));
                // Nothing to do.
                return $ret;
            } else {
                $ret .= $OUTPUT->notification(get_string('seatassigned', 'shophandlers_std_generateseats', $a));

                $enrolplugin->enrol_user($enrol, $usertoenrol->id, $role->id, $starttime, $endtime, ENROL_USER_ACTIVE);

                // Notify student user.
                $mailtitle = get_string('seatassigned_title', 'shophandlers_std_generateseats', $SITE->fullname);
                $a = new StdClass();
                $a->course = $course->fullname;
                $a->url = new moodle_url('/course/view.php', ['id' => $course->id]);
                $mailcontent = get_string('seatassigned_mail', 'shophandlers_std_generateseats', $a);
                email_to_user($usertoenrol, $USER, $mailtitle, $mailcontent);
            }

            // Enrol customer in course for supervision id not yet inside. USER is our customer user.
            if (!is_enrolled($coursecontext, $USER)) {
                $enrolplugin->enrol_user($enrol, $USER->id, $supervisorrole->id, $starttime, 0, ENROL_USER_ACTIVE);
            }

            // Check course has a group for the bill.
            if (!$group = $DB->get_record('groups', ['courseid' => $course->id, 'name' => $billnumber])) {
                $group = new StdClass();
                $group->courseid = $course->id;
                $group->name = $billnumber;
                $group->description = get_string('shopproductcreated', 'local_shop');
                $group->descriptionformat = 0;
                $group->timecreated = time();
                $group->timemodified = time();
                $group->id = $DB->insert_record('groups', $group);

                // Invalidate the grouping cache for the course.
                cache_helper::invalidate_by_definition('core', 'groupdata', [], [$course->id]);
            }

            // Put both users in group.
            groups_add_member($group->id, $usertoenrol->id);
            groups_add_member($group->id, $USER->id);

            // Mark product with enrolment instance.
            $ue = $DB->get_record('user_enrolments', ['enrolid' => $enrol->id, 'userid' => $usertoenrol->id]);
            $product->instanceid = $ue->id;
            $product->startdate = time();
            $product->save();
        } catch (Exception $exc) {
            $ret = '';
            $ret .= $OUTPUT->heading(get_string('productpostprocess', 'local_shop'));
            $ret .= $OUTPUT->notification('Error in assign / Error process to be finished');
        }
        return $ret;
    }

    /**
     * Post production : unassign assigned seats
     * @param Product ref &$product
     * @param object production info &$productioninfo
     */
    public function postprod_unassignseat(&$product, &$productioninfo) {
        global $COURSE, $OUTPUT, $DB;

        $enrolname = 'manual';

        if (!$ueinstance = $DB->get_record('user_enrolments', ['id' => $product->instanceid])) {
            echo $OUTPUT->header();
            $label = get_string('backtocourse', 'shophandlers_std_generateseats');
            echo $OUTPUT->single_button(new moodle_url('/course/view.php?id='.$COURSE->id), $label);
            echo $OUTPUT->footer();
            die;
        }
        $enrol = $DB->get_record('enrol', ['id' => $ueinstance->enrolid]);

        // Unenrol user if still exists.
        $params = ['enrol' => $enrolname, 'courseid' => $enrol->courseid, 'status' => ENROL_INSTANCE_ENABLED];
        if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin($enrolname); // the enrol object instance
            $enrolplugin->unenrol_user($enrol, $ueinstance->userid);
        }

        // Get bill information.
        $billid = $DB->get_field('local_shop_billitem', 'billid', ['id' => $product->currentbillitemid]);
        $bill = $DB->get_record('local_shop_bill', ['id' => $billid]);
        $billnumber = 'B'.sprintf('%010d', $bill->ordering);

        // Remove group marking for the product.
        if ($group = $DB->get_record('groups', ['courseid' => $enrol->courseid, 'name' => $billnumber])) {
            $DB->delete_records('groups_members', ['userid' => $ueinstance->userid, 'groupid' => $group->id]);
        }

        // Release product instance.
        $product->instanceid = 0;
        $DB->update_record('local_shop_product', $product);

        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('seatreleased', 'shophandlers_std_generateseats'));
        $label = get_string('backtocourse', 'shophandlers_std_generateseats');
        echo $OUTPUT->single_button(new moodle_url('/course/view.php', ['id' => $COURSE->id]), $label);
        echo $OUTPUT->footer();
    }

    /**
     * Display some info about the product
     * @param int $pid
     * @param object $pinfos
     */
    public function display_product_infos($pid, $pinfos) {
        global $DB;

        foreach ($pinfos as $infokey => $info) {
            if ($infokey == 'handler') {
                continue;
            }
            if ($infokey == 'enabledcourses') {
                echo '<b>'.get_string($infokey, 'shophandlers_std_generateseats').':</b><br/>';
                $courses = preg_split('/\\s+,/', $info);
                $coursedescs = [];
                foreach ($courses as $courseshort) {
                    $fullname = $DB->get_field('course', 'fullname', ['shortname' => $courseshort]);
                    $coursedescs[] = "[{$courseshort}] $fullname";
                }
                if (empty($coursedescs)) {
                    echo get_string('allcourses', 'shophandlers_std_generateseats');
                } else {
                    echo implode('<br/>', $coursedescs).'<br/>';
                }
                continue;
            }
            echo '<b>'.get_string($infokey, 'shophandlers_std_generateseats').':</b> '.urldecode($info).'<br/>';
        }
    }
}
