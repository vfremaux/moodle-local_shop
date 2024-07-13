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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_shop.class.php');
require_once($CFG->dirroot."/local/shop/classes/Shop.class.php");
require_once($CFG->dirroot."/local/shop/classes/Catalog.class.php");

use local_shop\Shop;
use local_shop\Catalog;

$context = context_system::instance();
$PAGE->set_context($context);

$config = get_config('local_shop');

$id = optional_param('id', 0, PARAM_INT); // Shop current shop id.
$shopid = optional_param('shopid', 0, PARAM_INT); // Shop current shop id.
$url = new moodle_url('/local/shop/shop/edit_shop.php', ['id' => $id]);

// Security.

require_login();
require_capability('local/shop:salesadmin', $context);

$shopid = $DB->get_field('local_shop', 'MIN(id)', []);

// Make page header and navigation.

$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if (!empty($id)) {
    $shop = new Shop($shopid);
    $customdata = ['what' => 'edit'];
} else {
    $shop = new Shop(null);
    $customdata = ['what' => 'add'];
}

$mform = new Shop_Form(new moodle_url('/local/shop/shop/edit_shop.php'), $customdata);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/index.php'));
}
if ($data = $mform->get_data()) {

    include_once($CFG->dirroot.'/local/shop/shop/shops.controller.php');
    $controller = new \local_shop\backoffice\shop_controller();
    $controller->receive('edit', $data, $mform);
    $controller->process('edit');

    redirect(new moodle_url('/local/shop/index.php'));
}

if (!empty($shopid)) {
    // switch item shopid for form.
    $formdata = $shop->record;
    $formdata->shopid = $shop->record->id;
    $formdata->id = $id;
    $mform->set_data($formdata);
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
