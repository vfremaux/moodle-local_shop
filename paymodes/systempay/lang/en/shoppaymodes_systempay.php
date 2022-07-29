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
 * @package     shoppaymodes_test
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shoppaymodes SystemPay does not directly store any personal data about any user.';

$string['configsystempaybankbrand'] = '';
$string['configsystempaycountry'] = 'Country';
$string['configsystempaycurrencycode'] = 'Currency Code';
$string['configsystempaymerchantid'] = 'This is the ID of the merchant service provided in your backoffice interface';
$string['configsystempayprodcertificate'] = 'The certificate key to encode production submissions';
$string['configsystempayserviceurl'] = 'Url for Payment Transaction Server';
$string['configsystempaytestcertificate'] = 'The certificate key to encode test submissions';
$string['configsystempayusesecure'] = '3D Secure is an option to the merchant payment contract that will callback the customer for a confirmation code.';
$string['configsystempayuselocaltime'] = 'Use local time for transations, or UTC/GMT time if not checked.';
$string['configsystempayalgorithm'] = 'Algorithm for signing VADS messages.';
$string['enablesystempay'] = 'System Pay PLUS (Caisse d\'Epargne/Banque Populaire/Société Générale)';
$string['enablesystempay2'] = 'Credit Cards (System Pay)';
$string['errorsystempaynotsetup'] = 'System Pay PLUS has no setup and cannot be used for payment.';
$string['pluginname'] = 'System Pay PLUS Pay Mode';
$string['systempay'] = 'systemPay Plus(Caisse d\'Epargne/Banque Populaire/Société Générale)';
$string['systempaybankbrand'] = 'Bank brand';
$string['systempaycountry'] = 'Country';
$string['systempaycurrencycode'] = 'Currency Code';
$string['systempaymerchantid'] = 'Merchant ID';
$string['systempaypaymentinvoiceinfo'] = 'SystemPay PLUS (Caisse d\'Epargne/Banque Populaire)';
$string['systempaypaymodeparams'] = 'System Pay PLUS Settings';
$string['systempayprodcertificate'] = 'Prod certificate';
$string['systempaysecreterror'] = 'Error in validating returned HMAC';
$string['systempayserviceurl'] = 'Service URL';
$string['systempaytestcertificate'] = 'Test certificate';
$string['systempayusesecure'] = 'Use 3D Secure service';
$string['systempayuselocaltime'] = 'Use local time';
$string['systempayalgorithm'] = 'Algorithm';

$string['systempayinfo'] = '
System Pay PLUS is a payment gateway developped by ATOS and has been adopted by several banks
such as Société Générale and Banque Populaire in France. Its setup needs setting up crypted certificates and description files on the server
that cannot be uploaded from the Web interface for security reasons. Please ask a Moodle Shop integrator to operate this setup for you and perform
appropriate testing and service kick-off.
';

$string['pending_followup_text_tpl'] = '
<p>Your purchase has been accepted by our Systempay payment partner. Your ordering will be processed
as soon the payment effective acceptation has been received. You will receive a product activation
email when processed. Thank you for your purchase.</p>
<p>In case your products are not available in the next 48 hours, please contact us with your ordering references
to check your transaction status.</p>
';

$string['success_followup_text_tpl'] = '
<p>Your payment has been confirmed by our payment partner (systemPay). We ae processing your products.</p>
<p>If you cannot access the training platform, please contact our sales service <%%SUPPORT%%>.</p>
';

$string['door_transfer_text_tpl'] = 'This site is going to tranfer you to the payment portal of our banking partner {$a}.
Click on the appropriate payment card type you are going to use.';

$string['england'] = 'England';
$string['france'] = 'France';
$string['germany'] = 'Germany';
$string['spain'] = 'Spain';

$string['cur978'] = 'Euro';
$string['cur840'] = 'US Dollar';
$string['cur826'] = 'British Pound';
$string['cur756'] = 'CH Franc';
$string['cur036'] = 'AU Dollar';
$string['cur124'] = 'CA Dollar';

$string['ce'] = 'Caisse d\'Epargne';
$string['bp'] = 'Banque Populaire';
$string['sg'] = 'Société générale';
