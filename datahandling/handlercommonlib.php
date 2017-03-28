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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

define('NO_HANDLER', 0);
define('EMPTY_HANDLER', '');
define('SPECIFIC_HANDLER', 1);

/**
 * @param objectref $data a full billitem object
 */
function shop_register_customer($data) {
    global $DB, $USER;

    $productionfeedback = new StdClass();
    $productionfeedback->public = '';
    $productionfeedback->private = '';
    $productionfeedback->salesadmin = '';

    if (empty($data->bill->customer)) {
        $data->bill->customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()));
    }
    if (isloggedin() && !isguestuser()) {
        if ($data->bill->customer->hasaccount != $USER->id) {
            /*
             * do it quick in this case. Actual user could authentify, so it is the legitimate account.
             * We guess if different non null id that the customer is using a new account. This should not really be possible
             */
            $data->bill->customer->hasaccount = $USER->id;
            $DB->update_record('local_shop_customer', $customer);
        } else {
            $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
            $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
            $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Prepay :";
            $message .= " Known account {$USER->username} at process entry.";
            shop_trace($message);
            return $productionfeedback;
        }
    } else {
        /*
         * In this case we can have a early Customer that never confirmed a product or a brand new Customer comming in.
         * The Customer might match with an existing user...
         * TODO : If a collision is to be detected, a question should be asked to the customer.
         */
        // Create Moodle User but no assignation (this will register in customer support if exists).
        if (!shop_create_customer_user($data, $data->bill->customer, $newuser)) {
            $message = "[{$data->transactionid}] STD_ASSIGN_ROLE_ON_CONTEXT Prepay Error :";
            $message .= " User could not be created {$newuser->username}.";
            shop_trace($message);
            $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
            $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
            $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
            return $productionfeedback;
        }

        $productionfeedback->public = get_string('productiondata_public', 'shophandlers_std_assignroleoncontext');
        $productionfeedback->private = get_string('productiondata_private', 'shophandlers_std_assignroleoncontext', $newuser->username);
        $fb = get_string('productiondata_sales', 'shophandlers_std_assignroleoncontext', $newuser->username);
        $productionfeedback->salesadmin = $fb;
    }

    return $productionfeedback;
}

/**
 * This enrols a customer user account into the designated customer support course as a student.
 * @param object $customer a customer record
 * @param int $courseid the Moodle id of the course used for customer support
 * @param string $transactionid the unique id of the transaction (for tracing puropse)
 */
function shop_register_customer_support($supportcoursename, $customeruser, $transactionid) {
    global $DB;

    $role = $DB->get_record('role', array('shortname' => 'student'));
    $role2 = $DB->get_record('role', array('shortname' => 'customer'));

    $now = time();

    if (!$course = $DB->get_record('course', array('shortname' => $supportcoursename))) {
        shop_trace("[{$transactionid}] Production Process Error : Customer support course does not exist.");
        return false;
    }

    $params = array('enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED);
    if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
        $enrol = reset($enrols);
        $enrolplugin = enrol_get_plugin('manual'); // The enrol object instance.
    } else {
        shop_trace("[{$transactionid}] Production Process Error : Customer support enrol failed // no enrol plugin.");
        return false;
    }

    try {
        $enrolplugin->enrol_user($enrol, $customeruser->id, $role->id, $now, 0, ENROL_USER_ACTIVE);
        $enrolplugin->enrol_user($enrol, $customeruser->id, $role2->id, $now, 0, ENROL_USER_ACTIVE);
        shop_trace("[{$transactionid}] Production Process : Customer support enrolled.");
    } catch (Exception $exc) {
        shop_trace("[{$transactionid}] Production Process Error : Customer support enrol failed.");
        return false;
    }
    return true;
}

/**
 * creates or update a user from commercial data and attach it to a customer account
 * @param objectref $data all shop collected data
 * @param objectref $customer the customer
 * @param objectref $newuser the user record that will be filled from converted data
 * @return true if user successfully created, false on error
 */
