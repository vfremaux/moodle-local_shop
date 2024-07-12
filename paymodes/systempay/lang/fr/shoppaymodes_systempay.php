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
 * @package     shoppaymodes_systempay
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
if (function_exists('core_tables_exist') && core_tables_exist()) {
    // Be carefull to the moodle install process asking for lang files...
    $config = get_config('local_shop');
}

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat SystemPay ne détient directement aucune donnée
relative aux utilisateurs.';

$string['configsystempaybankbrand'] = '';
$string['configsystempaycountry'] = 'Pays';
$string['configsystempaycurrencycode'] = 'Code Monnaie';
$string['configsystempaymerchantid'] = 'Il s\'agit de l\'identifiant de boutique fourni par le backoffice.';
$string['configsystempayprodcertificqate'] = 'Le certificat pour encoder les soumissions en production';
$string['configsystempayserviceurl'] = 'Url du service de paiement SystemPay';
$string['configsystempaytestcertificate'] = 'Le certificat pour encoder les soumissions en mode test';
$string['configsystempayusesecure'] = '3D Secure est une option du contrat de paiment en ligne qui demande un code de
confirmation de paiement à l\'acheteur par un canal tiers.';
$string['configsystempayuselocaltime'] = 'Si coché, utilise l\'heure locale du serveur pour dater les transations, sinon
prend l\'heure GMT.';
$string['configsystempayalgorithm'] = 'Algorithme pour signature VADS.';
$string['enablesystempay'] = 'System Pay PLUS (Caisse d\'Epargne/Banque Populaire/Société Générale)'; // Config form.
$string['enablesystempay2'] = 'Cartes de crédit (System Pay), un service de la <img valign="top"
src="'.$CFG->wwwroot.'/local/shop/paymodes/systempay/pix/logo_{$a->systempay_bank}.png" />'; // Shop GUI.
$string['errorsystempaynotsetup'] = 'System Pay PLUS n\'est pas configuré et ne peut être utilisé pour payer en ligne.';
$string['systempay'] = 'SystemPay Plus(CE/BP/SG)';
$string['systempaybankbrand'] = 'Banque';
$string['systempaycountry'] = 'Pays';
$string['systempaycurrencycode'] = 'Code Monnaie';
$string['systempaymerchantid'] = 'ID marchand';
$string['systempaypaymodeparams'] = 'Paramètres System Pay PLUS';
$string['systempayprodcertificate'] = 'Certificat de production';
$string['systempayserviceurl'] = 'URL de Service';
$string['systempaytestcertificate'] = 'Certificat de Test';
$string['systempayusesecure'] = 'Activer le service 3D Secure';
$string['systempayuselocaltime'] = 'Utiliser l\'heure locale du serveur';
$string['systempayalgorithm'] = 'Algorithme';

$string['systempaypaymentinvoiceinfo'] = 'SystemPay PLUS (Caisse d\'Epargne/Banque Populaire)';
$string['door_transfer_text_tpl'] = 'Nous allons vous transférer sur l\'interface de paiement <b>sécurisée</b> de notre partenaire
financier {$a}. Cliquez sur le type de carte bancaire que vous allez utiliser pour le paiement.';
$string['pluginname'] = 'Moyen de paiement System Pay PLUS';
$string['systempayinfo'] = 'System Pay PLUS est une interface de paiement développée par ATOS et a été adoptée par certaines banques
françaises telles que la Société Générale et les Banques Populaires . Sa configuration demande la mise en place de certificats
cryptés et de descriptions qui ne peuvent être apportées par l\'interface Web pour des raisons de sécurité. Contactez un intégrateur
spécialisé de Moodle pour effectuer cette mise en oeuvre et les tests qui vont avec.';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement SystemPay Plus. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre
situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par notre service de paiement (systemPay Plus). Nous avons procédé à la mise en oeuvre de vos
produits.</p><p>Consultez votre boite de courriel, nous vous avons envoyé des informations sur votre achat. Si vous ne trouvez
pas de courriel dans votre boite de réception, pensez à consulter votre boite de spam, et vos dossiers de "notifications".</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';

$string['england'] = 'Royaume Uni';
$string['france'] = 'France';
$string['germany'] = 'A1lemagne';
$string['spain'] = 'Espagne';

$string['cur978'] = 'Euro';
$string['cur840'] = 'Dollar US';
$string['cur826'] = 'Livre Sterling (UK)';
$string['cur756'] = 'Franc Suisse';
$string['cur036'] = 'Dollar australien';
$string['cur124'] = 'Dollar Canadien';

$string['ce'] = 'Caisse d\'Epargne';
$string['bp'] = 'Banque Populaire';
$string['sg'] = 'Société générale';
