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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionnaire d\'achat AddTrainingCredits ne détient directement aucune donnée relative
 aux utilisateurs.';

$string['handlername'] = 'Ajout de crédits pédagogiques (Enrolement par crédits)';
$string['pluginname'] = 'Ajout de crédits pédagogiques (Enrolement par crédits)';

$string['productiondata_public'] = '
<p>Votre compte utilisateur a été ouvert sur cette plate-forme. Un courriel vous a été envoyé
pour vous communiquer votre mot de passe.</p>
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
<p>Le compte utilisateur client a été ouvert sur la plate-forme.</p>
Identifiant : {$a->username}<br/>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a->credits} credits de cours ont été ajoutés à votre compte.<br/>
Votre solde de crédits est de  : {$a->coursecredits}</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a->credits} crédits de coours ont été rajoutés à votre compte. Vous pouvez vous insrire sur les cours
proposés dans votre espace personnel.</p>
<p>Votre solde de crédits est de  : {$a->coursecredits}</p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Accéder directement à votre compte</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>{$a->credits} crédits ont été rajoutés au compte client {$a->username}.</p>
<p>Le solde de crédits est de  : {$a->coursecredits}</p>
';
