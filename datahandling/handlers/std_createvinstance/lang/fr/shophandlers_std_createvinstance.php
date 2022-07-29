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

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat CreateVInstance ne détient directement aucune donnée relative aux utilisateurs.';

$string['handlername'] = 'Création d\'instances de Moodle';
$string['pluginname'] = 'Création d\'instances de Moodle';
$string['errorhostnameexists'] = 'Ce nom court de site {$a} est déja enregistré.';

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
de votre règlement. Vous pourrez vous connecter et bénéficier de vos accès sur vos volumes de travail. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Le compte utilisateur client {$a->username} a été ouvert sur la plate-forme.</p>
';

$string['productiondata_post_public'] = '
<p>Une nouvelle instance de Moodle a été crée. Un courriel vous a été envoyé
pour vous communiquer vos indicatifs d\'accès en tant que Gestionnaire.</p>
<p>Si vous avez effectué votre paiement en ligne, Votre nouvelle instance est créée et configurée dès la confirmation automatique
de votre règlement. Vous pourrez alors vous connecter et utiliser votre plate-forme. Dans le cas contraire vos accès
seront validés dès réception de votre paiement.</p>
<p><a href="{$a->wwwroot}/login/index.php">Accéder à votre plate-forme de formation</a></p>
';

$string['productiondata_post_private'] = '
<p>Votre compte Gestionnaire a été ouvert sur cette plate-forme.</p>
<p>Votre identifiant d\'administrateur : {$a->username}</p>
<p>Le mot de passe d\'administrateur vous a été envoyé dans un courriel séparé. <b>Veuillez noter vos coordonnées d\'accès quelque part où vous pouvez les retrouver avant de continuer...</b></p>
<p>Si vous avez effectué votre paiement en ligne, Votre plate-forme a été initialisée dès la confirmation automatique
de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="{$a->wwwroot}/login/index.php">Accéder à votre plate-forme de formation</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Accéder à votre compte client</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Le compte utilisateur client a été ouvert sur la plate-forme.</p>
Identifiant : {$a->username}<br/>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre plate-forme {$a->wwwroot} a été initialisée. Vous pouvez vous y connecter comme Gestionnaire.</p>
<p>Vos identifiants d\'administrateur sont :<br/>
Identifiant : {$a->managerusername}<br/>
Mot de passe : {"a->managerpassword}</p>

<p><a href="{$a->wwwroot}/login/index.php">Accéder directement à votre plate-forme</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Accéder directement à votre compte client</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>La plate-forme {$a->wwwroot} a été initialisée pour le client {$a->username}</p>
';