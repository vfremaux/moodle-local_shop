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
 * @package   local_shop
 * @category  local
 * @subpackage product_handlers
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
* STD_CREATE_COURSE is a standard shop product action handler that creates a course space for the customer
* and enrols the customer as editing teacher inside.
*
*/
include_once $CFG->dirroot.'/local/shop/datahandling/shophandler.class.php';
include_once $CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php';
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use local_shop\Product;
Use local_shop\Shop;

class shop_handler_std_createcourse extends shop_handler {

    function __construct($label) {
        $this->name = 'std_createcourse'; // for unit test reporting
        parent::__construct($label);
    }

    // Pre pay information always comme from shopping session.
    function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        // Get customersupportcourse designated by handler internal params

        if (!isset($data->actionparams['customersupport'])) {
            $theShop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theShop->defaultcustomersupportcourse;
        }

        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        if (isloggedin()) {
            if ($customer->hasaccount != $USER->id) {
                // do it quick in this case. Actual user could authentify, so it is the legitimate account.
                // We guess if different non null id that the customer is using a new account. This should not really be possible
                $customer->hasaccount = $USER->id;
                $DB->update_record('local_shop_customer', $customer);
            } else {
                $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
                $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
                shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Prepay : Known account {$USER->username} at process entry.");
                return $productionfeedback;
            }
        } else {
            // In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
            // The Customer cannot match with an existing user (this has been checked in customer.controller.php)
            // TODO : If a collision is to be detected, a question should be asked to the customer.

            if (!shop_create_customer_user($data, $customer, $newuser)) {
                shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Prepay Error : User could not be created {$newuser->username}.");
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }

            $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_createcourse', '');
            $a = new StdClass();
            $a->username = $newuser->username;
            $a->password = $customer->password;
            $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_createcourse', $a);
            $productionfeedback->salesadmin = get_string('productiondata_sales', 'shophandlers_std_createcourse', $newuser->username);
        }

        return $productionfeedback;
    }

    function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();

        if (!isset($data->actionparams['coursecategory'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay Error : Missing handler action data (coursecategory)");
            return;
        }

        if (!isset($data->actionparams['template'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay Error : Missing handler action data (template)");
            return;
        }

        if (!isset($data->actionparams['duration'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay : Missing handler action data (template)");
            return;
        }

        $coursetemplatename = $data->actionparams['template'];

        $now = time();
        $secsduration = $data->actionparams['duration'] * DAYSECS;
        $upto = ($secsduration) ? $now + $secsduration : 0;

        $c->category = $data->actionparams['coursecategory'];
        $c->shortname = shop_generate_shortname($data->user);
        $c->fullname = $data->customerdata['fullname'];
        $c->idnumber = $data->customerdata['idnumber'];
        $c->enrollable = 0;
        $c->timecreated = $now;
        $c->startdate = $now;
        $c->lang = '';
        $c->theme = '';
        $c->cost = '';

        $template = $DB->get_record('course', array('shortname' => $coursetemplatename));
        if ($templatepath = shop_delivery_check_available_backup($template->id)) {
            if ($c->id = shop_create_course_from_template($templatepath->path, $c)) {
                $context = context_course::instance($c->id);
            } else {
                $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : COURSE_CREATION', $CFG->dirroot.'/local/shop/datahandling/handlers/lang/');
                $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_createcourse', $data, $CFG->dirroot.'/local/shop/datahandling/handlers/lang/');
                $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_createcourse', $data, $CFG->dirroot.'/local/shop/datahandling/handlers/lang/');
                shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay Error : Course creation failure (DB reason)...");
                return $productionfeedback;
            }
        } else {
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : TEMPLATE_BACKUP', $CFG->dirroot.'/local/shop/datahandling/handlers/lang/');
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_createcourse', $data, $CFG->dirroot.'/local/shop/datahandling/handlers/lang/');
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_createcourse', $data, $CFG->dirroot.'/local/shop/datahandling/handlers/lang/');
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay Error : Template $coursetemplatename has no backup...");
            return $productionfeedback;
        }
        if (!$role = $DB->get_record('role', array('shortname' => 'courseowner'))) {
            $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        }
        $now = time();
        if (!role_assign($role->id, $data->user->id, 0, $context->id, $now, $upto, false, 'manual', time())) {
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_createcourse', 'Code : TEACHER_ROLE_ASSIGN');
            $productionfeedback->private = get_string('productiondata_failure2_private', 'shophandlers_std_createcourse', $c);
            $productionfeedback->salesadmin = get_string('productiondata_failure2_sales', 'shophandlers_std_createcourse', $c);
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay Error : Failed to assign teacher...");
            return $productionfeedback;
        }
        
        // Register product
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem
        $product->currentbillitemid = $data->id; // Data is a billitem
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'course';
        $product->instanceid = $c->id;
        $product->startdate = $now;
        $product->enddate = $upto;
        $product->reference = shop_generate_product_ref($data);
        $product->productiondata = Product::compile_production_data($data->actionparams);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // record a productevent
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        // Add user to customer support on real purchase
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $data->user, $data->transactionid);
        }

        $c->username = $data->user->username;
        $c->fullname = stripslashes($c->fullname);
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_createcourse');
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_createcourse', $c);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_createcourse', $c);
        shop_trace("[{$data->transactionid}] STD_CREATE_COURSE Postpay : Completed in course {$c->shortname}.");

        return $productionfeedback;
    }

    static function get_required_default() {
        return 'coursename|'.get_string('name').'|text;idnumber|'.get_string('idnumber').'|text';
    }

    static function get_actionparams_default() {
        return 'coursecategory={&duration=&customersupport=}';
    }

    function validate_required_data($itemname, $fieldname, $instance = 0, $value, &$errors) {
        global $SESSION, $DB;

        // ensure we have an integer index;
        $instance = 0 + $instance;

        $hasnolocalerrors = true;
        if ($fieldname == 'fullname') {
            if (empty($value)) {
                $errors[$itemname][$fieldname][$instance] = get_string('errorvalueempty', 'shophandlers_std_createcourse');
                $hasnolocalerros = false;
            }
        }

        if ($fieldname == 'idnumber') {
            // first check no course with that idnumber already
            if ($DB->count_records('course', array('idnumber' => $value))) {
                $errors[$itemname][$fieldname][$instance] = get_string('erroralreadyexists', 'shophandlers_std_createcourse');
                $hasnolocalerros = false;
            }
        }

        return $hasnolocalerrors;
    }

    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        parent::unit_test($data, $errors, $warnings, $messages);

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        if (!isset($data->actionparams['coursecategory'])) {
            $errors[$data->code][] = get_string('errornocategory', 'shophandlers_std_createcourse');
        } else {
            if (!$DB->record_exists('course_categories', array('id' => $data->actionparams['coursecategory']))) {
                $errors[$data->code][] = get_string('errorcategorynotexists', 'shophandlers_std_createcourse', $data->actionparams['coursecategory']);
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