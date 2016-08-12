<?php

global $CFG;

$string['handlername'] = 'Mise en place d\'une session de formation';
$string['pluginname'] = 'Mise en place d\'une session de formation';
$string['errorbadtaget'] = 'Erreur : cours cible non trouvé';
$string['errorcoursenotexists'] = 'Le cours {$a} n\'existe pas';
$string['errorcoursenotenrollable'] = 'Le cours {$a} n\'a pas de méthode d\'inscription adapté';
$string['errorsupervisorrole'] = 'Le rôle {$a} superviseur n\'existe pas';
$string['warningsupervisordefaultstoteacher'] = 'Le rôle superviseur n\'est pas défini. Le rôle "Enseignant non éditeur" est pris par défaut.';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Le cours support client est le cours par défaut du bloc';
$string['warningnocustomersupportcourse'] = 'Aucun cours support client défini';
$string['errornocustomersupportcourse'] = 'Le cours {$a} pour support client n\'existe pas ';
$string['coursename'] = 'Cours';
$string['beneficiary'] = 'Bénéficiaire';
$string['freeassign'] = 'Libérer le siège';

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