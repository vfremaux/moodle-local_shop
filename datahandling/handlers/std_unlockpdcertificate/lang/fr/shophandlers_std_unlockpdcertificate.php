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
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat UnlockPdCertificate ne détient directement aucune donnée relative aux utilisateurs.';

$string['handlername'] = 'Libération d\'un certificat';
$string['pluginname'] = 'Libération d\'un certificat';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre règlement a été validé. Votre certificat de formation \"{$a->name}\" dans le cours {$a->fullname} est libéré. Vous en recevrez une copie
dans la boite de courriel qui est renseignée sur votre compte.</p>

<p>vous pourrez néanmoins revenir en obtenir des copies à cette adresse : {$a->endpoint}</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre règlement a été validé. Votre certificat de formation \"{$a->name}\" dans le cours {$e->fullname} est libéré. Vous en recevrez une copie
dans la boite de courriel qui est renseignée sur votre compte.</p>

<p>vous pourrez néanmoins revenir en obtenir des copies à cette adresse : {$a->endpoint}</p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Le certificat du cours "{$a->fullname}" du client {$a->username} a été libéré.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accès au cours</a><br/>
<a href="{$a->enpoint}" >Accès au certificat</a></p>
';