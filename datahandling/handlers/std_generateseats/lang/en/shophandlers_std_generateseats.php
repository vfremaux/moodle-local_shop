<?php

global $CFG;

$string['handlername'] = 'Generate unassigned seats';
$string['pluginname'] = 'Generate unassigned seats';
$string['assignseat'] = 'Assign the seat';
$string['unassignseat'] = 'Unassign the seat';
$string['assignseatlocked'] = 'Seat assignment is locked by user\'s activity';
$string['assignedto'] = '<b>Assigned to:</b> {$a}';
$string['enabledcourses'] = 'Enabled courses';
$string['incourse'] = '<b>In course: </b>[{$a->shortname}] {$a->fullname}';
$string['assignavailableseat'] = 'Assign an available seat';
$string['assigninstructions'] = 'This seat is actually unassigned yet. Please choose one learner you have under your behalf and a course in which you want to add this user. If the user is already enrolled in this course, the seat will remain unassigned, you\'ll be notified and sollicitated to reassign this product.';
$string['backtocourse'] = 'Back to customer support area';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Customer support course defaults to settings';
$string['warningnocustomersupportcourse'] = 'No customer support area defined';
$string['errornocustomersupportcourse'] = 'Customer support course {$a} does not exist';
$string['errorsupervisorrole'] = 'Supervisor role {$a} does not exist';
$string['warningsupervisordefaultstoteacher'] = 'Supervisor role not defined. "Non editing Teacher" is used.';
$string['warningpacksizedefaultstoone'] = 'Packsize not defined, defaults to one seat';
$string['warningonecoursenotexists'] = 'Some course ({$a}) in course list does not exist';
$string['warningemptycourselist'] = 'No course list restriction is defined. the seats will be assignable to any visible course';
$string['errornoallowedcourses'] = 'Product seems misconfigured and has no course allowed for assign';
$string['seatassigned'] = 'Congratulations ! You just enrolled {$a->user} to the course ($a->course}. We notify him/her about the event. This product can be reassigned as long as the concerned user has no activity track in the course. It will be locked in at the first activity log.';
$string['seatalreadyassigned'] = 'Sorry ! It seems that {$a->user} is already enrolled to the course ($a->course}. We will not burn this product for this choice. Please choose another seat assignation for this product.';
$string['seatreleased'] = 'Seat released ! You can reassign it now to another user.';

$string['productiondata_public'] = '
<p>{$a} seats have been placed for you in your customer account. A mail has been sent to your given mailbox. As purchaser, you have an account in our site. You\'ll be 
notified for login and password if this is the first time you come here.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Browse to the site entrance</a></p>
';

$string['productiondata_private'] = '
<p>Your user account has been setup on this site.</p>
<p>Your  credentials are:<br/>
Login: {$a->username}<br/>
Password: {$a->password}<br/></p>
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

$string['productiondata_created_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. {$a} unassigned seats have been added to your customer account.</p>
';

$string['productiondata_created_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. {$a} unassigned seats have been added to your customer account. You may now browse into your customer area to use these seats.</p>
<p><a href="{$a->customesupporturl}">Direct access to your customer account</a></p>
';

$string['productiondata_created_sales'] = '
<p><b>Payment has been received</b></p>
<p>Customer {$a->username} has been credited with {$a->seats} new unassigned seats.</p>
';

$string['assignseat_title'] = 'Vous avez un nouveau cours sur {$a} !';

$string['assignseat_mail'] = '
<p>Your manager has enroled you in the course <a href="{$a->url}">{$a->course}</a>.</p>
<p>You can connect and start the course using the login information you received in previous mail.</p>
';
