<?php

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionnaire d\'achat AddQuizAttempts ne détient directement aucune donnée relative aux utilisateurs.';

$string['handlername'] = 'Ajout de tentatives de quiz (block_userquiz_limits)';
$string['pluginname'] = 'Ajout de tentatives de quiz (block_userquiz_limits)';
$string['warningmultiplecourses'] = 'Les quiz référencés sont dans des cours différents. Le contrôle d\'inscription ne se fera sque sur le premier. Il est conseillé de ne pointer que des quiz du même cours.';
$string['errornoquizvalid'] = 'Aucune des références de quiz n\'est valide.';
$string['errornotaquiz'] = 'Cette référence ne pointe pas un quiz';
$string['errorbadidnumber'] = 'Ce numéro d\'identification ($a) est invalide';
$string['errorbadcmref'] = 'Cette réfrence de module de cours ($a) est invalide';
$string['errorbadquizref'] = 'Cette référence de quiz ($a) est invalide';
$string['errorunassignedquiz'] = 'Aucune référence de quiz définie';
$string['warningnullcredits'] = 'Le nombre de tentatives supplémentaires (attemptsamount) est absent. 1 tentative sera ajoutée par défaut.';

$string['productiondata_public'] = '
<p>Des tentatives de quiz vous ont été attribuées</p>
<p><a href="{$a->ticket}">Aller au cours</a></p>
';

$string['productiondata_private'] = '
<p>{$a->attempts} tentatives ont été ajoutées à votre compte pour le quiz {$a->quizname}.</p>
<p><a href="{$a->ticket}">Aller au cours</a></p>
';

$string['productiondata_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p>Des tentatives de quiz ont été ajoutées au compte de :</p>
<p>Login: {$a->username}<br/>
Quiz: {$a->quizname}<br/>
</p>
';

$string['productiondata_post_public'] = '
<p><b>Votre paiement a été enregistré</b></p>
<p>Votre paiement a été validé. {$a->credits} tentatives ont été ajoutées à votre compte pour le quiz {$a->quizname}.</p>
<p>Votre nombre de tentatives autorisées est : {$a->attempts}</p>
';

$string['productiondata_post_private'] = '
<p><b>Votre paiement a été enregistré</b></p>
<p>Votre paiement a été validé. {$a->credits} tentatives ont été ajoutées à votre compte pour le quiz {$a->quizname}</p>
<p><a href="{$a->ticket}">Accès direct au cours</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Customer {$a->username} a reçu {$a->credits} tentatives sur le quiz {$a->quizname}.</p>
<p>Nombre de tentatives autorisées : {$a->attempts}</p>
';

$string['productiondata_failure_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>La notification pour le gestionnaire commercial a échouée.</b></p>
';

$string['productiondata_failure_public'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>La notification publique a échouée.</b></p>
';

$string['productiondata_failure_private'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>La notification privée a échouée.</b></p>
';