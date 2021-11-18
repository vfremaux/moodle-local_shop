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
$string['privacy:metadata'] = 'The local plugin Shophandler Std RegisteredProduct does not directly store any personal data about any user.';

$string['handlername'] = 'Registered Product';
$string['pluginname'] = 'Registered Product';

$string['warningnoduration'] = 'No duration defined. Ownership will be unlimited in time from the purchase date.';

$string['productiondata_post_public'] = '
<p><b>Your payment has been registered</b></p>

<p>the associated product is registered and ready for servicing.</p>
';

$string['productiondata_post_private'] = '
<p><b>Your payment has been registered</b></p>

<p>Your product is now registered and ready for servicing.</p>
';

$string['productiondata_post_sales'] = '
<p><b>TXID: {$a->txid}</b></p>
<p><b>Payement has been received</b></p>
<p>Customer {$a->username} has aquired a registered product {$a->productname}.</p>
<p><a href="'.$CFG->wwwroot.'/local/shop/bills/view.php?view=viewBill&id={$a->billid}">Access to bill</a></p>
<p><a href="'.$CFG->wwwroot.'/local/shop/purchasemanager/view.php?view=viewProduct&id={$a->productid}">Access to product record</a></p>
';