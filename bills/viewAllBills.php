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
$customerid = optional_param('customerid', 'ALL', PARAM_TEXT);
$nopaging = optional_param('nopaging', 0, PARAM_BOOL);
$pagesize = 20;
$billpage = optional_param('billpage', 0, PARAM_INT);
$offset = $billpage * $pagesize;

$y = optional_param('y', 0 + @$SESSION->shop->billyear, PARAM_INT);
$m = optional_param('m', 0 + @$SESSION->shop->billmonth, PARAM_INT);
$shopid = optional_param('shopid', 0, PARAM_INT);
$status = optional_param('status', 'COMPLETE', PARAM_TEXT);
$cur = optional_param('cur', 'EUR', PARAM_TEXT);

list($filter, $filterclause, $urlfilter) = shop_get_bill_filtering();

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

echo $renderer->bill_options($mainrenderer, $fullview);

$samecurrency = true;

if ($nopaging) {
    $bills = Bill::get_instances($filter, 'emissiondate', '*');
} else {
    $bills = Bill::get_instances($filter, 'emissiondate', '*', $offset, $pagesize);
}

if ($bills) {
    reset($bills);
    $firstbill = current($bills);
    $billcurrency = $firstbill->currency;
    foreach ($bills as $billid => $bill) {
        if ($billcurrency != $bill->currency) {
            $samecurrency = false;
        }
        // TODO : Make more efficent filter directly in SQL.
        // Redraw ShopObject to accept filter on calculated columns.
        /*
        if ($y) {
            if (date('Y', $bill->emissiondate) != $y) {
                unset($bills[$billid]);
            }
        }
        */
        $billsbystate[$bill->status][$bill->id] = $bill;
    }
} else {
    $billsbystate = array();
}

echo $OUTPUT->heading_with_help(get_string('billing', 'local_shop'), 'billstates', 'local_shop');

// Print tabs.
$total = Bill::count_by_states($fullview, $filterclause);
$rows = shop_get_bill_tabs($total, $fullview);
print_tabs($rows, $status);

// Print pager.
$urlpagingfilter = str_replace('nopaging=1', 'nopaging=0', $urlfilter);
$pagingbar = $OUTPUT->paging_bar($total->$status, $billpage, $pagesize, $url.'&'.$urlpagingfilter, 'billpage');
if ($pagingbar) {
    echo $pagingbar;
    echo $renderer->no_paging_switch($url, $urlfilter);
}

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
    foreach (array_keys($billsbystate) as $billstate) {
        echo $renderer->bill_status_line($billstate);

        $CFG->subtotal = 0;
        foreach ($billsbystate[$billstate] as $portlet) {
            $subtotal += floor($portlet->amount * 100) / 100;
            echo $renderer->bill_merchant_line($portlet);
        }

        echo $renderer->bill_group_subtotal($subtotal, $billcurrency, $samecurrency);

        $i++;
    }
    echo '</table>';
}

echo $renderer->bill_view_links($theshop);

// Print pager.
if ($pagingbar) {
    echo $pagingbar;
    echo $renderer->no_paging_switch($url, $urlfilter);
}

