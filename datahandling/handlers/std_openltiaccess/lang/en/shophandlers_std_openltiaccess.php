<?php

global $CFG;

$string['handlername'] = 'Open LTI Access ';
$string['pluginname'] = 'Open LTI Access ';
$string['errornocourse'] = 'Target course is not defined';
$string['warningnoduration'] = 'No duration defined. ownership enrolment will be unlimited in time.';
$string['warningdefaultsendgrades'] = 'Using default value for sending grades: Sending grades on.';
$string['warningdefaultmaxenrolled'] = 'Using default value for max enrolment limit: Unlimited.';

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
<p><b>Payement has been received</b></p>
<p>Customer {$a->username} has open LTI access to course {$a->fullname}.</p>
<p><a href="'.$CFG->wwwroot.'}/course/view.php?id={$a->id}">Access to coursespace</a></p>
';