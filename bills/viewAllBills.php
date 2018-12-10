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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;

$sortorder = optional_param('order', 'id', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);
$action = optional_param('what', '', PARAM_TEXT);
$status = optional_param('status', 'COMPLETE', PARAM_TEXT);
$customerid = optional_param('customerid', 'ALL', PARAM_TEXT);
$cur = optional_param('cur', 'EUR', PARAM_TEXT);
$y = optional_param('y', 0 + @$SESSION->shop->billyear, PARAM_INT);
$shopid = optional_param('shopid', 0, PARAM_INT);
$offset = optional_param('offset', 0, PARAM_INT);

if ($shopid) {
    $filter['shopid'] = $shopid;
}

if ($status != 'ALL') {
    $filter['status'] = $status;
}
if (!empty($cur)) {
    $filter['currency'] = $cur;
}
if (!empty($y)) {
    $filter['YEAR(FROM_UNIXTIME(emissiondate))'] = $y;
}

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/bills/bills.controller.php');
    $controller = new \local_shop\backoffice\bill_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $controller->process($action);
}

$fullview = get_user_preferences('local_shop_bills_fullview', false);
if (!$fullview && !in_array($status, array('SOLDOUT', 'COMPLETE'))) {
    $params = array('view' => 'viewAllBills', 'dir' => $dir, 'order' => $sortorder, 'status' => 'COMPLETE', 'customerid' => $customerid);
    redirect(new moodle_url('/local/shop/bills/view.php', $params));
}

echo $out;

$filterclause = '';

$template = new StdClass;
$params = array('view' => 'viewAllBills', 'dir' => $dir, 'order' => $sortorder, 'status' => $status, 'customerid' => $customerid);
$template->currencyselect = $mainrenderer->currency_choice($cur, new moodle_url('/local/shop/bills/view.php', $params));
$filterclause = " AND currency = '{$cur}' ";

$params = array('view' => 'viewAllBills', 'dir' => $dir, 'order' => $sortorder, 'status' => $status, 'customerid' => $customerid);
$template->shopselect = $mainrenderer->shop_choice(new moodle_url('/local/shop/bills/view.php', $params), true);
if ($shopid) {
    $filterclause .= " AND shopid = '{$shopid}' ";
}

$params = array('view' => 'viewAllBills', 'dir' => $dir, 'order' => $sortorder, 'status' => $status, 'customerid' => $customerid);
$template->yearselect = $mainrenderer->year_choice($y, new moodle_url('/local/shop/bills/view.php', $params), true);
if ($y) {
    $filterclause .= " AND YEAR(FROM_UNIXTIME(emissiondate)) = '{$y}' ";
}

$params = array('view' => 'search');
$template->searchurl = new moodle_url('/local/shop/bills/view.php', $params);
$template->searchinbillsstr = get_string('searchinbills', 'local_shop');

if ($fullview) {
    $params = array('view' => 'viewAllBills',
                    'dir' => $dir,
                    'order' => $sortorder,
                    'status' => $status,
                    'customerid' => $customerid,
                    'what' => 'switchfulloff');
    $template->switchfullviewurl = new moodle_url('/local/shop/bills/view.php', $params);
    $template->switchviewstr = get_string('fullviewoff', 'local_shop');
} else {
    $params = array('view' => 'viewAllBills',
                    'dir' => $dir,
                    'order' => $sortorder,
                    'status' => $status,
                    'customerid' => $customerid,
                    'what' => 'switchfullon');
    $template->switchfullviewurl = new moodle_url('/local/shop/bills/view.php', $params);
    $template->switchviewstr = get_string('fullviewon', 'local_shop');
}

echo $OUTPUT->render_from_template('local_shop/bills_options', $template);

$samecurrency = true;
if ($bills = Bill::get_instances($filter)) {
    reset($bills);
    $firstbill = current($bills);
    $billcurrency = $firstbill->currency;
    foreach ($bills as $billid => $bill) {
        if ($billcurrency != $bill->currency) {
            $samecurrency = false;
        }
        // TODO : Make more efficent filter directly in SQL.
        // Redraw ShopObject to accept filter on calculated columns.
        if ($y) {
            if (date('Y', $bill->emissiondate) != $y) {
                unset($bills[$billid]);
            }
        }
        $billsbystate[$bill->status][$bill->id] = $bill;
    }
} else {
    $billsbystate = array();
}

