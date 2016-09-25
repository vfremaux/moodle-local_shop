<?php

global $CFG;

$string['handlername'] = 'One Course Creation ';
$string['pluginname'] = 'One Course Creation ';

$string['errornocategory'] = 'Category is not defined';
$string['errorcategorynotexists'] = 'Category of id {$a} does not exist';
$string['warningnohandlerusingdefault'] = 'Using default template for course';
$string['errortemplatenocourse'] = 'The course template {$a} does not exist';
$string['erroralreadyexists'] = 'This ID is already used';
$string['errorvalueempty'] = 'This field cannot accept empty values';
$string['erroralreadyinform'] = 'This ID is already used in this form';
$string['warningnoduration'] = 'No duration defined. ownership enrolment will be unlimited.';

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

$string['productiondata_post_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your access permissions have been updated consequently. You can access directly your training
products after proper authenticaton.</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your course has been open with ID "{$a->shortname}". You can access directly your training
products after proper authentication.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Direct access to your coursespace</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>Payement has been received</b></p>
<p>Customer {$a->username} has been assigned a new course {$a->fullname} with id {$a->shortname}.</p>
<p><a href="'.$CFG->wwwroot.'}/course/view.php?id={$a->id}">Access to coursespace</a></p>
';