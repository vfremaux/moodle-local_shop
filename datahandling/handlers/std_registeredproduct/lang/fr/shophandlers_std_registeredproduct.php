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

defined('MOODLE_INTERNAL') || die();

global $CFG;

// Privacy.
$string['privacy:metadata'] = 'Le composant Gestionaire d\'achat RegisteredProduct ne détient directement aucune donnée relative aux utilisateurs.';

$string['handlername'] = 'Produit enregistré';
$string['pluginname'] = 'Produit enregistré';

$string['productiondata_post_public'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre produit est enregistré</p>
';

$string['productiondata_post_private'] = '
<p><b>Paiement enregistré</b></p>

<p>Votre produit est enregistré</p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Paiement enregistré</b></p>
<p>Le client {$a->username} a enregistré un produit {$a->productname}.</p>
<p><a href="'.$CFG->wwwroot.'/local/shop/bills/view.php?view=viewBill&id={$a->billid}">Accès à la facture</a></p>
<p><a href="'.$CFG->wwwroot.'/local/shop/purchasemanager/view.php?view=viewProduct&id={$a->productid}">Accès à l\'enregistrement produit</a></p>
';