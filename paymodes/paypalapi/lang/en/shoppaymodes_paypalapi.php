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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shoppaymodes PaypalApi does not directly store any personal data about any user.';

$string['ipnfortest'] = 'Test the IPN backcall with this transaction';
$string['enablepaypalapi'] = 'Paypal payment by Paypal Rest API';
$string['enablepaypalapi2'] = 'Paypal payment by Paypal Rest API';
$string['enablepaypalapi3'] = 'You choosed to pay using Paypal...';
$string['paypalapi'] = 'Paypal API';
$string['paypalmsg'] = 'Thanks using Paypal for payment';
$string['pluginname'] = 'Paypal API Pay Mode';
$string['paypalpaymodeparams'] = 'Paypal configuration options';
$string['paypalsellername'] = 'Paypal seller account';
$string['paypalsellertestname'] = 'Paypal seller test account (paypal sandbox)';
$string['selleritemname'] = 'Sales Service';
$string['sellertestitemname'] = 'Sales Service (test mode)';
$string['configpaypalsellername'] = 'The account id of the reseller';
$string['configselleritemname'] = 'An arbitrary label that allows sorting transactions against multiple selling services';
$string['paypalpaymodeinvoiceinfo'] = 'You have choosen Paypal as payment method.';

$string['door_transfer_text_tpl'] = '
<p><b>Card payment over Paypal:</b>
We will transfer you on the Paypal portal
';

$string['print_procedure_text_tpl'] = '
<p>Follow the Paypal process untill end and come back to our seller site. You will be provided an invoice on return.
';

$string['pending_followup_text_tpl'] = '
<p>Your transaction has been accepted by our paiement partner. Your order will be automatically processed
on reception of the validation notification from your account holder. You will ne sent a last activation email
when done. Thank you again for your purchase.</p>

<p>In case the activation has not occured in the next 48 hours, contact us to check your situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>Your transaction has been accepted by our paiement partner. Your order will be automatically processed
on reception of the validation notification from your account holder. You will ne sent a last activation email
when done. Thank you again for your purchase.</p>

<p>In case the activation has not occured in the next 48 hours, contact our sales service <%%SUPPORT%%>.</p>
';
