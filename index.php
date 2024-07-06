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
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');

use local_shop\Shop;
use local_shop\Catalog;
use local_shop\catalogs\catalog_controller;

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Page preparation.

$url = new moodle_url('/local/shop/index.php');

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('shop');
$PAGE->set_heading('shop');
$PAGE->navbar->add(get_string('salesservice', 'local_shop'));
$PAGE->set_pagelayout('admin');

// Controller.

$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    $controller = new \local_shop\backoffice\catalog_controller();
    $controller->receive($action);
    $returnurl = $controller->process($action);
    if (!empty($returnurl)) {
        redirect($returnurl);
    }
}

echo $OUTPUT->header();

$catalogs = Catalog::get_instances_for_admin();

$renderer = shop_get_renderer('catalogs');
$shoprenderer = shop_get_renderer('base');

echo $OUTPUT->heading(get_string('salesservice', 'local_shop'), 1);

echo $OUTPUT->heading(get_string('catalogadmin', 'local_shop'), 2);

echo $renderer->catalogs($catalogs);

if (local_shop_supports_feature('catalog/instances')) {
    echo $shoprenderer->new_catalogue_form();
}

echo $shoprenderer->reference_time();

echo $OUTPUT->heading(get_string('salesmanagement', 'local_shop'), 2);

echo $shoprenderer->main_menu($theshop);

echo $OUTPUT->footer();
