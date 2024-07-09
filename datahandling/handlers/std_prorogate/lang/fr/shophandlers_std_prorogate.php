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
 * Lang file
 *
 * @package   local_shop
 * @subpackage  shophandlers_std_prorogate
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat Prorogation ne détient directement aucune donnée
 relative aux utilisateurs.';

$string['handlername'] = 'Prorogation';
$string['pluginname'] = 'Prorogation';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre produit est prorogé.</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre produit est prorogé jusqu\'au {$a->enddatestr}</p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Le produit de référence {$a->reference} du client {$a->username} a été prorogé jusqu\'au {$a->enddatestr}.</p>
<p><a href="'.$CFG->wwwroot.'/local/shop/bills/view.php?view=viewBill&id={$a->billid}">Accès à la facture</a></p>
<p><a href="'.$CFG->wwwroot.'/local/shop/purchasemanager/view.php?view=viewProduct&id={$a->productid}">Accès à l\'enregistrement
 produit</a></p>';

$string['productiondata_failure_public'] = '
Le produit de référence {$a->reference} n\'a pas pu être trouvé.
';

$string['productiondata_failure_private'] = '
Le produit de référence {$a->reference} n\'a pas pu être trouvé.
';

$string['productiondata_failure_sale'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Erreur de prorogation : Le produit {$a->reference} du client {$a->username} n\'a pas pu être trouvé.</b></p>
';
