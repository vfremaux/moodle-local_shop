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
defined('MOODLE_INTERNAL') || die();

/*
 * This shop step will collect all needed users information, that is,
 * - information about customer identity
 * - information about billing identify if different from customer
 * - information about learners if some products operate in seat mode or are courses
 * - information about instructors
 * - information about learning supervisors
 */

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

$PAGE->requires->js('/local/shop/front/js/front.js.php?id='.$theshop->id);

// In case session is lost, go to the public entrance of the shop.
if (!isset($SESSION->shoppingcart) || empty($SESSION->shoppingcart->order)) {
    redirect(new moodle_url('/local/shop/front/view.php', array('id' => $theshop->id, 'view' => 'shop')));
}

$action = optional_param('what', '', PARAM_TEXT);

$data = null;
if (local_shop_supports_feature('shop/partners')) {
    // Resolve pre_auth.
    if (!empty($SESSION->shoppingcart->partner)) {
        include_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');
        $checked = \local_shop\Partner::checkauth($SESSION->shoppingcart->partner);
        list($action, $data) = \local_shop\Partner::resolve_customer_action($checked, $action);
    }
}

if ($action) {
    include($CFG->dirroot.'/local/shop/front/customer.controller.php');
    $controller = new \local_shop\front\customer_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action, $data);
    $resulturl = $controller->process($action);
    if (!empty($resulturl)) {
        redirect($resulturl);
    }
}

echo $out;

echo $renderer->progress('CUSTOMER');

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

echo $renderer->admin_options();

/*
echo '<div id="shop-cart-summary" class="shop-summary" style="float:left; margin-right:20px">';
echo $OUTPUT->heading(get_string('cartsummary', 'local_shop'));
echo $renderer->cart_summary();
echo '</div>';
*/

$template = new StdClass;

$template->shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'customer', 'id' => $theshop->id));
$template->canlogin = (!isloggedin() || isguestuser());
$template->oldaccount = (isloggedin() && !isguestuser() && \local_shop\Customer::has_account());
$template->shopid = $theshop->id;

$template->loginform = $renderer->login_form();

if (!empty($SESSION->shoppingcart->errors->customerinfo)) {
    $str = $OUTPUT->box_start('shop-error-notice');
    $str .= implode('<br/>', array_values($SESSION->shoppingcart->errors->customerinfo));
    $str .= $OUTPUT->box_end();
    $template->customerinfoerrors = $str;
}
$template->customerinfoform = $renderer->customer_info_form();

$template->invoiceinfostyle = (empty($SESSION->shoppingcart->usedistinctinvoiceinfo)) ? 'display:none' : '';

if (!empty($SESSION->shoppingcart->errors->invoiceinfo)) {
    $str = $OUTPUT->box_start('shop-error-notice');
    $str .= implode('<br/>', array_values($SESSION->shoppingcart->errors->invoiceinfo));
    $str .= $OUTPUT->box_end();
    $template->invoiceinfoerrors = $str;
}
$template->invoiceinfoform = $renderer->invoicing_info_form();

$options = array();
$options['inform'] = true;

$template->actionform = $renderer->action_form('customer', $options);

echo $OUTPUT->render_from_template('local_shop/front_customer', $template);