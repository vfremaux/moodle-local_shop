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
 * @package  shophandlers_std_unlockpdcertificate
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\Customer;
use local_shop\CatalogItem;

/**
 * STD_UNLICKPDCERTIFICATE is a standard shop product action handler that unlocks a loced issue in a pdcertificate
 * activitymodule.
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
class shop_handler_std_unlockpdcertificate extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_unlockpdcertificate'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * Who can use this handler ? 
     */
    public function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    /**
     * Validates data required frm the user when ordering.
     * @param StdClass $data a bill item (real or simulated).
     * @param bool $errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    function produce_prepay($data, & $errorstatus) {

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

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public function produce_postpay(&$data) {
        global $DB, $USER;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['certificate']) && !isset($data->actionparams['certificateid'])) {
            // @todo : handle better.
            return;
        }

        if (isset($data->actionparams['certificateid'])) {
            $certificate = $DB->get_record('pdcertificate', ['id' => $data->actionparams['certificateid']]);
            $cm = get_coursemodule_from_instance('pdcertificate', $certificate->id);
        } else if (isset($data->actionparams['certificate'])) {
            $module = $DB->get_record('modules', ['name' => 'pdcertificate']);
            $cm = $DB->get_record('course_modules', ['idnumber' => $data->actionparams['certificate'], 'moduleid' => $module->id]);
            $certificate = $DB->get_record('pdcertificate', ['id' => $cm->instanceid]);
        } else {
            shop_trace("[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay Error : No target certificate");
            return;
        }

        $issue = $DB->get_record('pdcertificate_issues', ['userid' => $USER->id, 'pdcertificateid' => $certificate->id]);

        if (!$issue) {
            // TODO : Try generating for the user if not yet generated.

            // Cannot generate.
            if (!$issue) {
                $pgn = 'shophandlers_std_unlockpdcertificate';
                $mess = get_string('productiondata_failure_public', $pgn, 'Code : CERTIFICATE_CREATION');
                $productionfeedback->public = $mess;
                $mess = get_string('productiondata_failure_private', 'shophandlers_std_unlockpdcertificate', $data);
                $productionfeedback->private = $mess;
                $mess = get_string('productiondata_failure_sales', 'shophandlers_std_unlockpdcertificate', $data);
                $productionfeedback->salesadmin = $mess;
                $mess = "[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay Error : ";
                $mess .= "Certificate unlocking failure (DB reason)...";
                shop_trace($mess);
                return $productionfeedback;
            }
        }

        $course = $DB->get_record('course', ['id', $certificate->course]);

        $issue->locked = 0;
        $DB->update_record('pdcertificate_issues', $issue);

        // @todo : Notify the user with a copy (when delivery by mail is enabled ?).

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
        $product->extradata = '';
        $product->reference = shop_generate_product_ref($data);
        $extra = ['handler' => 'std_unlockpdcertificate'];
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

        $e = new StdClass;
        $e->username = $data->customeruser->username;
        $e->fullname = $course->fullname;
        $e->name = $certificate->name;
        $e->cmid = $cm->id;
        $e->endpoint = new moodle_url('/mod/pdcertificate/view.php', ['id' => $cm->id]);
        $e->txid = $data->transactionid;
        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_unlockpdcertificate', $e);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_unlockpdcertificate', $e);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_unlockpdcertificate', $e);
        shop_trace("[{$data->transactionid}] STD_UNLOCK_PDCERTIFICATE Postpay : Certificate {$e->name} unlocked for {$e->username}.");

        return $productionfeedback;
    }

    /**
     * this product should not be available if the current user (purchaser) is
     * already certified, i.e. has a delivered certificate for the associated certificate.
     * this might be better checked by testing a shop product existance
     ù @param CatalogItem $catalogitem
     */
    public function is_available(CatalogItem $catalogitem) {
        global $DB, $USER;

        if (!empty($catalogitem->handlerparams['certificate'])) {
            $cm = $DB->get_record('course_modules', ['idnumber' => $catalogitem->handlerparams['certificate']]);
        } else if (!empty($catalogitem->handlerparams['certificateid'])) {
            $pdcertificate = $DB->get_record('pdcertificate', ['id' => $catalogitem->handlerparams['certificateid']]);
            $cm = get_course_module_from_instance('pdcertificate', $pdcertificate->id);
        }

        // Get all customerids for this moodle user.
        if ($meascustomers = Customer::get_instances(['hasaccount' => $USER->id])) {
            foreach ($meascustomers as $customer) {
                if (Product::count(['customerid' => $customer->id, 'contexttype' => 'course_module', 'instanceid' => $cm->id])) {
                    // If we hav one product owned for this course module, we already have the certificate. No need to purchase it agian.
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Disables the product effect in a way it can be restored
     * @param Product $product
     */
    public function soft_delete(Product $product) {
        // @TODO : Relock issue instance;
    }

    /** 
     * Restores the effect of the product instance
     * @param Product $product
     */
    public function soft_restore(Product $product) {
        // @TODO : Unlock issue instance back;
    }

    // No update.

    /**
     * Tests a product handler
     * @param object $data
     * @param arrayref &$errors
     * @param arrayref &$warnings
     * @param arrayref &$messages
     */
    function unit_test($data, &$errors, &$warnings, &$messages) {
        global $DB;

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['certificate']) && !isset($data->actionparams['certificateid'])) {
            $errors[$data->code][] = get_string('errornoinstance', 'shophandlers_std_unlockpdcertificate');
        }

        if (isset($data->actionparams['certificate'])) {
            // Check idnumber and course module/instance existance.
        }

        if (isset($data->actionparams['certificateid'])) {
            if (!$DB->get_record('pdcertificate', ['id' => $data->actionparams['certificateid']])) {
                $errors[$data->code][] = get_string('errorbadinstance', 'shophandlers_std_unlockpdcertificate');
            }
        }
    }
}
