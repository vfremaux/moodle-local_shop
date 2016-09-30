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
 * Defines form to add a new project
 *
 * @package    local_shop
 * @category   local
 * @reviewer   Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

?>
<tr>
    <td class="param">
       <?php echo $portlet->zoneCode ?>
    </td>
    <td class="value">
       <input type="text" name="zone_<?php echo $portlet->Id ?>" value="<?php echo @$portlet->value ?>" maxlength="10" size="10">
    </td>
    <td class="value">
       <input type="text" name="zone_<?php echo $portlet->Id ?>_formula" value="<?php echo @$portlet->formula ?>" maxlength="64" size="30">
    </td>
    <td class="value" width="50">
       <input type="text" name="zone_<?php echo $portlet->Id ?>_a" value="<?php echo @$portlet->a ?>" maxlength="10" size="10">
    </td>
    <td class="value" width="50">
       <input type="text" name="zone_<?php echo $portlet->Id ?>_b" value="<?php echo @$portlet->b ?>" maxlength="10" size="10">
    </td>
    <td class="value" width="50">
       <input type="text" name="zone_<?php echo $portlet->Id ?>_c" value="<?php echo @$portlet->c ?>" maxlength="10" size="10">
    </td>
</tr>