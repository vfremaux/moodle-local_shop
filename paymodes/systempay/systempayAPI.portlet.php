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

$config = get_config('local_shop');

/**
 * @package    shoppaymodes_systempay
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// mandatory
$parms['vads_action_mode'] = 'INTERACTIVE';
$parms['vads_currency'] = $config->systempay_currency_code;
$parms['vads_amount'] = floor($portlet->amount * 100);
$parms['vads_ctx_mode'] = ($config->test) ? 'TEST' : 'PRODUCTION';
$parms['vads_page_action'] = 'PAYMENT';
$parms['vads_payment_config'] = 'SINGLE';
$parms['vads_site_id'] = $config->systempay_merchant_id;
$parms['vads_trans_id'] = $portlet->onlinetransactionid; // 6 chars from 000000 to 899999 / no special chars
$parms['vads_trans_date'] =  gmdate('YmdHis'); // 20 chars max / no special chars
$parms['vads_version'] =  'V2'; // chars max / no special chars

// accessory

$parms['vads_shop_name'] = ''.@$this->theshop->name;

$parms['vads_cust_email'] = $portlet->customer->email;
$parms['vads_cust_city'] = $portlet->customer->city;
$parms['vads_cust_zip'] = $portlet->customer->zip;

$parms['vads_url_success'] = $portlet->returnurl; // return url (normal)
$parms['vads_url_error'] = $portlet->returnurl; // return url (normal)
$parms['vads_url_cancel'] = $portlet->returnurl; // return url (normal)
$parms['vads_url_refused'] = $portlet->returnurl; // return url (normal)

$lang = substr(current_language(), 0, 2);
if (!preg_match('/fr|en|us|it|de|nl|pt|es|ja|zh/', $lang)) $lang = 'en';
$parms['vads_language'] = $lang;

$return_context = 'systempayback' . '-' .$this->theshop->id. '-' .$portlet->transactionid;
$encodedcontext = base64_encode($return_context);
$parms['vads_order_info'] = $encodedcontext; // some private transaction data to restore context on return

// technical tuning
$parms['vads_return_mode'] = 'GET';
$parms['vads_validation_mode'] = 0;

// 3D Secure options
$parms['vads_threeds_mpi'] = 0; // enabled
if (empty($config->systempay_use_3dsecure)) {
    $parms['vads_threeds_mpi'] = 2; // disabled
}

// last signature calculation in test or production mode
$certificate = ($config->test) ? @$config->systempay_test_certificate : @$config->systempay_prod_certificate;
$parms['signature'] = $this->generate_sign($parms, $certificate);

$url = $config->systempay_service_url;

$confirmstr = get_string('confirmorder', 'local_shop');

if (!empty($url)) {
?>
<div class="payportlet">
    <form method="POST" name="systempayform" action="<?php echo $url ?>">
        <?php
        foreach ($parms as $key => $value) {
            $value = htmlentities($value);
            $lang = substr(current_language(), 0, 2);
            echo "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
        }
        echo '<center>';
        echo "<input type=\"image\" src=\"{$CFG->wwwroot}/local/shop/paymodes/systempay/pix/{$lang}.png\" value=\"{$confirmstr}\" />";
        echo '</center>';
        ?>
    </form>
</div>
<?php
} else {
    print_box(get_string('errorsystempaynotsetup', 'shoppaymodes_systempay'));
}
?>