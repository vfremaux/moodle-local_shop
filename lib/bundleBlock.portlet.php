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

defined('MOODLE_INTERNAL') || die();

?>
<table class="shop-article" width="100%">
   <tr valign="top">
        <td width="200" rowspan="4">
            <img src="<?php echo $portlet->thumb ?>" vspace="10" border="0"><br>
<?php
if ($portlet->image != '') {
?>
            <a href="Javascript:openPopup('photo.php?img=<?php echo $portlet->image ?>')"><?php echo get_string('viewlarger', 'local_shop') ?></a>
<?php
}
?>
        </td>
        <td width="*" class="shop-producttitle">
            <?php echo $portlet->name ?>
        </td>
    </tr>
    <tr>
        <td class="shop-productcontent">
         <?php echo format_string($portlet->description) ?>
        </td>
   </tr>
   <tr>
           <td>
            <?php

            function bundle_subportlet($portlet) {
                global $CFG, $OUTPUT;

                include $CFG->dirroot.'/local/shop/lib/productBlock.portlet.php';
            }

            $TTCprice = 0;
            foreach ($portlet->set as $subportlet) {
                $subportlet->TTCprice = shop_calculate_taxed($subportlet->price1, $subportlet->taxcode);
                $TTCprice += $subportlet->TTCprice;
                $subportlet->noorder = true; // Bundle can only be purchased as a group.
                bundle_subportlet($subportlet);
            }
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <strong><?php print_string('ref', 'local_shop') ?> : <?php echo $portlet->code ?> - </strong>
            <?php print_string('puttc', 'local_shop') ?> = <b><?php echo $TTCprice.' '. $portlet->currency ?> </b><br>
            <input type="button" name="" value="<?php print_string('buy', 'local_shop') ?>" onclick="addOneUnit('<?php echo $CFG->wwwroot ?>', '<?php echo $portlet->shortname ?>', '<?php echo $portlet->code ?>', <?php echo $TTCprice ?>, '<?php echo $portlet->maxdeliveryquant ?>')">
            <span id="bag_<?php echo $portlet->shortname ?>"></span>
        </td>
    </tr>
</table>