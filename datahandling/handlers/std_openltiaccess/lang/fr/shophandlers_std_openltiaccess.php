<?php

global $CFG;

$string['handlername'] = 'Ajout d\'un accès LTI';
$string['pluginname'] = 'Ajout d\'un accès LTI';
$string['warningnoduration'] = 'Pas de durée d\'inscription définie. Les accès seront acquis pour une durée illimitée.';
$string['warningdefaultsendgrades'] = 'Valeur par défaut pour le retour des scores : Actif.';
$string['warningdefaultmaxenrolled'] = 'Valeur par défaut pour la limite de siègess : Sans limite de sièges.';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Un accès Fournisseur LTI a été créé pour le cours {$a->coursename}. Le point d\'accès
est :</p>

<p>{$a->endpoint}</p>

<p>Avec le code d\'accès :</p>

<p><ode>{$a->secret}</code></p>

<p>Vous pouvez fournir ces deux informations pour raccorder un Client LTI (par exemple un autre Moodle
avec un "Outil Externe") et acheminer les étudiants.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Un accès Fournisseur LTI a été créé pour le cours {$a->coursename}. Le point d\'accès
est :</p>

<p>{$a->endpoint}</p>

<p>Avec le code d\'accès :</p>

<p><code>{$a->secret}</code></p>

<p>Vous pouvez fournir ces deux informations pour raccorder un Client LTI (par exemple un autre Moodle
avec un "Outil Externe") et acheminer les étudiants.</p>
';

$string['productiondata_post_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>Un accès LTI a été créé pour le cours "{$a->coursename}" pour le client {$a->username}.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accès au cours</a></p>
';