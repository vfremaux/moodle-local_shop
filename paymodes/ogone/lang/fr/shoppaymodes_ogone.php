<?php

$string['enableogone'] = 'Réglement par Ingénico/Ogone';
$string['enableogone2'] = 'Réglement par Ingénico/Ogone';
$string['pluginname'] = 'Payment par passerelle Ingénico/Ogone';
$string['ogone'] = 'Ingénico/Ogone';
$string['ogonepaymodeparams'] = 'Paramètres de configuration Ingénico/Ogone';
$string['ogonepaymodeinvoiceinfo'] = 'Vous avez choisi de régler par carte via la passerelle Ingénico/Ogone.';
$string['psid'] = 'Identifiant de compte marchand Ingénico/Ogone';
$string['configpsid'] = 'Cet identifiant vous est fourni par Ingénico/Ogone lors de la création de votre compte marchand.';
$string['secretin'] = 'Clef secrète d\'entrée';
$string['configsecretin'] = 'Cette clef secrète encode les données que vous envoyez à la passerelle. Elle doit être identique à celle définie dans votre compte marchand.';
$string['secretout'] = 'Clef secrète de vérification des données sortantes de passerelle';
$string['configsecretout'] = 'Cette clef secrète vous permet de vérifier les réponses que vous revcevez de passerelle. Elle doit être identique à celle définie dans votre compte marchand.';
$string['paramvar'] = 'Clef de routage multiboutiques';
$string['configparamvar'] = 'Cette clef permet à Ogone de router différement le retour du processus d\'achat (Optionnel - Voir la documentation Ingénico/Ogone)';
$string['logourl'] = 'URL de logo de la page de paiement';
$string['configlogourl'] = 'URL de logo personnalisé sur la page de paiement distante (Elle doit être une URL HTTPS, car les interfaces d\'Ogone sont sécurisées)';

$string['door_transfer_text_tpl'] = '
<p><b>Paiement en ligne par Ingénico/Ogone :</b> 
Nous allons vous transférer sur le portail de paiement sécurisé de notre partenaire financier. 
';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre situation.</p>
<p>Service support : <%%SUPPORT%%></p>
';

$string['print_procedure_text_tpl'] = '
<p>Suivez la procédure Ogone jusqu\'à son terme. Nous vous réservons une facture en fin de paiement.
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par Ogone. Nous avons prcoédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez pas à contacter notre service commercial <%%SUPPORT%%>.
';
