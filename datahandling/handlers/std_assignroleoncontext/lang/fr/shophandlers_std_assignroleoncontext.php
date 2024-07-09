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
 * @package      local_shop
 * @subpackage   shophanlders_std_assignroleoncontext
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat AssignRoleOnContext ne détient directement aucune donnée relative
 aux utilisateurs.';


$string['handlername'] = 'Assignation de rôle sur contexte';
$string['pluginname'] = 'Assignation de rôle sur contexte';

$string['errormissingcontextlevel'] = 'Niveau de contexte non défini';
$string['errorunsupportedcontextlevel'] = 'Niveau de contexte {$a} non supporté';
$string['errorrole'] = 'Le rôle {$a} n\'existe pas';
$string['errormissingrole'] = 'Le rôle n\'est pas défini.';
$string['errorcontext'] = 'Ce contexte {$a} n\'existe pas';
$string['errormissingcontext'] = 'Instance non définie.';
$string['warningcustomersupportcoursedefaultstosettings'] = 'L\'espace client par défaut sera utilisé';
$string['warningnocustomersupportcourse'] = 'Espace client non défini';
$string['errornocustomersupportcourse'] = 'Le cours pour l\'espace client {$a} n\'existe pas';
$string['warningonlyforselfproviding'] = 'Ce gestionnaire ne fonctionne que pour des achats internes';
$string['erroremptyuserriks'] = 'Ce gestionnaire n\'est pas compatible avec cette zone de diffusion. Modifiez le produit pour
 des  "achats internes", ou ajoutez la demande de paramètre "foruser"';

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
pouvez les retrouver avant de continuer...</b></p>
<p>Si vous avez effectué votre paiement en ligne, Vos produits de formation seront initialisés dès la confirmation automatique
de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Le compte utilisateur client a été ouvert sur la plate-forme.</p>
Identifiant : {$a->username}<br/>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correpondant.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Un rôle a été changé pour vous donner accès à des services supplémentaires.</p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Accéder à votre offre de formration</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Le role {$a->role} a été donnée à l\'utilisateur {$a->username} sur le contexte {$a->instancename}.</p>
';
