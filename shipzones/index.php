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
 * 
 * Display all defined zones within the catalog.
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipZone.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;
use local_shop\CatalogShipZone;

// get the block reference and key context.
list($theShop, $theCatalog, $theBlock) = shop_build_context();

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Execute controller.

$action = optional_param('what', '', PARAM_TEXT);
if (!empty($action)) {
    include($CFG->dirroot.'/local/shop/shipzones/shipzones.controller.php');
    $controller = new shpipzones_controller();
    $controller->process($action);
}

// Define page.

$url = new moodle_url('/local/shop/shipzones/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('shipzones', 'local_shop'));
$PAGE->set_pagelayout('admin');

$renderer = shop_get_renderer('shipzones');
$mainrenderer = $PAGE->get_renderer('local_shop');

echo $OUTPUT->header();

if ($zones = CatalogShipZone::get_instances(array('catalogid' => $theCatalog->id))) {

    echo $mainrenderer->catalog_choice($url);

    echo $OUTPUT->heading(get_string('catalog', 'local_shop'));

    echo $renderer->catalog_data($theCatalog);

    echo $OUTPUT->heading(get_string('shipzones', 'local_shop'));

    echo $renderer->zones($zones);

} else {
    echo $OUTPUT->notification(get_string('nozones', 'local_shop'));
}

// Add instance link

$addshippingzonestr = get_string('addshippingzone', 'local_shop');
$addzoneurl = new moodle_url('/local/shop/shipzones/edit_shippingzone.php', array('what' => 'add'));
echo '<div class="addlink"><a href="'.$addzoneurl.'">'.$addshippingzonestr.'</a></div>';

// Navigation return

echo '<center>';
echo $OUTPUT->single_button(new moodle_url('/local/shop/index.php'), get_string('backtoshopadmin', 'local_shop'), 'get'); 
echo '</center>';

echo $OUTPUT->footer();