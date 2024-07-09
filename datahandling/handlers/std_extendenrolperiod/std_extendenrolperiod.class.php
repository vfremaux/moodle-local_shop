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
 * @package     local_shop
 * @subpackage  shophandler_std_extendenrolperiod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/enrollib.php');
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\ProductEvent;

/**
 * STD_EXTEND_ENROL_PERIOD is a standard shop product action handler that can extend en enrolment record
 * that has been purchased before.
 */
class shop_handler_std_extendenrolperiod extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_extendenrolperiod'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public function produce_prepay(&$data) {

        if (!isloggedin() && !isguestuser()) {
            $productionfeedback->public = get_string('needsenrol', 'local_shop');
            $productionfeedback->private = get_string('needsenrol', 'local_shop');
            $productionfeedback->salesadmin = get_string('needsenrol', 'local_shop');
            return $productionfeedback;
        }

        // Void action. Nothing to do prepay.
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
        global $USER, $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (empty($data->actionparams['coursename']) && empty($data->actionparams['courseid'])) {
            throw new moodle_exception(get_string('errormissingactiondata', 'local_shop', $this->get_name()));
        }

        if (!empty($data->actionparams['coursename'])) {
            if (!$course = $DB->get_record('course', ['shortname' => $data->actionparams['coursename']])) {
                shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD PostPay : failed... Bad course shortname");
                throw new moodle_exception(get_string('erroractiondatavalue', 'local_shop', $this->get_name()));
            }
        } else {
            if (!$course = $DB->get_record('course', ['id' => $data->actionparams['courseid']])) {
                shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD PostPay : failed... Bad course id");
                throw new moodle_exception(get_string('erroractiondatavalue', 'local_shop', $this->get_name()));
            }
        }

        if (!isset($data->actionparams['enroltype'])) {
            throw new moodle_exception(get_string('errormissingactiondata', 'local_shop', $this->get_name()));
        }

        if (!enrol_get_plugin($data->actionparams['enroltype'])) {
            throw new moodle_exception(get_string('genericerror', 'local_shop', get_string('errorenrolnotinstalled', 'shophandlers_std_extendenrolperiod')));
        }

        if (!is_enrol_enabled($data->actionparams['enroltype'])) {
            throw new moodle_exception(get_string('genericerror', 'local_shop', get_string('errorenroldisabled', 'shophandlers_std_extendenrolperiod')));
        }

        if (!isset($data->actionparams['extension'])) {
            throw new moodle_exception(get_string('errormissingactiondata', 'local_shop', get_string('extension', 'shophandlers_std_extendenrolperiod')));
        }

        // Quantity addresses number of elementary extension period.
        $rangeextension = $data->actionparams['extension'] * DAYSECS * $data->quantity;

        $enrol = $DB->get_record('enrol', ['courseid' => $course->id, 'enrol' => $data->actionparams['enroltype']]);

        $context = context_course::instance($course->id);
        $userid = (empty($data->foruser)) ? $USER->id : $data->foruser;

        if (!$enroldata = $DB->get_record('user_enrolments', ['enrolid' => $enrol->id, 'userid' => $userid])) {
            $productiondata->public = get_string('processerror', 'local_shop');
            $productiondata->private = get_string('processerror', 'local_shop');
            $productiondata->salesadmin = "No assignation for this user $userid in context $context->id. Nothing done.";
            shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD Postpay Error : Could not extend...");
            return $productiondata;
        }

        /*
         * If we have time left in course, extend the period over that time.
         * If we are already out of time, just open a new rangextension period from now.
         */
        $now = time();
        $enroldata->timeend = ($now > $enroldata->timeend) ? $now + $rangeextension : $enroldata->timeend + $rangeextension;

        if (!$DB->update_record('user_enrolments', $enroldata)) {
            $productionfeedback->public = get_string('processerror', 'local_shop');
            $productionfeedback->private = get_string('processerror', 'local_shop');
            $productionfeedback->salesadmin = "Update Error ";
            shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD Postpay Error : Could not extend...");
            return $productionfeedback;
        }

        // Find existing product and add an event.
        // Register product.
        $product = $DB->get_record('local_shop_product', ['reference' => $data->required['productcode']]);
        $product->enddate = $enroldata->timeend;
        $DB->update_record('local_shop_product', $product);

        // Record an event.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->save();

        $e = new Stdclass;
        $e->courseid = $course->id;
        $e->extension = $rangeextension / DAYSECS; // Given in days.
        $e->username = fullname($data->bill->user);

        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_extendenrolperiod', $e);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_extendenrolperiod', $e);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_extendenrolperiod', $e);
        shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD Postpay : Complete...");

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

        if (!isset($data->actionparams['coursename'])) {
            $errors[$data->code][] = get_string('errornocourse', 'shophandlers_std_extendenrolperiod');
        } else {
            if (!$DB->get_record('course', ['shortname' => $data->actionparams['coursename']])) {
                $cn = $data->actionparams['coursename'];
                $err = get_string('errorextcoursenotexists', 'shophandlers_std_extendenrolperiod', $cn);
                $errors[$data->code][] = $err;
            }
        }

        if (!isset($data->actionparams['enroltype'])) {
            $warnings[$data->code][] = get_string('warningenroltypedefaultstomanual', 'shophandlers_std_extendenrolperiod');
            $data->actionparams['enroltype'] = 'manual';
        }

        $enroltype = $data->actionparams['enroltype'];
        if (!enrol_get_plugin($enroltype)) {
            $err = get_string('errorenrolpluginnotavailable', 'shophandlers_std_extendenrolperiod', $enroltype);
            $errors[$data->code][] = $err;
        }

        if (!enrol_is_enabled($data->actionparams['enroltype'])) {
            $err = get_string('errorenroldisabled', 'shophandlers_std_extendenrolperiod', $data->actionparams['enroltype']);
            $errors[$data->code][] = $err;
        }

        if (!isset($data->actionparams['extension'])) {
            $warnings[$data->code][] = get_string('warningnullextension', 'shophandlers_std_extendenrolperiod');
        }
    }
}
