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
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');

use \local_shop\Shop;
use \local_shop\Catalog;
use \local_shop\catalogs\catalog_controller;

// get all the shop session context objects
list($theShop, $theCatalog, $theBlock) = shop_build_context();

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
    $controller = new catalog_controller();
    $controller->process($action);
}

echo $OUTPUT->header();

$catalogs = Catalog::get_instances_for_admin();

$renderer = shop_get_renderer('catalogs');
$shoprenderer = $PAGE->get_renderer('local_shop');

echo $OUTPUT->heading(get_string('salesservice', 'local_shop'), 1);

echo $OUTPUT->heading(get_string('cataloguemanagement', 'local_shop'), 2);

echo '<p><center>';

echo $renderer->catalogs($catalogs);

echo '<div id="shop-new-catalog" class="pull-right">';
$editurl = new moodle_url('/local/shop/catalogs/edit_catalogue.php');
echo '<a href="'.$editurl.'">'.get_string('newcatalog', 'local_shop').'</a>';
echo '</div>';

echo '<br/>';
echo '<br/>';

echo $shoprenderer->main_menu($theShop);

echo $OUTPUT->footer();