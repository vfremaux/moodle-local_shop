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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

$string['handlername'] = 'Ajout d\'un accès LTI';
$string['pluginname'] = 'Ajout d\'un accès LTI';
$string['warningnoduration'] = 'Pas de durée d\'inscription définie. Les accès seront acquis pour une durée illimitée.';
$string['warningdefaultsendgrades'] = 'Valeur par défaut pour le retour des scores : Actif.';
$string['warningdefaultmaxenrolled'] = 'Valeur par défaut pour la limite de siègess : Sans limite de sièges.';
$string['maxenrolled'] = '{$a} étudiants maximum';
$string['secret'] = 'Clef secrète';
$string['globalsharedsecret'] = 'Secret partagé de site';
$string['unlimited'] = 'Illimité';
$string['endpoint'] = 'URL du point d\'accès';
$string['capacity'] = 'Capacité du lien';
$string['coursename'] = 'Cours';
$string['extname'] = 'Nom exposé du cours';
$string['startdate'] = 'Date de début';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Un accès Fournisseur LTI a été créé pour le cours {$a->coursename}. Le point d\'accès
est :</p>

<div class="code"><code>{$a->endpoint}</code></div>

<p>Avec le code d\'accès :</p>

<div class="access-code code"><code>{$a->secret}</code></div>

<p>Vous pouvez fournir ces deux informations pour raccorder un Client LTI (par exemple un autre Moodle
avec un "Outil Externe") et acheminer les étudiants.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Un accès Fournisseur LTI a été créé pour le cours {$a->coursename}. Le point d\'accès
est :</p>

<div class="code"><code>{$a->endpoint}</code></div>

<p>Avec le code d\'accès :</p>

<div class="access-code code"><code>{$a->secret}</code></div>

<p>Vous pouvez fournir ces deux informations pour raccorder un Client LTI (par exemple un autre Moodle
avec un "Outil Externe") et acheminer les étudiants.</p>
';

$string['productiondata_post_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>Un accès LTI a été créé pour le cours "{$a->coursename}" pour le client {$a->username}.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accès au cours</a></p>
';