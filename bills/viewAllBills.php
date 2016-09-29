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
$status = optional_param('status', 'ALL', PARAM_TEXT);
$customerid = optional_param('customerid', 'ALL', PARAM_TEXT);
$cur = optional_param('cur', 'EUR', PARAM_TEXT);

$shopid = optional_param('shopid', 0, PARAM_INT);

if ($shopid) {
    $filter['shopid'] = $shopid;
}

if ($status != 'ALL') {
    $filter['status'] = $status;
}
if (!empty($cur)) {
    $filter['currency'] = $cur;
}

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/bills/bills.controller.php');
    $controller = new bills_controller();
    $controller->process($action);
}

echo $out;

$params = array('view' => 'viewAllbills', 'dir' => $dir, 'order' => $sortorder, 'status' => $status, 'customerid' => $customerid);
echo $mainrenderer->currency_choice($cur, new moodle_url('/local/shop/bills/view.php', $params));
$curclause = " AND currency = '{$cur}' ";

$params = array('view' => 'viewAllbills', 'dir' => $dir, 'order' => $sortorder, 'status' => $status, 'customerid' => $customerid);
echo $mainrenderer->shop_choice(new moodle_url('/local/shop/bills/view.php', $params), true);

$samecurrency = true;
if ($bills = Bill::get_instances($filter)) {
    reset($bills);
    $firstbill = current($bills);
    $billcurrency = $firstbill->currency;
    foreach ($bills as $bill) {
        if ($billcurrency != $bill->currency) {
            $samecurrency = false;
        }
        $billsbystate[$bill->status][$bill->id] = $bill;
    }
} else {
    $billsbystate = array();
}

echo $OUTPUT->heading_with_help(get_string('billing', 'local_shop'), 'billstates', 'local_shop');

// Print tabs.
$total = new StdClass;
$total->WORKING = $DB->count_records_select('local_shop_bill', " status = 'WORKING' $curclause");
$total->PLACED = $DB->count_records_select('local_shop_bill', "status = 'PLACED' $curclause");
$total->PENDING = $DB->count_records_select('local_shop_bill', " status = 'PENDING' $curclause");
$total->SOLDOUT = $DB->count_records_select('local_shop_bill', "status = 'SOLDOUT' $curclause");
$total->COMPLETE = $DB->count_records_select('local_shop_bill', "status = 'COMPLETE' $curclause");
$total->CANCELLED = $DB->count_records_select('local_shop_bill', " status = 'CANCELLED' $curclause");
$total->FAILED = $DB->count_records_select('local_shop_bill', "status = 'FAILED' $curclause");
$total->PAYBACK = $DB->count_records_select('local_shop_bill', "status = 'PAYBACK' $curclause");
$total->ALL = $DB->count_records_select('local_shop_bill', " 1 $curclause ");
$label = get_string('bill_WORKINGs', 'local_shop');
$rows[0][] = new tabobject('WORKING', "$url&status=WORKING&cur=$cur", $label.' ('.$total->WORKING.')');
$label = get_string('bill_PLACEDs', 'local_shop');
$rows[0][] = new tabobject('PLACED', "$url&status=PLACED&cur=$cur", $label.' ('.$total->PLACED.')');
$label = get_string('bill_PENDINGs', 'local_shop');
$rows[0][] = new tabobject('PENDING', "$url&status=PENDING&cur=$cur", $label.' ('.$total->PENDING.')');
$label = get_string('bill_SOLDOUTs', 'local_shop');
$rows[0][] = new tabobject('SOLDOUT', "$url&status=SOLDOUT&cur=$cur", $label.' ('.$total->SOLDOUT.')');
$label = get_string('bill_COMPLETEs', 'local_shop');
$rows[0][] = new tabobject('COMPLETE', "$url&status=COMPLETE&cur=$cur", $label.' ('.$total->COMPLETE.')');
$label = get_string('bill_CANCELLEDs', 'local_shop');
$rows[0][] = new tabobject('CANCELLED', "$url&status=CANCELLED&cur=$cur", $label.' ('.$total->CANCELLED.')');
$label = get_string('bill_FAILEDs', 'local_shop');
$rows[0][] = new tabobject('FAILED', "$url&status=FAILED&cur=$cur", $label.' ('.$total->FAILED.')');
$label = get_string('bill_PAYBACKs', 'local_shop');
$rows[0][] = new tabobject('PAYBACK', "$url&status=PAYBACK&cur=$cur", $label.' ('.$total->PAYBACK.')');
$label = get_string('bill_ALLs', 'local_shop');
$rows[0][] = new tabobject('ALL', "$url&status=ALL&cur=$cur", $label.' ('.$total->ALL.')');

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
        echo '<tr>';
        echo '<td colspan="5" class="grouphead">',
        echo '<b>'.get_string('bill_' . $status . 's', 'local_shop').'</b>';
        echo '</td>';
        echo '</tr>';

        $CFG->subtotal = 0;
        foreach ($billsbystate[$status] as $portlet) {
            $subtotal += floor($portlet->amount * 100) / 100;
            echo $renderer->bill_merchant_line($portlet);
        }
?>
    <tr>
        <td colspan="2" class="groupSubtotal">
        </td>
        <td colspan="3" align="right" class="groupsubtotal">
            <?php
            if ($samecurrency) {
                echo sprintf('%.2f', round($subtotal, 2));
                echo ' ';
                echo get_string($billcurrency.'symb', 'local_shop');
            } else {
                print_string('nosamecurrency', 'local_shop');
            }
            ?>
        </td>
    </tr>
<?php
        $i++;
    }
    echo '</table>';
}

$excelurl = new moodle_url('/local/shop/export/export.php', array('what' => 'allbills', 'format' => 'excel'));
$billurl = new moodle_url('/local/shop/bills/edit_bill.php', array('shopid' => $theshop->id));
?>

<table width="100%">
   <tr>
      <td align="left">
      </td>
      <td align="right">
         <a href="<?php echo ''.$excelurl ?>" target="_blanck"><?php print_string('exportasxls', 'local_shop') ?></a>
          - <a href="<?php echo ''.$billurl ?>"><?php print_string('newbill', 'local_shop') ?></a>
      </td>
   </tr>
</table>
<br />