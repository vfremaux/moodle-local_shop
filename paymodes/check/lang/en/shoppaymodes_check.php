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
 * @package    shoppaymodes_check
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'The local plugin Shoppaymodes Check does not directly store any personal data
 about any user.';

$string['pluginname'] = 'Bank check';
$string['check'] = 'Check';
$string['enablecheck'] = 'Check payment';
$string['enablecheck2'] = 'Check payment';
$string['enablecheck2'] = 'You choosed check as payment...';

$string['procedure_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> send this document and your check at : <br>
<center>
<div>
<b><%%SELLER%%></b><br>
<%%ADDRESS%%><br>
<%%ZIP%%> <%%CITY%%><br>
<%%COUNTRY%%>
</div>
</center>
';

$string['procedure_text_invoice_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> you have sent the order document and your check at : <br>
<center>
<div>
<b><%%SELLER%%></b><br>
<%%ADDRESS%%><br>
<%%ZIP%%> <%%CITY%%><br>
<%%COUNTRY%%>
</div>
</center>
';

$string['pay_instructions_tpl'] = '
To order, you have to print this document, and to send it with your check. Your order will be send after reception.
';

$string['pay_instructions_invoice_tpl'] = '
To confirm and make this order being processed for you, print the invoice, and to send it by terrestrial mail
with your check. You\'ll get a confirmation mail advice when your products are ready.
';

$string['pending_followup_text_tpl'] = '
<p>We are awaiting your payment reception to activate your products. You will be notified by mail as soon as it has been
processed.</p><p>If your activation seems being late (your standard terrestrial mail delay plus 24 to 48 work hours),
please contact our sales services <%%SUPPORT%%>.</p>
';

$string['print_procedure_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Print <a href="<%%BILL_URL%%>" target="_blank">the printable
version of the document</a>
';

$string['print_procedure_text_invoice_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Print <a href="<%%BILL_URL%%>" target="_blank">the paper
version of the bill</a>
';

$string['success_followup_text_tpl'] = '
<p>Your paiment has been received and checked by our sales service. We have processed your products.</p>
<p>If you fail to access to your training material, contact our sales service <%%SUPPORT%%>.</p>
';
