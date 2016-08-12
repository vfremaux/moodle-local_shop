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
<tr class="<?php echo strtolower($subportlet->status) ?>line">
    <td class="<?php echo (@$portlet->masterrecord == 0) ? "" : "engraved" ; ?>">
       <img src="<?php echo $subportlet->thumb ?>" vspace="10" border="0" height="50">
    </td>
    <td class="bundleElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo $subportlet->code ?>
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo $subportlet->shortname ?>
    </td>
    <td class="setElementCode <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo $subportlet->name ?>
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo sprintf("%.2f", round($subportlet->price1, 2)) ?><br>
        (<?php echo $subportlet->taxcode ?>)
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo sprintf("%.2f", round($subportlet->TTCprice, 2)) ?><br>
    </td>
    <td class="setElementAttribute <?php echo (@$subportlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo get_string($subportlet->status, 'local_shop') ?>
    </td>
    <td align="right">
        <?php
        if (@$portlet->masterrecord == 0) {
            echo "<a href=\"{$CFG->wwwroot}/local/shop/products/edit_product.php?id={$id}&amp;productid={$subportlet->id}\"><img src=\"".$OUTPUT->pix_url('t/edit')."\" title=\"".get_string('editproduct', 'local_shop')."\"></a><br/>";
        }
        ?>
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=unlinkproduct&amp;productid={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('unlink', 'local_shop') ?>" title="<?php print_string('removeproductfrombundle', 'local_shop') ?>"></a><br/>
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=deleteitems&amp;items={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('t/delete') ?>" title="<?php print_string('delete') ?>"></a><br/>
<?php
if ($portlet->catalog->isslave) {
    if ($portlet->masterrecord == 1) {
?>
     <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=makecopy&amp;productId={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('copy', 'local_shop') ?>" title="<?php print_string('createoverride', 'local_shop') ?>"></a>
<?php
    } else{
?>
     <a href="<?php echo $CFG->wwwroot."/local/shop/products/viewAllProducts.php?id=$id&cmd=freecopy&productid={$subportlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('uncopy', 'local_shop') ?>" border="0" title="<?php print_string('removeoverride', 'local_shop') ?>"></a>
<?php
    }
}
?>
    </td>
</tr>