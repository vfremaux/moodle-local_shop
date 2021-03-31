<?php

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std AddtrainingCredits does not directly store any personal data about any user.';

$string['handlername'] = 'Add course credits to user (TrainingCredit Enrolment)';
$string['pluginname'] = 'Add course credits to user (TrainingCredit Enrolment)';

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
<p>Your password has been sent to you in a separate mail. <b>Please store them in a safe place before pursuing...</b></p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order.
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Browse to the site entrance</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>A user account has been created.</p>
<p>Login: {$a->username}<br/>
';

$string['productiondata_post_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. {$a->credits} course credits have been added to your account.</p>
<p>Your credit amount is now: {$a->coursecredits}</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. {$a->credits} course credits have been added to your account. You may now enrol to proposed courses enabled for course credit program.</p>
<p>Your credit amount is now: {$a->coursecredits}</p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Direct access to your account</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payment has been received</b></p>
<p>Customer {$a->username} has been fed with {$a->credits} course credits.</p>
<p>Credit amount is now: {$a->coursecredits}</p>
';