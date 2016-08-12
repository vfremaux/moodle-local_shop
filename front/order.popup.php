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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use \local_shop\Bill;

// get all context information

$config = get_config('local_shop');

list($theShop, $theCatalog, $theBlock) = shop_build_context();

$renderer = shop_get_renderer('front');
$renderer->load_context($theShop, $theBlock);

// Security

// require_login();

// invoke controller.

$action = optional_param('what', '', PARAM_TEXT);
$transid = required_param('transid', PARAM_TEXT);

try {
    $aFullBill = Bill::get_by_transaction($transid);
} catch(Exception $e) {
    print_error('invalidbillid', 'local_shop', new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'shopid' => $theShop->id, 'blockid' => (0 + @$theBlock->instance->id))));
}

$url = new moodle_url('/local/shop/front/order.popup.php', array('shopid' => $theShop->id, 'blockid' => (0 + @$theBlock->instance->id), 'transid' => $transid));
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('popup');

echo $OUTPUT->header();

echo '<div style="max-width:780px">';

?>
<table>
    <tr>
        <td><img src="<?php echo $OUTPUT->pix_url('logo', 'theme') ?>"></td>
        <td align="right"><a href="#" onclick="window.print();return false;"><?php echo get_string('printorderlink', 'local_shop') ?></a></td>
    </tr>
    <tr>
        <td colspan="2" align="center">
        <?php
        $headerstring = ($aFullBill->idnumber) ? get_string('bill', 'local_shop') : get_string('ordersheet', 'local_shop') ;
        echo $OUTPUT->heading($headerstring, 1);
        ?>
        </td>
    </tr>
    <tr valign="top">
      <td width="60%">
         <b><?php echo get_string('transactioncode', 'local_shop') ?>:</b><br />
         <code style="background-color : #E0E0E0"><?php echo $transid ?></code><br />
         <span class="smaltext"><?php echo get_string('providetransactioncode', 'local_shop') ?></span>
      </td>
      <td width="40%" align="right" rowspan="5" class="order-preview-seller-address">
         <b><?php echo get_string('on', 'local_shop') ?>:</b> <?php echo userdate($aFullBill->emissiondate) ?><br />
         <br />
         <b><?php echo $config->sellername ?></b><br />
         <b><?php echo $config->selleraddress ?></b><br />
         <b><?php echo $config->sellerzip ?> <?php echo  $config->sellercity ?></b><br />
         <?php echo $config->sellercountry ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('customer', 'local_shop') ?>: </b> <?php echo $aFullBill->customer->lastname ?> <?php echo $aFullBill->customer->firstname ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('city') ?>: </b>
         <?php echo $aFullBill->customer->zip ?> <?php echo $aFullBill->customer->city ?>
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('country') ?>: </b> <?php echo  strtoupper($aFullBill->customer->country) ?>
      </td>
      <td>
      &nbsp;
      </td>
   </tr>
   <tr>
      <td width="60%" valign="top">
         <b><?php echo get_string('email') ?>: </b> <?php echo $aFullBill->customer->email ?>
      </td>
   </tr>
   <tr>
      <td colspan="2">
      &nbsp;<br />
      </td>
   </tr>
   <tr>
      <td colspan="2" class="sectionHeader">
         <?php echo $OUTPUT->heading(get_string('order', 'local_shop'), 2); ?>
      </td>
   </tr>
</table>

<?php
echo '<table>';
echo $renderer->order_line(null);
foreach ($aFullBill->items as $item) {
    echo $renderer->order_line($item->catalogitem->shortname);
}
echo '</table>';

echo $renderer->full_order_totals();
echo $renderer->full_order_taxes();

echo $OUTPUT->heading(get_string('paymentmode', 'local_shop'), 2);

require_once $CFG->dirroot.'/local/shop/paymodes/'.$aFullBill->paymode.'/'.$aFullBill->paymode.'.class.php';

$classname = 'shop_paymode_'.$aFullBill->paymode;

echo '<div id="shop-order-paymode">';
$pm = new $classname($theShop);
$pm->print_name();
echo '</div>';

echo '<div id="order-mailto">';
echo '<p>'.get_string('forquestionssendmailto', 'local_shop').' : <a href="mailto:'.$config->sellermail.'">'.$config->sellermail.'</a>';
echo '</div>';
echo '</div>';
echo $OUTPUT->footer();