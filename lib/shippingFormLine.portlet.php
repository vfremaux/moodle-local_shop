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