<tr class="shop-<?php echo strtolower($subportlet->status) ?>line">
    <td class="<?php echo (@$subportlet->masterrecord == 0) ? "" : "engraved" ; ?>">
           <img src="<?php echo $subportlet->thumb ?>" vspace="10" border="0" height="50">
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
         <b><?php echo $subportlet->code ?></b>
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
        <?php echo $subportlet->shortname ?>
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
        <?php echo sprintf("%.2f", round($portlet->price1, 2)) ?><br>
        (<?php echo $subportlet->taxcode ?>)
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? '' : 'slaved' ; ?>">
        <?php print_string($subportlet->status, 'local_shop') ?>
    </td>
    <td align="right" width="10">
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/edit_product.php?id={$id}&amp;productid={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('t/edit') ?>" title="<?php print_string('editproduct', 'local_shop') ?>" /></a><br/>
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=unlinkproduct&amp;productid={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('unlink', 'local_shop') ?>" title="<?php print_string('removeproductfromset', 'local_shop') ?>" /></a><br/>
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=deleteItems&amp;items={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('t/delete') ?>" title="<?php print_string('deleteproduct', 'local_shop') ?>" /></a>
<?php
if ($subportlet->catalog->isslave) {
    if ($subportlet->masterrecord == 1) {
?>
     <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=makecopy&productid={$subportlet->id}" ?>"><img src="<?php $OUTPUT->pix_url('t//copy') ?>" title="<?php print_string('createlocalversion', 'local_shop') ?>" /></a>
<?php
    } else {
?>
     <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=freecopy&productid={$portlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('uncopy', 'local_shop') ?>" title="<?php print_string('removelocalversion', 'local_shop') ?>" /></a>
<?php
    }
}
?>
    </td>
</tr>