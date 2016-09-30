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

echo $OUTPUT->heading(get_string('customer', 'local_shop'), 2);
?>
<table class="generalbox" width="100%">
    <tr>
        <td class="cell">
           <?php echo $portlet->firstname ?> <?php echo $portlet->lastname ?> <b>
            <?php print_string('identifiedby', 'local_shop') ?></b>
            (<a href="mailto:<?php echo  $portlet->email ?>"><?php echo $portlet->email ?></a>)<br />
           <b><?php echo $portlet->city ?> (<?php echo $portlet->country ?>)<br />
        </td>
        <td align="right">
            <a href="<?php echo $CFG->wwwroot."/local/shop/customers/view.php?id={$id}&view=viewCustomer&customer={$portlet->id}" ?>" target="_blank"><?php print_string('seethecustomerdetail', 'local_shop') ?></a>
        </td>
    </tr>
</table>