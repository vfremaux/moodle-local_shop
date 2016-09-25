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
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * STD_OPEN_LTI_ACCESS is a standard shop product action handler that creatres an LTI Provider
 * wrapper upon an existing course. By buying this hanlded product, you will receive an 
 * LTI provider identity you can provide to your customers to access the ocurse.
 * The handler builds the LTI Provider records. this hanlde only supports "connected"
 * customer situation as the applicable course must preexist.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use local_shop\Product;
Use local_shop\Shop;

class shop_handler_std_openltiaccess extends shop_handler {

    function __construct($label) {
        $this->name = 'std_openltiaccess'; // for unit test reporting
        parent::__construct($label);
    }

    function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    // Pre pay information always comme from shopping session.
    function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        // Get customersupportcourse designated by handler internal params.
        if (!isset($data->actionparams['customersupport'])) {
            $theShop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theShop->defaultcustomersupportcourse;
            if ($data->actionparams['customersupport']) {
                shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Prepay Warning : Customer support defaults to shop settings.");
            } else {
                shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Prepay Warning : No customer support area defined.");
            }
        }

        // If Customer already has account in incoming data we have nothing to do.
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
                shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Prepay : Known account {$USER->username} at process entry.");
                return $productionfeedback;
            }
        } else {
        // In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
        // The Customer might match with an existing user... 
        // TODO : If a collision is to be detected, a question should be asked to the customer.

            if (!shop_create_customer_user($data, $customer, $newuser)) {
                shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Prepay Error : User could not be created {$newuser->username}.");
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }

            $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_openltiaccess');
            $a->username = $newuser->username;
            $a->password = $customer->password;
            $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_openltiaccess', $a);
            $productionfeedback->salesadmin = get_string('productiondata_sales', 'shophandlers_std_openltiaccess', $newuser->username);
        }

        return $productionfeedback;
    }

    function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();

        if (!isset($data->actionparams['coursename']) && !isset($data->actionparams['courseid'])) {
            shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay Error : Missing handler action data (coursename)");
            return;
        }

        if (!isset($data->customerdata['sendgrades'])) {
            $data->customerdata['sendgrades'] = 1;
        }

        if (!isset($data->customerdata['enrolstartdate'])) {
            $data->customerdata['enrolstartdate'] = 0;
        }

        if (!isset($data->customerdata['enrolenddate'])) {
            $data->customerdata['enrolenddate'] = 0;
        }

        if (!isset($data->actionparams['enrolperiod'])) {
            $data->actionparams['enrolperiod'] = 0;
        }

        $now = time();
        $secsduration = $data->actionparams['enrolperiod'];
        $upto = ($secsduration) ? $now + $secsduration : 0 ;

        $config = get_config('local_ltiprovider');

        if ($data->actionparams['coursename']) {
            $course = $DB->get_record('course', array('shortname' => $data->actionparams['coursename']));
        }
        if (!$course) {
            if ($data->actionparams['courseid']) {
                $course = $DB->get_record('course', array('id' => $data->actionparams['courseid']));
            }
        }
        if(!$course) {
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_openltiaccess', 'Code : LTI_SETUP');
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_openltiaccess', $data);
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_openltiaccess', $data);
            shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay Error : LTI Setup failure (DB reason)...");
            return $productionfeedback;
        } else {
            // 
            $ltiaccess = new StdClass();
            $ltiaccess->courseid = $course->id;
            $ltiaccess->contextid = context_course::instance($course->id)->id;
            $ltiaccess->extname = $course->fullname;
            $ltiaccess->disabled = false;
            $ltiaccess->sendgrades = $data->customerdata['sendgrades'];
            $ltiaccess->forcenavigation = true;
            $ltiaccess->croleinst = $config->default_croleinst;
            $ltiaccess->aroleinst = $config->default_aroleinst;
            $ltiaccess->crolelearn = $config->default_crolelearn;
            $ltiaccess->arolelearn = $config->default_aroleinst;
            $ltiaccess->secret = produce_generate_secret(12);
            $ltiaccess->encoding = $config->default_encoding;
            $ltiaccess->institution = $config->default_institution;
            if (!empty($data->bill->customer->organisation)) {
                $ltiaccess->institution = organisation;
            }
            $ltiaccess->lang = ($config->default_lang) ? $config->default_lang : $data->bill->customeruser->lang;
            $ltiaccess->country = ($config->default_country) ? $config->default_country : $data->bill->customeruser->country;
            $ltiaccess->city = $config->default_city;
            $ltiaccess->timezone = $config->default_timezone;
            $ltiaccess->maildisplay = $config->default_maildisplay;
            $ltiaccess->hidepageheader = $config->default_hidepageheader;
            $ltiaccess->hidepagefooter = $config->default_hidepagefooter;
            $ltiaccess->hideleftblocks = $config->default_hideleftblocks;
            $ltiaccess->hiderightblocks = $config->default_hiderightblocks;
            $ltiaccess->hidecustommenu = $config->default_hidecustommenu;

            $ltiaccess->customcss = $config->default_customcss;

            $ltiaccess->enrolstartdate = $data->customerdata['enrolstartdate'];
            $ltiaccess->enrolenddate = $data->customerdata['enrolenddate'];
            $ltiaccess->enrolperiod = $data->actionparams['enrolperiod'];

            if (empty($data->actionparams['maxenrolled'])) {
                if (empty($config->default_maxenrolled)) {
                    $ltiaccess->maxenrolled = 0 + @$config->default_maxenrolled;
                }
            } else {
                $ltiaccess->maxenrolled = $data->actionparams['maxenrolled'];
            }

            $ltiaccess->autogroup = true;

            $ltiaccess->timecreated = $now;
            $ltiaccess->timemodified = $now;
            $ltiaccess->lastsync = 0;

            $ltiaccess->id = $DB->insert_record('local_ltiprovider', $ltiaccess);
        }

        // Register product.
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem
        $product->currentbillitemid = $data->id; // Data is a billitem
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'ltiprovider';
        $product->instanceid = $ltiaccess->id;
        $product->startdate = $now;
        $product->enddate = $upto;
        $product->reference = shop_generate_product_ref($data);
        $product->productiondata = Product::compile_production_data($data->actionparams, $data->customerdata);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        // Add user to customer support on real purchase
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $data->user, $data->transactionid);
        }

        $c = new StdClass;
        $c->username = $data->bill->customeruser->username;
        $c->fullname = stripslashes(fullname($data->bill->customeruser));
        $c->endpoint = $CFG->wwwroot.'/local/ltiprovider/tool.php';
        $c->secret = $ltiaccess->secret;
        $c->shortname = $course->shortname;
        $c->coursename = $course->fullname;
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_openltiaccess', $c);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_openltiaccess', $c);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_openltiaccess', $c);
        shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay : Completed in course {$c->shortname}.");

        return $productionfeedback;
    } 

    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $erors, $warnings, $messages);

        if (!isset($data->actionparams['coursename'])) {
            $errors[$data->code][] = get_string('errornocourse', 'shophandlers_std_openltiaccess');
        }

        if (!isset($data->actionparams['sendgrades'])) {
            $warnings[$data->code][] = get_string('warningdefaultsendgrades', 'shophandlers_std_openltiaccess');
        }

        if (!isset($data->actionparams['maxenrolled'])) {
            $warnings[$data->code][] = get_string('warningdefaultmaxenrolled', 'shophandlers_std_openltiaccess');
        }

        if (!isset($data->actionparams['enrolperiod'])) {
            $warnings[$data->code][] = get_string('warningnoduration', 'shophandlers_std_openltiaccess');
        }
    }
}