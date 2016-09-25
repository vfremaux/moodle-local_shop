<?php

$string['ipnfortest'] = 'Tester le retour IPN avec cette transaction';
$string['enablepaypal'] = 'Réglement par Paypal';
$string['enablepaypal2'] = 'Réglement par Paypal';
$string['enablepaypal3'] = 'Vous avez choisi de régler via Paypal...';
$string['paypal'] = 'Paypal';
$string['paypalmsg'] = 'Merci d\'utiliser Paypal pour vos transactions en ligne';
$string['paypalpaymodeparams'] = 'Paramètres de configuration Paypal';
$string['paypalsellertestname'] = 'Compte Paypal vendeur test (sandbox)';
$string['paypalsellername'] = 'Compte Paypal vendeur';
$string['pluginname'] = 'Moyen de paiement Paypal';
$string['selleritemname'] = 'Service de vente';
$string['sellertestitemname'] = 'Service de vente (mode test)';
$string['configpaypalsellername'] = 'Doit être l\'identifiant de compte Paypal du commerçant';
$string['configselleritemname'] = 'Un label identifiant la transaction auprès de Paypal';
$string['paypalmsg'] = 'Effectuez vos paiements via PayPal : une solution rapide, gratuite et sécurisée !';
$string['paypalpaymodeinvoiceinfo'] = 'Vous avez choisi de régler par Paypal.';

$string['door_transfer_tpl'] = '
<p><b>Paiement en ligne par Paypal :</b> 
Nous allons vous transférer sur le portail de paiement sécurisé de notre partenaire financier. 
';

$string['print_procedure_text_tpl'] = '
<p>Suivez la procédure Paypal jusqu\'à son terme. Nous vous réservons une facture en fin de paiement.
';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par Paypal. Nous avons prcoédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';