<tr valign="top">
   <td valign="top" class="billlineabstract">
      <?php echo $portlet->name ?>
   </td>
   <td valign="top" class="billlinecode">
      <?php echo $portlet->code ?>
   </td>
   <td valign="top" class="billlineprice" align="right">
      <?php echo sprintf("%.2f", round($portlet->taxedprice, 2)) ?>&nbsp;&nbsp;
   </td>
   <td valign="top" class="billlinequantity" align="right">
      <?php echo $portlet->quant ?>&nbsp;&nbsp;
   </td>
   <td valign="top"  class="billlineprice" align="right">
      <?php echo sprintf("%.2f", round($portlet->quant * $portlet->taxedprice, 2)) ?>&nbsp;&nbsp;
   </td>
</tr>