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
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat Transfer ne détient directement aucune donnée relative aux utilisateurs.';

$string['enabletransfer'] = 'Réglement par virement bancaire';
$string['enabletransfer2'] = 'Réglement par virement bancaire';
$string['enabletransfer3'] = 'Vous avez choisi de régler par virement bancaire...';
$string['transfer'] = 'Virement bancaire';
$string['pluginname'] = 'Moyen de paiement Virement Bancaire';

$string['pay_instructions_tpl'] = '
Pour passer votre commande, il vous suffit d\'effectuer directement votre règlement par virement bancaire (<b>sauf swift</b>) sur le compte indiqué, et de valider le bon de commande ci-dessous. Votre commande sera exécutée dès notification de votre virement par notre banque.
';

$string['pay_instructions_invoice_tpl'] = '
Pour passer votre commande, il vous suffit d\'effectuer directement votre règlement par virement bancaire (<b>sauf swift</b>) sur le compte indiqué, et de valider la facture proforma ci-dessus. Votre commande sera exécutée dès notification de votre virement par notre banque.
';

$string['pending_followup_text_tpl'] = '
<p>Nous attendons réception de vore réglement pour activer votre achat. Vous recevrez un mail de confirmation dès ce moment.</p>
<p>Si votre activation tarde à venir (le temps de notification de votre virement plus un temps de traitement de 24 à 48 heures), n\'hésitez
pas à contacter notre service commercial.</p>
';

$string['print_procedure_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Effectuez auprès de votre banque votre virement aux coordonnées suivantes :<br>

<p><b><%%SELLER%%></b> - <%%ADDRESS%%> - <%%ZIP%%> <%%CITY%%><br>

<p><b><u>Domiciliation bancaire</u> :</b> <em><%%BANKING%%></em><br>

<p><center><table class="generaltable shop-table">
      <tr>
         <th class="header c0">
            Code banque
         </th>
         <th class="header c1">
            Guichet
         </th>
         <th class="header c2">
            N° de compte
         </th>
         <th class="header c3 lastcol">
            Clé
         </th>
      </tr>
      <tr>
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

   <p><center><table border="0" cellspacing="5" class="width500">
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
            N° d\'Identification Intercommunautaire (TVA)
         </td>
         <td class="colvalue">
            &nbsp;<%%TVA_EUROPE%%>
         </td>
      </tr>
   </table>
   </center>
</p>
';

$string['print_procedure_text_invoice_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Effectuez auprès de votre banque votre virement aux coordonnées suivantes :<br>

<p><b><%%SELLER%%></b> - <%%ADDRESS%%> - <%%ZIP%%> <%%CITY%%><br>

<p><b><u>Domiciliation bancaire</u> :</b> <em><%%BANKING%%></em><br>

<p><table border="0" cellspacing="1" class="width500">
      <tr>
         <td class="colhead">
            Code banque
         </td>
         <td class="colhead">
            Guichet
         </td>
         <td class="colhead">
            N° de compte
         </td>
         <td class="colhead">
            Clé
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
            N° d\'Identification Intercommunautaire (TVA)
         </td>
         <td class="colvalue">
            &nbsp;<%%TVA_EUROPE%%>
         </td>
      </tr>
   </table>
</p>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par le gestionnaire de votre compte. Nous avons procédé à la mise en oeuvre de vos produits.</p>
<p>Consultez votre boite de courriel, nous vous avons envoyé des informations sur votre achat. Si vous ne trouvez pas de courriel dans votre boite de réception, 
pensez à consulter votre boite de spam, et vos dossiers de "notifications".</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';
