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

/**
 * @package     shoppaymodes_test
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat Test ne détient directement aucune donnée relative
aux utilisateurs.';

$string['enabletest'] = 'Paiement test pour mise au point';
$string['enabletest2'] = 'Paiement test pour mise au point';
$string['enabletst3'] = 'Ce mode de paiement ne doit pas être activé en production. Il permet de régler virtuellement une facture
pour tester les actions de production...';
$string['test'] = 'Test';
$string['pluginname'] = 'Module de test de paiement';
$string['interactivepay'] = 'Paiement interactif';
$string['ipnpay'] = 'Déclenchement IPN';
$string['ipnpayandclose'] = 'Déclenchement IPN avec terminaison';
$string['paydelayedforipn'] = 'Paiement via IPN (émission)';

$string['testadvice'] = '
<p>Ceci est une simulation de méthode de paiement à destination de test de mises en oeuvre de produits. Ne jamais utiliser sur un
site de production, à moins de voir des
produits achetés mais jamais payés !</p>
<p>A partir d\'ici vous pouvez déclencher quatre simulation : </p>
<ul><li><b>Paiement direct interactif :</b> vous activez directement la commande et la payez (virtuellement) et vous déclenchez
immédiatement la production de la commande. Ceci ne représente pas un cycle réel car une transaction sera toujours différée soit
en demandant une procédure d\'activation back-office (offline), ou mise en attente d\'une confirmation asynchrone d\'un système
de paiement.</li>
<li><b>simulation d\'appel à un système de paiement externe (IPN)</b>: Ceci simule le comportement de la plupart des interfaces
de paiement. La production effective est réalisée au retour de notification de paiement (IPN). Ce scénario simule un acheteur
émettant le paiement sur l\'interface bancaire et revenant à la boutique avant obtention de la notification.</li>
<li><b>Déclencheur IPN sans terminaison</b> : Ceci simule l\'activation du traitement de retour d\'IPN dans une fenêtre séparée.
Vous pouvez lancer ce lien avant même l\'émission du paiement, la situation alors simulée est celle d\'un service bancaire réactif
qui valide le paiement avant que le client ne retourne à la boutique. La commande est complètement traitée dans ce cas. Avec cette
action, la commande reste non traitée pour pouvoir être relancée plusieurs fois.</li>
<li><b>Déclencheur IPN avec terminaison</b> : Ceci simule l\'activation du traitement de retour d\'IPN dans une fenêtre séparée.
Vous pouvez lancer ce lien avant même l\'émission du paiement, la situation alors simulée est celle d\'un service bancaire réactif
qui valide le paiement avant que le client ne retourne à la boutique. La commande est complètement traitée dans ce cas.</li>
</ul>
';

$string['pending_followup_text_tpl'] = '
<p>Votre commande est en attente d\'une confirmation de paiement par IPN. La mise en oeuvre de votre commande interviendra dès
l\'activation de la simulation d\'IPN.</p>
<p>Support de Test : <%%SUPPORT%%>.</p>
';

$string['success_followup_text_tpl'] = '
<p>Le paiement a été enregistré. Votre commande est en cours de traitement.</p>
<p>Support de test : <%%SUPPORT%%>.</p>
';
