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
 * @package     local_shop
 * @category    local
 * @subpackage  producthandlers
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once($CFG->dirroot.'/lib/enrollib.php');

class shop_handler_std_extendenrolperiod extends shop_handler{

    public function __construct($label) {
        $this->name = 'std_extendenrolperiod'; // for unit test reporting
        parent::__construct($label);
    }

    public function produce_prepay(&$data) {

        if (!isloggedin()) {
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

    public function produce_postpay(&$data) {
        global $USER, $DB;

        $productionfeedback = new StdClass();

        if (!isset($data->actionparams['coursename'])) {
            print_error('errormissingactiondata', 'local_shop', $this->get_name());
        }

        // Assign Student role in course for the period
        if (!$course = $DB->get_record('course', array('shortname' => $data->actionparams['coursename']))) {
            print_error('erroractiondatavalue', 'local_shop', $this->get_name());
        }

        if (!isset($data->actionparams['enroltype'])) {
            print_error('errormissingactiondata', 'local_shop', $this->get_name());
        }

        if (!$enrolplugin = enrol_get_plugin($data->actionparams['enroltype'])) {
            print_error('genericerror', 'local_shop', get_string('errorenrolnotinstalled', 'shophandlers_std_extendenrolperiod'));
        }

        if (!is_enrol_enabled($data->actionparams['enroltype'])) {
            print_error('genericerror', 'local_shop', get_string('errorenroldisabled', 'shophandlers_std_extendenrolperiod'));
        }

        if (!isset($data->actionparams['extension'])) {
            print_error('errormissingactiondata', 'local_shop', get_string('extension', 'shophandlers_std_extendenrolperiod'));
        }

        // Quantity addresses number of elementary extension period.
        $rangeextension = $data->actionparams['extension'] * DAYSECS * $data->quantity;

        $enrol = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => $data->actionparams['enroltype']));

        $context = context_course::instance($course->id);
        $userid = (empty($data->foruser)) ? $USER->id : $data->foruser;
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

        if (!$enroldata = $DB->get_record('user_enrolments', array('enrolid' => $enrol->id, 'userid' => $userid))) {
            $productiondata->public = get_string('processerror', 'local_shop');
            $productiondata->private = get_string('processerror', 'local_shop');
            $productiondata->salesadmin = "No assignation for this user $userid in context $context->id. Nothing done.";
            shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD Postpay Error : Could not extend...");
            return $productiondata;
        }

        /*
         * If we have time left in course, extend the period over that time. If we are already out of time, just open
         * a new rangextension period from now.
         */
        $now = time();
        $enroldata->timeend = ($now > $enroldata->timeend) ? $now +  $rangeextension : $enroldata->timeend + $rangeextension;

        if (!$DB->update_record('user_enrolments', $enroldata)) {
            $productionfeedback->public = get_string('processerror', 'local_shop');
            $productionfeedback->private = get_string('processerror', 'local_shop');
            $productionfeedback->salesadmin = "Update Error ";
            shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD Postpay Error : Could not extend...");
            return $productionfeedback;
        }

        // Find existing product and add an event.
        // Register product.
        $product = $DB->get_record('local_shop_product', array('reference' => $data->required['productcode']));
        $product->enddate = $endtime;
        $DB->update_record('local_shop_product', $product);

        // Record an event.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        $maildata->courseid = $course->id;
        $maildata->extension = $rangeextension / DAYSECS; // given in days
        $maildata->username = fullname($data->bill->user);

        $productionfeedback->public = get_string('productiondata_public', 'shophandlers_STD_EXTEND_ENROL_PRERIOD', $maildata);
        $productionfeedback->private = get_string('productiondata_private', 'shophandlers_STD_EXTEND_ENROL_PRERIOD', $maildata);
        $productionfeedback->salesadmin = get_string('productiondata_sales', 'shophandlers_STD_EXTEND_ENROL_PRERIOD', $maildata);
        shop_trace("[{$data->transactionid}] STD_EXTEND_ENROL_PERIOD Postpay : Complete...");

        return $productionfeedback;
    }

    public function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['coursename'])) {
            $errors[$data->code][] = get_string('errornocourse', 'shophandlers_std_extendenrolperiod');
        } else {
            if (!$course = $DB->get_record('course', array('shortname' => $data->actionparams['coursename']))) {
                $errors[$data->code][] = get_string('errorextcoursenotexists', 'shophandlers_std_extendenrolperiod', $data->actionparams['coursename']);
            }
        }

        if (!isset($data->actionparams['enroltype'])) {
            $warnings[$data->code][] = get_string('warningenroltypedefaultstomanual', 'shophandlers_std_extendenrolperiod');
            $data->actionparams['enroltype'] = 'manual';
        }

        if (!$enrolplugin = enrol_get_plugin($data->actionparams['enroltype'])) {
            $errors[$data->code][] = get_string('errorenrolpluginnotavailable', 'shophandlers_std_extendenrolperiod', $data->actionparams['enroltype']);
        }

        if (!enrol_is_enabled($data->actionparams['enroltype'])) {
            $errors[$data->code][] = get_string('errorenroldisabled', 'shophandlers_std_extendenrolperiod', $data->actionparams['enroltype']);
        }

        if (!isset($data->actionparams['extension'])) {
            $warnings[$data->code][] = get_string('warningnullextension', 'shophandlers_std_extendenrolperiod');
        }
    }
}