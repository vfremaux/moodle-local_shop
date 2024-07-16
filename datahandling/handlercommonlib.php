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
 * A common, set of functions for hanlders.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_HANDLER', 0);
define('EMPTY_HANDLER', '');
define('SPECIFIC_HANDLER', 1);

/**
 * Registers an associated customer moodle user reference.
 * If new customer, will ask for a moodle user creation.
 * @param object $data a full billitem object
 * @param arrayref @$errorstatus an error reporting array.
 */
function shop_register_customer($data, &$errorstatus) {
    global $DB, $USER;

    $productionfeedback = new StdClass();
    $productionfeedback->public = '';
    $productionfeedback->private = '';
    $productionfeedback->salesadmin = '';

    if (empty($data->bill->customer)) {
        $data->bill->customer = $DB->get_record('local_shop_customer', ['id' => $data->get_customerid()]);
    }

    if (isloggedin() && !isguestuser()) {
        if ($data->bill->customer->hasaccount != $USER->id) {
            /*
             * do it quick in this case. Actual user could authentify, so it is the legitimate account.
             * We guess if different non null id that the customer is using a new account. This should not really be possible
             */
            $data->bill->customer->hasaccount = $USER->id;
            $DB->update_record('local_shop_customer', $data->bill->customer);
            $message = "[{$data->transactionid}] Prepay Commons :";
            $message .= " Logged in customer. Udating customer account.";
            shop_trace($message);
        } else {
            $productionfeedback->public = get_string('knownaccount', 'local_shop', $USER->username);
            $productionfeedback->private = get_string('knownaccount', 'local_shop', $USER->username);
            $productionfeedback->salesadmin = get_string('knownaccount', 'local_shop', $USER->username);
            $message = "[{$data->transactionid}] Prepay Commons :";
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
        // TODO : eliminate $data->customer ? Seems no need for.
        if (empty($data->bill->customer) && empty($data->customer)) {
            // No way to retrieve user.
            $message = "[{$data->transactionid}] Prepay Commons Error : ";
            $message .= " Customer is gone away (no data).";
            shop_trace($message);
            $errorstatus = true;
            $productionfeedback->public = get_string('customerisgone', 'local_shop');
            $productionfeedback->private = get_string('customerisgone', 'local_shop');
            $productionfeedback->salesadmin = get_string('customerisgone', 'local_shop');
            return $productionfeedback;
        }

        if (empty($data->bill->customer->hasaccount)) {
            // Customer may have been created by a previous product in bill or in bundle.
            if (!shop_create_customer_user($data, $data->bill->customer, $newuser)) {
                $message = "[{$data->transactionid}] Prepay Commons Error :";
                $message .= " User could not be created {$newuser->username}.";
                shop_trace($message);
                $errorstatus = true;
                $productionfeedback->public = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->private = get_string('customeraccounterror', 'local_shop', $newuser->username);
                $productionfeedback->salesadmin = get_string('customeraccounterror', 'local_shop', $newuser->username);
                return $productionfeedback;
            }
            $message = "[{$data->transactionid}] Prepay Commons :";
            $message .= " New user created {$newuser->username}.";
            shop_trace($message);

            $e = clone($newuser);
            $e->txid = $data->transactionid;
            $fb = get_string('productiondata_public', 'shophandlers_std_assignroleoncontext', $e);
            $productionfeedback->public = $fb;
            $fb = get_string('productiondata_private', 'shophandlers_std_assignroleoncontext', $e);
            $productionfeedback->private = $fb;
            $fb = get_string('productiondata_sales', 'shophandlers_std_assignroleoncontext', $e);
            $productionfeedback->salesadmin = $fb;
        }
    }

    return $productionfeedback;
}

/**
 * This enrols a customer user account into the designated customer support course as a student.
 * @param int $supportcoursename the Moodle shortname of the course used for customer support
 * @param object $customeruser a customer's moodle user record
 * @param string $transactionid the unique id of the transaction (for tracing puropse)
 */
function shop_register_customer_support($supportcoursename, $customeruser, $transactionid) {
    global $DB;

    $role = $DB->get_record('role', ['shortname' => 'student']);

    if (empty($role)) {
        throw new moodle_exception('Legacy Role student may have been deleted from this moodle. This should not happen.');
    }

    $role2 = $DB->get_record('role', ['shortname' => 'customer']);

    if (empty($role2)) {
        $mess = 'Role customer may have been renamed from this moodle or may not have been installed properly.';
        $mess .= ' Try to reinstall the shop again.';
        throw new moodle_exception($mess);
    }

    $now = time();

    if (!$course = $DB->get_record('course', ['shortname' => $supportcoursename])) {
        shop_trace("[{$transactionid}] Production Process Error : Customer support course does not exist.");
        return false;
    }

    $params = ['enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED];
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
 * This enrols a user account into the designated extra support courses as a student for the
 * product validity time range.
 * @param int $supportcoursename the Moodle shortname of the course used for extra support
 * @param object $customer a customer record
 * @param string $transactionid the unique id of the transaction (for tracing puropse)
 * @param string $enrolendtime a unix timestamp for ending enrolment.
 */
function shop_register_extra_support($supportcoursename, $customeruser, $transactionid, $enrolendtime) {
    global $DB;

    $role = $DB->get_record('role', ['shortname' => 'student']);

    if (empty($role)) {
        throw new moodle_exception('Legacy Role student may have been deleted from this moodle. This should not happen.');
    }

    $now = time();

    if (!$course = $DB->get_record('course', ['shortname' => $supportcoursename])) {
        shop_trace("[{$transactionid}] Production Process Error : Extra support course does not exist.");
        return false;
    }

    $params = ['enrol' => 'manual', 'courseid' => $course->id, 'status' => ENROL_INSTANCE_ENABLED];
    if ($enrols = $DB->get_records('enrol', $params, 'sortorder ASC')) {
        $enrol = reset($enrols);
        $enrolplugin = enrol_get_plugin('manual'); // The enrol object instance.
    } else {
        shop_trace("[{$transactionid}] Production Process Error : Extra support enrol failed // no enrol plugin.");
        return false;
    }

    try {
        $enrolplugin->enrol_user($enrol, $customeruser->id, $role->id, $now, $enrolendtime, ENROL_USER_ACTIVE);
        shop_trace("[{$transactionid}] Production Process : Extra support enrolled.");
    } catch (Exception $exc) {
        shop_trace("[{$transactionid}] Production Process Error : Extra support enrol failed.");
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
    $newuser->username = shop_generate_username($customer, true); // Unique username.
    $newuser->city = $customer->city;
    $newuser->country = (!empty($customer->country)) ? $customer->country : $CFG->country;
    $newuser->lang = (!empty($customer->lang)) ? $customer->lang : $CFG->lang;
    $newuser->firstname = $customer->firstname;
    $newuser->lastname = $customer->lastname;
    $newuser->email = $customer->email;

    $newuser->auth = 'manual';
    $newuser->confirmed = 1;
    $newuser->lastip = getremoteaddr();
    $newuser->timemodified = time();
    $newuser->mnethostid = $CFG->mnet_localhost_id;

    $params = ['lastname' => $newuser->lastname, 'email' => $newuser->email];
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

    shop_set_and_send_password($data->bill->customeruser);

    if (!$oldrec = $DB->get_record('user_preferences', ['userid' => $newuser->id, 'name' => 'auth_forcepasswordchange'])) {
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
 * Create a user in moodle for the customer
 *
 * @param $participant a minimal object with essential user information
 * @param object $data a full set of data from the order/bill
 * @param object $participant
 * @param string $supervisorrole
 */
function shop_create_moodle_user($data, $participant, $supervisorrole) {
    global $CFG, $DB;

    if (!$customer = $DB->get_record('local_shop_customer', ['id' => $data->get_customerid()])) {
        return false;
    }
    if (!$DB->get_record('user', ['id' => $customer->hasaccount])) {
        return false;
    }

    $customercontext = context_user::instance($customer->hasaccount);
    $studentrole = $DB->get_record('role', ['shortname' => 'student']);

    $participant->username = shop_generate_username($participant, true); // Makes it unique.

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
        $complete = get_complete_user_data('username', $participant->username);
        shop_set_and_send_password($complete);
        set_user_preference('auth_forcepasswordchange', 0, $participant->id);
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

/**
 * Generates and setup a password for a user.
 * @param object $user a User record.
 * @param bool $nosend if true, the password will be generated and stored, but no mail goes out.
 * @param bool $testmode if true, the password is generated but not stored. With nosend, will just return a new password.
 */
function shop_set_and_send_password($user, $testmode = false) {
    global $DB, $SITE, $CFG;

    $password = generate_password(8);

    $hashed = hash_internal_user_password($password);
    $config = get_config('local_shop');

    if (!$testmode) {
        $DB->set_field('user', 'password', $hashed, ['id' => $user->id]);
    }

    // Start with the default platform language.
    $subject = new lang_string('new_password_subject_tpl', 'local_shop', $CFG->lang);
    $subject = str_replace('%SITE%', $SITE->fullname, $subject);

    $message = new lang_string('new_password_message_tpl', 'local_shop', $CFG->lang);
    $message = str_replace('%LOGIN%', $user->username, $message);
    $message = str_replace('%PASSWORD%', $password, $message);
    $message = str_replace('%FULLNAME%', fullname($user), $message);
    $loginurl = get_login_url();
    $message = str_replace('%SITE%', $SITE->fullname, $message);
    $message = str_replace('%URL%', $loginurl, $message);

    $support = '';
    if (!empty($config->sellermailsupport)) {
        $support = '<a href="mailto:">'.$config->sellermailsupport.'</a>';
    }
    if (!empty($config->sellerphonesupport)) {
        $support .= ' Tel: '.$config->sellerphonesupport;
    }
    $message = str_replace('%SUPPORT%', $support, $message);

    if ($testmode) {
        $repl = '<p><b>This is a test message. Please do not care if you receive it, ';
        $repl .= "your password HAS NOT been changed.</b></p>\n";
        $message = str_replace('%TESTMODE%', $repl, $message);
    } else {
        $message = str_replace('%TESTMODE%', '', $message);
    }

    $rawmessage = strip_tags($message);

    // Send password to user.
    $result = email_to_user($user, ''.@$config->sellermailsupport, $subject, $message, $rawmessage, '', '', false /* trueadress */);
    if ($result) {
        $trace = $subject."\n\n".$message;
        shop_trace($trace, 'mail', $user);
    } else {
        shop_trace("ERROR > Password Mail Sending Error", 'mail', $user);
    }
}
