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

global $CFG;

$string['handlername'] = 'Inscription à un cours';
$string['pluginname'] = 'Inscription à un cours';

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
<p>Vos coordonnées sont:<br/>
Identifiant : {$a->username}<br/>
Mot de passe : {$a->password}<br/></p>
<p><b>Veuillez les noter quelque part où vous pouvez les retrouver avant de continuer...</b></p>
<p>Si vous avez effectué votre paiement en ligne, Vos produits de formation seront été initialisés dès la confirmation automatique
de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p>Le compte utilisateur client a été ouvert sur la plate-forme.</p>
Identifiant : {$a}<br/>
';

$string['productiondata_assign_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correpondant. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
';

$string['productiondata_assign_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Vos accès ont été ajoutés au parcours de formation correpondant. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a}">Accéder directement à votre produit</a></p>
';

$string['productiondata_assign_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>Les accès client ont été ouverts sur le cours de test.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a}">Accès à la formation</a></p>
';