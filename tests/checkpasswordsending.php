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
 * This test service checks if a product key is well formed, aka has
 * the correct checksum.
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/tests/check_password_sending_form.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

$url = new moodle_url('/local/shop/tests/checkpasswordsending.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);

require_login();
require_capability('moodle/site:config', $context);

$mform = new check_password_sending_form();

$checked = false;
if ($data = $mform->get_data()) {
    $checkeduser = $DB->get_record('user', ['id' => $data->userid]);
    $password = shop_set_and_send_password($checkeduser, true /* no real change */);

    $logfile = $CFG->dataroot.'/merchant_mail_trace.log';
    $logend = tailCustom($logfile, 30, true);
}

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('checkpasswordemission', 'local_shop'));

if (isset($checkeduser)) {
    echo $OUTPUT->notification(get_string('sentto', 'local_shop', $checkeduser->username), 'notifysuccess');
    echo $OUTPUT->heading(get_string('maillog', 'local_shop'));
    echo '<pre>';
    echo "...\n";
    echo strip_tags($logend);
    echo '</pre>';
}

$mform->display();

echo $OUTPUT->footer();
