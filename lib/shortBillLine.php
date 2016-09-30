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
   <td>
        <a href="<?php echo $CFG->wwwroot.'/local/shop/bills/view?billid={$portlet->id}' ?>"><?php echo $portlet->id ?></a>
   </td>
   <td>
        <?php echo  $portlet->title ?>
   </td>
   <td>
        <?php echo  $portlet->userid ?>
   </td>
   <td>
        <?php echo  $portlet->emissiondate ?>
   </td>
   <td>
        <?php echo  $portlet->transactionid ?>
   </td>
</tr>