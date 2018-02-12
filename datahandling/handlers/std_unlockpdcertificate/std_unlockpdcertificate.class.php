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
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/*
 * STD_OPEN_LTI_ACCESS is a standard shop product action handler that creatres an LTI Provider
 * wrapper upon an existing course. By buying this hanlded product, you will receive an
 * LTI provider identity you can provide to your customers to access the ocurse.
 * The handler builds the LTI Provider records. this hanlde only supports "connected"
 * customer situation as the applicable course must preexist.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\Customer;

class shop_handler_std_unlockpdcertificate extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_unlockpdcertificate'; // For unit test reporting.
        parent::__construct($label);
    }

    public function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    // Pre pay information always comme from shopping session.
    function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        // Get customersupportcourse designated by handler internal params and prepare customer support action.
        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
        }

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        return $productionfeedback;
    }

    public function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['certificate']) && !isset($data->actionparams['certificateid'])) {
            // TODO : handle better.
            return;
        }

        if (isset($data->actionparams['certificateid'])) {
            $certificate = $DB->get_record('pdcertificate', array('id' => $data->actionparams['certificateid']));
        } else if (isset($data->actionparams['certificate'])) {
            $module = $DB->get_record('modules', array('name' => 'pdcertificate'));
            $cm = $DB->get_record('course_modules', array('idnumber' => $data->actionparams['certificate'], 'moduleid' => $module->id));
            $certificate = $DB->get_record('pdcertificate', array('id' => $cm->instanceid));
        } else {
            shop_trace("[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay Error : No target certificate");
            return;
        }

        $issue = $DB->get_record('pdcertificate_issues', array('userid' => $USER->id, 'pdcertificateid' => $certificate->id));

        if (!$issue) {
            // TODO : Try generating for the user if not yet generated.

            // Cannot generate.
            if (!$issue) {
                $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_unlockpdcertificate', 'Code : COURSE_CREATION');
                $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_unlockpdcertificate', $data);
                $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_unlockpdcertificate', $data);
                shop_trace("[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay Error : Certificate unlocking failure (DB reason)...");
                return $productionfeedback;
            }
        }

        $issue->locked = 0;
        $DB->update_record('pdcertificate_issues', $issue);

        // TODO : Notify the user with a copy (when delivery by mail is enabled ?).

        // Register product.
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'course_module';
        $product->instanceid = $cm->id;
        $product->startdate = $now;
        $product->enddate = 0;
        $product->reference = shop_generate_product_ref($data);
        $extra = array('handler' => 'std_unlockpdcertificate');
        $product->productiondata = Product::compile_production_data($data->actionparams, $data->customerdata, $extra);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record a productevent.
        $productevent = new StdClass();
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);

        // Add user to customer support on real purchase.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $data->customeruser, $data->transactionid);
        }

        $c->username = $data->customeruser->username;
        $c->name = $certificate->name;
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_unlockpdcertificate');
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_unlockpdcertificate', $c);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_unlockpdcertificate', $c);
        shop_trace("[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay : Certificate {$c->name} unlocked for {$c->username}.");

        return $productionfeedback;
    }

    /**
     * this product should not be available if the current user (purchaser) is
     * already certified, i.e. has a delivered certificate for the associated certificate.
     * this might be better checked by testing a shop product existance
     */
    public function is_available(&$catalogitem) {
        global $USER, $DB;

        if (!empty($catalogitem->handlerparams['certificate'])) {
            $cm = $DB->get_record('course_modules', array('idnumber' => $catalogitem->handlerparams['certificate']));
        } else if (!empty($catalogitem->handlerparams['certificateid'])) {
            $pdcertificate = $DB->get_record('pdcertificate', array('id' => $catalogitem->handlerparams['certificateid']));
            $cm = get_course_module_from_instance('pdcertificate', $pdcertificate->id);
        }

        // Get all customerids for this moodle user.
        if ($meascustomers = Customer::get_instances(array('hasaccount' => $USER->id))) {
            foreach ($meascustomers as $customer) {
                if (Product::count(array('customerid' => $customer->id, 'contexttype' => 'course_module', 'instanceid' => $cm->id))) {
                    // If we hav one product owned for this course module, we already have the certificate. No need to purchase it agian.
                    return false;
                }
            }
        }
        return true;
    }

    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $erors, $warnings, $messages);

        if (!isset($data->actionparams['certificate']) && !isset($data->actionparams['certificateid'])) {
            $errors[$data->code][] = get_string('errornoinstance', 'shophandlers_std_unlockpdcertificate');
        }

        if (isset($data->actionparams['certificate'])) {
            // Check idnumber and course module/instance existance.
        }

        if (isset($data->actionparams['certificateid'])) {
            if (!$certificate = $DB->get_record('pdcertificate', array('id' => $data->actionparams['certificateid']))) {
                $errors[$data->code][] = get_string('errorbadinstance', 'shophandlers_std_unlockpdcertificate');
            }
        }
    }
}