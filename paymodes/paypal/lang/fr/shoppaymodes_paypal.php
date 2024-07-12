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
 * @package  shoppaymodes_paypal
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat Paypal ne détient directement aucune donnée relative
aux utilisateurs.';

$string['ipnfortest'] = 'Tester le retour IPN avec cette transaction';
$string['enablepaypal'] = 'Réglement par Paypal';
$string['enablepaypal2'] = 'Réglement par Paypal';
$string['enablepaypal3'] = 'Vous avez choisi de régler via Paypal...';
$string['paypal'] = 'Paypal';
$string['paypalaccepted'] = 'Paiement par Paypal';
$string['paypalmsg'] = 'Merci d\'utiliser Paypal pour vos transactions en ligne';
$string['paypalpaymodeparams'] = 'Paramètres de configuration Paypal';
$string['paypalipnsimulation'] = 'Simulation de paiement Paypal (mode test uniquement)';
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
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour
vérifier votre situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par Paypal. Nous avons procédé à la mise en oeuvre de vos produits.</p>
<p>Consultez votre boite de courriel, nous vous avons envoyé des informations sur votre achat. Si vous ne trouvez pas de
courriel dans votre boite de réception, pensez à consulter votre boite de spam, et vos dossiers de "notifications".</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';

global $CFG;
$string['paypaltest_desc'] = '
Lorsque vous utilisez le mode test, toutes vos transations Paypal sont redirigées vers la Sandbox Paypal. Cet environnement
de test ne produit pas de retour IPN automatiquement et vous devrez le simuler en utilisant la bo^te à outil de développement
de Paypal (Paypal IPN Simulator). Le point d\'entrée du handler de traitement IPN, pour ce site, est à l\'adresse suivante :
'.$CFG->wwwroot.'/local/shop/paymodes/paypal/paypal_ipn.php.';

