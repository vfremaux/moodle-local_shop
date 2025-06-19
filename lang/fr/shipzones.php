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
 * Lang for shipping zones
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addshipping'] = 'Ajouter un frais de port';
$string['addshippingzone'] = 'Nouvelle zone';
$string['applicability'] = 'Formule d\'applicabilité';
$string['applicability_desc'] = 'Une formule qui active ou inhibe l\'application du port';
$string['billscopeamount'] = 'Part affectée';
$string['billscopeamount_desc'] = 'Part du montant de facture affecté par le port.';
$string['deleteshipping'] = 'Supprimer un frais de port';
$string['deletezone'] = 'Supprimer une zone';
$string['formula'] = 'Formule';
$string['newshipping'] = 'Nouveau frais de port';
$string['noshippings'] = 'Aucun calcul de frais de port défini';
$string['nozones'] = 'Aucune zone définie';
$string['param_a'] = 'Paramètre \'$a\'';
$string['param_b'] = 'Paramètre \'$b\'';
$string['param_c'] = 'Paramètre \'$c\'';
$string['shippingfixedvalue'] = 'Frais fixe';
$string['shippings'] = 'Frais de port';
$string['shippingzone'] = 'Zone de port';
$string['editshippingzone'] = 'Modifier la zone de port';
$string['shipzones'] = 'Zones de livraison';
$string['shipzone'] = 'Zone de livraison';
$string['usedentries'] = 'Frais de ports associés';
$string['zonecode'] = 'Code de zone';
$string['zoneid'] = 'Zone de port ';

$string['shippingfixedvalue_help'] = '
# Port fixe

Lorsque vous pouvez réduire le coût de port à une valuer fixe et simple, Utilisez ce champ pour donner la valeur de port pour le produit
concerné en devise par défaut de la boutique associée.
';

$string['formula_help'] = '
# Port calculé

Give a parsable formula to calculate the shipping cost. You can use standard path functions (sqrt, arythmetic operators, log)
and use variables placeholders  as $ttc, $ht or one of the following parameters $a, $b or $c.
';
