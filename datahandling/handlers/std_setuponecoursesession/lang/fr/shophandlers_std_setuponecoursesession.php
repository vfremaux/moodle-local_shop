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
 * @subpackage  shophandlers_std_setuponecoursesession
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat SetupOneCourseSession ne détient directement aucune donnée relative aux utilisateurs.';

$string['handlername'] = 'Mise en place d\'une session de formation';
$string['pluginname'] = 'Mise en place d\'une session de formation';
$string['errorbadtaget'] = 'Erreur : cours cible non trouvé';
$string['errorcoursenotexists'] = 'Le cours {$a} n\'existe pas';
$string['errorcoursenotenrollable'] = 'Le cours {$a} n\'a pas de méthode d\'inscription adapté';
$string['errorsupervisorrole'] = 'Le rôle {$a} superviseur n\'existe pas';
$string['warningsupervisordefaultstoteacher'] = 'Le rôle superviseur n\'est pas défini. Le rôle "Enseignant non éditeur"
 est pris par défaut.';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Le cours support client est le cours par défaut du bloc';
$string['warningnocustomersupportcourse'] = 'Aucun cours support client défini';
$string['errornocustomersupportcourse'] = 'Le cours {$a} pour support client n\'existe pas ';
$string['coursename'] = 'Cours';
$string['beneficiary'] = 'Bénéficiaire';
$string['freeassign'] = 'Libérer le siège';

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
pouvez les retrouver avant de continuer...</b></p><p>Si vous avez effectué votre paiement en ligne, Vos produits de formation
seront initialisés dès la confirmation automatique de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p>Le compte utilisateur client {$a->username} a été ouvert sur la plate-forme.</p>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correpondant. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correpondant. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accéder directement à votre produit</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Les accès client ont été ouverts sur le cours de test.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accès à la formation</a></p>
';
