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

$string['card'] = 'Carte bleue';
$string['configsherlocksAPIurl'] = 'Cette Url est donnée par le support d\'Intégration sherlocks';
$string['configsherlockscountry'] = 'Pays d\'usage';
$string['configsherlockscurrencycode'] = 'Code devise sherlocks';
$string['configsherlocksmerchantid'] = 'Numéro de contrat marchand. Ce numéro est fourni lors de la souscription sherlocks.';
$string['configsherlockspathbin'] = 'Chemin physique des exécutables sherlocks';
$string['configsherlocksprocessortype'] = 'Type de processeur pour l\'implémntation Linux';
$string['emptymessage'] = 'Message de retour sherlocks vide.';
$string['enablesherlocks'] = 'Réglement par carte bleue (Sherlocks)';
$string['enablesherlocks2'] = 'Réglement par carte bleue (Sherlocks)';
$string['errorcallingAPI'] = 'Erreur d\'appel de L\'API sherlocks<br/>Exécutable non trouvé dans le chemin : {$a}';
$string['errorcallingAPI2'] = 'Erreur d\'appel de L\'API sherlocks<br/>Erreur d\'exécution : {$a}';
$string['generatingpathfile'] = 'Génération du fichier des chemins de configuration sherlocks';
$string['makepathfile'] = 'Générer le fichier Pathfile';
$string['sherlocks'] = 'Carte bleue (sherlocks)';
$string['sherlocksAPIurl'] = 'sherlocks API Url';
$string['sherlocksapierror'] = 'Erreur de l\'API sherlocks';
$string['sherlockscertificateid'] = 'ID Certificat sherlocks';
$string['sherlockscountry'] = 'Code pays';
$string['sherlockscurrencycode'] = 'Code devise';
$string['sherlockserror'] = 'Erreur  : {$a}';
$string['sherlocksapierror'] = 'Erreur de l\'API sherlocks';
$string['sherlocksmerchantid'] = 'ID commerçant sherlocks';
$string['sherlockspaymodeinvoiceinfo'] = 'Vous avez choisi de régler par notre partenaire Sherlocks (LCL).';
$string['sherlockspaymodeparams'] = 'Paramètres de configuration sherlocks';
$string['sherlocksprocessortype'] = 'Type de processeur';
$string['pluginname'] = 'Moyen de paiement sherlocks';
$string['continueaftersuccess'] = 'Continuer après succès payment';
$string['continueafterfailure'] = 'Continuer après echec payment';
$string['continueaftersoldout'] = 'Continuer après production (postprod)';
$string['gotestipn'] = 'Déclencher manuellement le processus IPN';

$string['france'] = 'France';
$string['belgium'] = 'Belgique';
$string['england'] = 'Royame Uni';
$string['germany'] = 'Allemagne';
$string['spain'] = 'Espagne';

$string['cur978'] = 'Euros';
$string['cur840'] = 'Dollar Américain';
$string['cur756'] = 'Franc Suisse';
$string['cur826'] = 'Livre Sterling';
$string['cur124'] = 'Dollar Canadien';
$string['cur949'] = 'Nouvelle Livre Turque';
$string['cur036'] = 'Dollar Australien';
$string['cur554'] = 'Dollar Néo-Zélandais';
$string['cur578'] = 'Couronne Norvégienne';
$string['cur986'] = 'Real brésilien';
$string['cur032'] = 'Peso Argentin';
$string['cur116'] = 'Riel';
$string['cur901'] = 'Dollar de Taiwan';
$string['cur752'] = 'Couronne Suédoise';
$string['cur208'] = 'Couronne Danoise';
$string['cur702'] = 'Dollar de Singapour';

$string['pending_followup_text_tpl'] = '
<p>Votre transaction a été acceptée chez le partenaire de paiement Sherlocks (LCL). Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier votre situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>... Votre paiement a été confirmé par notre service de paiement (sherlocks LCL). Nous avons procédé à la mise en oeuvre de vos produits.</p>
<p>Si vous éprouvez des difficultés d\'accès, n\'hésitez
pas à contacter notre service commercial <%%SUPPORT%%>.</p>
';

$string['door_transfer_text_tpl'] = 'Nous allons vous transférer sur l\'interface de paiement <b>sécurisée</b> de notre partenaire
financier LCL Paribas. Cliquez sur le type de carte bancaire que vous allez utiliser pour le paiement.';
