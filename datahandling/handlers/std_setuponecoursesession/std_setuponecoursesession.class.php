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
 * @package   shophandlers_std_setuponecoursesession
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

use local_shop\Product;
use local_shop\ProductEvent;
use local_shop\Shop;
use local_shop\Customer;
use local_shop\CatalogItem;

/**
 * STD_SETUP_ONE_COURSE_SESSION is a standard shop product action handler that allows the shop operator to
 * prepare and setup a training session for other stakeholders
 * actiondata is defined as an action customized information for a specific product in the
 * product definition, where one standard handler is choosen.
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
class shop_handler_std_setuponecoursesession extends shop_handler {

    /** @var array roles required to declare when ordering */
    protected $requiredroles;

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_setuponecoursesession'; // For unit test reporting.
        parent::__construct($label);
        $this->requiredroles = ['student', 'teacher', 'supervisor', 'owner'];
    }

    /**
     * What is happening on order time, before it has been actually paied out
     * @param objectref &$data a bill item (real or simulated).
     * @param boolref &$errorstatus an error status to report to caller.
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     */
    public function produce_prepay(&$data, &$errorstatus) {

        // Get customersupportcourse designated by handler internal params.

        if (!isset($data->actionparams['customersupport'])) {
            $theshop = new Shop($data->shopid);
            $data->actionparams['customersupport'] = 0 + @$theshop->defaultcustomersupportcourse;
        }

        // Check customer state and create account if necessary.

        $productionfeedback = shop_register_customer($data, $errorstatus);

        return $productionfeedback;
    }

    /**
     * What is happening after it has been actually paied out, interactively
     * or as result of a delayed sales administration action.
     * @param objectref &$data a bill item (real or simulated).
     * @return an array of three textual feedbacks, for direct display to customer,
     * summary messaging to the customer, and sales admin backtracking.
     *
     * Scenario :
     * One course, a list of accounts to enrol or create and enrol.
     * List of accounts comme from production data, previously from $SESSION->shoppingcart->$roles
     */
    public function produce_postpay(&$data) {
        global $DB;

        $config = get_config('local_shop');

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['coursename'])) {
            throw new moodle_exception(get_string('errormissingactiondata', 'local_shop', $this->get_name()));
        }

        // Get course designated by handler internal params.
        $coursename = $data->actionparams['coursename'];

        if (!$course = $DB->get_record('course', ['shortname' => $coursename])) {
            $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Internal Failure :";
            $message .= " Target Course Error [{$coursename}].";
            shop_trace($message);
            throw new moodle_exception(get_string('errorbadtarget', 'shophandlers_std_setuponecoursesession'));
        }

        if (!isset($data->actionparams['supervisor'])) {
            $data->actionparams['supervisor'] = 'teacher';
        }

        if (!$supervisorrole = $DB->get_record('role', ['shortname' => $data->actionparams['supervisor']])) {
            $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Internal Failure :";
            $message .= " Supervisor Role Do Not Exist [{$data->actionparams['supervisor']}].";
            shop_trace($message);
            throw new moodle_exception(get_string('errorsupervisorrole', 'shophandlers_std_setuponecoursesession'));
        }

        // Compute start and end time.
        $starttime = shop_compute_enrol_time($data, 'starttime', $course);
        $endtime = shop_compute_enrol_time($data, 'endtime', $course);

        // Get manual enrol plugin to that course for user enrolments.

        $params = ['enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED];
        if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
            $enrol = reset($enrols);
            $enrolplugin = enrol_get_plugin('manual'); // The enrol object instance.
        }

        if (empty($enrol)) {
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Failure : Not enrollable instance");
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_setuponecoursesession', 'Code : ENROL MISSING');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_setuponecoursesession', $course->id);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_setuponecoursesession', $course->id);
            $productionfeedback->salesadmin = $fb;
            return $productionfeedback;
        }

        if (!isset($data->actionparams['autoenrolsupervisor']) && !empty($data->customer->hasaccount)) {
            $role = $DB->get_record('role', ['shortname' => $data->actionparams['supervisor']]);
            $enrolplugin->enrol_user($enrol, $data->customer->hasaccount, $role->id, $starttime, $endtime, ENROL_USER_ACTIVE);
        }

        $now = time();

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

        if (!empty($data->productiondata->users)) {
            foreach ($data->productiondata->users as $roleshort => $participants) {
                foreach ($participants as $p) {
                    if (!$user = $DB->get_record('user', ['email' => $p->email])) {
                        $courseusers[$roleshort][] = shop_create_moodle_user($data, $p, $supervisorrole);
                        $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay :";
                        $message .= " Creating user [{$p->username}].";
                        shop_trace($message);
                    } else {
                        $courseusers[$roleshort][] = $user;
                        $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay :";
                        $message .= " Registering existing user [{$user->username}].";
                        shop_trace($message);
                    }
                }
            }

            if (!empty($courseusers)) {
                try {
                    foreach ($courseusers as $roleshort => $users) {
                        if ($roleshort == '_supervisor') {
                            $role = $supervisorrole;
                        } else {
                            $role = $DB->get_record('role', ['shortname' => $roleshort]);
                        }

                        if (empty($role)) {
                            // Default moodle student role.
                            $role = $DB->get_record('role', ['shortname' => 'student']);
                        }

                        shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Enrolling $roleshort.");

                        foreach ($users as $u) {
                            $enrolplugin->enrol_user($enrol, $u->id, $role->id, $starttime, $endtime, ENROL_USER_ACTIVE);
                            $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay :";
                            $message .= " $u->lastname $u->firstname ($u->username) enrolled.";
                            shop_trace($message);

                            $ueid = $DB->get_field('user_enrolments', 'id', ['userid' => $u->id, 'enrolid' => $enrol->id]);
                            // Register a product (userenrol instance) for each.
                            $product = new StdClass();
                            $product->catalogitemid = $data->catalogitem->id;
                            $product->initialbillitemid = $data->id; // Data is a billitem.
                            $product->currentbillitemid = $data->id; // Data is a billitem.
                            $product->customerid = $data->bill->customerid;
                            $product->contexttype = 'userenrol';
                            $product->instanceid = $ueid;
                            $product->startdate = $starttime;
                            $product->enddate = $endtime;
                            $product->extradata = '';
                            $product->reference = shop_generate_product_ref($data);
                            $product->test = $config->test;
                            $itemproductiondata = [];
                            $itemproductiondata['courseid'] = $course->id;
                            $itemproductiondata['handler'] = 'std_setuponecoursesession';
                            $itemproductiondata['coursename'] = $coursename;
                            $itemproductiondata['userid'] = $u->id;
                            $itemproductiondata['starttime'] = $starttime;
                            $itemproductiondata['endtime'] = $endtime;
                            $itemproductiondata['supervisor'] = $data->actionparams['supervisor'];
                            // This data is the data required to produce again this product.
                            $product->productiondata = Product::compile_production_data($itemproductiondata);
                            unset($itemproductiondata); // Clean some mem.
                            $product->id = $DB->insert_record('local_shop_product', $product);

                            // Should we record a productevent.
                            $productevent = new ProductEvent(null);
                            $productevent->productid = $product->id;
                            $productevent->billitemid = $data->id;
                            $productevent->datecreated = $now = time();
                            $productevent->save();

                            // GC a bit intermediate stuctures.
                            unset($product);
                            unset($productevent);
                        }
                    }
                } catch (Exception $exc) {
                    $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay Failure :";
                    $message .= " enrolled failed with exception ({$exc->getMessage()}...";
                    shop_trace($message);
                    $fb = get_string('productiondata_failure_public', 'shophandlers_std_setuponecoursesession', 'Code : ROLE ASSIGN');
                    $productionfeedback->public = $fb;
                    $fb = get_string('productiondata_failure_private', 'shophandlers_std_setuponecoursesession', $course->id);
                    $productionfeedback->private = $fb;
                    $fb = get_string('productiondata_failure_sales', 'shophandlers_std_setuponecoursesession', $course->id);
                    $productionfeedback->salesadmin = $fb;
                    return $productionfeedback;
                }
            }
        }

        // Make a group if needed for the customer.
        if (!$group = $DB->get_record('groups', ['courseid' => $course->id, 'name' => 'customer_'.$customeruser->username])) {
            $group = new StdClass();
            $group->courseid = $course->id;
            $group->idnumber = $data->transactionid;
            $group->name = 'customer_'.$customeruser->username;
            $group->description = get_string('providedbymoodleshop', 'local_shop');
            $group->descriptionformat = 1;
            $group->enrolmentkey = 0;
            $group->timecreated = $now;
            $group->timemodified = $now;
            $group->id = $DB->insert_record('groups', $group);
            $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay :";
            $message .= " Creating group ['customer_{$customeruser->username}].";
            shop_trace($message);
        }

        // Add all created users to group.
        if (!empty($courseusers)) {
            foreach ($courseusers as $roleshort => $users) {
                foreach ($users as $u) {
                    if (!$DB->record_exists('groups_members', ['groupid' => $group->id, 'userid' => $u->id])) {
                        $groupmember = new StdClass();
                        $groupmember->groupid = $group->id;
                        $groupmember->userid = $u->id;
                        $groupmember->timeadded = time();
                        $DB->insert_record('groups_members', $groupmember);
                        $message = "[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay :";
                        $message .= " Binding ({$u->username} in group ['customer_{$customeruser->username}].";
                        shop_trace($message);
                    }
                }
            }
        }

        // Enrol customer in course.
        $enrolplugin->enrol_user($enrol, $customeruser->id, $supervisorrole->id, $starttime, $endtime, ENROL_USER_ACTIVE);

        // Add customer to group.
        if (!$DB->record_exists('groups_members', ['groupid' => $group->id, 'userid' => $customeruser->id])) {
            $groupmember = new StdClass();
            $groupmember->groupid = $group->id;
            $groupmember->userid = $customeruser->id;
            $groupmember->timeadded = time();
            $DB->insert_record('groups_members', $groupmember);
        }

        // Enrol customer in support course if needed and possible. silently fail if not possible.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        // Finished.
        shop_trace("[{$data->transactionid}] STD_SETUP_ONE_COURSE_SESSION Postpay : All complete in $coursename.");

        $e = new StdClass;
        $e->txid = $data->transactionid;
        $e->courseid = $course->id;

        $fb = get_string('productiondata_post_public', 'shophandlers_std_setuponecoursesession', $e);
        $productionfeedback->public = $fb;
        $fb = get_string('productiondata_post_private', 'shophandlers_std_setuponecoursesession', $e);
        $productionfeedback->private = $fb;
        $fb = get_string('productiondata_post_sales', 'shophandlers_std_setuponecoursesession', $e);
        $productionfeedback->salesadmin = $fb;

        return $productionfeedback;
    }

    /*
     * Gets a thumbnail from course overview files as thumb.
     * @param CatalogItem $catalogitem
     */
    public function get_alternative_thumbnail_url(CatalogItem $catalogitem) {
        global $DB, $CFG;

        $shouldexist = false;
        $course = null;
        if (!empty($catalogitem->handlerparams['coursename'])) {
            $params = ['shortname' => $catalogitem->handlerparams['coursename']];
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        } else if (!empty($catalogitem->handlerparams['courseidnumber'])) {
            $params = ['idnumber' => $catalogitem->handlerparams['courseidnumber']];
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        } else if (!empty($catalogitem->handlerparams['courseid'])) {
            $params = ['id' => $catalogitem->handlerparams['courseid']];
            $course = $DB->get_record('course', $params);
            $shouldexist = true;
        }

        if (!$course) {
            global $OUTPUT;
            $context = context_system::instance();
            if ($shouldexist && has_capability('local/shop:salesadmin', $context)) {
                echo $OUTPUT->notification(get_string('potentialhandlererror', 'local_shop', $catalogitem->code), 'error');
            }
            return null;
        }

        // Thumb or viewable image.
        // Take first available image NOT TOO LARGE (800px)
        $courseinlist = \local_shop\compat::get_course_list($course);
        foreach ($courseinlist->get_course_overviewfiles() as $file) {
            if ($isimage = $file->is_valid_image()) {
                $imageinfo = $file->get_imageinfo();
                if ($imageinfo['width'] < 800) {
                    $path = '/'. $file->get_contextid(). '/'. $file->get_component().'/';
                    $path .= $file->get_filearea().$file->get_filepath().$file->get_filename();
                    $return = ''.file_encode_url("$CFG->wwwroot/pluginfile.php", $path, !$isimage);
                    return $return;
                }
            }
        }

        return null;
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
     * @param Product $product
     */
    public function delete(Product $product) {
        global $DB;

        if ($product->contexttype == 'userenrol') {
            if ($ue = $DB->get_record('user_enrolments', ['id' => $product->instanceid])) {
                $enrol = $DB->get_record('enrol', ['id' => $ue->enrolid]);
                $enrolplugin = enrol_get_plugin($enrol->enrol);
                shop_trace('[] Deleting user enrolment on {$ue->enrolid} for user {$ue->userid}');
                $enrolplugin->unenrol_user($enrol, $ue->userid);
            }
        }
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
            $errors[$data->code][] = get_string('errornotarget', 'shophandlers_std_setuponecoursesession');
        } else {
            if (!$course = $DB->get_record('course', ['shortname' => $data->actionparams['coursename']])) {
                $cn = $data->actionparams['coursename'];
                $err = get_string('errorcoursenotexists', 'shophandlers_std_setuponecoursesession', $cn);
                $errors[$data->code][] = $err;
            }

            // Check enrollability.
            $params = ['enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED];
            if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
                $enrol = reset($enrols);
            }

            if (empty($enrol)) {
                $cn = $data->actionparams['coursename'];
                $err = get_string('errorcoursenotenrollable', 'shophandlers_std_setuponecoursesession', $cn);
                $errors[$data->code][] = $err;
            }
        }

        if (!isset($data->actionparams['supervisor'])) {
            $warnings[$data->code][] = get_string('warningsupervisordefaultstoteacher', 'shophandlers_std_setuponecoursesession');
            $data->actionparams['supervisor'] = 'teacher';
        }

        if (!$DB->get_record('role', ['shortname' => $data->actionparams['supervisor']])) {
            $err = get_string('errorsupervisorrole', 'shophandlers_std_setuponecoursesession', $data->actionparams['supervisor']);
            $errors[$data->code][] = $err;
        }
    }

    /**
     * this method renders part of the product post purchase management GUI
     * for products generated with this handler
     * @param int $pid the product instance id
     * @param array $params production related info stored at purchase time
     *
     * @todo : Generalize to all logstores
     */
    public function display_product_actions($pid, $params) {
        global $DB;

        // Here we can unassign a product if it has not been used.
        // Check this in logs.
        $params = (array)$params; // Just to be sure.

        $sqlparams = [$params['courseid'], $params['userid'], $params['starttime']];

        $select = " courseid = ?  AND userid = ? AND timecreated > ? ";

        if ($params['endtime']) {
            $sqlparams[] = $params['endtime'];
            $select .= " AND timecreated < ? ";
        }

        $hasentered = $DB->record_exists_select('logstore_standard_log', $select, $sqlparams);

        if (!$hasentered) {
            $str = '';
            $freeassignstr = get_string('freeassign', 'shophandlers_std_setuponecoursesession');
            $params = ['id' => $params['courseid'], 'pid' => $pid, 'method' => 'freeassign'];
            $postprodurl = new moodle_url('/local/shop/datahandling/postproduction.php', $params);
            $str .= '<a href="'.$postprodurl.'">'.$freeassignstr.'</a>';
        } else {
            $str = get_string('nonmutable', 'local_shop');
        }
        return $str;
    }

    /**
     * this method renders user formated information about product (contextually to handler)
     * for products generated with this handler
     * @param int $pid the product instance id
     * @param array $pinfo product info
     */
    public function display_product_infos($pid, $pinfo) {
        global $DB;

        $str = '';

        $str .= '<div><div class="cs-product-key">'.get_string('coursename', 'shophandlers_std_setuponecoursesession').'</div>';
        $str .= '<div class="cs-product-value">'.$pinfo->coursename.'</div></div>';
        $str .= '<div><div class="cs-product-key">'.get_string('beneficiary', 'shophandlers_std_setuponecoursesession').'</div>';
        $u = $DB->get_record('user', ['id' => $pinfo->userid]);
        $userurl = new moodle_url('/user/view.php', ['id' => $u->id]);
        $str .= '<div class="cs-product-value"><a href="'.$userurl.'">'.fullname($u).'</a></div></div>';

        $str .= '<div><div class="cs-product-key">'.get_string('role').'</div>';
        if ($pinfo->supervisor) {
            $str .= '<div class="cs-product-value">MANAGER</div></div>';
        } else {
            $str .= '<div class="cs-product-value">LEARNER</div></div>';
        }

        return $str;
    }
}
