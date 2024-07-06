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
 * @subpackage  shophandlers
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * STD_CREATE_CATEGORY is a standard shop product action handler that creates a category for the customer
 * and enrols the customer as course creator (category manager) inside.
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

class shop_handler_std_createcategory extends shop_handler {

    public function __construct($label) {
        $this->name = 'STD_CREATE_CATEGORY'; // For unit test reporting.
        parent::__construct($label);
    }

    public function produce_prepay(&$data, &$errorstatus) {

        // Get customersupportcourse designated by handler internal params.

        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
        }

        $productionfeedback = shop_register_customer($data, $errorstatus);

        return $productionfeedback;
    }

    public function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['parentcategory'])) {
            $message = "[{$data->transactionid}] STD_CREATE_CATEGORY Postpay Error :";
            $message = " Missing action data (parentcategory)";
            shop_trace($message);
            return array();
        }
        $catparent = $data->actionparams['parentcategory'];

        $now = time();
        $secsduration = @$data->actionparams['duration'] * DAYSECS;
        $upto = ($secsduration) ? $now + $secsduration : 0;

        if (empty($data->required['catname'])) {
            $cat->name = generate_catname($data->customeruser);
        } else {
            // Let format the final catname using an administrable string pattern.
            $cat->name = get_string('catnameformatter', 'shophandler_std_createcategory', $data->required['catname']);
        }

        $customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
        $customeruser = $DB->get_record('user', array('id' => $customer->hasaccount));

        if ($catid = shop_fast_make_category($catname, $description, $catparent)) {

            if (!$role = $DB->get_record('role', array('shortname' => 'categoryowner'))) {
                // Non standard specific role when selling parts of managmeent delegation.
                $role = $DB->get_record('role', array('shortname' => 'coursecreator')); // Fall back for standard implementations.
            }

            if (!role_assign($role->id, $customeruser->id, 0, $context->id, $now, $upto, false, 'manual', time())) {
                $fb = get_string('productiondata_failure_public', 'shophandlers_std_createcategory', 'Code : COURSECREATOR ROLE ASSIGN');
                $productionfeedback->public = $fb;
                $fb = get_string('productiondata_failure_private', 'shophandlers_std_createcategory', $data);
                $productionfeedback->private = $fb;
                $fb = get_string('productiondata_failure_sales', 'shophandlers_std_createcategory', $data);
                $productionfeedback->salesadmin = $fb;
                shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay : Failed to assign course creator...");
                return $productionfeedback;
            }

        } else {
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_createcategory', 'Code : CATEGORY CREATION');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_createcategory', $data);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_createcategory', $data);
            $productionfeedback->salesadmin = $fb;
            shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay Error : Failed to create catgory.");
            return $productionfeedback;
        }

        // Register product.
        $product = new StdClass();
        $product->catalogitemid = $data->catalogitem->id;
        $product->initialbillitemid = $data->id; // Data is a billitem.
        $product->currentbillitemid = $data->id; // Data is a billitem.
        $product->customerid = $data->bill->customerid;
        $product->contexttype = 'category';
        $product->instanceid = $catid;
        $product->startdate = $starttime;
        $product->enddate = $endtime;
        $product->extradata = '';
        $product->reference = shop_generate_product_ref($data);
        $extra = array('handler' => 'std_createcategory');
        $product->productiondata = Product::compile_production_data($data->actionparams, $extra);
        $product->id = $DB->insert_record('local_shop_product', $product);

        // Record an event.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = $data->id;
        $productevent->datecreated = $now = time();
        $productevent->save();

        // Add user to customer support.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        $e = new StdClass;
        $e->txid = $data->transactionid;
        $e->catid = $catid;
        $e->catname = $cat->name;

        $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_createcategory', $e);
        $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_createcategory', $e);
        $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_createcategory', $e);
        shop_trace("[{$data->transactionid}] STD_CREATE_CATEGORY Postpay : Complete.");

        return $productionfeedback;
    }

    public static function get_required_default() {
        return '';
    }

    public static function get_actionparams_default() {
        return 'parentcategory={&duration=&customersupport=}';
    }

    /**
     * Dismounts all effects of the handler production when a product is deleted.
     * The contexttype will denote the type of Moodle object that was created. some
     * hanlders may deal with several contexttypes if they have a complex production
     * operation. the instanceid is moslty a moodle table id that points the concerned instance 
     * within the context type scope.
     *
     * In createcategory plugin, deletes all courses and subcategories, delete
     * root category assigned to the product. Other role assignations will remain unchanged.
     *
     * @param string $contexttype type of context to dismount
     * @param integer/string $instanceid identifier of the instance
     */
    public function delete(&$product) {
        global $DB;

        if ($cat = $DB->get_record('course_categories', array('id' => $product->instanceid))) {
            $this->delete_rec($cat);
        }

        // Remove local courses.
        $this->delete_cat_courses($product->instanceid);
    }

    protected function delete_rec(&$cat) {

        $subcats = $DB->get_records('course_categories', array('parent' => $cat->id));
        if ($subcats) {
            foreach($subcats as $subcat) {
                $this->delete_rec($subcat);
            }
        }
        $this->delete_cat_courses($catid);
    }

    protected function delete_cat_courses($catid) {
        if ($courses = $DB->get_records('course', array('category' => $catid))) {
            foreach ($courses as $c) {
                delete_course($c);
            }
        }
    }

    /**
     * Attempts to disable the product effect while preserving the data so the product
     * can be restored in active state without data loss. This is done by :
     *
     * - Hiding the root category.
     * - Removing roles to the owner that would allow him to sho it again.
     *
     * @param string $contexttype
     * @param integer/string $instanceid
     */
    public function soft_delete(&$product) {
        global $DB;

        // Set root cat non visible.
        if ($cat = $DB->get_record('course_categories', array('id' => $product->instanceid))) {
            $cat->visible = 0;
            $DB->update_record('course_categories', $cat);
        }

        // Remove power role to customer.
        $catcontext = context_coursecat::instance($product->instanceid);

        $role = $DB->get_role('role', array('shortname' => 'categoryowner'));
        $DB->delete_record('role_assignments', array('contextid' => $catcontext->id, 'roleid' => $role->id));
    }

    /**
     * Restores what soft_delete switches off in order to restore use of the product
     * @param string $contexttype
     * @param integer/string $instanceid
     */
    public function soft_restore(&$product) {
        global $DB;

        // Set root cat visible.
        if ($cat = $DB->get_record('course_categories', array('id' => $product->instanceid))) {
            $cat->visible = 1;
            $DB->update_record('course_categories', $cat);
        }

        // Restore power role to customer.
        $catcontext = context_coursecat::instance($product->instanceid);
        $role = $DB->get_role('role', array('shortname' => 'categoryowner'));
        $customer = new Customer($product->customerid);
        role_assign($role->id, $customer->hasaccount);
    }

    public function unit_test($data, &$errors, &$warnings, &$messages) {

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);
        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['parentcategory'])) {
            $errors[$data->code][] = get_string('errormissingparentcategory', 'shophandlers_std_createcategory');
        }
    }
}