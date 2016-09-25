<?php

global $CFG;

$string['handlername'] = 'Create one course category';
$string['pluginname'] = 'Create one course category';

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
<p>Your payment has been validated. The category "{$a->catname}" has been created for your usage as Course creator. You can access directly your training
products after proper authenticaton.</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. The category "{$a->catname}" has been created for your usage as Course creator.</p>
<p><a href="'.$CFG->wwwroot.'/course/category.php?id={$a->catid}">Direct access to the category</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>Payement has been received</b></p>
<p>Customer has been assigned a new category "{$a->catname}" .</p>
<p><a href="'.$CFG->wwwroot.'/course/category.php?id={$a->catid}">Access the category</a></p>
';