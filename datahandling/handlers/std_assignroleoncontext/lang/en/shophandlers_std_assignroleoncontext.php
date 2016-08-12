<?php

global $CFG;

$string['handlername'] = 'Single role assign';
$string['pluginname'] = 'Single role assign';

$string['errormissingcontextlevel'] = 'No context level given for operation';
$string['errorunsupportedcontextlevel'] = 'Unsupported context level {$a}';
$string['errorrole'] = 'Role {$a} does not exist';
$string['errormissingrole'] = 'Role not defined.';
$string['errorcontext'] = 'This context {$a} does not exist';
$string['errormissingcontext'] = 'Instance not defined.';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Customer support course defaults to settings';
$string['warningnocustomersupportcourse'] = 'No customer support area defined';
$string['errornocustomersupportcourse'] = 'Customer support course {$a} does not exist';
$string['warningonlyforselfproviding'] = 'Settings constraints to providing for logged in only';
$string['erroremptyuserriks'] = 'This product handler is not compatible with is providing area. Change product settings to "logged in only", or add "foruser" external request';

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
<p>Your role have been changed. this opens you new permissions</p>
';