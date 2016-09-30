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

// Hide discount individual lines.
if ($portlet->type == 'DISCOUNT') {
    return;
}

if ($portlet->type == 'BILLING') {
    $rowcount = (0 + @$rowcount + 1) % 2;
    $rowclass = ($rowcount) ? 'odd' : 'even';
?>
<tr valign="top" class="<?php echo $rowclass ?>">
  <td align="left" class="cell c0">
     <?php echo $portlet->name ?>
  </td>
  <td align="left" class="cell c1">
     <?php echo $portlet->code ?>
  </td>
  <td align="left" class="cell c2">
     <?php echo sprintf("%.2f", round($portlet->taxedprice, 2)) ?>
  </td>
  <td align="left" class="cell c3">
     <?php echo $portlet->quantity ?>
  </td>
  <td align="right" class="cell lastcol">
     <?php echo sprintf("%.2f", round($portlet->quantity * $portlet->taxedprice, 2 )) ?>&nbsp;&nbsp;
  </td>
</tr>
<?php
} else if ($portlet->type == 'COMMENT') {
?>
<tr class="<?php echo $rowclass ?>">
  <td valign="top" class="billlinecomment" colspan="5">
     <?php echo $portlet->abstract ?>&nbsp;
  </td>
</tr>
<?php
}
$requireddata = $DB->get_field('local_shop_catalogitem', 'requireddata', array('code' => $portlet->code));
$label = $DB->get_field('local_shop_catalogitem', 'shortname', array('code' => $portlet->code));
if (!empty($requireddata)) {
?>
<tr valign="top" class="<?php echo $rowclass ?>">
  <td class="requireddatatitle" colspan="1" align="left">
     <?php print_string('requireddata', 'local_shop') ?>&nbsp;
  </td>
  <td class="requireddata" colspan="4" align="left">
  <?php
    $datapairs = explode(';', $requireddata);
    foreach ($datapairs as $apair) {
        list($fieldname, $fieldtype, $fieldlabel) = split(':', $apair);
        echo $fieldlabel;
        if (preg_match('/^!(.*)$/', $fieldname, $matches)) {
            $fieldname = $matches[1];
            $hasrequireddata[] = "required_{$label}_{$fieldname}";
            echo ' <span style="color:red"><sup>*</sup></span>: ';
        }
        switch ($fieldtype) {
            case 'textfield' :
                echo "<input type=\"text\" name=\"required_{$label}_{$fieldname}\" size=\"40\" onchange=\"listen_to_required_changes()\" />";
                break;
            case 'checkbox' :
                echo "<input type=\"checkbox\" name=\"required_{$label}_{$fieldname}\" value=\"0\"  onchange=\"listen_to_required_changes()\" /> ".get_string('no');
                echo " - <input type=\"checkbox\" name=\"required_{$label}_{$fieldname}\" value=\"1\"  onchange=\"listen_to_required_changes()\" /> ".get_string('yes');
                break;
            case 'textarea' :
                echo "<textarea name=\"required_{$label}_{$fieldname}\" rows=\"5\" cols=\"30\"  onchange=\"listen_to_required_changes()\"></textarea>";
                break;
        }
        echo '<br/>';
    }
  ?>
  </td>
</tr>
<?php
}