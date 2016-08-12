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

?>
<tr class="taxe" valign="top" >
   <td class="cell c1">
      <?php echo $portlet->title ?>
   </td>
   <td class="cell c2">
       <?php echo $portlet->country ?>
   </td>
   <td class="cell c3">
       <?php echo $portlet->ratio ?>
   </td>
   <td align="right" class="cell lastcol">
      <a class="activeLink" href="<?php echo $CFG->wwwroot."/local/shop/taxes/edit_tax.php?id={$id}&taxid={$portlet->id}&cmd=updatetax"; ?>"><img src="<?php echo $OUTPUT->pix_url('t/edit') ?>" /></a>
      <a class="activeLink" href="<?php echo $portlet->url."&taxid={$portlet->id}&cmd=deletetax"; ?>"><img src="<?php echo $OUTPUT->pix_url('t/delete') ?>" /></a>
   </td>
</tr>