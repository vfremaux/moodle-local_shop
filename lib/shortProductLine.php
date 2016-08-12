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
<tr class="shop-<?php echo strtolower($portlet->status) ?>line">
   <td>
        <a href="<?php echo $CFG->wwwroot.'/local/shop/products/view.php?view=viewProduct&productid={$portlet->id}&id={$id}' ?>"><?php echo $portlet->code ?></a>
   </td>
   <td>
        <?php echo $portlet->name ?>
   </td>
   <td>
        <?php print_string($portlet->status) ?>
   </td>
   <td>
        <?php echo $portlet->stock ?>
   </td>
   <td>
        <?php echo sprintf("%.2f", round($portlet->price1, 2)) ?>
   </td>
</tr>