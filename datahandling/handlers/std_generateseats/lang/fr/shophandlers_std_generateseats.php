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
 * @package shophandlers_std_generateseats
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat GenerateSeats ne détient directement aucune donnée relative
 aux utilisateurs.';

$string['handlername'] = 'Ajout de sièges non affectés';
$string['pluginname'] = 'Ajout de sièges non affectés';

$string['allcourses'] = 'Tous les cours';
$string['assignavailableseat'] = 'Assigner un siège disponible';
$string['assignedto'] = '<b>Attribué à :</b> {$a}';
$string['assigninstructions'] = 'Ce siège est actuellement non assigné. vous pouvez choisir de l\'assigner à un apprenant
sous votre responsablité. Si l\'apprenant est déjà inscrit dans ce cours, vous serez averti et pourrez à nouveau faire une
nouvelle attribution.';
$string['assignseat'] = 'Attribuer le siège';
$string['assignseatlocked'] = '<span class="error">L\'assignation du siège est verrouillée par des activités de l\'utilisateur
dans le cours.</span>';
$string['backtocourse'] = 'Revenir à l\'espace client';
$string['enabledcourses'] = 'Modules autorisés';
$string['errornoallowedcourses'] = 'Le produit semble mal configuré et ne semble pas avoir de cours désigné pour affecter
des apprenants';
$string['errornocustomersupportcourse'] = 'Le cours espace client {$a} n\'existe pas';
$string['errorsupervisorrole'] = 'Le rôle superviseur {$a} n\'existe pas';
$string['enrolinstructions'] = '
Pour le moment, aucun apprenant n\'est encore enregistré sous votre responsabilité. Vous devez d\'abord importer des
utilisateurs pour pouvoir les attribuer aux sièges dont vous disposez. Utilisez le (TODO) lien suivant. Vous pourrez
utiliser un simple fichier texte pour donner les informations nécessaires.
';

$string['incourse'] = '<b>Dans le module :</b> [{$a->shortname}] {$a->fullname}';
$string['seatalreadyassigned'] = 'Désolé ! Il semble que {$a->user} soit déjà inscrit dans le cours {$a->course}.
Vous n\'allez pas "brûler" un siège pour ça ! Choisissez une nouvelle affectation pour ce siège.';
$string['seatassigned'] = 'Bravo et merci ! Vous avez inscrit {$a->user} au cours {$a->course}. Une confirmation
va être envoyée à votre apprenant. Ce produit peut être réattribué tant que votre apprenant ne s\'est pas manifesté
dans le cours. Le produit sera consommé définitivement au premier signe d\'activité de votre apprenant dans le module.';
$string['seatreleased'] = 'Ce siège est libéré. Vous pouvez le réassigner à une autre personne.';
$string['supervisor'] = 'Superviseur de formation.';
$string['unassignseat'] = 'Libérer le siège';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Le cours de support client est celui par défaut';
$string['warningemptycourselist'] = 'Aucune liste de cours (courselist) n\'est définie, les sièges générés pourront être assignés à tous les cours visible de la plate-forme.';
$string['warningnocustomersupportcourse'] = 'Aucun espace client (customersupport) défini';
$string['warningonecoursenotexists'] = 'Certains cours ({$a}) définis dans la liste d\'autorisation n\'existent pas';
$string['warningpacksizedefaultstoone'] = 'Le nombre de sièges (packsize) n\'est pas défini, un siège par défaut';
$string['warningsupervisordefaultstoteacher'] = 'Le rôle superviseur n\'est pas défini. "Enseignant non éditeur" est utilisé à la place.';

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
<p>Un mot de passe vous a été envoyé dans un courriel séparé. <b>Veuillez noter vos coordonnées d\'accès quelque part où vous pouvez les retrouver avant de continuer...</b></p>
<p>Si vous avez effectué votre paiement en ligne, Vos produits de formation seront initialisés dès la confirmation automatique
de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p>Le compte utilisateur client {$a->username} a été ouvert sur la plate-forme.</p>
';

$string['productiondata_created_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a->seats} sièges à affecter ont été ajoutés à votre <a href="{$a->customersupporturl}">compte client</a>.</p>
';

$string['productiondata_created_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a->seats} sièges à affecter ont été ajoutés à votre compte client. Vous pouvez les affecter en vous rendant
sur votre espace support client.</p>
<p><a href="{$a->customersupporturl}">Accéder directement à votre support client</a></p>
';

$string['productiondata_created_public_no_support'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a->seats} sièges à affecter ont été ajoutés à votre compte client.</p>
';

$string['productiondata_created_private_no_support'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a->seats} sièges à affecter ont été ajoutés à votre compte client. Vous pouvez les affecter en vous rendant
sur votre espace support client.</p>
';

$string['productiondata_created_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>{$a->seats} sièges pont été ajoutés au compte client de {$a->username}.</p>
';

$string['seatassigned_title'] = 'Un nouveau cours est ouvert pour vous : {$a} !';

$string['seatassigned_mail'] = '
<p>Votre référent vous a inscrit sur le cours <a href="{$a->url}">{$a->course}</a>.</p>
<p>vous pouvez vous y connecter dès à présent avec les identifiants que vous avez reçu précédemment.</p>
';
