<?php

global $CFG;

$string['handlername'] = 'One course enrolment';
$string['pluginname'] = 'One course enrolment';

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