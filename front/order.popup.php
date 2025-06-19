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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;

// Get all context information.

$config = get_config('local_shop');

list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

// Invoke controller.

$action = optional_param('what', '', PARAM_TEXT);
$transid = required_param('transid', PARAM_TEXT);

try {
    $afullbill = Bill::get_by_transaction($transid);
} catch (Exception $e) {
    $params = ['view' => 'shop', 'shopid' => $theshop->id, 'blockid' => (0 + @$theblock->instance->id)];
    $viewurl = new moodle_url('/local/shop/front/view.php', $params);
    throw new moodle_exception(get_string('invalidbillid', 'local_shop', $viewurl));
}

$params = ['shopid' => $theshop->id, 'blockid' => (0 + @$theblock->instance->id), 'transid' => $transid];
$url = new moodle_url('/local/shop/front/order.popup.php', $params);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('popup');

$renderer = shop_get_renderer('front');
$renderer->load_context($theshop, $thecatalog, $theblock);

echo $OUTPUT->header();

echo '<div style="max-width:780px">';

echo $renderer->invoice_header($afullbill);

echo '<table>';
echo $renderer->order_line(null);
foreach ($afullbill->items as $item) {
    echo $renderer->order_line($item->catalogitem->shortname);
}
echo '</table>';

echo $renderer->full_order_totals($afullbill, $theshop);
echo $renderer->full_order_taxes($afullbill, $theshop);

echo $OUTPUT->heading(get_string('paymode', 'local_shop'), 2);

require_once($CFG->dirroot.'/local/shop/paymodes/'.$afullbill->paymode.'/'.$afullbill->paymode.'.class.php');

$classname = 'shop_paymode_'.$afullbill->paymode;

echo '<div id="shop-order-paymode">';
$pm = new $classname($theshop);
$pm->print_name();
echo '</div>';

echo '<div id="order-mailto">';
$hlpstr = get_string('forquestionssendmailto', 'local_shop');
echo '<p>'.$hlpstr.' : <a href="mailto:'.$config->sellermail.'">'.$config->sellermail.'</a>';
echo '</div>';
echo '</div>';
echo $OUTPUT->footer();
