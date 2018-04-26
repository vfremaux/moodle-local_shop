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

global $CFG;

$string['handlername'] = 'One course multiple enrolment';
$string['pluginname'] = 'One course multiple enrolment';

$string['pluginname_desc'] = 'This plugin allows purchasing a course enrolment several times, when f.e. a course access is needed
associated to a license counter (multiple product instance served within the same support course). This product type will not
lock the purchase hability of the customer if he already has one product and enrolment active in this course. The enrolment 
validity period will be pushed ahead to the last product end date in the customer account.';

$string['errornocourse'] = 'No target course for product';
$string['errorcoursenotexists'] = 'Course {$a} does not exist';
$string['errorrole'] = 'Enrolment role {$a} does not exist';
$string['warningroledefaultstoteacher'] = 'Enrolment role not defined. "Student" is used.';

$string['productiondata_public'] = '
<p>Your user account has been open on the site. A mail has been sent to your given mailbox. You will find appropriate login information.</p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order.
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Browse to the site entrance</a></p>
';

$string['productiondata_private'] = '
<p>Your user account has been setup on this site.</p>
<p>Your login is: {$a->username}</p>
<p>A personal password will be sent in a next mail.</p>
<p><b>Please note this information in a safe place before you continue...</b></p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order.
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Browse to the site entrance</a></p>
';

$string['productiondata_sales'] = '
<p>A user account has been created.</p>
<p>Login: {$a}<br/>
';

$string['productiondata_assign_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your access permissions have been updated consequently. You can access directly your training
products after proper authenticaton.</p>
';

$string['productiondata_assign_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your access permissions have been updated consequently. You can access directly your training
products after proper authentication.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a}">Direct access to your training</a></p>
';

$string['productiondata_assign_sales'] = '
<p><b>Payement has been received</b></p>
<p>Customer access have been open on course.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a}">Access to course</a></p>
';

$string['productiondata_failure_public'] = '
<p><b>Product Production error</b></p>
<p>Something failed when setting up the product referenced by {$a->code}.</p>
<p>Administrators should have been notified with this error and should contact you to solve the issue</p>
';

$string['productiondata_failure_private'] = '
<p><b>Product Production error</b></p>
<p>Something failed when setting up the product referenced by : {$a->code}.</p>
';

$string['productiondata_failure_sales'] = '
<p><b>Product Production error</b></p>
<p>Something failed when setting up the product instance referenced by : {$a->code}
error code : {$a->errorcode}.</p>
';