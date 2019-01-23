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

$string['card'] = 'Credit card';
$string['configsherlocksAPIurl'] = 'This url is usually provided by sherlocks Integration support';
$string['configsherlockscountry'] = 'sherlocks country';
$string['configsherlockscurrencycode'] = 'Currency code';
$string['configsherlocksmerchantid'] = 'Merchant ID. Provided by sherlocks.';
$string['configsherlocksprocessortype'] = 'Determines API Processor type to be used (Linux versions)';
$string['enablesherlocks'] = 'Credit card (Sherlocks LCL)';
$string['enablesherlocks2'] = 'Credit card payment';
$string['errorcallingAPI'] = 'sherlocks API Call error :<br/>Executable not found (or not executable) in path : {$a}';
$string['errorcallingAPI2'] = 'sherlocks API error :<br/>Running error : {$a}';
$string['generatingpathfile'] = 'sherlocks path configuration file generation';
$string['makepathfile'] = 'Generate the Pathfile file';
$string['sherlocks'] = 'Credit card (sherlocks)';
$string['sherlocksAPIurl'] = 'Url of the sherlocks API';
$string['sherlockscountry'] = 'Country code';
$string['sherlockscurrencycode'] = 'Currency code';
$string['sherlockserror'] = 'sherlocks Error : {$a}';
$string['sherlocksapierror'] = 'sherlocks API Error';
$string['sherlocksmerchantid'] = 'sherlocks Merchant ID';
$string['sherlockspaymodeinvoiceinfo'] = 'You have choosen sherlocks (LCL) as payment method.';
$string['sherlockspaymodeparams'] = 'sherlocks configuration options';
$string['sherlocksprocessortype'] = 'Processor type';
$string['pluginname'] = 'Pay Mode sherlocks';
$string['continueaftersuccess'] = 'Continue after success';
$string['continueafterfailure'] = 'Continue after failure';
$string['continueaftersoldout'] = 'Continue after soldout';
$string['gotestipn'] = 'Trigger IPN processing manually';

$string['france'] = 'France';
$string['belgium'] = 'Belgium';
$string['england'] = 'England';
$string['germany'] = 'Germany';
$string['spain'] = 'Spain';

$string['cur978'] = 'Euros';
$string['cur840'] = 'US Dollar';
$string['cur756'] = 'Switz Franc';
$string['cur826'] = 'English Pound';
$string['cur124'] = 'Canadian Dollar';
$string['cur949'] = 'New Turkish Pound';
$string['cur036'] = 'Austalian Dollar';
$string['cur554'] = 'New-Zelander Dollar';
$string['cur578'] = 'Norvegian Crown';
$string['cur986'] = 'Brazilian Real';
$string['cur032'] = 'Argentinian Peso';
$string['cur116'] = 'Riel';
$string['cur901'] = 'Taiwanee Dollar';
$string['cur752'] = 'Sweden Crown';
$string['cur208'] = 'Danish Crown';
$string['cur702'] = 'Singapore Dollar';

$string['pending_followup_text_tpl'] = '
<p>Your purchase has been accepted by our sherlocks payment partner. Your ordering will be processed
as soon the payment effective acceptation has been received. You will receive a product activation
email when processed. Thank you for your purchase.</p>
<p>In case your products are not available in the next 48 hours, please contact us with your ordering references
to check your transaction status.</p>
';

$string['success_followup_text_tpl'] = '
<p>Your payment has been confirmed by our payment partner (sherlocks LCL). We ae processing your products.</p>
<p>If you cannot access the training platform, please contact our sales service <%%SUPPORT%%>.</p>
';

$string['door_transfer_text_tpl'] = 'We are up to transfer you on the <b>secure</b> payment service of our banking partner LCL.
Click over the card type you are going to use for payment.';
