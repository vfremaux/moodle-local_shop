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
 * @package     local_shop
 * @subpackage  shophandlers_std_addtrainingcredits
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std AddtrainingCredits does not directly store any personal data
 about any user.';

$string['handlername'] = 'Add course credits to user (TrainingCredit Enrolment)';
$string['pluginname'] = 'Add course credits to user (TrainingCredit Enrolment)';

$string['productiondata_public'] = '
<p>Your user account has been open on the site. A mail has been sent to your given mailbox. You will find appropriate login
 information.</p><p>If you made an online payment, your purchased products will be processed on automatic return of your payment
 order. You will be able to connect at once and get to your training volumes. On the other hand will our commercial service
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
<p>Your payment has been validated. {$a->credits} course credits have been added to your account. You may now enrol to proposed
courses enabled for course credit program.</p><p>Your credit amount is now: {$a->coursecredits}</p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Direct access to your account</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payment has been received</b></p>
<p>Customer {$a->username} has been fed with {$a->credits} course credits.</p>
<p>Credit amount is now: {$a->coursecredits}</p>
';
