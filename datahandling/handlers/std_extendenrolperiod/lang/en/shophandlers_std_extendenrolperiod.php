<?php

global $CFG;

$string['handlername'] = 'Enrol extension';
$string['pluginname'] = 'Enrol extension';

$string['warningenroltypedefaultstomanual'] = 'Enrol type defaults to manual';
$string['warningnullextension'] = 'Extension period value is null. No effect.';
$string['errornocourse'] = 'No target course defined';
$string['errorextcoursenotexists'] = 'Target course {$a} does not exists';
$string['errorenrolpluginnotavailable'] = 'Enrol plugin "{$a}" is not installed or not available';
$string['errorenroldisabled'] = 'Enrol plugin "{$a}" is here, but disabled';

$string['productiondata_public'] = '
<p>You have acquired {$a->extension} days of additional enrolment.</p>
<p>If you choosed an online payment method, your extension has been instantly processed. You can connect and 
use your additional time. If your payment method is delayed, your extension will be added as soon as your payment recieved.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Go to your course</a></p>
';

$string['productiondata_private'] = '
<p>You have acquired {$a->extension} days of additional enrolment.</p>
<p>If you choosed an online payment method, your extension has been instantly processed. You can connect and 
use your additional time. If your payment method is delayed, your extension will be added as soon as your payment recieved.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Go to your course</a></p>
';

$string['productiondata_sales'] = '
<p>Customer {$a->username} has extended his enrolment.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Go to the course</a></p>
';