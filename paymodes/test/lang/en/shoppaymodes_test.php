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
 * @package     shoppaymodes_test
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shoppaymodes Test does not directly store any personal data
 about any user.';

$string['enabletest'] = 'Test payment';
$string['enabletest2'] = 'Test payment';
$string['enabletest3'] = 'You choosed using shop test payment...';
$string['pluginname'] = 'Test Payment';
$string['test'] = 'Test payment';
$string['interactivepay'] = 'Interactive payment';
$string['ipnpay'] = 'IPN Trigger without closing';
$string['ipnpayandclose'] = 'IPN Trigger with transaction closing';
$string['paydelayedforipn'] = 'Payment with IPN deferred';

$string['testadvice'] = '
<p>This is a fake simulated payment method to allow processing tests on the end of the purchase path. Never use in production mode,
or real customers could buy products without effective fund transaction !</p><p>Here you can trigger four testing actions : </p>
<ul><li><b>Direct pay:</b> As fastest way to validate a bill and get production triggered. This will never represent a real
transaction as transaction is either delayed for payment confirmation (offline), or delayed for some Online IPN asynchronous
returns.</li>
<li><b>Triggering payement with IPN</b>: This is the simulation of most online payment methods, as real operations will be
processed on IPN return. This may simulate when a customer comes back from payment service interface and returns back to
the site.</li>
<li><b>IPN Return without termination</b>: This activates a simulation of an IPN return in a separate window. You can use this
trigger before the above link and check order is complete. the order is not clesed so it can be played again for test purpose.</li>
<li><b>IPN Trigger with termination</b>: This activates a simulation of an IPN return in a separate window. You can use this trigger
before the above link and check order is complete. the order will be terminated and the shopping cart set back to empty state.</li>
</ul>';

$string['pending_followup_text_tpl'] = '
<p>Purchase is waiting for IPN return. Processing will be done on IPN runtime.</p>
<p>Test suport info: <%%SUPPORT%%>.</p>
';

$string['success_followup_text_tpl'] = '
<p>Test payment has been registered in DB. Products are being processed.</p>
<p>Test support information: <%%SUPPORT%%>.</p>
';
