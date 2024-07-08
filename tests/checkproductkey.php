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
 * This test service checks if a product key is well formed, aka has
 * the correct checksum.
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_once($CFG->dirroot.'/local/shop/tests/check_product_key_form.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');

$url = new moodle_url('/local/shop/tests/checkproductkey.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($url);

require_login();
require_capability('moodle/site:config', $context);

$mform = new check_product_key_form();

$checked = false;
if ($data = $mform->get_data()) {
    $check = shop_check_product_ref($data->productkey);
    $hasproduct = $DB->record_exists('local_shop_product', array('reference' => $data->productkey));
    $checked = true;
}

echo $OUTPUT->header();

if ($checked) {
    if ($check) {
        echo $OUTPUT->notification(get_string('isvalid', 'local_shop'), 'notifysuccess');
    } else {
        echo $OUTPUT->notification(get_string('isnotvalid', 'local_shop'), 'notifyproblem');
    }

    if ($hasproduct) {
        echo $OUTPUT->notification(get_string('hasproductinstance', 'local_shop'), 'notifysuccess');
    } else {
        echo $OUTPUT->notification(get_string('hasnoproductinstance', 'local_shop'), 'notifyproblem');
    }
}

$mform->display();

echo $OUTPUT->footer();
