<?php

global $CFG;

$string['handlername'] = 'Création d\'instances de Moodle';
$string['pluginname'] = 'Création d\'instances de Moodle';
$string['errorhostnameexists'] = 'Ce nom court de site {$a} est déja enregistré.';

$string['productiondata_public'] = '
<p>Une nouvelle instance de Moodle a été crée. Un courriel vous a été envoyé
pour vous communiquer vos indicatifs d\'accès en tant que Gestionnaire.</p>
<p>Si vous avez effectué votre paiement en ligne, Votre nouvelle instance est créée et configurée dès la confirmation automatique
de votre règlement. Vous pourrez alors vous connecter et utiliser votre plate-forme. Dans le cas contraire vos accès 
seront validés dès réception de votre paiement.</p>
<p><a href="{$a->wwwroot}/login/index.php">Accéder à votre plate-forme de formation</a></p>
';

$string['productiondata_private'] = '
<p>Votre compte Gestionnaire a été ouvert sur cette plate-forme.</p>
<p>Vos coordonnées sont:<br/>
Site : {$a->wwwroot}<br/>
Identifiant : {$a->username}<br/>
Mot de passe : {$a->password}<br/></p>
<p><b>Veuillez les noter quelque part où vous pouvez les retrouver avant de continuer...</b></p>
<p>Si vous avez effectué votre paiement en ligne, Votre plate-forme a été initialisée dès la confirmation automatique
de votre règlement. Vous pourrez vous connecter
et bénéficier de vos accès de formation. Dans le cas contraire vos accès seront validés dès réception de votre paiement.</p>
<p><a href="{$a->wwwroot}/login/index.php">Accéder à votre plate-forme de formation</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Accéder à votre compte client</a></p>
';

$string['productiondata_sales'] = '
<p>La plate-forme {$a->wwwroot} a été créée.</p>
<p>Le compte utilisateur client a été ouvert sur la plate-forme.</p>
Identifiant : {$a}<br/>
';

$string['productiondata_delivered_public'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé.</p>
';

$string['productiondata_delivered_private'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre règlement a été validé. Votre plate-forme {$a->wwwroot} a été initialisée. Vous pouvez vous y connecter comme Gestionnaire.</p>
<p><a href="{$a->wwwroot}/login/index.php">Accéder directement à votre plate-forme</a></p>
<p><a href="'.$CFG->wwwroot.'/my/index.php">Accéder directement à votre compte client</a></p>
';

$string['productiondata_delivered_sales'] = '
<p><b>Paiement enregistré</b></p>
<p>Votre plate-forme {$a->wwwroot} a été initialisée</p>
';