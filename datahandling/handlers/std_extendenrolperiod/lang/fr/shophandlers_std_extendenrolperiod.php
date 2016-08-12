<?php

global $CFG;

$string['handlername'] = 'Extension d\'inscription';
$string['pluginname'] = 'Extension d\'inscription';

$string['productiondata_public'] = '
<p>Vous avez obtenu {$a->extension} jours d\'entrainement supplémentaire(s).</p>
<p>Si vous avez effectué votre paiement en ligne, Votre extension est immédiatement réalisée. Vous pouvez vous connecter
et bénéficier de votre temps supplémentaires. Dans le cas contraire votre extension sera validée dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accéder à la formation</a></p>
';

$string['productiondata_private'] = '
<p>Vous avez obtenu {$a->extension} jours d\'entrainement supplémentaire(s).</p>
<p>Si vous avez effectué votre paiement en ligne, Votre extension est immédiatement réalisée. Vous pouvez vous connecter
et bénéficier de votre temps supplémentaires. Dans le cas contraire votre extension sera validée dès réception de votre paiement.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accéder à la formation</a></p>
';

$string['productiondata_sales'] = '
<p>Le client {$a->username} a étendu sa durée.</p>
<p><a href="'.$CFG->wwwroot.'/course/view.php?id={$a->courseid}">Accéder à la formation</a></p>
';