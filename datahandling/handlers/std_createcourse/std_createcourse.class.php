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
 * @subpackage product_handlers
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * STD_CREATE_COURSE is a standard shop product action handler that creates a course space for the customer
 * and enrols the customer as editing teacher inside.
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

class shop_handler_std_createcourse extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_createcourse'; // For unit test reporting.
        parent::__construct($label);
    }

    // Pre pay information always comme from shopping session.
    public function produce_prepay(&$data) {

        // Get customersupportcourse designated by handler internal params.

        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
        }

        $productionfeedback = shop_register_customer($data);

        return $productionfeedback;
    }

    public function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['coursecategory'])) {
            $message = "[{$data->transactionid}] STD_CREATE_COURSE Postpay Error :";
            $message .= " Missing handler action data (coursecategory)";
            shop_trace($message);
            return;
        }

        if (!isset($data->actionparams['template'])) {
            $message = "[{$data->transactionid}] STD_CREATE_COURSE Postpay Error :";
            $message .= " Missing handler action data (template)";
            shop_trace($message);
            return;
        }

        if (!isset($data->actionparams['duration'])) {
            $message = "[{$data->transactionid}] STD_CREATE_COURSE Postpay :";
            $message .= " Missing handler action data (template)";
            shop_trace($message);
            return;
        }

        $now = time();
        $secsduration = $data->actionparams['duration'] * DAYSECS;
        $upto = ($secsduration) ? $now + $secsduration : 0;

        $c->category = $data->actionparams['coursecategory'];
        $c->shortname = shop_generate_shortname($data->customeruser);
        $c->fullname = $data->customerdata['fullname'];
        $c->idnumber = $data->customerdata['idnumber'];
        $c->enrollable = 0;
        $c->timecreated = $now;
        $c->startdate = $now;
        $c->lang = '';
        $c->theme = '';
        $c->cost = '';

        if (!empty($data->actionparams['template'])) {
            $coursetemplatename = $data->actionparams['template'];
    
            $template = $DB->get_record('course', array('shortname' => $coursetemplatename));
            if ($templatepath = shop_delivery_check_available_backup($template->id)) {
                if ($c->id = shop_create_course_from_template($templatepath->path, $c)) {
                    $context = context_course::instance($c->id);
                } else {
                    $fb = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : COURSE_CREATION');
                    $productionfeedback->public = $fb;
                    $fb = get_string('productiondata_failure_private', 'shophandlers_std_createcourse', $data);
                    $productionfeedback->private = $fb;
                    $fb = get_string('productiondata_failure_sales', 'shophandlers_std_createcourse', $data);
                    $productionfeedback->salesadmin = $fb;
                    $message = "[{$data->transactionid}] STD_CREATE_COURSE Postpay Error :";
                    $message .= " Course creation failure (DB reason)...";
                    shop_trace($message);
                    return $productionfeedback;
                }
            } else {
                $fb = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : TEMPLATE_BACKUP');
                $productionfeedback->public = $fb;
                $fb = get_string('productiondata_failure_private', 'shophandlers_std_createcourse', $data);
                $productionfeedback->private = $fb;
                $fb = get_string('productiondata_failure_sales', 'shophandlers_std_createcourse', $data);
                $productionfeedback->salesadmin = $fb;
                $message = "[{$data->transactionid}] STD_CREATE_COURSE Postpay Error :";
                $message .= " Template $coursetemplatename has no backup...";
                shop_trace($message);
                return $productionfeedback;
            }
        } else {
            if (!create_course($c, null)) {
                $fb = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : DEFAULT_COURSE_FAILURE');
                $productionfeedback->public = $fb;
                $fb = get_string('productiondata_failure_private', 'shophandlers_std_createcourse', $data);
                $productionfeedback->private = $fb;
                $fb = get_string('productiondata_failure_sales', 'shophandlers_std_createcourse', $data);
                $productionfeedback->salesadmin = $fb;
                $message = "[{$data->transactionid}] STD_CREATE_COURSE Postpay Error :";
                $message .= " Failed creating default course...";
                shop_trace($message);
                return $productionfeedback;
            }
        }
        if (!$role = $DB->get_record('role', array('shortname' => 'courseowner'))) {
            $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        }
        $now = time();
        if (!role_assign($role->id, $data->customeruser->id, 0, $context->id, $now, $upto, false, 'manual', time())) {
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : TEACHER_ROLE_ASSIGN');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure2_private', 'shophandlers_std_createcourse', $c);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure2_sales', 'shophandlers_std_createcourse', $c);
            $productionfeedback->salesadmin = $fb;
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay Error : Failed to assign teacher...");
            return $productionfeedback;
        }

        // Register product.
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'course';
        $product->instanceid = $c->id;
        $product->startdate = $now;
        $product->enddate = $upto;
        $product->reference = shop_generate_product_ref($data);
        $extra = array('handler' => 'std_createcourse');
        $product->productiondata = Product::compile_production_data($data->actionparams, $extra);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->save();

        // Add user to customer support on real purchase.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $data->customeruser, $data->transactionid);
        }

        $c->username = $data->customeruser->username;
        $c->fullname = stripslashes($c->fullname);
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_createcourse');
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_createcourse', $c);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_createcourse', $c);
        shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay : Completed in course {$c->shortname}.");

        return $productionfeedback;
    }

    public static function get_required_default() {
        return 'coursename|'.get_string('name').'|text;idnumber|'.get_string('idnumber').'|text';
    }

    public static function get_actionparams_default() {
        return 'coursecategory={&duration=&customersupport=}';
    }

    public function validate_required_data($itemname, $fieldname, $instance = 0, $value, &$errors) {
        global $SESSION, $DB;

        // Ensure we have an integer index.
        $instance = 0 + $instance;

        $hasnolocalerrors = true;
        if ($fieldname == 'fullname') {
            if (empty($value)) {
                $err = get_string('errorvalueempty', 'shophandlers_std_createcourse');
                $errors[$itemname][$fieldname][$instance] = $err;
                $hasnolocalerros = false;
            }
        }

        if ($fieldname == 'idnumber') {
            // First check no course with that idnumber already.
            if ($DB->count_records('course', array('idnumber' => $value))) {
                $err = get_string('erroralreadyexists', 'shophandlers_std_createcourse');
                $errors[$itemname][$fieldname][$instance] = $err;
                $hasnolocalerros = false;
            }
        }

        return $hasnolocalerrors;
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

        if ($product->contexttype == 'course') {
            if ($course = $DB->get_record('course', array('id' => $product->instanceid))) {
                shop_trace('[] Deleting all course data {$course->shortname}');
                delete_course($course, false);
            }
        }
    }

    /**
     * Attempts to disable the product effect while preserving the data so the product
     * can be restored in active state without data loss. This is done by setting all
     * user enrolments inactive in the target course.
     * @param string $contexttype
     * @param integer/string $instanceid
     */
    public function soft_delete(&$product) {
        global $DB;

        if ($product->contexttype == 'course') {
            if ($course = $DB->get_record('course', array('id' => $product->instanceid))) {
                $enrol = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
                $sql = "
                    UPDATE
                        {user_enrolments}
                    SET
                        status = 1
                    WHERE
                        enrolid = ?
                ";
                $DB->execute($sql);
            }
        }
    }

    /**
     * Restores what soft_delete switches off in order to restore use of the product
     * @param string $contexttype
     * @param integer/string $instanceid
     */
    public function soft_restore(&$product) {
        global $DB;

        if ($product->contexttype == 'course') {
            if ($course = $DB->get_record('course', array('id' => $product->instanceid))) {
                $enrol = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'));
                $sql = "
                    UPDATE
                        {user_enrolments}
                    SET
                        status = 0
                    WHERE
                        enrolid = ?
                ";
                $DB->execute($sql);
            }
        }
    }

    public function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        parent::unit_test($data, $errors, $warnings, $messages);

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        if (!isset($data->actionparams['coursecategory'])) {
            $errors[$data->code][] = get_string('errornocategory', 'shophandlers_std_createcourse');
        } else {
            $catid = $data->actionparams['coursecategory'];
            if (!$DB->record_exists('course_categories', array('id' => $catid))) {
                $code= get_string('errorcategorynotexists', 'shophandlers_std_createcourse', $catid);
                $errors[$data->code][] = $code;
            }
        }

        if (!isset($data->actionparams['template'])) {
            $warnings[$data->code][] = get_string('warningnohandlerusingdefault', 'shophandlers_std_createcourse');
        } else {
            if (!$DB->record_exists('course', array('shortname' => $data->actionparams['template']))) {
                $errors[$data->code][] = get_string('errortemplatenocourse', 'shophandlers_std_createcourse');
            }
        }

        if (!isset($data->actionparams['duration'])) {
            $warnings[$data->code][] = get_string('warningnoduration', 'shophandlers_std_createcourse');
        }
    }
}