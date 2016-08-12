<?php

global $CFG;

$string['handlername'] = 'Ajout de sièges non affectés';
$string['pluginname'] = 'Ajout de sièges non affectés';

$string['assignseat'] = 'Attribuer le siège';
$string['assignedto'] = '<b>Attribué à :</b> {$a}';
$string['incourse'] = '<b>Dans le module :</b> [{$a->shortname}] {$a->fullname}';
$string['enabledcourses'] = 'Modules autorisés';
$string['unassignseat'] = 'Libérer le siège';
$string['assignseatlocked'] = '<span class="error">L\'assignation du siège est verrouillée par des activités de l\'utilisateur dans le cours.</span>';
$string['assigninstructions'] = 'Ce siège est actuellement non assigné. vous pouvez choisir de l\'assigner à un apprenant sous votre responsablité. Si l\'apprenant est déjà inscrit dans ce cours, vous serez averti et pourrez à nouveau faire une nouvelle attribution.';
$string['warningcustomersupportcoursedefaultstosettings'] = 'Le cours de support client est celui par défaut';
$string['warningnocustomersupportcourse'] = 'Aucun espace client défini';
$string['errornocustomersupportcourse'] = 'Le cours espace client {$a} n\'existe pas';
$string['errorsupervisorrole'] = 'Le rôle superviseur {$a} n\'existe pas';
$string['warningsupervisordefaultstoteacher'] = 'Le rôle superviseur n\'est pas défini. "Enseignant non éditeur" est utilisé à la place.';
$string['warningpacksizedefaultstoone'] = 'Le nombre de sièges n\'est pas défini, un siège par défaut';
$string['warningonecoursenotexists'] = 'Certains cours ({$a}) définis dans la liste d\'autorisation n\'existent pas';
$string['warningemptycourselist'] = 'Aucune liste de cours n\'est définie, les sièges générés pourront être assignés à tous les cours visible de la plate-forme.';
$string['errornoallowedcourses'] = 'Le produit semble mal configuré et ne semble pas avoir de cours désigné pour affecter des apprenants';
$string['backtocourse'] = 'Revenir à l\'espace client';
$string['seatassigned'] = 'Bravo et merci ! Vous avez inscrit {$a->user} au cours {$a->course}. Une confirmation va être envoyée à votre apprenant. Ce produit peut être réattribué tant que votre apprenant ne s\'est pas manifesté dans le cours. Le produit sera consommé définitivement au premier signe d\'activité de votre apprenant dans le module.';
$string['seatalreadyassigned'] = 'Désolé ! Il semble que {$a->user} soit déjà inscrit dans le cours {$a->course}. Vous n\'allez pas "brûler" un siège pour ça ! Choisissez une nouvelle affectation pour ce siège.';
$string['seatreleased'] = 'Ce siège est libéré. Vous pouvez le réassigner à une autre personne.';

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

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a} sièges à affecter ont été ajoutés à votre compte client.</p>
';

$string['productiondata_assign_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. {$a} sièges à affecter ont été ajoutés à votre compte client. Vous pouvez les affecter en vous rendant
sur votre espace support client.</p>
<p><a href="{$a->customersupporturl}">Accéder directement à votre compte client</a></p>
';

$string['productiondata_assign_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>{$a->seats} sièges pont été ajoutés au compte client de {$a->username}.</p>
';

$string['assignseat_title'] = 'You have a new course at {$a} !';

$string['assignseat_mail'] = '
<p>Votre référent vous a inscrit sur le cours <a href="{$a->url}">{$a->course}</a>.</p>
<p>vous pouvez vous y connecter dès à présent avec les identifiants que vous avez reçu précédemment.</p>
';
