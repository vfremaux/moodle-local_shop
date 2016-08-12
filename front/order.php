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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// in case session is lost, go to the public entrance of the shop
if (!isset($SESSION->shoppingcart) || !isset($SESSION->shoppingcart->customerinfo)) {
    redirect(new moodle_url('/local/shop/front/view.php', array('shopid' => $theShop->id, 'blockid' => 0 + @$theBlock->id, 'view' => 'shop')));
}

$action = optional_param('what', '', PARAM_TEXT);
if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/order.controller.php');
    $controller = new \local_shop\front\order_controller($theShop, $theCatalog, $theBlock);
    $controller->process($action);
}

// as we sould know enough about customer here, we can calculate shipping and eventuel discount

if (empty($SESSION->shoppingcart->transid)) { // locks a transition ID for new incomers.
    $SESSION->shoppingcart->transid = shop_get_transid();
}

echo $out;

// print_object($SESSION->shoppingcart);

// Start ptinting page 

echo $OUTPUT->heading(format_string($theShop->name), 2, 'shop-caption');

echo $OUTPUT->box_start('', 'orderpanel');

echo $renderer->progress('CONFIRM');

echo $renderer->admin_options();

$bill = null;
echo $renderer->customer_info($bill);

$eulas = $renderer->check_and_print_eula_conditions();
$initialview = (empty($eulas)) ? '' : ' style="display:none" ';

// Print main ordering table

echo '<form name="navigate" action="'.$CFG->wwwroot.'/local/shop/front/view.php" method="post">';

echo '<div id="order" '.$initialview.'>';

echo '<table cellspacing="5" class="generaltable" width="100%">';

$null = null;
echo $renderer->order_line($null);
$hasrequireddata = array();

foreach ($SESSION->shoppingcart->order as $shortname => $fooq) {
    echo $renderer->order_line($shortname);
}
echo '</table>';

echo $renderer->full_order_totals();
echo $renderer->full_order_taxes();
echo $renderer->payment_block();

if (!empty($config->sellermail)) {
    echo '<p>';
    print_string('forquestionssendmailto', 'local_shop');
    echo ": <a href=\"mailto:{$config->sellermail}\">{$config->sellermail}</a>";
    echo '</p>';
}

echo $OUTPUT->box_end();


echo '</div>';

$options = array();
$options['inform'] = true;
$options['nextstring'] = 'launch';

echo $renderer->action_form('order', $options);

/*
echo '<p align="center">';
echo '<input type="hidden" name="view" value="order" />';
echo '<input type="hidden" name="id" value="'.$theShop->id.'" />';
echo '<input type="hidden" name="what" value="navigate" />';
echo '<input type="submit" name="back" value="'.get_string('previous', 'local_shop').'" />';
echo '&nbsp;<input type="submit" name="go" class="shop-final-step-button" value="'.get_string('launch', 'local_shop').'" />';
echo '</p>';
*/
echo '</form>';

// Hide all region-pre to avoid side blocks to mess.
if (!empty($eulas)) {
    echo '<script type="text/javascript">';
    echo 'function hideblocks() {';
    echo 'preregion = document.getElementById(\'region-pre\');';
    echo 'preregion.style.display = \'none\';';
    echo '}';
    echo 'window.onload = function() { hideblocks(); }';
    echo '</script>';
}

echo $eulas;