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
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat PayBox ne détient directement aucune donnée relative aux utilisateurs.';

$string['enablepaybox'] = 'Réglement par Paybox';
$string['enablepaybox2'] = 'Réglement par Paybox';
$string['pluginname'] = 'Moyen de paiement Paybox';
$string['paybox'] = 'Paybox';
$string['payboxpaymodeparams'] = 'Paramètres de configuration Paybox';
$string['payboxpaymodeinvoiceinfo'] = 'Vous avez choisi de régler par Paybox.';

$string['door_transfer_text_tpl'] = '
<p><b>Paiement en ligne par Paybox :</b>
Nous allons vous transférer sur le portail de paiement sécurisé de notre partenaire financier.
';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre situation.</p>
';

$string['print_procedure_text_tpl'] = '
<p>Suivez la procédure Paybox jusqu\'à son terme. Nous vous réservons une facture en fin de paiement.
';

$string['success_followup_text_tpl'] = '
<p>Votre paiement a été confirmé par Paybox. Nous avons prcoédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez pas à contacter notre service commercial <%%SUPPORT%%>.
';
