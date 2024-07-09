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
 * @package   local_shop
 * @subpackage shophandlers
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat EnrolOneCourseMultiple ne détient directement aucune donnée relative
 aux utilisateurs.';

$string['handlername'] = 'Inscription (multiples) à un cours';
$string['pluginname'] = 'Inscription (mulitples) à un cours';

$string['pluginname_desc'] = 'Ce plugin permet la vente d\'une inscription qui peut être répétée plusieurs fois pour le même cours,
 permettant ainsi de disposer de plusieurs unités de produid dans le même contexte de cours support. Un achat de ce produit ne
 bloquera pas le processus d\'achat d\'unités supplémentaires du même produit avec le même cours support. La période de validité
 de l\'inscription sera repoussée à la date la plus lointaine de tous les produits détenus par l\'utilisateur.';

$string['errornocourse'] = 'cours cible non défini';
$string['errorcoursenotexists'] = 'Cours cible {$a} non existant';
$string['errorrole'] = 'Rôle {$a} non existant';
$string['warningroledefaultstoteacher'] = 'Le rôle n\'est pas précisé. "Etudiant" utilisé par défaut.';

$string['productiondata_public'] = '
<p>Votre compte utilisateur a été ouvert sur cette plate-forme. Un courriel vous a été envoyé
pour vous communiquer vos indicatifs d\'accès.</p>
<p>Si vous avez effectué votre paiement en ligne, Vos produits de formation seront initialisés dès la confirmation automatique
de votre règlement. Vous pourrez alors vous connecter et bénéficier de vos accès de formation. Dans le cas contraire vos accès
seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_private'] = '
<p>Votre compte utilisateur a été ouvert sur cette plate-forme.</p>
<p>Votre identifiant : {$a->username}</p>
<p>Un mot de passe vous a été envoyé dans un courriel séparé. <b>Veuillez noter vos coordonnées d\'accès quelque part où vous
 pouvez les retrouver avant de continuer...</b></p> <p>Si vous avez effectué votre paiement en ligne, Vos produits de formation
  seront initialisés dès la confirmation automatique de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Le compte utilisateur client {$a->username} a été ouvert sur la plate-forme.</p>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correspondant. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correspondant. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accéder directement à votre produit</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Les accès client {$a->username} ont été ouverts sur le cours.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accès à la formation</a></p>
';
