<?php

global $CFG;

$string['systempaypaymodeparams'] = 'Paramètres System Pay PLUS';
$string['enablesystempay'] = 'System Pay PLUS (Caisse d\'Epargne)'; // config form
$string['enablesystempay2'] = 'Cartes de crédit (System Pay), un service de la <img valign="top" src="'.$CFG->wwwroot.'/local/shop/paymodes/systempay/pix/logo_ce.png" />'; // shop GUI
$string['systempaycountry'] = 'Pays';
$string['configsystempaycountry'] = 'Pays';
$string['systempaycurrencycode'] = 'Code Monnaie';
$string['configsystempaycurrencycode'] = 'Code Monnaie';
$string['systempayserviceurl'] = 'URL de Service';
$string['configsystempayserviceurl'] = 'Url du service de paiement SystemPay';
$string['systempaypaymentinvoiceinfo'] = 'SystemPay PLUS (Caisse d\'Epargne)';
$string['systempayDoorTransferText'] = 'Nous allons vous transférer sur l\'interface de paiement <b>sécurisée</b> de notre partenaire financier Caisse d\'Epargne. Cliquez sur le type de carte bancaire que vous allez utiliser pour le paiement.';
$string['pluginname'] = 'Moyen de paiement System Pay PLUS';
$string['systempayinfo'] = 'System Pay PLUS est une interface de paiement développée par ATOS et a été adoptée par certaines banques 
françaises telles que la Société Générale et les Banques Populaires . Sa configuration demande la mise en place de certificats
cryptés et de descriptions qui ne peuvent être apportées par l\'interface Web pour des raisons de sécurité. Contactez un intégrateur
spécialisé de Moodle pour effectuer cette mise en oeuvre et les tests qui vont avec.';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement Mercanet. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par notre service de paiement (Mercanet BNP). Nous avons prcoédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';

$string['england'] = 'Royaume Uni';
$string['france'] = 'France';
$string['germany'] = 'A1lemagne';
$string['spain'] = 'Espagne';

$string['cur978'] = 'Euro';
$string['cur840'] = 'Dollar US';
$string['cur756'] = 'Franc Suisse';
$string['cur036'] = 'Dollar australien';
$string['cur124'] = 'Dollar Canadien';
