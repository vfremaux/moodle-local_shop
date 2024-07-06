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
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');
    $controller = new catalog_controller();
    $controller->process($action);
}

$catalogid = optional_param('catalogid', 0, PARAM_INT);

// Make page header and navigation.

$url = new moodle_url('/local/shop/catalogs/edit_catalog.php', ['catalogid' => $catalogid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('catalogs', 'local_shop'));

if ($catalogid) {
    $catalog = new Catalog($catalogid);
    $formdata = new StdClass;
    $formdata = $catalog->record;
    $formdata->catalogid = $catalog->id;
    $formdata->id = $theshop->id;
    if ($catalog->ismaster) {
        $formdata->linked = 'master';
    } else if ($catalog->isslave) {
        $formdata->linked = 'slave';
    } else {
        $formdata->linked = 'free';
    }
    $formdata->blockid = 0 + @$theblock->instance->id;

    $mform = new Catalog_Form('', ['what' => 'edit']);
    $mform->set_data($formdata);
} else {
    $mform = new Catalog_Form('', ['what' => 'add']);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/index.php'));
}

if ($data = $mform->get_data()) {

    include_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');
    $controller = new \local_shop\backoffice\catalog_controller();
    $controller->receive('edit', $data, $mform);
    $controller->process('edit');

    redirect(new moodle_url('/local/shop/index.php'));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
