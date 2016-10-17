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