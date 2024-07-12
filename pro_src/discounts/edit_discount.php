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
 * Edits a discount policy
 *
 * @package    local_shop
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/pro/forms/form_discount.class.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');

use local_shop\Discount;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$discountid = optional_param('discountid', '', PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/pro/discounts/edit_discount.php', array('discountid' => $discountid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('editdiscount', 'local_shop'));

if ($discountid) {
    $customdata = ['what' => 'edit', 'thecatalog' => $thecatalog];
    $mform = new Discount_Form(null, $customdata);
} else {
    $customdata = ['what' => 'add', 'thecatalog' => $thecatalog];
    $mform = new Discount_Form(null, $customdata);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/pro/discounts/view.php', ['view' => 'viewAllDiscounts']));
}

if ($data = $mform->get_data()) {
    include_once($CFG->dirroot.'/local/shop/pro/discounts/discounts.controller.php');
    $controller = new \local_shop\backoffice\discounts_controller();
    $controller->receive('edit', $data, $mform);
    $controller->process('edit');
    redirect(new moodle_url('/local/shop/pro/discounts/view.php', ['view' => 'viewAllDiscounts']));
}

if ($discountid) {
    $mform = new Discount_Form('', ['what' => 'edit', 'thecatalog' => $thecatalog]);
    $discount = Discount::instance($discountid);
    $discountdata = clone($discount->record);
    $mform->set_data($discountdata);
} else {
    $mform = new Discount_Form('', ['what' => 'add', 'thecatalog' => $thecatalog]);
    $formdata = new StdClass;
    $formdata->shopid = $theshop->id;
    $mform->set_data($formdata);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