function shop_create_customer_user(&$data, &$customer, &$newuser) {
    global $CFG, $DB;

    // Create Moodle User but no assignation.
    $newuser = new StdClass();
    $newuser->username = shop_generate_username($data->customer);
    $newuser->city = $data->customer->city;
    $newuser->country = (!empty($data->customer->country)) ? $data->customer->country : $CFG->country;
    $newuser->lang = (!empty($data->customer->lang)) ? $data->customer->lang : $CFG->lang;
    $newuser->firstname = $data->customer->firstname;
    $newuser->lastname = $data->customer->lastname;
    $newuser->email = $data->customer->email;

    $newuser->auth = 'manual';
    $newuser->confirmed = 1;
    $newuser->lastip = getremoteaddr();
    $newuser->timemodified = time();
    $newuser->mnethostid = $CFG->mnet_localhost_id;

    $params = array('lastname' => $newuser->lastname, 'email' => $newuser->email);
    if (!$olduser = $DB->get_record('user', $params)) {
        $newuser->id = $DB->insert_record('user', $newuser);
    } else {
        $newuser->id = $olduser->id;
        $DB->update_record('user', $newuser);
    }

    if (!$newuser->id) {
        return false;
    }

    /*
     * We have a real new customer here. We setup in bill's construct for next steps.
     * @see /front/produce.controller.php
     */
    $data->bill->customeruser = get_complete_user_data('username', $newuser->username);

    // Passwords will be created and sent out on cron.
    if (!$oldrec = $DB->get_record('user_preferences', array('userid' => $newuser->id, 'name' => 'create_password'))) {
        $pref = new StdClass();
        $pref->userid = $newuser->id;
        $pref->name = 'create_password';
        $pref->value = 1;
        $DB->insert_record('user_preferences', $pref);
    } else {
        $oldrec->value = 1;
        $DB->update_record('user_preferences', $oldrec);
    }

    if (!$oldrec = $DB->get_record('user_preferences', array('userid' => $newuser->id, 'name' => 'auth_forcepasswordchange'))) {
        $pref = new StdClass();
        $pref->userid = $newuser->id;
        $pref->name = 'auth_forcepasswordchange';
        $pref->value = 0;
        $DB->insert_record('user_preferences', $pref);
    } else {
        $oldrec->value = 0;
        $DB->update_record('user_preferences', $oldrec);
    }

    // Bind customer record to Moodle userid.
    $customer->hasaccount = $newuser->id;
    $DB->update_record('local_shop_customer', $customer);

    shop_trace("[{$data->transactionid}] GENERIC : New user created {$newuser->username} with ID {$newuser->id}.");

    if ($data->actionparams['customersupport']) {
        shop_trace("[{$data->transactionid}] GENERIC : Registering Customer Support");
        shop_register_customer_support($data->actionparams['customersupport'], $newuser, $data->transactionid);
    }

    return $newuser->id;
}

/**
 * @param $participant a minimal object with essential user information
 * @param object $data a full set of data from the order/bill
 * @param object $participant
 */
function shop_create_moodle_user(&$data, $participant, $supervisorrole) {
    global $CFG, $DB;

    if (!$customer = $DB->get_record('local_shop_customer', array('id' => $data->get_customerid()))) {
        return false;
    }
    if (!$DB->get_record('user', array('id' => $customer->hasaccount))) {
        return false;
    }

    $customercontext = context_user::instance($customer->hasaccount);
    $studentrole = $DB->get_record('role', array('shortname' => 'student'));

    $participant->username = shop_generate_username($participant); // Makes it unique.

    /*
     * Let cron generate passwords.
     * @see hash_internal_user_password
     */

    $participant->lang = $CFG->lang;
    $participant->deleted = 0;
    $participant->confirmed = 1;
    $participant->timecreated = time();
    $participant->timemodified = time();
    $participant->mnethostid = $CFG->mnet_localhost_id;
    if (!isset($participant->country)) {
        $participant->country = $CFG->country;
    }

    if ($participant->id = $DB->insert_record('user', $participant)) {

        // Passwords will be created and sent out on cron.
        $pref = new StdClass();
        $pref->userid = $participant->id;
        $pref->name = 'create_password';
        $pref->value = 1;
        $DB->insert_record('user_preferences', $pref);

        $pref = new StdClass();
        $pref->userid = $participant->id;
        $pref->name = 'auth_forcepasswordchange';
        $pref->value = 0;
        $DB->insert_record('user_preferences', $pref);
    }

    /*
     * Assign role to customer for behalf on those users.
     * Note that supervisor role SHOULD HAVE the block/user_delegation::isbehalfedof allowed to
     * sync the user delegation handling.
     */
    $usercontext = context_user::instance($participant->id);
    $now = time();
    role_assign($supervisorrole->id, $customer->hasaccount, $usercontext->id, '', 0, $now);

    if ($participant->id) {
        // Assign mirror role for behalf on those users.
        role_assign($studentrole->id, $participant->id, $customercontext->id, '', 0, $now);
    }

    shop_trace("[{$data->transactionid}] GENERIC : New user created {$participant->username} with ID {$participant->id}.");

    return $participant;
}