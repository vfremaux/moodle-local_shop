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
 * @package  shophandlers_std_createcourse
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat CreateCourse ne détient directement aucune donnée relative
 aux utilisateurs.';

$string['handlername'] = 'Création d\'un cours';
$string['pluginname'] = 'Création d\'un cours';

$string['errornocategory'] = 'La catégorie n\'est pas définie';
$string['errorcategorynotexists'] = 'La catégorie d\'id {$a} n\'existe pas';
$string['warningnohandlerusingdefault'] = 'Le modèle de cours par défaut sera utilisé';
$string['errortemplatenocourse'] = 'Le cours modèle {$a} n\'existe pas';
$string['erroralreadyexists'] = 'Ce code d\'identification est déjà utilisé';
$string['errorvalueempty'] = 'Ce champ ne peut pas être vide';
$string['erroralreadyinform'] = 'Ce code d\'identification est déjà utilisé dans cette commande';
$string['warningnoduration'] = 'La durée d\'abonnement n\'est pas précisée. Le produit est fourni pour une durée illimitée.';

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
seront initialisés dès la confirmation automatique
de votre règlement. Vous pourrez vous connecter et bénéficier de vos accès sur vos volumes de travail. Dans le cas contraire
 vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Le compte utilisateur client {$a->username} a été ouvert sur la plate-forme.</p>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre cours est créé et les accès ont été mis en place. Un message mail vous
donne les références d\'accès à votre volume. Vous pouvez accéder directement à ce produit après vous être authentifié.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre cours intitulé "{$a->fullname}" d\'identifiant "{$a->shortname}" est créé. Vous pouvez
y accéder directement pour commencer votre construction après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accéder directement à votre cours</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Le cours "{$a->fullname}" a été créé et les roles donnés au client {$a->username}.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accès au cours</a></p>
';
