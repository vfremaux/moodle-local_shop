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
 * Main handler class.
 *
 * @package     local_shop
 * @category    local
 * @subpackage  product_handlers
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * STD_ADD_TRAINING_CREDITS is a standard shop product action handler that adds coursecredits to the customer
 * credit account. This will only work when the trainingcredits enrol method is installed an enabled.
 */
require_once($CFG->dirroot.'/local/shop/datahandling/shophandler.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

class shop_handler_std_addtrainingcredits extends shop_handler {

    public function __construct($label) {
        $this->name = 'std_addtrainingcredits'; // For unit test reporting.
        parent::__construct($label);
    }

    public function supports() {
        return PROVIDING_LOGGEDIN_ONLY;
    }

    public function produce_prepay(&$data, &$errorstatus) {
        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';
        return $productionfeedback;
    }

    public function produce_postpay(&$data) {
        global $CFG, $DB, $USER;

        $productionfeedback = new StdClass();
        $productionfeedback->public = '';
        $productionfeedback->private = '';
        $productionfeedback->salesadmin = '';

        if (!isset($data->actionparams['creditsamount'])) {
            $message = "[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay Warning :";
            $message /= " No credits defined. defaults to one per quantity unit";
            shop_trace($message);
            $data->actionparams['creditsamount'] = 1;
        }

        if (is_dir($CFG->dirroot.'/enrol/trainingcredits')) {
            if (!$creditsrec = $DB->get_record('enrol_trainingcredits', array('userid' => $USER->id))) {
                $creditsrec = new StdClass;
                $creditsrec->userid = $USER->id;
                $creditsrec->coursecredits = $data->actionparams['creditsamount'] * $data->quantity;
                $DB->insert_record('enrol_trainingcredits', $creditsrec);
            } else {
                $creditsrec->coursecredits += $data->actionparams['creditsamount'] * $data->quantity;
                $DB->update_record('enrol_trainingcredits', $creditsrec);
            }

            $e = new Stdclass;
            $e->txid = $data->transactionid;
            $e->coursecredits = $creditsrec->coursecredits;
            $e->credits = $data->actionparams['creditsamount'] * $data->quantity;
            $e->username = $USER->username;
            $fb = get_string('productiondata_post_public', 'shophandlers_std_addtrainingcredits', $e);
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_post_private', 'shophandlers_std_addtrainingcredits', $e);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_post_sales', 'shophandlers_std_addtrainingcredits', $e);
            $productionfeedback->salesadmin = $fb;
        } else {
            // Training credits not installed.
            $fb = get_string('productiondata_failure_public', 'shophandlers_std_addtrainingcredits', 'Code : ADD TRAINING CREDITS');
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_failure_private', 'shophandlers_std_addtrainingcredits', $data);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_failure_sales', 'shophandlers_std_addtrainingcredits', $data);
            $productionfeedback->salesadmin = $fb;
            shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay Error : Training credits not installed.");
            return $productionfeedback;
        }

        // Add user to customer support on real purchase.
        if (!empty($data->actionparams['customersupport'])) {
            shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay : Registering Customer Support");
            shop_register_customer_support($data->actionparams['customersupport'], $customeruser, $data->transactionid);
        }

        shop_trace("[{$data->transactionid}] STD_ADD_TRAINING_CREDITS Postpay : Complete.");
        return $productionfeedback;
    }

    public function unit_test($data, &$errors, &$warnings, &$messages) {

        $messages[$data->code][] = get_string('usinghandler', 'local_shop', $this->name);

        parent::unit_test($data, $errors, $warnings, $messages);

        if (!isset($data->actionparams['creditsamount'])) {
            $warnings[$data->code][] = get_string('warningnullcredits', 'shophandlers_std_addtrainingcredits');
        }
    }
}