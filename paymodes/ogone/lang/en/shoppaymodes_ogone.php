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
 * @package    shoppaymodes_ogone
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shoppaymodes Ogone does not directly store any personal data
about any user.';

$string['enableogone'] = 'Ogone/Ingenico Gateway payment';
$string['enableogone2'] = 'Ogone/Ingenico Gateway payment';
$string['pluginname'] = 'Ogone/Ingenico Pay Mode';
$string['ogone'] = 'Ogone/Ingenico';
$string['ogonepaymodeparams'] = 'Ogone/Ingenico configuration options';
$string['ogonoepaymodeinvoiceinfo'] = 'You have choosen Ogone/Ingenico Gateway as payment method.';
$string['psid'] = 'Ogone/Ingenico Merchant Idendifier';
$string['configpsid'] = 'Ogone/Ingenico Merchant Idendifier';
$string['secretin'] = 'Secret glue for gateway input';
$string['configsecretin'] = 'This glue is used to secure the transfer of your merchant data to the ogone gateway.
It needs to be identical to the secret you have setup in the ogone account backoffice.';
$string['secretout'] = 'Secret glue for gateway output';
$string['configsecretout'] = 'This glue is used to check the answers of the ogone gateway. It needs to be identical
to the secret you have setup in the ogone account backoffice.';
$string['paramvar'] = 'Multiple Shop diverter info';
$string['configparamvar'] = 'This is to be used in case you have multiple shops hosted on the same facility
(See Ingenico/Ogone Integration Documentation)';
$string['logourl'] = 'Logo URL';
$string['configlogourl'] = 'An URL to your logo for the remote payment page. (must be an HTTPS url as ogone payment service
is secured)';

$string['door_transfer_tpl'] = '
<p><b>Card o online payment through Ogone/Ingenico Financial Services:</b>
We will transfer you on the portal of Ingenico Collect
';

$string['pending_followup_text_tpl'] = '
<p>Your transaction has been accepted by our paiement partner. Your order will be automatically processed
on reception of the validation notification from your account holder. You will ne sent a last activation email
when done. Thank you again for your purchase.</p>

<p>In case the activation has not occured in the next 48 hours, contact us to check your situation.</p>
';

$string['print_procedure_text_tpl'] = '
<p>Follow the Ingenico process till the the end. We will provide an invoice after paiement.
';

$string['success_followup_text_tpl'] = '
<p>Your transaction has been accepted by our paiement partner. Your order will be automatically processed
on reception of the validation notification from your account holder. You will ne sent a last activation email
when done. Thank you again for your purchase.</p>

<p>In case the activation has not occured in the next 48 hours, contact our sales service <%%SUPPORT%%>.</p>
';
