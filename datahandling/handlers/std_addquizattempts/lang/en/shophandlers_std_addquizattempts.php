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
 * @package  shophandlers_std_addquizattempts
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shophandler Std AddQuizAttempts does not directly store any personal data about
 any user.';

$string['handlername'] = 'Add quiz attempts to user (block_userquiz_limits)';
$string['pluginname'] = 'Add quiz attempts to user (block_userquiz_limits)';
$string['warningmultiplecourses'] = 'Multiple courses affected by quiz refs. Only first ref course enroll will be checked. You
 should point quizzes in the same course.';
$string['errornoquizvalid'] = 'None of the quiz ref is a valid quiz.';
$string['errornotaquiz'] = 'This quiz ref $a is not a quiz module';
$string['errorbadidnumber'] = 'This quiz idnumber $a is invalid';
$string['errorbadcmref'] = 'This course module ref $a is invalid';
$string['errorbadquizref'] = 'This quiz ref $a is invalid';
$string['errorunassignedquiz'] = 'Quiz assignation not set';
$string['warningnullcredits'] = 'Attempts amount (attemptsamount) not set. Will default to 1.';

$string['productiondata_public'] = '
<p>quiz attempts have been added to your account</p>
<p><a href="{$a->ticket}">Browse to the course</a></p>
';

$string['productiondata_private'] = '
<p>{$a->attempts} attempts have been added to your account for the quiz {$a->quizname}.</p>
<p><a href="{$a->ticket}">Browse to the course</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Quiz attemtps have been added to user: </p>
<p>Login: {$a->username}<br/>
Quiz: {$a->quizname}<br/>
</p>
';

$string['productiondata_post_public'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. {$a->credits} attempts have been added to your account for the quiz {$a->quizname}.</p>
<p>Your attempts amount is now: {$a->attempts}</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been received</b></p>
<p>Your payment has been validated. {$a->credits} attempts have been added to your account for the quiz {$a->quizname}</p>
<p><a href="{$a->ticket}">Direct access to your course</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payment has been received</b></p>
<p>Customer {$a->username} has been fed with {$a->credits} attempts on quiz {$a->quizname}.</p>
<p>Credit amount is now: {$a->attempts}</p>
';

$string['productiondata_failure_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Commercial notification failure</b></p>
';

$string['productiondata_failure_public'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Public notification failure</b></p>
';

$string['productiondata_failure_private'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Private notification failure</b></p>
';
