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
 * @package     shoppaymodes_monetico
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
if (function_exists('core_tables_exist') && core_tables_exist()) {
    // Be carefull to the moodle install process asking for lang files...
    $config = get_config('local_shop');
}

$string['enablemonetico'] = 'Monetico (Crédit Mutuel)'; // Config form.
$string['enablesystempay2'] = 'Cartes de crédit (Monetico), un service du Crédit Mutuel'; // Shop GUI.
$string['systempay'] = 'Monetico (Crédit Mutuel)';
$string['configmoneticomerchantid'] = 'ID marchand';
$string['configmoneticomerchantid_desc'] = 'ID marchand';
$string['configmoneticoserviceurl'] = 'URL de Service';
$string['configmoneticoserviceurl_desc'] = 'URL de Service';
$string['configmoneticousesecure'] = 'Activer le service 3D Secure';
$string['configmoneticousesecure_desc'] = 'Activer le service 3D Secure';
$string['configmoneticopaymodeparams'] = 'Paramètres Monetico';
$string['configmoneticopaymodeparams_desc'] = 'Paramètres Monetico';
$string['configmoneticoprodcertificate'] = 'Certificat de production';
$string['configmoneticoprodcertificate_desc'] = 'Certificat de production';
$string['configmoneticobankbrand'] = 'Logo Banque';
$string['configmoneticobankbrand_desc'] = 'Logo Banque';
$string['configmoneticocountry'] = 'Pays';
$string['configmoneticocountry_desc'] = 'Pays';
$string['configmoneticocurrencycode'] = 'Devise';
$string['configmoneticocurrencycode_desc'] = 'Devise';

$string['france'] = 'France';
$string['england'] = 'Royaume uni';
$string['germany'] = 'Allemagne';
$string['spain'] = 'Espagne';

$string['cur978'] = 'Euro';
$string['cur840'] = 'Dollar US';
$string['cur826'] = 'Livre Sterling';
$string['cur756'] = 'Franc suisse';
$string['cur036'] = 'Dollar AU';
$string['cur124'] = 'Dollar CA';

$string['cm'] = 'Crédit Mutuel';

$string['moneticopaymentinvoiceinfo'] = 'Monetico (Crédit Mutuel)';
$string['door_transfer_text_tpl'] = 'Nous allons vous transférer sur l\'interface de paiement <b>sécurisée</b> de notre partenaire financier {$a}. Cliquez sur le type de carte bancaire que vous allez utiliser pour le paiement.';
$string['pluginname'] = 'Moyen de paiement Monetico';
$string['systempayinfo'] = 'Monetico est une interface de paiement adoptée par certaines banques
françaises. Sa configuration demande la mise en place de certificats
cryptés et de descriptions qui ne peuvent être apportées par l\'interface Web pour des raisons de sécurité. Contactez un intégrateur
spécialisé de Moodle pour effectuer cette mise en oeuvre et les tests qui vont avec.';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement Monetico. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par notre service de paiement (Monetico). Nous avons prcoédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';
