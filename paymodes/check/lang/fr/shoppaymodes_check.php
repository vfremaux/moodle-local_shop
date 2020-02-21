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

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat Check ne détient directement aucune donnée relative aux utilisateurs.';

$string['pluginname'] = 'Réglement par chèque bancaire';
$string['check'] = 'Chèque bancaire';
$string['enablecheck'] = 'Réglement par chèque';
$string['enablecheck2'] = 'Réglement par chèque';

$string['procedure_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> envoyez ce bon de commande avec votre chèque de règlement à : <br>
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
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> envoyez cette facture proforma avec votre chèque de règlement à : <br>
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
Pour passer commande, il vous suffit d\'imprimer le bon de commande ci-après, et de nous l\'envoyer par courrier postal avec votre chèque de règlement. Votre commande vous sera envoyée dès réception.
';

$string['pay_instructions_invoice_tpl'] = '
Pour confirmer définitivement et faire exécuter cette commande, il vous suffit d\'imprimer cette facture proforma, et de nous l\'envoyer par courrier postal avec votre chèque de règlement. Votre commande vous sera envoyée dès réception.
';

$string['pending_followup_text_tpl'] = '
<p>Nous attendons réception de vore réglement pour activer votre achat. Vous recevrez un mail de confirmation dès ce moment.</p>
<p>Si votre activation tarde à venir (le temps d\'acheminement de votre courrier plus un temps de traitement de 24 à 48 heures), n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';

$string['print_procedure_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Imprimez <a href="Javascript:document.bill.submit();">la version imprimable du bon de commande</a>
';

$string['print_procedure_invoice_text_tpl'] = '
<p><span class="procedureOrdering"><%%PROC_ORDER%%></span> Imprimez <a href="Javascript:document.bill.submit();">la version imprimable de la facture proforma</a>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par le responsable de votre compte client. Nous avons prcoédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';
