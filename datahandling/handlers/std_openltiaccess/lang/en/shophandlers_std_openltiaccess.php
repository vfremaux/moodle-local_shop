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
 * @subpackage  shophandler_std_openltiaccess
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std OpenltiAccess does not directly store any personal data
 about any user.';

$string['handlername'] = 'Open LTI Access ';
$string['pluginname'] = 'Open LTI Access ';
$string['errornocourse'] = 'Target course is not defined';
$string['warningnoduration'] = 'No duration defined. ownership enrolment will be unlimited in time.';
$string['warningdefaultsendgrades'] = 'Using default value for sending grades: Sending grades on.';
$string['warningdefaultmaxenrolled'] = 'Using default value for max enrolment limit: Unlimited.';
$string['maxenrolled'] = 'Maximum to {$a} students';
$string['secret'] = 'Client Secret';
$string['globalsharedsecret'] = 'Site Shared Secret';
$string['unlimited'] = 'No limit';
$string['endpoint'] = 'LTI Access point URL';
$string['capacity'] = 'Access capacity';
$string['coursename'] = 'Course';
$string['extname'] = 'Exposed name';
$string['startdate'] = 'Start date';

$string['productiondata_post_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. The course {$a->coursename} is now open as LTI access at the URL:</p>

<p>{$a->endpoint}</p>

<p>with secret : </p>

<p>{$a->secret}

<p>You can use this provided data to configure a LTI Client in your LMS (f.e. another Moodle using External tool).</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. The course {$a->coursename} is now open as LTI access at the URL:</p>

<p>{$a->endpoint}</p>

<p>with secret : </p>

<p>{$a->secret}

<p>You can use this provided data to configure a LTI Client in your LMS (f.e. another Moodle using External tool).</p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payement has been received</b></p>
<p>Customer {$a->username} has open LTI access to course {$a->fullname}.</p>
<p><a href="'.$CFG->wwwroot.'}/course/view.php?id={$a->id}">Access to coursespace</a></p>
';
