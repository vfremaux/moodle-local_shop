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
 * @package    shoppaymodes_paypal
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$lang = substr(current_language(), 0, 2);
if ($lang == 'en') $lang = 'us';
$ulang = strtoupper($lang);
?>
<div align="center">

<p><?php print_string("paymentrequired") ?></p>
<p><b><?php echo $instancename; ?></b></p>
<p><img alt="<?php print_string('paypalaccepted', 'shoppaymodes_paypal') ?>" src="<?php echo $portlet->paypallogo_url ?>" /></p>
<p><?php print_string("paymentinstant") ?></p>
<?php
if (!empty($config->test)) {
?>
<table class="width500" style="border : 2px solid red">
    <tr>
        <td align="center">
           <span class="error"><?php print_string('testmode', 'local_shop') ?></span><br />
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" style="display : inline">
            <input type="hidden" name="business" value="<?php echo $config->paypal_sellertestname ?>">
            <input type="hidden" name="item_name" value="<?php echo $config->paypal_sellertestitemname ?>">
<?php
} else {
?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="display : inline">

<input type="hidden" name="business" value="<?php echo $config->paypal_sellername ?>">
<input type="hidden" name="item_name" value="<?php echo $config->paypal_selleritemname ?>">
<?php
}
?>
<input type="hidden" name="charset" value="utf-8" />
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="currency_code" value="<?php echo $portlet->currency ?>">
<input type="hidden" name="amount" value="<?php echo $portlet->amount ?>">
<input type="hidden" name="quantity" value="1">
<input type="hidden" name="item_number" value="">
<input type="hidden" name="shipping" value="">
<!-- input type="hidden" name="shipping2" value="" --> 
<!-- input type="hidden" name="handling" value="" --> 
<!-- input type="hidden" name="tax" value="" --> 
<input type="hidden" name="for_auction" value="false" />
<input type="hidden" name="no_shipping" value="1" >
<!-- input type="hidden" name="cn" value="" -->
<input type="hidden" name="no_note" value="1">  
<input type="hidden" name="on0" value="<?php print_string("user") ?>" />
<input type="hidden" name="os0" value="<?php p($portlet->lastname.' '.$portlet->firstname) ?>" />
<!-- input type="hidden" name="on1" value="" -->
<!-- input type="hidden" name="os1" value="" -->
<input type="hidden" name="custom" value="<?php echo $this->theshop->id ?>">
<input type="hidden" name="invoice" value="<?php echo $portlet->transid ?>">
<input type="hidden" name="notify_url" value="<?php echo $portlet->notify_url ?>">
<input type="hidden" name="return" value="<?php echo $portlet->return_url ?>">
<input type="hidden" name="cancel_return" value="<?php echo $portlet->cancel_return ?>">
<input type="hidden" name="rm" value="2">
<input type="hidden" name="image_url" value="">
<input type="hidden" name="lc" value="<?php echo $portlet->lang ?>">
<input type="hidden" name="cs" value="1">
<input type="hidden" name="cbt" value="" />

<input type="hidden" name="email" value="<?php echo $portlet->email ?>">
<input type="hidden" name="first_name" value="<?php echo $portlet->firstname ?>">
<input type="hidden" name="last_name" value="<?php echo $portlet->lastname ?>">
<input type="hidden" name="address1" value="<?php echo $portlet->address ?>">
<input type="hidden" name="city" value="<?php echo $portlet->city ?>">
<input type="hidden" name="country" value="<?php echo $portlet->country ?>">
<input type="hidden" name="zip" value="<?php echo $portlet->zip ?>">

<!-- input type="hidden" name="night_phone_a" value="1">
<input type="hidden" name="night_phone_b" value="1">
<input type="hidden" name="day_phone_a" value="1">
<input type="hidden" name="day_phone_b" value="1" -->

<input type="submit" value="<?php print_string("paypalmsg", "shoppaymodes_paypal") ?>" />

</form>
<?php
if (!empty($config->test)) {
?>
        </td>
     </tr>
</table>
<?php
}


