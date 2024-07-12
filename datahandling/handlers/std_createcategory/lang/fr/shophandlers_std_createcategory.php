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
 * @package  shophandlers_std_registeredproduct
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat CreatCategory ne détient directement aucune donnée relative
 aux utilisateurs.';

$string['handlername'] = 'Création d\'une catégorie de cours';
$string['pluginname'] = 'Création d\'une catégorie de cours';

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
<p><b>TXID: {$a->txid}</b></p>
<p>Le compte utilisateur client {$a->username} a été ouvert sur la plate-forme.</p>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre catégorie de cours a été créée à votre usage. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
';

$string['productiondata_assign_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre catégorie de cours "{$a->catname}" a été créée à votre usage. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/category.php?id={$a->catid}">Accéder directement à votre produit</a></p>
';

$string['productiondata_assign_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>La catégorie {$a->catname} a été créée. Les accès client ont été ouverts sur la catégorie en Créateur de cours.</p>
<p><a href="'.$CFG->wwwroot.'/course/category.php?id={$a->catid}">Accès à la catégorie</a></p>
';
