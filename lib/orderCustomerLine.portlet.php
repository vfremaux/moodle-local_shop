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
?>
<tr valign="top">
   <td valign="top" class="billlineabstract">
      <?php echo $portlet->name ?>
   </td>
   <td valign="top" class="billlinecode">
      <?php echo $portlet->code ?>
   </td>
   <td valign="top" class="billlineprice" align="right">
      <?php echo sprintf("%.2f", round($portlet->taxedprice, 2)) ?>&nbsp;&nbsp;
   </td>
   <td valign="top" class="billlinequantity" align="right">
      <?php echo $portlet->quant ?>&nbsp;&nbsp;
   </td>
   <td valign="top"  class="billlineprice" align="right">
      <?php echo sprintf("%.2f", round($portlet->quant * $portlet->taxedprice, 2)) ?>&nbsp;&nbsp;
   </td>
</tr>