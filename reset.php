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
 * Resets all or parts of the shop data.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/shop/forms/form_reset.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

// Get all the shop session context objects.

list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

require_login();
require_capability('local/shop:salesadmin', context_system::instance());

// Prepare page.

$url = new moodle_url('/local/shop/reset.php', ['shopid' => $theshop->id]);
$PAGE->set_url($url);

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_title('shop');
$PAGE->set_heading('shop');
$salesurl = new moodle_url('/local/shop/index.php', ['shopid' => $theshop->id]);
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), $salesurl);
$PAGE->navbar->add(get_string('reset', 'local_shop'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable('');
$PAGE->set_pagelayout('standard');

// Add the shop admin secondary nav.
$nav = shop_get_admin_navigation($theshop);
$PAGE->set_secondarynav($nav);
$PAGE->set_secondary_navigation(true);
$PAGE->set_secondary_active_tab('reset');

$out = '';

$mform = new ResetForm();

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    include_once($CFG->dirroot.'/local/shop/reset.controller.php');
    $controller = new \local_shop\backoffice\reset_controller();
    $controller->receive('reset', $data);
    $out .= $controller->process('reset');
}
echo $OUTPUT->header();

if ($out) {
    echo $OUTPUT->box_start();
    echo $out;
    echo $OUTPUT->box_end();
}

echo $OUTPUT->heading(get_string('reset', 'local_shop'));
echo $OUTPUT->box_start();
print_string('resetguide', 'local_shop');

$formdata = new StdClass;
$formdata->shopid = $theshop->id;
$mform->set_data($formdata);
$mform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();
