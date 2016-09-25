<?php

global $CFG;

$string['handlername'] = 'Création d\'un cours';
$string['pluginname'] = 'Création d\'un cours';

$string['errornocategory'] = 'La catégorie n\'est pas définie';
$string['errorcategorynotexists'] = 'La catégorie d\'id {$a} n\'existe pas';
$string['warningnohandlerusingdefault'] = 'Le modèle de cours par défaut sera utilisé';
$string['errortemplatenocourse'] = 'Le cours modèle {$a} n\'existe pas';
$string['erroralreadyexists'] = 'Ce code d\'identification est déjà utilisé';
$string['errorvalueempty'] = 'Ce champ ne peut pas être vide';
$string['erroralreadyinform'] = 'Ce code d\'identification est déjà utilisé dans cette commande';
$string['warningnoduration'] = 'La durée d\'abonnement n\'est pas précisée. Le produit est fourni pour une durée illimitée.';

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
de votre règlement. Vous pourrez vous connecter et bénéficier de vos accès sur vos volumes de travail. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/login/index.php">Accéder à la plate-forme de formation</a></p>
';

$string['productiondata_sales'] = '
<p>Le compte utilisateur client a été ouvert sur la plate-forme.</p>
Identifiant : {$a}<br/>
';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre cours est créé et les accès ont été mis en place. Un message mail vous 
donne les références d\'accès à votre volume. Vous pouvez accéder directement à ce produit après vous être authentifié.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre cours intitulé "{$a->fullname}" d\'identifiant "{$a->shortname}" est créé. Vous pouvez
y accéder directement pour commencer votre construction après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accéder directement à votre cours</a></p>
';

$string['productiondata_post_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>Le cours "{$a->fullname}" a été créé et les roles donnés au client {$a->username}.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->id}">Accès au cours</a></p>
';