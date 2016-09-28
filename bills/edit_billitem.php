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
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_billitem.class.php');
require_once($CFG->dirroot.'/local/shop/forms/form_product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Catalog;
use local_shop\Bill;
use local_shop\BillItem;
use local_shop\Tax;

// Get the block reference and key context.

// get all the shop session context objects.
list($theShop, $theCatalog, $theBlock) = shop_build_context();

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

$billid = required_param('billid', PARAM_INT);
$billitemid = optional_param('billitemid', 0, PARAM_INT);

// Make page header and navigation.

$url = new moodle_url('/local/shop/bills/edit_billitem.php', array('billid' => $billid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->set_pagelayout('admin');

try {
    $bill = new Bill($billid);
} catch (Exception $e) {
    print_error('objecterror', 'local_shop', $e->get_message());
}

if ($billitemid) {
    $billitem = new BillItem($billitemid);
    $billitemrec = $billitem->record;
    $mform = new BillItem_Form('', array('what' => 'edit', 'catalog' => $theCatalog));
    $mform->set_data($billitemrec);
} else {
    $billitem = new BillItem(null);
    $billitemrec = $billitem->record;
    $mform = new BillItem_Form('', array($cmd => 'add', 'catalog' => $theCatalog));
    $billitemrec->billid = $bill->id;
    $mform->set_data($billitemrec);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/bills/view.php', array('view' => 'viewBill', 'billid' => $billid)));
}

if ($billitem = $mform->get_data()) {
    $billitem->totalprice = $billitem->quantity * $billitem->unitcost;
    $bill->totalprice += ($billitem->unitcost * $billitem->quantity);
    $bill->untaxedamount += $bill->totalprice;
    $billitem->id = $billitem->billitemid;
    unset($billitem->billitemid);

    if ($bill->ignoretax == 0) {
        $tax = new Tax($billitem->taxcode);
        $bill->taxes += (($bill->totalprice * $tax->ratio) / 100);
    }

    $bill->amount = $bill->amount + ($bill->untaxedamount + $bill->taxes);
    $billitem->billid = $billid;

    if (empty($billitem->id)) {
        $billitem->id = $DB->insert_record('local_shop_billitem', $billitem);
    } else {
        $DB->update_record('local_shop_billitem', $billitem);
    }

    $DB->update_record('local_shop_bill', $bill);

    redirect(new moodle_url('/local/shop/bills/view.php', array('view' => 'viewBill', 'billid' => $billid)));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->fooer();