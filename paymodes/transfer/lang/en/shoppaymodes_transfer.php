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
$string['privacy:metadata'] = 'The local plugin Shoppaymodes Transfer does not directly store any personal data
about any user.';

$string['enabletransfer'] = 'Bank transfer payment';
$string['enabletransfer2'] = 'Bank transfer payment';
$string['enabletransfer3'] = 'You choosed to pay transfering funds...';
$string['transfer'] = 'Bank transfer';
$string['pluginname'] = 'Bank Wired Pay Mode';

$string['pay_instructions_tpl'] = '
To confirm your order, you have to pay via a bank transfer on the indicated account. Your order will be sent when we get a bank
notification, confirming your transfer.
';

$string['pay_instructions_invoice_tpl'] = '
You have paied using fund tranfer.
';

$string['pending_followup_text_tpl'] = '
<p>We are awaiting your payment reception to activate your products. You will be notified by mail as soon as it has been
processed.</p><p>If your activation seems being late (the fund transfer normal delay plus 24 to 48 work hours), please contact
our sales services.</p>
';

$string['print_procedure_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Order a fund transfer from your banking agency to:<br>

<p><b><%%SELLER%%></b> - <%%ADDRESS%%> - <%%ZIP%%> <%%CITY%%><br>

<p><b><u>Banking</u>:</b> <em><%%BANKING%%></em><br>

<p><center><table class="generaltable shop-table">
      <tr>
         <th class="header c0">
            Bank code
         </th>
         <th class="header c1">
            Agency code
         </th>
         <th class="header c2">
            Account number
         </th>
         <th class="header c3 lastcol">
            Key
         </th>
      </tr>
      <tr class="r1">
         <td class="cell c0">
            <%%BANK_CODE%%>
         </td>
         <td class="cell c1">
            <%%BANK_OFFICE%%>
         </td>
         <td class="cell c2">
            <%%BANK_ACCOUNT%%>
         </td>
         <td class="cell c3">
            <%%ACCOUNT_KEY%%>
         </td>
      </tr>
   </table>
   </center></p>

   <p><center><table class="generaltable shop-table">
      <tr>
         <th class="header">
            IBAN (Internationnal Bank Account Number)
         </th>
         <td class="cell">
            &nbsp;<%%IBAN%%>
         </td>
      </tr>
      <tr>
         <th class="header">
            Bank Identification Code (BIC)
         </th>
         <td class="cell">
            &nbsp;<%%BIC%%>
         </td>
      </tr>
      <tr>
         <th class="header">
            Intracommunautary VAT code (TVA)
         </th>
         <td class="cell">
            &nbsp;<%%TVA_EUROPE%%>
         </td>
      </tr>
   </table>
   </center>
</p>
';

$string['print_procedure_text_invoice_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> You have payed by fund transfer from your banking agency to:<br>

<p><b><%%SELLER%%></b> - <%%ADDRESS%%> - <%%ZIP%%> <%%CITY%%><br>

<p><b><u>Banking</u>:</b> <em><%%BANKING%%></em><br>

<p><table border="0" cellspacing="1" class="width500">
      <tr>
         <td class="colhead">
            Bank code
         </td>
         <td class="colhead">
            Agency code
         </td>
         <td class="colhead">
            Account number
         </td>
         <td class="colhead">
            Key
         </td>
      </tr>
      <tr>
         <td class="colvalue">
            <%%BANK_CODE%%>
         </td>
         <td class="colvalue">
            <%%BANK_OFFICE%%>
         </td>
         <td class="colvalue">
            <%%BANK_ACCOUNT%%>
         </td>
         <td class="colvalue">
            <%%ACCOUNT_KEY%%>
         </td>
      </tr>
   </table>
   <br>

   <p><table border="0" cellspacing="5" class="width500">
      <tr>
         <td class="colhead">
            IBAN (Internationnal Bank Account Number)
         </td>
         <td class="colvalue">
            &nbsp;<%%IBAN%%>
         </td>
      </tr>
      <tr>
         <td class="colhead">
            Bank Identification Code (BIC)
         </td>
         <td class="colvalue">
            &nbsp;<%%BIC%%>
         </td>
      </tr>
      <tr>
         <td class="colhead">
            Intracommunautary VAT code (TVA)
         </td>
         <td class="colvalue">
            &nbsp;<%%TVA_EUROPE%%>
         </td>
      </tr>
   </table>
</p>
';

$string['success_followup_text_tpl'] = '
<p>Your paiment has been confirmed by your payment operator. We have processed to the realisation of your products.</p>
<p>If you fail to access to your training material, contact our sales service <%%SUPPORT%%>.</p>
';
