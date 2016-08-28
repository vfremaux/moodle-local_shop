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
<tr class="">
      <!-- td width="30" class="<?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
          <input type="checkbox" name="items[]" value="<?php echo $portlet->id ?>" />
      </td -->
      <td class="<?php echo (@$portlet->masterrecord == 0) ? "" : "engraved" ; ?>">
         <img src="<?php echo $OUTPUT->pix_url('productbundle', 'local_shop') ?>" height="50" />
      </td>
      <td class="code <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
         <b><?php echo $portlet->code ?></b><br/>
         (<?php echo $portlet->shortname ?>)
    </td>
      <td class="name productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
          <?php echo $portlet->name ?>
      </td>
    <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo sprintf("%.2f", round($portlet->price1, 2)) ?><br>
        (<?php echo $portlet->taxcode ?>)
    </td>
    <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo sprintf("%.2f", round($portlet->bundleTTCPrice, 2)) ?>
    </td>
    <td class="status productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>">
        <?php echo get_string($portlet->status, 'local_shop') ?>
    </td>
    <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
        <?php echo $portlet->sold ?>
    </td>
    <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
        <?php echo $portlet->stock ?>
    </td>
    <td class="amount productAdminLine <?php echo (@$portlet->masterrecord == 0) ? "" : "slaved" ; ?>" align="center">
        <?php echo ($portlet->renewable) ? get_string('yes') : get_string('no') ?>
    </td>
    <td align="right">
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/edit_bundle.php?id={$id}&amp;bundleid={$portlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('t/edit') ?>" title="<?php echo get_string('editbundle', 'local_shop') ?>" /></a><br/>
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=unlinkset&amp;bundleid={$portlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('t/delete') ?>" title="<?php echo get_string('deletebundle', 'local_shop') ?>" /></a><br/>
        <a href="<?php echo $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=delete&amp;bundleid={$portlet->id}" ?>"><img src="<?php echo $OUTPUT->pix_url('unlink', 'local_shop') ?>" title="<?php echo get_string('deletealllinkedproducts', 'local_shop') ?>" /></a><br/>
        <?php
        if ($portlet->catalog->isslave) {
            if ($portlet->masterrecord == 1) {
                $createoverrridestr = get_string('createoverride', 'local_shop');
                 echo "<a href=\"{$CFG->wwwroot}/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=makecopy&amp;productid={$portlet->id}\"><img src=\"".$OUTPUT->pix_url('copy', 'block_coursesop')."\" title=\"{$createoverridestr}\" /></a>";
            } else {
                $deleteoverrridestr = get_string('deleteoverride', 'local_shop');
                 echo "<a href=\"{$CFG->wwwroot}/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts&amp;cmd=freecopy&amp;productid={$portlet->id}\"><img src=\"".$OUTPUT->pix_url('uncopy', 'block_coursesop')."\" title=\"{$deleteoverridestr}\" /></a>";
            }
        }
        ?>
    </td>
</tr>
<tr>
       <td colspan="2">
           &nbsp;
       </td>
       <td class="list" colspan="9">
        <?php
        if (count($portlet->set) == 0) {
            echo get_string('noproductinbundle', 'local_shop');
        } else {
            $codestr = get_string('code', 'local_shop');
            $namestr = get_string('name', 'local_shop');
            $pricestr = get_string('price', 'local_shop');
            $ttcstr = get_string('TTC', 'local_shop');
            $availabilitystr = get_string('availability', 'local_shop');

            $table = new html_table();
            $table->head = array('', "<b>$codestr</b>", "<b>$namestr</b>", "<b>$pricestr</b>", "<b>$ttcstr</b>", "<b>$availabilitystr</b>", '');
            $table->width = '100%';
            $table->size = array('10%', '10%', '30%', '10%', '10%', '10%', '20%');
            $table->align = array('left', 'left', 'left', 'right', 'right', 'center', 'right');
            foreach ($portlet->set as $subportlet) {
                if ($subportlet->masterrecord == 1) {
                    $table->rowclasses[] = 'slaved';
                } else {
                    $table->rowclasses[] = '';
                }
                $row = array();
                $row[] = "<img class=\"thumb\" src=\"{$subportlet->thumb}\" height=\"50\">";
                $row[] = $subportlet->code;
                $row[] = $subportlet->name;
                $row[] = sprintf("%.2f", round($subportlet->price1, 2)).'<br/>('.$subportlet->taxcode.')';
                $row[] = sprintf("%.2f", round($subportlet->TTCprice, 2));
                $row[] = get_string($subportlet->status, 'local_shop');

                $commands = '';
                if (@$subportlet->masterrecord == 0) {
                    $commands .= "<a href=\"{$CFG->wwwroot}/local/shop/products/edit_product.php?id={$id}&amp;productid={$subportlet->id}\"><img src=\"".$OUTPUT->pix_url('t/edit').'" title="'.get_string('editproduct', 'local_shop')."\"></a><br/>";
                }
                $baseurl = $CFG->wwwroot."/local/shop/products/view.php?id={$id}&amp;view=viewAllProducts";
                $commands .= "<a href=\"{$baseurl}&amp;cmd=unlinkproduct&amp;productid={$subportlet->id}\"><img src=\"".$OUTPUT->pix_url('unlink', 'local_shop').'" title=\"'.get_string('removeproductfrombundle', 'local_shop').'" /></a><br/>';
                $commands .= "<a href=\"{$baseurl}&amp;cmd=deleteitems&amp;items={$subportlet->id}\"><img src=\"".$OUTPUT->pix_url('t/delete').'" title="'.get_string('delete').'" /></a><br/>';
                if ($subportlet->catalog->isslave) {
                    if ($subportlet->masterrecord == 1) {
                         $commands .= "<a href=\"{$baseurl}&amp;cmd=makecopy&amp;productId={$subportlet->id}\"><img src=\"".$OUTPUT->pix_url('copy', 'local_shop').'" title="'.get_string('createoverride', 'local_shop').'" /></a>';
                    } else{
                         $commands .= "<a href=\"{$baseurl}&amp;cmd=freecopy&amp;productid={$subportlet->id}\"><img src=\"".$OUTPUT->pix_url('uncopy', 'local_shop').'" title="'.get_string('removeoverride', 'local_shop').'" /></a>';
                    }
                }
                $row[] = $commands;

                $table->data[] = $row;
            }

            echo html_writer::table($table);
        }
        ?>
    </td>
</tr>