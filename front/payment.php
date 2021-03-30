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
 * Payment phase of the purchase process
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

// In case session is lost, go to the public entrance of the shop.
if (!isset($SESSION->shoppingcart) || !isset($SESSION->shoppingcart->customerinfo)) {
    $params = array('id' => $theshop->id, 'blockid' => @$theblock->id, 'view' => 'shop');
    redirect(new moodle_url('/local/shop/front/view.php', $params));
}

$paymentplugin = shop_get_payment_plugin($theshop); // Finds in session the paymode.

$action = optional_param('what', '', PARAM_TEXT);
if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/payment.controller.php');
    $controller = new \local_shop\front\payment_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $resulturl = $controller->process($action);
    if (!empty($resulturl)) {
        redirect($resulturl);
    }
}

echo $out;

// Start printing page.

echo $renderer->progress('PAYMENT');

echo $OUTPUT->box_start('', 'shop-payment');

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

echo '<center>';
echo $OUTPUT->heading(get_string('pluginname', 'shoppaymodes_'.$SESSION->shoppingcart->paymode), 1);
echo '</center>';

$renderer->field_start(get_string('ordersummary', 'local_shop'), 'shop-information-area');
$renderer->order_short();
$renderer->field_end();

if (!empty($config->test) && empty($config->testoverride) &&
            !has_capability('local/shop:salesadmin', context_system::instance())) {
    echo $OUTPUT->notification(get_string('testmodeactive', 'local_shop'));
} else {
    $renderer->field_start(get_string('procedure', 'local_shop'), 'shop-information-area');
    echo $paymentplugin->print_payment_portlet($SESSION->shoppingcart);
    $renderer->field_end();
}

echo $OUTPUT->box_end();

$options['overtext'] = get_string('continue', 'local_shop');
$options['nextstring'] = 'finish';
$options['nextstyle'] = 'shop-next-button';
$options['hidenext'] = !$paymentplugin->needslocalconfirm();

echo '<center>';
echo $renderer->action_form('payment', $options);
echo '</center>';