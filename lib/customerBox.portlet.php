<?php 
echo $OUTPUT->heading(get_string('customer', 'local_shop'), 2); 
?>
<table class="generalbox" width="100%">
    <tr>
        <td class="cell">
           <?php echo $portlet->firstname ?> <?php echo $portlet->lastname ?> <b> <?php print_string('identifiedby', 'local_shop') ?></b> (<a href="mailto:<?php echo  $portlet->email ?>"><?php echo $portlet->email ?></a>)<br />
           <b><?php echo $portlet->city ?> (<?php echo $portlet->country ?>)<br />           
        </td>
        <td align="right">
            <a href="<?php echo $CFG->wwwroot."/local/shop/customers/view.php?id={$id}&view=viewCustomer&customer={$portlet->id}" ?>" target="_blank"><?php print_string('seethecustomerdetail', 'local_shop') ?></a>
        </td>
    </tr>
</table>