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
 * @package  shoppaymodes_stripe_checkout
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat StripeCheckout ne détient directement aucune donnée
 relative aux utilisateurs.';

$string['cancelurl'] = 'URL d\'annulation';
$string['checkout'] = 'Payer maintenant !';
$string['configcancelurl'] = 'Url appelée après exécution du paiement';
$string['configcancelurl'] = 'Url appelée quand le client annule la procédure sur la page de paiement Stripe';
$string['configsecret'] = 'Secret fourni par Stripe pour authentifier les transactions';
$string['configsid'] = 'Identificant de compte Stripe';
$string['configtestsecret'] = 'Secret fourni par Stripe pour authentifier les transactions de test';
$string['configtestsid'] = 'Identificant de compte Stripe de test';
$string['enablestripe_checkout'] = 'Paiement par Stripe Checkout Server';
$string['enablestripe_checkout2'] = 'Paiement par Stripe Checkout Server';
$string['pluginname'] = 'Stripe Checkout Server';
$string['returnurl'] = 'URL de retour';
$string['sid'] = 'Identifiant de compte Stripe';
$string['stripe_checkout'] = 'Stripe Checkout';
$string['stripe_checkoutpaymodeinvoiceinfo'] = 'You have choosen Stripe Checkout  Gateway as payment method.';
$string['stripe_checkoutpaymodeparams'] = 'Stripe Checkout Server configuration options';
$string['secret'] = 'Secret de compte commerçant';
$string['testsecret'] = 'Secret de compte test commerçant';
$string['testsid'] = 'Identifiant de compte Stripe de test';

$string['door_transfer_text_tpl'] = '
<p><b>Paiement en ligne par Ingénico/Ogone :</b>
Nous allons vous transférer sur le portail de paiement sécurisé de notre partenaire financier.
';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour
vérifier votre situation.</p>
<p>Service support : <%%SUPPORT%%></p>
';

$string['print_procedure_text_tpl'] = '
<p>Suivez la procédure Stripe jusqu\'à son terme. Nous vous réservons une facture en fin de paiement.
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par Stripe. Nous avons procédé à la mise en oeuvre de vos produits.</p>
<p>Consultez votre boite de courriel, nous vous avons envoyé des informations sur votre achat. Si vous ne trouvez
pas de courriel dans votre boite de réception, 
pensez à consulter votre boite de spam, et vos dossiers de "notifications".</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez pas à contacter notre service commercial <%%SUPPORT%%>.
';
