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

/**
 * Lang file
 *
 * @package  shophandlers_std_assignroleoncontext
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std AssignRoleOnContext does not directly store any personal data
 about any user.';

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
$string['erroremptyuserriks'] = 'This product handler is not compatible with is providing area. Change product settings
to "logged in only", or add "foruser" external request';

$string['productiondata_public'] = '
<p>Your user account has been open on the site. A mail has been sent to your given mailbox. You will find appropriate login
information.</p><p>If you made an online payment, your purchased products will be processed on automatic return of your
payment order. You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
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
<p>Your payment has been validated. Your access permissions have been updated consequently. You can access directly your training
products after proper authentication.</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. Your access permissions have been updated consequently. You can access directly your training
products after proper authentication.</p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Direct access to your training</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Role of user {$a->username} have been changed with role {$a->role} in context {$a->instancename}. This opens new permissions</p>
';
