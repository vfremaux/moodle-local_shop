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
$string['configmercanetAPIurl'] = 'This url is usually provided by Mercanet Integration support';
$string['configmercanetcountry'] = 'Mercanet country';
$string['configmercanetcurrencycode'] = 'Currency code';
$string['configmercanetmerchantid'] = 'Merchant ID. Provided by Mercanet.';
$string['configmercanetprocessortype'] = 'Determines API Processor type to be used (Linux versions)';
$string['enablemercanet'] = 'Credit card (Mercanet)';
$string['enablemercanet2'] = 'Credit card payment';
$string['errorcallingAPI'] = 'Mercanet API Call error :<br/>Executable not found (or not executable) in path : {$a}';
$string['errorcallingAPI2'] = 'Mercanet API error :<br/>Running error : {$a}';
$string['generatingpathfile'] = 'Mercanet path configuration file generation';
$string['makepathfile'] = 'Generate the Pathfile file';
$string['mercanet'] = 'Credit card (Mercanet)';
$string['mercanetAPIurl'] = 'Url of the Mercanet API';
$string['mercanetcountry'] = 'Country code';
$string['mercanetcurrencycode'] = 'Currency code';
$string['mercaneterror'] = 'Mercanet Error : {$a}';
$string['mercanetapierror'] = 'Mercanet API Error';
$string['mercanetmerchantid'] = 'Mercanet Merchant ID';
$string['mercanetpaymodeinvoiceinfo'] = 'You have choosen Mercanet (BNP) as payment method.';
$string['mercanetpaymodeparams'] = 'Mercanet configuration options';
$string['mercanetprocessortype'] = 'Processor type';
$string['pluginname'] = 'Pay Mode Mercanet';
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
<p>Your purchase has been accepted by our Mercanet payment partner. Your ordering will be processed
as soon the payment effective acceptation has been received. You will receive a product activation
email when processed. Thank you for your purchase.</p>
<p>In case your products are not available in the next 48 hours, please contact us with your ordering references
to check your transaction status.</p>
';

$string['success_followup_text_tpl'] = '
<p>Your payment has been confirmed by our payment partner (Mercanet BNP). We ae processing your products.</p>
<p>If you cannot access the training platform, please contact our sales service <%%SUPPORT%%>.</p>
';

$string['door_transfer_text_tpl'] = 'We are up to transfer you on the <b>secure</b> payment service of our banking partner BNP Paribas. Click over the card type you are going to use for payment.';
