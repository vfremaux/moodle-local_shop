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
$string['privacy:metadata'] = 'The local plugin Shoppaymodes PublicMandate does not directly store any personal data about any user.';

$string['enablepublicmandate'] = 'Public mandate';
$string['enablepublicmandate2'] = 'Public mandate';
$string['enablepublicmandate3'] = 'You choosed to pay with a public mandate...';
$string['publicmandate'] = 'Public mandate';
$string['pluginname'] = 'Public mandate paymode';

$string['pay_instructions_tpl'] = '
To confirm your order, you need provide the public mandate ID and upload the pdf copy of the public purchase mandate. Your products will be immediately activated.
';

$string['pay_instructions_invoice_tpl'] = '
You have paied with a public purchase mandate.
';

$string['print_procedure_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Please prepare the mandate ID you got from your administration and a digital version of the original purchase order.

';

$string['success_followup_text_tpl'] = '
<p>Your purchase order has been registered. We have processed your products for immediate availablity.</p>
<p>If you fail to access to your training material, contact our sales service <%%SUPPORT%%>.</p>
';
