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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();


// Hide discount individual lines.
if ($portlet->type == 'DISCOUNT') return;

if ($portlet->type == 'BILLING') {
?>
<tr class="<?php echo shop_switch_style('odd','even'); ?>">
   <td valign="top" class="billlineabstract">
      <?php echo  $portlet->abstract ?>
   </td>
   <td valign="top" class="billlinecode">
      <?php echo  $portlet->itemcode ?>
   </td>
   <td valign="top" class="billlineprice" align="right">
      <?php echo  sprintf("%.2f", round($portlet->unitcost, 2)) ?>&nbsp&nbsp;
   </td>
   <td valign="top" class="billlinequantity" align="right">
      <?php echo  $portlet->quantity ?>&nbsp;&nbsp;
   </td>
   <td align="right" valign="top"  class="billtaxamount" align="right">
      <?php echo  sprintf("%.2f", round($portlet->quantity * $portlet->taxamount, 2)) ?>&nbsp&nbsp;
   </td>
   <td align="right" valign="top"  class="billtaxcode" align="right">
      <?php echo  sprintf("%.1f", round($portlet->ratio, 2)) ?>&nbsp&nbsp;
   </td>
   <td align="right" valign="top"  class="billlineprice" align="right">
      <?php echo  sprintf("%.2f", round($portlet->quantity * $portlet->taxedprice, 2)) ?>&nbsp&nbsp;
   </td>
</tr>
<?php
} else {
?>
<tr class="<?php echo  switchStyle('odd','even'); ?>">
    <td colspan="7" class="billlineabstract">
        <?php echo  $portlet->abstract ?>&nbsp;
    </td>
</tr>
<?php
}