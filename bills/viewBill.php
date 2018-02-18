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

// We needs them later in this script.
$relocated = optional_param('relocated', '', PARAM_TEXT);
$z = optional_param('z', '', PARAM_TEXT);

/* perform local commands on orderitems */
$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/bills/bills.controller.php');
    $controller = new \local_shop\backoffice\bill_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $controller->process($action);
}

$afullbill = new Bill($billid); // Complete bill data.

echo $out;
echo $OUTPUT->box_start('', 'billpanel');

echo '<form name="selection" action="'.$url.'" method="get">';
echo '<input type="hidden" name="what" value="" />';
echo '<input type="hidden" name="items" value="" />';
echo '</form>';

echo '<table class="generaltable" width="100%">';
echo '<tr>';
echo '<td valign="top" style="padding : 2px" colspan="5" class="billListTitle">';

if ($afullbill->status == 'PENDING' || $afullbill->status == 'PLACED') {
    $heading = get_string('order', 'local_shop');
} else {
    $heading = get_string('bill', 'local_shop');
}
$billunique = 'B-'.date('Ymd', $afullbill->emissiondate).'-'.$afullbill->id;
$heading .= ' <span class="titleData">'.$billunique.'</span></h1><br/>';
$heading .= userdate($afullbill->emissiondate);

echo $OUTPUT->heading($heading);
echo '</td>';

echo '<td colspan="3">';
echo '<b>'.get_string('transactionid', 'local_shop').': </b><br />';
$params = array('id' => $theshop->id, 'transid' => $afullbill->transactionid);
$scanurl = new moodle_url('/local/shop/front/scantrace.php', $params);
echo '<div id="transactionid"><a href="'.$scanurl.'" target="_blank">'.$afullbill->transactionid.'</a></div><br />';

if ($afullbill->onlinetransactionid != '') {
    echo '<b>'.get_string('paimentcode', 'local_shop').'</b><br />';
    echo '<div id="transactionid">'.$afullbill->onlinetransactionid.'</div>';
}
if ($afullbill->transactionid == '') {
    echo get_string('nocodegenerated', 'local_shop').'<br/>';
    echo '<a href="'.$url.'&what=generatecode">'.get_string('generateacode', 'local_shop').'</a>';
}
echo '</td>';

echo '<td colspan="2" valign="top">';
echo '<b>'.get_string('lettering', 'local_shop').'</b>';
echo $OUTPUT->help_icon('lettering', 'local_shop');
echo '<br/>';

if ($afullbill->status == 'PENDING' || $afullbill->status == 'PLACED' || $afullbill->status == 'WORKING') {
    print_string('noletteringaspending', 'local_shop');
    echo '<br/>';
} else {
    if (!empty($letteringfeedback)) {
        echo $letteringfeedback;
    }
    echo $renderer->lettering_form($theshop->id, $afullbill);
    echo '<br/>';
}
echo '<b>'.get_string('paymodes', 'local_shop').': </b>';
echo get_string($afullbill->paymode, 'shoppaymodes_'.$afullbill->paymode);
echo '</td>';

echo '</tr>';

echo '<tr>';
echo '<td valign="top" style="padding : 2px" colspan="10" class="billTitle">';
echo $OUTPUT->heading(get_string('title', 'local_shop').' : <span class="titleData">'.$afullbill->title.'</span>');
echo '</td>';
echo '</tr>';

echo '</table>';

echo $renderer->customer_info($afullbill, true);

echo $OUTPUT->heading(get_string('order', 'local_shop'), 2);

echo '<table class="generaltable" width="100%">';
if (count($afullbill->items) == 0) {
    echo $renderer->no_items();
} else {
    echo $renderer->billitem_line(null);
    if ($afullbill->items) {
        foreach ($afullbill->items as $portlet) {
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

echo $renderer->full_bill_totals($afullbill);
echo $renderer->full_bill_taxes($afullbill);

echo '<div class="shop-bills-flowcontrol">';
echo $renderer->flow_controller($afullbill->status, $url);
echo '</div>';

echo '<div class="shop-bills-attachments">';
echo $renderer->attachments($afullbill);
echo '</div>';

echo $renderer->bill_controls($afullbill);

echo $OUTPUT->box_end();