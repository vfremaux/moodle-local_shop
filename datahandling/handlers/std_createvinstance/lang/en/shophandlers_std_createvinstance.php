<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std CreateVInstance does not directly store any personal data about any user.';

$string['handlername'] = 'Creates a full instance of Moodle';
$string['pluginname'] = 'Creates a full instance of Moodle';
$string['errorhostnameexists'] = 'This host shortname ({$a}) is already used';

$string['productiondata_post_public'] = '
<p>A new account has been created for you for your customer support. A mail has been sent to your given mailbox with full credentials to administrate it.</p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order.
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="{$a->wwwroot}/login/index.php">Browse to your site entrance</a></p>
';

$string['productiondata_post_private'] = '
<p>A new account has been created for you for your customer support.</p>
<p>Your  credentials are:<br/>
Login: {$a->username}<br/>
<p>Your password has been sent to you in a separate mail. <b>Please store them in a safe place before pursuing...</b></p>
<p>If you made an online payment, your purchased products will be processed on automatic return of your payment order.
You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
validate your purchase on payment confirmation.</p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Browse to your personall customer services</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>A user account has been created.</p>
<p>Login: {$a->username}<br/>
';

$string['productiondata_post_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your new platform has been initialized. check your email for complete information.</p>
';

$string['productiondata_delivered_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your new site {$a->wwwroot} has been initialized. You may now log in to configure and administrate it.</p>

<p>Your administrator credentials:<br/>
Login: {$a->managerusername}<br/>
PAssword: {$a->managerpassword}</p>

<p><a href="{$a->wwwroot}/login/index.php">Direct access to your new moodle site</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Direct access to your customer services</a></p>
';

$string['productiondata_delivered_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payment has been received</b></p>
<p>Customer {$a->username} has been initiated to use {$a->wwwroot} new Moodle instance.</p>
';