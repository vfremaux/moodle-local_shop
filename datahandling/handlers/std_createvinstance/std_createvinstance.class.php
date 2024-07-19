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
 * @package shophandlers_std_createvinstance
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * STD_CREATE_VINSTANCE is a standard shop product action handler that can deply a full Virtualized
 * Moodle instance in the domaine scope.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

Use local_shop\Product;
Use local_shop\Shop;

/**
 * STD_CREATE_VINSTANCE is a standard shop product action handler that can deply a full Virtualized
 * Moodle instance in the domaine scope.
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
s */
class shop_handler_std_createvinstance extends shop_handler {

    /**
     * Constructor
     * @param string $label
     */
    public function __construct($label) {
        $this->name = 'std_createvinstance'; // For unit test reporting.
        parent::__construct($label);
    }

    /**
     * Validates data required frm the user when ordering.
     * @param string $itemname
     * @param string $fieldname
     * @param object $instance
     * @param mixed $value
     * @param arrayref &$errors
     */
    public function validate_required_data($itemname, $fieldname, $instance, $value, &$errors) {
        global $DB;

        if ($fieldname == 'shortname') {
            if ($DB->record_exists('block_vmoodle', ['shortname' => $value])) {
                $err = get_string('errorhostnameexists', 'shophanlders_createvinstance', $value);
                $errors[$itemname][$fieldname][$instance] = $err;
                return false;
            }
        }
        return true;
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

        if (!isset($data->customerdata['shortname'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay Error : No shortname defined");
            $preprocesserror = 1;
        }

        if (!isset($data->customerdata['name'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay Error : No name defined");
            $preprocesserror = 1;
        }

        if (!isset($data->actionparams['template'])) {
            shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay Error : No template name defined");
            $preprocesserror = 1;
        }

        if (is_dir($CFG->dirroot.'/local/vmoodle')) {
            require_once($CFG->dirroot.'/local/vmoodle/locallib.php');

            if (!vmoodle_exist_template($data->actionparams['template'])) {
                shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay Error : Template not available");
                $preprocesserror = 1;
            }
        } else {
            shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay Error : VMoodle not installed");
            $preprocesserror = 1;
        }

        if (!$preprocesserror) {
            $domain = @$data->actionparams['domain'];
            if (empty($domain)) {
                // Use same domain than current.
                $parts = explode('.', $_SERVER['SERVER_NAME']);
                array_shift($parts);
                $domain = implode('.', $parts);
            }

            $dbuser = @$data->actionparams['dbuser'];
            if (empty($dbuser)) {
                $dbuser = $CFG->dbuser;
            }

            $dbpass = @$data->actionparams['dbpass'];
            if (empty($dbpass)) {
                $dbpass = $CFG->dbpass;
            }

            $dbhost = @$data->actionparams['dbhost'];
            if (empty($dbhost)) {
                $dbhost = $CFG->dbhost;
            }

            $dbtype = @$data->actionparams['dbtype'];
            if (empty($dbtype)) {
                $dbtype = $CFG->dbtype;
            }

            $data->required['shortname'] = $this->clean_hostname($data->customerdata['shortname']);

            $SESSION->vmoodledata['vhostname'] = $data->customerdata['shortname'].'.'.$domain;
            $SESSION->vmoodledata['name'] = $data->actionparams['name'];
            $SESSION->vmoodledata['shortname'] = $data->actionparams['shortname'];
            $SESSION->vmoodledata['description'] = '';
            $SESSION->vmoodledata['vdbtype'] = $dbtype;
            $SESSION->vmoodledata['vdbname'] = 'vmdl_'.$data->actionparams['vhostname'];
            $SESSION->vmoodledata['vdblogin'] = $dbuser;
            $SESSION->vmoodledata['vdbpass'] = $dbpass;
            $SESSION->vmoodledata['vdbhost'] = $dbhost;
            $SESSION->vmoodledata['vdbpersist'] = 0 + @$CFG->block_vmoodle_dbpersist;
            $SESSION->vmoodledata['vdbprefix'] = ($CFG->block_vmoodle_dbprefix) ? $CFG->block_vmoodle_dbprefix : 'mdl_';
            $SESSION->vmoodledata['vtemplate'] = $data->handlerparams['template'];
            $SESSION->vmoodledata['vdatapath'] = dirname($CFG->dataroot).'/'.$data->actionparams['vhostname'];
            $SESSION->vmoodledata['mnet'] = 'NEW';

            for ($step = 0; $step <= 4; $step++) {
                include $CFG->dirroot.'/local/vmoodle/controller.management.php';
            }

            // Now setup local "manager" account.

            $VDB = vmoodle_setup_DB((object)$SESSION->vmoodledata);

            /*
             * special fix for deployed networks :
             * Fix the master node name in mnet_host
             * We need overseed the issue of loosing the name of the master node in the deploied instance
             * TODO : this is a turnaround quick fix.
             */
            if ($remote_vhost = $VDB->get_record('mnet_host', ['wwwroot' => $CFG->wwwroot])) {
                global $SITE;
                $remote_vhost->name = $SITE->fullname;
                $VDB->update_record('mnet_host', $remote_vhost, 'id');
            }

            // Setup the customer as manager account.
            $customer = $DB->get_record('local_shop_customer', ['id' => $data->get_customerid()]);
            $customeruser = $DB->get_record('user', ['id' => $customer->hasaccount]);

            $manager = new StdClass();
            $manager->firstname = 'Manager';
            $manager->lastname = 'Site';
            $manager->username = 'manager';
            $password = generate_password(8);
            $manager->password = hash_internal_user_password($password);
            $manager->confirmed = 1;
            $manager->city = strtoupper($customer->city);
            $manager->country = strtoupper($customer->country);
            $manager->lang = strtoupper($customer->country);
            $manager->email = $customer->email;
            $VDB->insert_record('user', $manager);

            // @todo : review validity period management
            $starttime = time();
            $endtime = starttime + DAYSECS * 365;

            // TODO : Enrol him as manager at system level.

            // Register product.
            $product = new StdClass();
            $product->catalogitemid = $data->catalogitem->id;
            $product->initialbillitemid = $data->id; // Data is a billitem.
            $product->currentbillitemid = $data->id; // Data is a billitem.
            $product->customerid = $data->bill->customerid;
            $product->contexttype = 'vmoodle';
            $product->instanceid = ''; // TODO : determine how to grab the vmoodle instance ID
            $product->startdate = $starttime;
            $product->enddate = $endtime;
            $product->extradata = '';
            $product->reference = shop_generate_product_ref($data);
            $extra = ['handler' => 'std_createvinstance'];
            $product->productiondata = Product::compile_production_data($data->actionparams,
                                                                        $data->customerdata,
                                                                        $extra);
            $product->id = $DB->insert_record('local_shop_product', $product);
    
            // Record an event.
            $productevent = new StdClass();
            $productevent->productid = $product->id;
            $productevent->billitemid = $data->id;
            $productevent->datecreated = time();
            $productevent->id = $DB->insert_record('local_shop_productevent', $productevent);
    
            // Add user to customer support.
            if (!empty($data->actionparams['customersupport'])) {
                shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay : Registering Customer Support");
                shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
            }

            $e = new StdClass;
            $e->managerusername = 'manager';
            $e->managerpassword = $password;
            $e->wwwroot = 'https://'.$data->customerdata['shortname'].'.'.$domain;
            $productionfeedback->public = get_string('productiondata_post_public', 'shophandlers_std_createvinstance', $e);
            $productionfeedback->private = get_string('productiondata_post_private', 'shophandlers_std_createvinstance', $e);
            $productionfeedback->salesadmin = get_string('productiondata_post_sales', 'shophandlers_std_createvinstance', $e);
        } else {
            // Vmoodle not installed.
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_createvinstance', 'Code : MOODLE VINSTANCE CREATION');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_createvinstance', $data);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_createvinstance', $data);
            $productionfeedback->salesadmin = $fb;
            $message = "[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay Error :";
            $message .= " Training credits not installed.";
            shop_trace($message);
            return $productionfeedback;
        }

        shop_trace("[{$data->transactionid}] STD_CREATE_VINSTANCE Postpay : Complete.");
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

        if (!isset($data->actionparams['name'])) {
            $errors[$data->code][] = get_string('errornoname', 'shophandlers_std_createvinstance');
        }

        if (!isset($data->actionparams['shortname'])) {
            $errors[$data->code][] = get_string('errornoshortname', 'shophandlers_std_createvinstance');
        }

        if (!isset($data->actionparams['vhostname'])) {
            $errors[$data->code][] = get_string('errornohostname', 'shophandlers_std_createvinstance');
        } else {

            if (strlen($data->actionparams['vhostname']) > 25) {
                $errors[$data->code][] = get_string('errortoolong', 'shophandlers_std_createvinstance');
            }

            if (preg_match('/\\./', $data->actionparams['vhostname'])) {
                $errors[$data->code][] = get_string('errorbadtoken', 'shophandlers_std_createvinstance');
            }
        }

        if (empty($data->handlerparams['template'])) {
            $errors[$data->code][] = get_string('errornotemplate', 'shophandlers_std_createvinstance');
        }

        if (empty($data->handlerparams['dbuser'])) {
            $warnings[$data->code][] = get_string('warningemptydbuser', 'shophandlers_std_createvinstance');
        }

        if (empty($data->handlerparams['dbpass'])) {
            $warnings[$data->code][] = get_string('warningemptydbpass', 'shophandlers_std_createvinstance');
        }

        if (empty($data->handlerparams['dbtype'])) {
            $warnings[$data->code][] = get_string('warningemptydbtype', 'shophandlers_std_createvinstance');
        }

        if (empty($data->handlerparams['dbhost'])) {
            $warnings[$data->code][] = get_string('warningemptydbhost', 'shophandlers_std_createvinstance');
        }
    }

    /**
     * cleans hostname
     * @param string $str hostname
     */
    protected function clean_hostname($str) {
        $str = str_replace(' ', '-', $str);

        return $str;
    }
}
