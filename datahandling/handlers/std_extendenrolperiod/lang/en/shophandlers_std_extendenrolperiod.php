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
 * Lang file
 *
 * @package   local_shop
 * @subpackage shophandler_std_extendenrolperiod
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std ExtendEnrolPeriod does not directly store any personal data
 about any user.';

$string['handlername'] = 'Enrol extension';
$string['pluginname'] = 'Enrol extension';

$string['warningenroltypedefaultstomanual'] = 'Enrol type defaults to manual';
$string['warningnullextension'] = 'Extension period value is null. No effect.';
$string['errornocourse'] = 'No target course defined';
$string['errorextcoursenotexists'] = 'Target course {$a} does not exists';
$string['errorenrolpluginnotavailable'] = 'Enrol plugin "{$a}" is not installed or not available';
$string['errorenroldisabled'] = 'Enrol plugin "{$a}" is here, but disabled';

$string['productiondata_post_public'] = '
<p>You have acquired {$a->extension} days of additional enrolment.</p>
<p>If you choosed an online payment method, your extension has been instantly processed. You can connect and
use your additional time. If your payment method is delayed, your extension will be added as soon as your payment recieved.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Go to your course</a></p>
';

$string['productiondata_post_private'] = '
<p>You have acquired {$a->extension} days of additional enrolment.</p>
<p>If you choosed an online payment method, your extension has been instantly processed. You can connect and
use your additional time. If your payment method is delayed, your extension will be added as soon as your payment recieved.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Go to your course</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Customer {$a->username} has extended his enrolment by {$a->extension} days.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Go to the course</a></p>
';
