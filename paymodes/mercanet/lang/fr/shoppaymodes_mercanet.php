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
 * @package    shoppaymodes_mercanet
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat Mercanet ne détient directement aucune donnée relative aux utilisateurs.';

$string['card'] = 'Carte bleue';
$string['configmercanetAPIurl'] = 'Cette Url est donnée par le support d\'Intégration Mercanet';
$string['configmercanetcountry'] = 'Pays d\'usage';
$string['configmercanetcurrencycode'] = 'Code devise Mercanet';
$string['configmercanetmerchantid'] = 'Numéro de contrat marchand. Ce numéro est fourni lors de la souscription
 Mercanet.';
$string['configmercanetpathbin'] = 'Chemin physique des exécutables mercanet';
$string['configmercanetprocessortype'] = 'Type de processeur pour l\'implémntation Linux';
$string['configmercanetlogofilename'] = 'Nom du fichier de logo téléchargé sur la console de gestion Mercanet';
$string['emptymessage'] = 'Message de retour Mercanet vide.';
$string['enablemercanet'] = 'Réglement par carte bleue (Mercanet)';
$string['enablemercanet2'] = 'Réglement par carte bleue (Mercanet)';
$string['errorcallingAPI'] = 'Erreur d\'appel de L\'API Mercanet<br/>Exécutable non trouvé dans le chemin : {$a}';
$string['errorcallingAPI2'] = 'Erreur d\'appel de L\'API Mercanet<br/>Erreur d\'exécution : {$a}';
$string['generatingpathfile'] = 'Génération du fichier des chemins de configuration Mercanet';
$string['makepathfile'] = 'Générer le fichier Pathfile';
$string['mercanet'] = 'Carte bleue (Mercanet)';
$string['mercanetAPIurl'] = 'Mercanet API Url';
$string['mercanetapierror'] = 'Erreur de l\'API Mercanet';
$string['mercanetcertificateid'] = 'ID Certificat Mercanet';
$string['mercanetcountry'] = 'Code pays';
$string['mercanetcurrencycode'] = 'Code devise';
$string['mercaneterror'] = 'Erreur  : {$a}';
$string['mercanetapierror'] = 'Erreur de l\'API Mercanet';
$string['mercanetmerchantid'] = 'ID commerçant Mercanet';
$string['mercanetpaymodeinvoiceinfo'] = 'Vous avez choisi de régler par notre partenaire Mercanet (BNP).';
$string['mercanetpaymodeparams'] = 'Paramètres de configuration Mercanet';
$string['mercanetprocessortype'] = 'Type de processeur';
$string['mercanetlogofilename'] = 'Logo';
$string['pluginname'] = 'Moyen de paiement Mercanet';
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
<p>Votre transaction a été acceptée chez le partenaire de paiement Mercanet. Votre commande sera automatiquement
exécutée dès la réception de la notification d\'acceptation du réglement. Vous recevrez un dernier
courriel d\'activation dès ce moment. Merci de votre achat.</p>
<p>Au cas où l\'activation de votre achat n\'aurait pas eu lieu dans les prochaines 48 heures, contactez-nous pour vérifier
votre situation.</p>
';

$string['success_followup_text_tpl'] = '
<p>... Votre paiement a été confirmé par notre service de paiement (Mercanet BNP). Nous avons procédé à la mise en oeuvre de
vos produits.</p><p>Consultez votre boite de courriel, nous vous avons envoyé des informations sur votre achat. Si vous ne
trouvez pas de courriel dans votre boite de réception, pensez à consulter votre boite de spam, et vos dossiers de
"notifications".</p><p>Si vous éprouvez des difficultés d\'accès, n\'hésitez pas à contacter notre service commercial
<%%SUPPORT%%>.</p>
';

$string['door_transfer_text_tpl'] = 'Nous allons vous transférer sur l\'interface de paiement <b>sécurisée</b> de notre
partenaire financier BNP Paribas. Cliquez sur le type de carte bancaire que vous allez utiliser pour le paiement.';
