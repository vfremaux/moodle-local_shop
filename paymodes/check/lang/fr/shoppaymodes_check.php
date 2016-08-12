<?php

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