echo $OUTPUT->heading_with_help(get_string('billing', 'local_shop'), 'billstates', 'local_shop');

// Print tabs.
$total = new StdClass;
$total->WORKING = $DB->count_records_select('local_shop_bill', " status = 'WORKING' $filterclause");
if ($total->WORKING) {
    $label = get_string('bill_WORKINGs', 'local_shop');
    $rows[0][] = new tabobject('WORKING', "$url&status=WORKING&cur=$cur", $label.' ('.$total->WORKING.')');
}

if ($fullview) {
    $total->PLACED = $DB->count_records_select('local_shop_bill', "status = 'PLACED' $filterclause");
    $label = get_string('bill_PLACEDs', 'local_shop');
    $rows[0][] = new tabobject('PLACED', "$url&status=PLACED&cur=$cur", $label.' ('.$total->PLACED.')');

    $total->PENDING = $DB->count_records_select('local_shop_bill', " status = 'PENDING' $filterclause");
    $label = get_string('bill_PENDINGs', 'local_shop');
    $rows[0][] = new tabobject('PENDING', "$url&status=PENDING&cur=$cur", $label.' ('.$total->PENDING.')');
}

$total->SOLDOUT = $DB->count_records_select('local_shop_bill', "status = 'SOLDOUT' $filterclause");
$label = get_string('bill_SOLDOUTs', 'local_shop');
$rows[0][] = new tabobject('SOLDOUT', "$url&status=SOLDOUT&cur=$cur", $label.' ('.$total->SOLDOUT.')');

$total->COMPLETE = $DB->count_records_select('local_shop_bill', "status = 'COMPLETE' $filterclause");
$label = get_string('bill_COMPLETEs', 'local_shop');
$rows[0][] = new tabobject('COMPLETE', "$url&status=COMPLETE&cur=$cur", $label.' ('.$total->COMPLETE.')');

if ($fullview) {
    $total->CANCELLED = $DB->count_records_select('local_shop_bill', " status = 'CANCELLED' $filterclause");
    $label = get_string('bill_CANCELLEDs', 'local_shop');
    $rows[0][] = new tabobject('CANCELLED', "$url&status=CANCELLED&cur=$cur", $label.' ('.$total->CANCELLED.')');

    $total->FAILED = $DB->count_records_select('local_shop_bill', "status = 'FAILED' $filterclause");
    $label = get_string('bill_FAILEDs', 'local_shop');
    $rows[0][] = new tabobject('FAILED', "$url&status=FAILED&cur=$cur", $label.' ('.$total->FAILED.')');
}

$total->PAYBACK = $DB->count_records_select('local_shop_bill', "status = 'PAYBACK' $filterclause");
if ($total->PAYBACK) {
    $label = get_string('bill_PAYBACKs', 'local_shop');
    $rows[0][] = new tabobject('PAYBACK', "$url&status=PAYBACK&cur=$cur", $label.' ('.$total->PAYBACK.')');
}

if ($fullview) {
    $total->ALL = $DB->count_records_select('local_shop_bill', " 1 $filterclause ");
    $label = get_string('bill_ALLs', 'local_shop');
    $rows[0][] = new tabobject('ALL', "$url&status=ALL&cur=$cur", $label.' ('.$total->ALL.')');
}

print_tabs($rows, $status);

// Print bills.

$subtotal = 0;
if (empty($billsbystate)) {
    echo $OUTPUT->box_start();
    echo get_string('nobills', 'local_shop');
    echo $OUTPUT->box_end();
} else {

    echo '<table width="100%" class="generaltable">';
    echo $renderer->bill_merchant_line(null);
    $i = 0;
    foreach (array_keys($billsbystate) as $status) {
        echo $renderer->bill_status_line($status);

        $CFG->subtotal = 0;
        foreach ($billsbystate[$status] as $portlet) {
            $subtotal += floor($portlet->amount * 100) / 100;
            echo $renderer->bill_merchant_line($portlet);
        }

        echo $renderer->bill_group_subtotal($subtotal, $billcurrency, $samecurrency);

        $i++;
    }
    echo '</table>';
}

echo $renderer->bill_view_links($theshop);
