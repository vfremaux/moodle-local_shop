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
 * Lang for discounts
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['adddiscount'] = 'Add discount instance';
$string['applieddiscounts'] = 'Discounts';
$string['applyon'] = 'Scope';
$string['applydata'] = 'Application data';
$string['argument'] = 'Argument';
$string['editdiscount'] = 'Edit discount instance';
$string['discountinstitutionmatch'] = 'By Institution Match';
$string['discountlongtimecustomer'] = 'Long time customers';
$string['discountsuccessfullordernum'] = 'Order num reached';
$string['discountorderamount'] = 'Order amount reached (current order)';
$string['discountunconditional'] = 'Unconditional';
$string['discountusercapability'] = 'User capability';
$string['discountoffercode'] = 'Offer code';
$string['multiplediscountoffercode'] = 'Multiple Offer codes';
$string['partnerdiscount'] = 'Partner discount';
$string['partnermultiplediscountoffercode'] = 'Partner bound Multiple Offer codes';
$string['discountenabled'] = 'Discount enabled';
$string['discountname'] = 'Discount name';
$string['discounttype'] = 'Discount algorithm';
$string['discountruledata'] = 'Specific data for the algorithm';
$string['discountapplieson'] = 'Discount scope';
$string['discountapplydata'] = 'Specific discount scope data';
$string['errordiscountnameexistsinshop'] = 'Error: Name already used in this shop instance.';
$string['erroremptydiscountitemlist'] = 'Error: discount scope is not full bill and item list is empty.';
$string['errordiscount:badratioformat'] = 'Error: Ratio is not numeric';
$string['errordiscount:emptycode'] = 'Error: Offer Code empty';
$string['errordiscount:notenougharguments'] = 'Error: Not enough arguments on definition line';
$string['multipleratios'] = 'Multiple (see attributes)';
$string['nodiscounts'] = 'No discount defined';
$string['newdiscountinstance'] = 'New discount instance';
$string['onitemlist'] = 'Selected item list';
$string['itemlist'] = 'Item list';
$string['onbill'] = 'Full order range';
$string['ratio'] = 'Ratio';
$string['enabled'] = 'Enabled';
$string['operator'] = 'Operator';
$string['accumulate'] = 'Accumulates (default)';
$string['takeover'] = 'Takes over (and stops chain)';
$string['stopchainifapplies'] = 'Stop chain if applies';
$string['stopchainifnotapplies'] = 'Stop chains if not applies';
$string['entercode'] = 'enter code here';
$string['codeverified'] = 'Code has been verified and addresses an applicable discount';
$string['codefailed'] = 'Code could not be verified as matching an applicable discount';

$string['ratio_help'] = 'Percentage of discount over the discount scope. In same cases (depending on algorithm choice and specific data), this rate has no effect and is delegated to more specific options of the choosen algorithm.';
$string['applydata_help'] = '
A discount may be applied to the full available catalog, or only a selection of products.
Select products that will be allowed for the discount application.
';


$string['type_help'] = '
<h3>Discount algorithms:</h3>

<ul>
<li><b>Unconditional</b> : Discount applies on all orders.</li>
<li><b>Institution match</b> : Discount applies on users that can be checked for an institution (ruledata -- institution).</li>
<li><b>Long time customers</b> : Discount applies for customer moodle accounts older than (ruledata -- days).</li>
<li><b>Num of orders</b> : Discount applies for identified customers having at least (ruledata -- integer) successfull orders in their account.</li>
<li><b>Order amount reached</b> : Discount applies if order amount reaches (ruledata -- number).</li>
<li><b>User capability</b> : Discount applies if the identified moodle user has some capability (ruledata -- json structure as {"capability": "<capname>"[, "contextid":"<contextid>"]}).</li>
<li><b>Offer code</b> : Discount applies if the user enters the required offer code.</li>
<li><b>Multiple offer code</b> : Discount applies if the user enters one of the required offer that maps to some discount ratio.</li>
<li><b>Partners multiple offer code</b> : Discount applies if the user enters one of the required offer that maps to some discount ratio. the bill is tagged to the associated parner ID.</li>
</ul>
';

$string['operator_help'] = '
<h3>Discount stack operation:</h3>

<p>Tells what is done for the discount when step is evaluated in order of the discount stack:</p>
<ul>
<li><b>Accumulates:</b> accumulates the discount over the previous result</li>
<li><b>Take over:</b> the discount is applied as replacement of the previous result. All preceeding agreed discounts are removed. The discount evaluation is stopped and resumed.</li>
<li><b>Stop when applies:</b> the discount is applied in accumulative mode, then the discount evaluation is stopped and resumed if applicability was sucessfull.</li>
<li><b>Stop when NOT applies:</b> the discount is applied in accumulative mode, then the discount evaluation is stopped and resumed if applicability failed.</li>
</ul>';
