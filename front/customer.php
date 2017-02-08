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

$PAGE->requires->js('/local/shop/front/js/front.js.php?id='.$theshop->id);

// In case session is lost, go to the public entrance of the shop.
if (!isset($SESSION->shoppingcart) || empty($SESSION->shoppingcart->order)) {
    redirect(new moodle_url('/local/shop/front/view.php', array('id' => $theshop->id, 'view' => 'shop')));
}

$action = optional_param('what', '', PARAM_TEXT);

if ($action) {
    include($CFG->dirroot.'/local/shop/front/customer.controller.php');
    $controller = new \local_shop\front\customer_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $resulturl = $controller->process($action);
    if (!empty($resulturl)) {
        redirect($resulturl);
    }
}

echo $out;

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

echo $renderer->progress('CUSTOMER');

echo $renderer->admin_options();

echo '<div id="shop-cart-summary" class="shop-summary" style="float:left; margin-right:20px">';
echo $OUTPUT->heading(get_string('cartsummary', 'local_shop'));
echo $renderer->cart_summary();
echo '</div>';

echo '<div id="shop-customer-info" class="shop-data">';

$shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'customer', 'id' => $theshop->id));

if (!isloggedin() || isguestuser()) {
    $loginurl = new moodle_url('/login/index.php');
    echo '<form name="loginform" action="'.$loginurl.'" method="post">';
    echo '<input type="hidden" name="wantsurl" value"'.$shopurl.'">';
    echo '<input type="hidden" name="id" value="'.$theshop->id.'" />';
    echo '<fieldset>';
    echo '<legend>'.get_string('login', 'local_shop').'</legend>';
    echo '<table width="100%" class="generaltable"><tr valign="top">';
    echo '<td>';
    echo $renderer->login_form();
    echo '</td>';
    echo '</tr>';
    echo '</table>';
    echo '</fieldset>';
    echo '</form>';
}

echo '<form name="driverform" action="'.$shopurl.'" method="post">';

echo '<fieldset>';
if (!empty($SESSION->shoppingcart->errors)) {
    echo $OUTPUT->box_start('shop-error-notice');
    echo implode('<br/>', array_values($SESSION->shoppingcart->errors));
    echo $OUTPUT->box_end();
}
echo '</fieldset>';

echo '<fieldset>';
if (isloggedin() && !isguestuser()) {
    echo '<legend>'.get_string('customerinfo', 'local_shop').'</legend>';
} else {
    echo '<legend>'.get_string('newaccountinfo', 'local_shop').'</legend>';
}
echo '<table width="100%" class="generaltable"><tr valign="top">';
echo '<td>';
echo $renderer->customer_info_form();
echo '</td>';
echo '</tr>';
echo '<tr>';
$invoiceinfostyle = (empty($SESSION->shoppingcart->usedistinctinvoiceinfo)) ? 'display:none' : '';
echo '<td style="'.$invoiceinfostyle.'" id="shop-invoiceinfo-wrapper" >';
echo $renderer->invoicing_info_form();
echo '</td>';
echo '</tr></table>';
echo '</fieldset>';

$options = array();
$options['inform'] = true;

echo $renderer->action_form('customer', $options);

echo '</form>';
echo '</div>';