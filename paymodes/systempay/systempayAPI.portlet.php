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
 * @package    shoppaymodes_systempay
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$parms['siret'] = $portlet->merchant_id ;
$parms['reference'] = $portlet->transactionid ; // 20 chars max / no special chars

$lang = strtoupper(substr(current_language(), 0, 2));
if (!preg_match('/FR|EN|US|IT|DE|NL|PT|ES/', $lang)) $lang = 'EN';
$parms['langue'] = $lang;

$parms['devise'] = $config->defaultcurrency;
$parms['montant'] = sprintf("%.2f", $portlet->amount);
$parms['taxe'] = '0.00';
$parms['validite'] = date('j/d/Y', time() + DAYSECS); // one day ahead validity
$parms['hmac'] = $this->encode_bin($portlet); 
$parms['moyen'] = 'CBS';
$parms['modalite'] = '1x'; // for CBS : 1x|2x|3x|xx|nx
$parms['methode'] = 'SSL'; // SSL || authent
$parms['valauto'] = 't'; // full automated
$parms['email'] = $portlet->customer->email;
$parms['urlretour'] = $portlet->returnurl; // return url (normal)
$parms['arg1'] = ''; // return url (normal)
$parms['arg2'] = ''; // return url (normal)

$return_context = 'systempayback' . '-' .$this->shopblock->instance->id.'-' .$portlet->transactionid;
$encodedcontext = base64_encode($return_context);
$parms['arg3'] = $encodedcontext; // some private transaction data to restore context on return
$parms['version'] = 1; // SPPLUS Version

$url = $config->systempay_service_url;

$confirmstr = get_string('confirmorder', 'local_shop');

if (!empty($url)) {
?>
<div class="payportlet">
    <form method="get" name="systempayform" action="<?php echo $url ?>">
        <?php 
        foreach ($parms as $key => $value) {
            $value = htmlentities($value);
            $lang = substr(current_language(), 0, 2);
            echo "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
        }
        echo "<input type=\"image\" src=\"{$CFG->wwwroot}/local/shop/paymodes/systempay/pix/{$lang}.png\" value=\"{$confirmstr}\" />";
        ?>
    </form>
</div>
<?php
} else {
}