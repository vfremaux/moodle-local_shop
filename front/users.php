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

$action = optional_param('what', '', PARAM_TEXT);
$PAGE->requires->js('/local/shop/front/js/front.js.php?id='.$theshop->id);

// In case session is lost, go to the public entrance of the shop.
if (!isset($SESSION->shoppingcart) || !isset($SESSION->shoppingcart->order)) {
    $params = array('id' => $theshop->id, 'blockid' => $theblock->id, 'view' => 'shop');
    redirect(new moodle_url('/local/shop/front/view.php', $params));
}

// If we have no seats to assign and collect, then jump directly to customer view.
if (!$required = $thecatalog->check_required_seats()) {
    $action = 'navigate';
}

if ($action) {
    include_once($CFG->dirroot.'/local/shop/front/users.controller.php');
    $controller = new \local_shop\front\users_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $returnurl = $controller->process($action);
    if (!empty($returnurl)) {
        redirect($returnurl);
    }
}

// Calculates and updates the seat count and add to session cart.
$requiredroles = $thecatalog->check_required_roles();
$assigned = shop_check_assigned_seats($requiredroles);

// Get all data about order in session.
$orderbag = shop_get_orderbag($thecatalog);

echo $out;

echo '<center>';

echo $OUTPUT->heading(format_string($theshop->name), 2, 'shop-caption');

echo $renderer->progress('USERS');

echo $renderer->admin_options();

echo '<fieldset>';
echo '<legend>'.get_string('participants', 'local_shop').'</legend>';

$newparticipantstyle = '';

if (empty($SESSION->shoppingcart->participants)) {
    $SESSION->shoppingcart->participants = [];
}

echo $renderer->add_participant();

echo '<table width="100%" id="participantlist" class="generaltable">';
$i = 0;
echo $renderer->participant_row(); // Print caption.
if (!empty($SESSION->shoppingcart->participants)) {
    foreach ($SESSION->shoppingcart->participants as $participant) {
        $participant->id = $i;
        echo $renderer->participant_row($participant, false);
        $i++;
    }
}
for (; $i < $SESSION->shoppingcart->seats; $i++) {
    echo $renderer->participant_blankrow();
}
echo '</table>';

foreach ($orderbag as $orderentry) {
    echo $renderer->seat_roles_assignation_form($orderentry->catalogentry, $requiredroles, $orderentry->shortname, $orderentry->seats);
}

$options['nextstyle'] = ($assigned < $required) ? 'opacity:0.5' : '';
$options['nextdisabled'] = ($assigned < $required) ? 'disabled="disabled"' : '';
$options['overtext'] = ($assigned < $required) ? get_string('notallassigned', 'local_shop') : get_string('continue', 'local_shop');
$options['nextstring'] = 'next';

echo $renderer->action_form('users', $options);

echo '</center>';