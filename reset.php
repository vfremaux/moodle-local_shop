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
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
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

$url = new moodle_url('/local/shop/reset.php', array('shopid' => $theshop->id));
$PAGE->set_url($url);

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_title('shop');
$PAGE->set_heading('shop');
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), $CFG->wwwroot."/local/shop/index.php?shopid={$theshop->id}");
$PAGE->navbar->add(get_string('reset', 'local_shop'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable('');

$out = '';

$mform = new ResetForm();

if ($mform->is_cancelled()) {
    redirect($url);
} else if ($data = $mform->get_data()) {
    if (!empty($data->bills) || !empty($data->customers) || !empty($data->catalogs)) {
        $out .= $OUTPUT->notification(get_string('billsdeleted', 'local_shop'));
        $DB->delete_records('local_shop_bill', null);
        $DB->delete_records('local_shop_billitem', null);
    }
    if (!empty($data->customers)) {
        $out .= $OUTPUT->notification(get_string('customersdeleted', 'local_shop'));
        $DB->delete_records('local_shop_customer', null);
    }
    if (!empty($data->catalogs)) {
        $out .= $OUTPUT->notification(get_string('catalogsdeleted', 'local_shop'));
        $DB->delete_records('local_shop_catalogitem', array('catalogid' => $theblock->config->catalogid));
        $DB->delete_records('local_shop_catalog', array('id' => $theblock->config->catalogid));
    }
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