<?php

global $CFG;

$string['handlername'] = 'Creates a full instance of Moodle';
$string['pluginname'] = 'Creates a full instance of Moodle';
$string['errorhostnameexists'] = 'This host shortname ({$a}) is already used';


$string['productiondata_public'] = '
<p>Your new Moodle instance has been created. A mail has been sent to your given mailbox with full credentials to administrate it.</p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order. 
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="{$a->wwwroot}/login/index.php">Browse to your site entrance</a></p>
';

$string['productiondata_private'] = '
<p>Your new moodle instance has been initialized.</p>
<p>Your  credentials are:<br/>
Url: {$a->wwwroot}<br/>
Login: {$a->username}<br/>
Password: {$a->password}<br/></p>
<p><b>Please note this information in a safe place before you continue...</b></p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order. 
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="{$a->wwwroot}/login/index.php">Browse to your site entrance</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Browse to your personall customer services</a></p>
';

$string['productiondata_sales'] = '
<p>A new Moodle instance has been created as {$a->wwwroot}.</p>
<p>A user account has been created.</p>
<p>Login: {$a}<br/>
';

$string['productiondata_delivered_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your new platform has been initialized.</p>
';

$string['productiondata_delivered_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your new site {$a->wwwroot} has been initialized. You may now log in to configure and administrate it.</p>
<p><a href="{$a->wwwroot}/login/index.php">Direct access to your new moodle site</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Direct access to your customer services</a></p>
';

$string['productiondata_delivered_sales'] = '
<p><b>Payment has been received</b></p>
<p>Customer {$a->username} has been initiated to use {$a->wwwroot} new Moodle instance.</p>
';