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

/**
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$str = '';

$str .= '<tr class="billBloc '.$portlet->status.'">';
$str .= '<td valign="top" style="padding : 2px" colspan="4" class="billCategoryTitleBox">';
$str .= get_string('billStatusTitle_{$portlet->status}', 'local_shop');
$str .= '</td>';
$str .= '</tr>';
$str .= '<tr class="bill">';
$str .= '<td width="120" valign="top" style="padding : 2px" class="billId">';
$linkurl = new moodle_url('/local/shop/bills/view.php', array('id' => $id, 'view' => 'viewBill', 'billId' => $portlet->id));
$str .= '<a class="activeLink" href="'.$linkurl.'">B-'.$portlet->date.'-'.$portlet->id.'</a>';
$str .= '</td>';
$str .= '<td width="*" valign="top" style="padding : 2px" class="billTitle">';
$str .= $portlet->title;
$str .= '</td>';
$str .= '<td width="100" valign="top" style="padding : 2px" class="billStatus">';
$str .= $portlet->status;
$str .= '</td>';
$str .= '<td width="100" valign="top" style="padding : 2px" class="billAmount">';
$str .= sprintf("%.2f", round($portlet->amount, 2)).' '.$portlet->currency;
$str .= '</td>';
$str .= '</tr>';

echo $str;