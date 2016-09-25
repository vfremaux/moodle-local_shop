<?php

global $CFG;

$string['handlername'] = 'Libération d\'un certificat';
$string['pluginname'] = 'Libération d\'un certificat';

$string['productiondata_produce_public'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre règlement a été validé. Votre certificat de formation dans le cours {$c->fullname} est libéré. Vous en recevrez une copie 
dans la boite de courriel qui est renseignée sur votre compte.</p>

<p>vous pourrez néanmoins revenir en obtenir des copies à cette adresse : {$a->endpoint}</p>
';

$string['productiondata_produce_private'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre règlement a été validé. Votre certificat de formation dans le cours {$c->fullname} est libéré. Vous en recevrez une copie 
dans la boite de courriel qui est renseignée sur votre compte.</p>

<p>vous pourrez néanmoins revenir en obtenir des copies à cette adresse : {$a->endpoint}</p>
';

$string['productiondata_produce_sales'] = '
<p><b>Paiement enregistré</b></p>

<p>Le certificat du cours "{$a->fullname}" du client {$a->username} a été libéré.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accès au cours</a></p>

';