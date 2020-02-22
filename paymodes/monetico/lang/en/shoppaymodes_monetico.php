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
 * @package     shoppaymodes_monetico
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['enablemonetico'] = 'Monetico (Credit Mutuel)';
$string['enablemonetico2'] = 'Credit Cards (Monetico)';
$string['pluginname'] = 'Monetico Pay Mode';
$string['monetico'] = 'Monetico (Credit Mutuel)';
$string['moneticocurrencycode'] = 'Currency Code';
$string['moneticomerchantid'] = 'Merchant ID';
$string['moneticopaymentinvoiceinfo'] = 'Monetico (Crédit Mutuel)';
$string['moneticoserviceurl'] = 'Service URL';
$string['moneticousesecure'] = 'Use 3D Secure service';
$string['moneticopaymodeparams'] = 'Monetico Paymode settings';
$string['configmoneticoprodcertificate'] = 'Production certificate';
$string['configmoneticoprodcertificate_desc'] = 'Production certificate';
$string['configmoneticobankbrand'] = 'Bank brand';
$string['configmoneticobankbrand_desc'] = 'Bank brand';
$string['configmoneticocountry'] = 'Country';
$string['configmoneticocountry_desc'] = 'Country';
$string['configmoneticocurrencycode'] = 'Currency';
$string['configmoneticocurrencycode_desc'] = 'Currency';

$string['france'] = 'France';
$string['england'] = 'England';
$string['germany'] = 'Germany';
$string['spain'] = 'Spain';

$string['cur978'] = 'Euro';
$string['cur840'] = 'US Dollar';
$string['cur826'] = 'British Pound';
$string['cur756'] = 'CH Franc';
$string['cur036'] = 'AU Dollar';
$string['cur124'] = 'CA Dollar';

$string['cm'] = 'Crédit Mutuel';

$string['moneticoinfo'] = '
Monetico is a payment gateway adopted by several banks
in France. Its setup needs setting up crypted certificates and description files on the server
that cannot be uploaded from the Web interface for security reasons. Please ask a Moodle Shop integrator to operate this setup for you and perform
appropriate testing and service kick-off.
';

$string['pending_followup_text_tpl'] = '
<p>Your purchase has been accepted by our Monetico payment partner. Your ordering will be processed
as soon the payment effective acceptation has been received. You will receive a product activation
email when processed. Thank you for your purchase.</p>
<p>In case your products are not available in the next 48 hours, please contact us with your ordering references
to check your transaction status.</p>
';

$string['success_followup_text_tpl'] = '
<p>Your payment has been confirmed by our payment partner (Monetico). We ae processing your products.</p>
<p>If you cannot access the training platform, please contact our sales service <%%SUPPORT%%>.</p>
';

$string['door_transfer_text_tpl'] = 'This site is going to tranfer you to the payment portal of our banking partner {$a}.
Click on the appropriate payment card type you are going to use.';
