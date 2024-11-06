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
 * Proces a request, possibly in unconnected mode.
 *
 * @package    shoppaymodes_mercanet
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 * Get DATA param string from Mercanet API and redirect to shop.
 */

/**
 * phpcs:disable moodle.Files.RequireLogin.Missing
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/mercanet/mercanet.class.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

$config = get_config('local_shop');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/shop/paymodes/mercanet/process.php'));

/*
 * we cannot know yet which block instanceplays as infomation is in the mercanet
 * cryptic answer. Process() decodes cryptic answer and get this context information to
 * go further.
 */
$shopinstance = null;
$payhandler = new shop_paymode_mercanet($shopinstance);
$payhandler->process();

/*
 * this part will continue in case process is executed in test mode
 * proposes a bounce on process_ipn, using same post data.
 */

if (!empty($config->test)) {
    // Be sure we are in test.
    $ipnurl = new moodle_url('/local/shop/paymodes/mercanet/mercanet_ipn.php');

    echo '<hr/>';
    echo '<h2>IPN Bounce test</h2>';
    echo '<form name="test_ipn" method="POST" action="'.$ipnurl.'" />';
    echo '<input type="hidden" name="DATA" value="'.urlencode($_POST['DATA']).'" />';
    echo '<input type="submit" name="GoTest" value="'.get_string('gotestipn', 'shoppaymodes_mercanet').'" />';
    echo '</form />';
}

echo $OUTPUT->footer();
