<?php

// hide discount individual lines
if ($portlet->type == 'DISCOUNT') return;

if ($portlet->type == 'BILLING') {
    $rowcount = (0 + @$rowcount + 1) % 2;
    $rowclass = ($rowcount) ? 'odd' : 'even' ;
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
} elseif ($portlet->type == 'COMMENT') {
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