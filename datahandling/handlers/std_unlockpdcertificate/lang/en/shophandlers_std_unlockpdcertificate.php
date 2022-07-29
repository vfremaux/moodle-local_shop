<?php

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std UnlockPdCertificate does not directly store any personal data about any user.';

$string['handlername'] = 'Unlock certificate';
$string['pluginname'] = 'Unlock certificate';

$string['errornoinstance'] = 'Target certificate instance is not defined';
$string['errorbadinstance'] = 'Target certificate instance could not be found';
$string['warningnoduration'] = 'No duration defined. ownership enrolment will be unlimited.';

$string['productiondata_post_public'] = '
<p><b>Your payment has been registered</b></p>

<p>Your certificate for the course {$a->fullname} has been unlocked. We will send a copy in the mailbox
registered with your user account.</p>

<p>You may get further copies of your certificate at this location : {$a->endpoint}</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been registered</b></p>

<p>Your certificate {$a->name} for the course {$a->fullname} has been unlocked. We will send a copy in the mailbox
registered with your user account.</p>

<p>You may get further copies of your certificate at this location : {$a->endpoint}</p>
';

$string['productiondata_produced_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payement has been received</b></p>
<p>Customer {$a->username} has unlocked his certificate {$a->name} in course {$a->fullname}.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Access to coursespace</a><br/>
<a href="{$a->endpoint}">Access to certificate</a></p>
';