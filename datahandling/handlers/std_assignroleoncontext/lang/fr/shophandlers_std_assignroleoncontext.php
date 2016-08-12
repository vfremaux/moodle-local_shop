<?php

global $CFG;

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
$string['erroremptyuserriks'] = 'Ce gestionnaire n\'est pas compatible avec cette zone de diffusion. Modifiez le produit pour des  "achats internes", ou ajoutez la demande de paramètre "foruser"';

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
<p>Votre règlement a été validé. Un rôle a été changé pour vous donner accès à des services supplémentaires. Vous pouvez
accéder directement à ce produit après vous être authentifié.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a}">Accéder directement à votre produit</a></p>
';

$string['productiondata_assign_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>Un role a été changé pour vous donner accès à des services supplémentaires.</p>
';