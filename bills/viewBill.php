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

require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

use \local_shop\Bill;
use \local_shop\BillItem;

$relocated = optional_param('relocated', '', PARAM_TEXT);
$z = optional_param('z', '', PARAM_TEXT);

/* perform local commands on orderitems */
$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/bills/bills.controller.php');
    $controller = new \local_shop\bills\bills_controller();
    $controller->process($action);
}

$aFullBill = new Bill($billid); // Complete bill data

echo $out;
echo $OUTPUT->box_start('', 'billpanel');

?>

<form name="selection" action="<?php echo $url ?>" method="get">
<input type="hidden" name="what" value="" />
<input type="hidden" name="items" value="" />
</form>

<table class="generaltable" width="100%">
<tr>
   <td valign="top" style="padding : 2px" colspan="5" class="billListTitle">
      <h1><?php
              if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'PLACED') {
                  print_string('order', 'local_shop');
              } else {
                  print_string('bill', 'local_shop');
              }
              ?> <span class="titleData">B-<?php echo date('Ymd', $aFullBill->emissiondate); ?>-<?php echo $aFullBill->id; ?></span></h1><br/>
      <?php echo userdate($aFullBill->emissiondate) ?>
   </td>
   <td colspan="3">
       <b><?php print_string('transactionid', 'local_shop') ?>: </b><br />
       <div id="transactionid"><?php echo $aFullBill->transactionid ?></div><br />
<?php
if ($aFullBill->onlinetransactionid != '') {
    echo '<b>'.get_string('paimentcode', 'local_shop').'</b><br />';
    echo '<div id="transactionid">'.$aFullBill->onlinetransactionid.'</div>';
}
if ($aFullBill->transactionid == '') {
    echo get_string('nocodegenerated', 'local_shop').'<br/>';
    echo '<a href="'.$url.'&cmd=generatecode">'.get_string('generateacode', 'local_shop').'</a>';
}
?>
       </td>
       <td colspan="2" valign="top">
           <b><?php print_string('lettering', 'local_shop') ?></b>
           <?php echo $OUTPUT->help_icon('lettering', 'local_shop'); ?>
           <br/>
           <?php
        if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'PLACED' || $aFullBill->status == 'WORKING') {
            print_string('noletteringaspending', 'local_shop');
            echo '<br/>';
        } else {
               if (!empty($letteringfeedback)) {
                   echo $letteringfeedback;
               }
           ?>
           <form name="billletteringform" action="" method="post" >
           <input type="hidden" name="view" value="viewBill" />
           <input type="hidden" name="id" value="<?php p($id) ?>" />
           <input type="hidden" name="billid" value="<?php p($aFullBillid) ?>" />
           <input type="hidden" name="what" value="reclettering" />
           <input type="text" name="idnumber" value="<?php echo $aFullBill->idnumber ?>" />
           <input type="submit" name="go_lettering" value="<?php print_string('updatelettering', 'local_shop') ?>" />
           </form><br/>
           <?php
           }
           ?>
           <b><?php print_string('paymodes', 'local_shop') ?>: </b><?php echo get_string($aFullBill->paymode, 'shoppaymodes_'.$aFullBill->paymode) ?>
    </td>
</tr>
<tr>
   <td valign="top" style="padding : 2px" colspan="10" class="billTitle">
      <h1><?php print_string('title', 'local_shop') ?> : <span class="titleData"><?php echo $aFullBill->title ?></span></h1>
   </td>
</tr>
</table>
<?php

echo $renderer->customer_info($aFullBill, true);

echo $OUTPUT->heading(get_string('order', 'local_shop'), 2);

echo '<table class="generaltable" width="100%">';
if (count($aFullBill->items) == 0) {
    echo $renderer->no_items();
} else {
    echo $renderer->billitem_line(null);
    if ($aFullBill->items) {
        foreach ($aFullBill->items as $portlet) {
            if (($action == 'relocating') && ($portlet->ordering <= $z)) {
                echo $renderer->relocate_box($portlet->id, $portlet->ordering, $z, $relocated);
            }
            if (($action != 'relocating') || ($portlet->id != $relocated)) {
                echo $renderer->billitem_line($portlet);
            }
            if (($action == 'relocating') && ($portlet->ordering > $z)) {
                echo $renderer->relocate_box($portlet->id, $portlet->ordering, $z, $relocated);
            }
        }
    }
}
echo '</table>';

echo $renderer->full_bill_totals($aFullBill);
echo $renderer->full_bill_taxes($aFullBill);

echo '<div class="shop-bills-flowcontrol">';
echo $renderer->flow_controller($aFullBill->status, $url);
echo '</div>';

echo '<div class="shop-bills-attachments">';
echo $renderer->attachments($aFullBill);
echo '</div>';

echo $renderer->bill_controls($aFullBill);

echo $OUTPUT->box_end();