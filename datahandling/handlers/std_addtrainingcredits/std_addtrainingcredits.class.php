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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @category    local
 * @subpackage  product_handlers
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * STD_ADD_TRAINING_CREDITS is a standard shop product action handler that adds coursecredits to the customer
 * credit account. This will only work when the trainingcredits enrol method is installed an enabled.
 */
include_once $CFG->dirroot.'/local/shop/datahandling/shophandler.class.php';

class shop_handler_std_addtrainingcredits extends shop_handler{

    function __construct($label) {
        $this->name = 'std_addtrainingcredits'; // for unit test reporting
        parent::__construct($label);
    }

    function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    function produce_prepay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();

        return $productionfeedback;
    }

    function produce_postpay(&$data) {
        global $CFG, $DB;

        $productionfeedback = new StdClass();

        if (!isset($data->actionparams['creditsamount'])) {
            shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay Warning : No credits defined. defaults to one per quantity unit");
            $data->actionparams['creditsamount'] = 1;
        }

        if (is_dir($CFG->dirroot.'/enrol/trainingcredits')) {
            if (!$creditsrec = $DB->get_record('trainingcredits', array('userid' => $USER->id))) {
                $creditrec->userid = $USER->id;
                $creditrec->coursecredits = $data->actionparams['creditsamount'] * $data->quantity;
                $DB->insert_record('trainingcredits', $creditrec);
            } else {
                $creditrec->coursecredits += $data->actionparams['creditsamount'] * $data->quantity;
                $DB->update_record('trainingcredits', $creditrec);
            }

            $productionfeedback->public = get_string('productiondata_assign_public', 'shophandlers_std_addtrainingcredits', '');
            $productionfeedback->private = get_string('productiondata_assign_private', 'shophandlers_std_addtrainingcredits', $creditrec->coursecredits);
            $productionfeedback->salesadmin = get_string('productiondata_assign_sales', 'shophandlers_std_addtrainingcredits', $creditrec->coursecredits);
        } else {
            // training credits not installed. 
            $productionfeedback->public = get_string('productiondata_failure_public', 'shophandlers_std_addtrainingcredits', 'Code : CATEGORY CREATION');
            $productionfeedback->private = get_string('productiondata_failure_private', 'shophandlers_std_addtrainingcredits', $data);
            $productionfeedback->salesadmin = get_string('productiondata_failure_sales', 'shophandlers_std_addtrainingcredits', $data);
            shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay Error : Training credits not installed.");
            return $productionfeedback;
        }

        // Add user to customer support on real purchase
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay : Complete.");
        return $productionfeedback;
    } 

    function unit_test($data, &$errors, &$warnings, &$messages) {

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['creditsamount'])) {
            $warnings[$data->code][] = get_string('warningnullcredits', 'shophandlers_std_addtrainingcredits');
        }
    }
}