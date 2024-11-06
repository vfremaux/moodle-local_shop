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
 * @package  shophandler_std_openltiaccess
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Product;
use local_shop\ProductEvent;
use local_shop\Shop;

/**
 * STD_OPEN_LTI_ACCESS is a standard shop product action handler that creatres an LTI Provider
 * wrapper upon an existing course. By buying this hanlded product, you will receive an
 * LTI provider identity you can provide to your customers to access the ocurse.
 * The handler builds the LTI Provider records. this hanlde only supports "connected"
 * customer situation as the applicable course must preexist.
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
class shop_handler_std_openltiaccess extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_openltiaccess'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * Who can use this handler
     */
    public function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    function produce_prepay($data, &$errorstatus) {

        // Get customersupportcourse designated by handler internal params.

        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
            if ($data->actionparams['customersupport']) {
                shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Prepay Warning : Customer support defaults to shop settings.");
            } else {
                shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Prepay Warning : No customer support area defined.");
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
        global $CFG, $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['coursename']) && !isset($data->actionparams['courseid'])) {
            shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay Error : Missing handler action data (coursename)");
            return;
        }

        $required = $data->required;
        if (!isset($required['sendgrades'])) {
            $required['sendgrades'] = 1;
        }

        if (!isset($required['enrolstartdate'])) {
            $required['enrolstartdate'] = 0;
        }

        if (!isset($required['enrolenddate'])) {
            $required['enrolenddate'] = 0;
        }

        if (!isset($data->actionparams['enrolperiod'])) {
            $data->actionparams['enrolperiod'] = 0;
        }

        $now = time();
        $secsduration = $data->actionparams['enrolperiod'];
        $upto = ($secsduration) ? $now + $secsduration : 0;

        $config = get_config('local_ltiprovider');

        if ($data->actionparams['coursename']) {
            $course = $DB->get_record('course', ['shortname' => $data->actionparams['coursename']]);
        }

        if (!$course) {
            if ($data->actionparams['courseid']) {
                $course = $DB->get_record('course', ['id' => $data->actionparams['courseid']]);
            }
        }

        if (!$customerid = $data->customer->id) {
            $customerid = $data->get_customerid();
        }

        $customer = new Customer($customerid);
        $customeruser = $DB->get_record('user', ['id' => $customer->hasaccount]);

        if(!$course) {
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_openltiaccess', 'Code : LTI_SETUP');
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_openltiaccess', $data);
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_openltiaccess', $data);
            shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay Error : LTI Setup failure (DB reason)...");
            return $productionfeedback;
        } else {
            $ltiaccess = new StdClass();
            $ltiaccess->courseid = $course->id;
            $ltiaccess->contextid = context_course::instance($course->id)->id;
            $ltiaccess->extname = $course->fullname;
            $ltiaccess->disabled = false;
            $ltiaccess->sendgrades = $required['sendgrades'];
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
            $ltiaccess->lang = ($config->default_lang) ? $config->default_lang : $customeruser->lang;
            $ltiaccess->country = ($config->default_country) ? $config->default_country : $customeruser->country;
            $ltiaccess->city = $config->default_city;
            $ltiaccess->timezone = $config->default_timezone;
            $ltiaccess->maildisplay = 0 + @$config->default_maildisplay;
            $ltiaccess->hidepageheader = 0 + @$config->default_hidepageheader;
            $ltiaccess->hidepagefooter = 0 + @$config->default_hidepagefooter;
            $ltiaccess->hideleftblocks = 0 + @$config->default_hideleftblocks;
            $ltiaccess->hiderightblocks = 0 + @$config->default_hiderightblocks;
            $ltiaccess->hidecustommenu = 0 + @$config->default_hidecustommenu;

            $ltiaccess->customcss = $config->default_customcss;

            $ltiaccess->enrolstartdate = $required['enrolstartdate'];
            $ltiaccess->enrolenddate = $required['enrolenddate'];
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
        $product->extradata = '';
        $product->reference = shop_generate_product_ref($data);
        $extra = ['handler' => 'std_openltiaccess'];
        $product->productiondata = Product::compile_production_data($data->actionparams, $required, $extra);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->save();

        // Add user to customer support on real purchase.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        $e = new StdClass;
        $e->txid = $data->transactionid;
        $e->username = $customeruser->username;
        $e->fullname = stripslashes(fullname($customeruser));
        $e->endpoint = $CFG->wwwroot.'/local/ltiprovider/tool.php?id='.$ltiaccess->id;
        $e->secret = $ltiaccess->secret;
        $e->shortname = $course->shortname;
        $e->coursename = $course->fullname;
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_openltiaccess', $e);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_openltiaccess', $e);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_openltiaccess', $e);
        shop_trace("[{$data->transactionid}] STD_OPEN_LTI_ACCESS Postpay : Completed in course {$e->shortname}.");

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

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

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

    /**
     * this method renders user formated information about production information (contextually to handler)
     * for products generated with this handler
     * @param int $pid the product instance id
     * @param array $pinfo production related info stored at purchase time
     */
    public function display_product_infos($pid, $pinfo) {
        global $DB, $CFG;

        $product = $DB->get_record('local_shop_product', ['id' => $pid]);
        $ltiaccess = $DB->get_record('local_ltiprovider', ['id' => $product->instanceid]);
        $course = $DB->get_record('course', ['id' => $ltiaccess->courseid]);

        $str = '';

        $config = get_config('local_ltiprovider');

        $endpointurl = $CFG->wwwroot.'/local/ltiprovider/tool.php?id='.$product->instanceid;

        $str .= '<div><div class="cs-product-key">'.get_string('coursename', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value">'.$pinfo->coursename.'<br/>'.$course->summary.'</div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('extname', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value">'.$ltiaccess->extname.'</div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('startdate', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value">'.userdate($product->startdate).'</div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('endpoint', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value">'.$endpointurl.'</div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('globalsharedsecret', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value"><code class="code">'.$config->globalsharedsecret.'</code></div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('secret', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value"><code class="code">'.$ltiaccess->secret.'</code></div></div>';

        if ($ltiaccess->maxenrolled) {
            $maxenrolledstr = get_string('maxenrolled', 'shophandlers_std_openltiaccess', $ltiaccess->maxenrolled);
        } else {
            $maxenrolledstr = get_string('unlimited', 'shophandlers_std_openltiaccess');
        }

        $str .= '<div><div class="cs-product-key">'.get_string('capacity', 'shophandlers_std_openltiaccess').'</div>';
        $str .= '<div class="cs-product-value">'.$maxenrolledstr.'</div></div>';

        return $str;
    }
}
