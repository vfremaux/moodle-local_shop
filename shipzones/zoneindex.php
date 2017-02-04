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
 * Displays per zone all registered shippings for products
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipZone.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipping.class.php');

use local_shop\Catalog;
use local_shop\CatalogShipping;
use local_shop\CatalogShipZone;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$zoneid = optional_param('zoneid', 0, PARAM_INT);

// Security.

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/shop:salesadmin', $context);

try {
    $zone = new CatalogShipZone($zoneid);
} catch (Exception $e) {
    print_error('objecterror', 'local_shop', $e->message);
}

// Execute controller.
$action = optional_param('what', '', PARAM_TEXT);
if (!empty($action)) {
    include_once($CFG->dirroot.'/local/shop/shipzones/zoneindex.controller.php');
    $controller = new shipzones_controller();
    $controller->process($action);
}

$url = new moodle_url('/local/shop/shipzones/zoneindex.php');
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$zonesurl = new moodle_url('/local/shop/shipzones/index.php', array('id' => $theshop->id));
$PAGE->navbar->add(get_string('shipzones', 'local_shop'), $zonesurl);
$PAGE->set_url($url);

$renderer = shop_get_renderer('shipzones');
$renderer->load_context($theshop, $thecatalog, $theblock);

echo $OUTPUT->header();

// If slave get entries in master catalog and then overrides whith local descriptions.
echo $OUTPUT->heading(format_string($thecatalog->name));

echo $renderer->catalog_data($thecatalog);

echo $OUTPUT->heading(get_string('shipzone', 'local_shop'));

echo $renderer->zone_data($zone);

echo $OUTPUT->heading(get_string('shippings', 'local_shop'));

if ($shippings = CatalogShipping::get_instances(array('zoneid' => $zoneid))) {
    echo $renderer->shippings($shippings);
} else {
    echo $OUTPUT->notification(get_string('noshippings', 'local_shop'));
}

$addshippingstr = get_string('addshipping', 'local_shop');
$addshippingurl = new moodle_url('/local/shop/shipzones/edit_shipping.php', array('what' => 'add', 'zoneid' => $zoneid));
echo '<div class="addlink"><a href="'.$addshippingurl.'">'.$addshippingstr.'</a></div>';

echo '<br/><center>';
echo $OUTPUT->single_button(new moodle_url('/local/shop/shipzones/index.php'), get_string('backtoshopadmin', 'local_shop'), 'get');
echo '</center><br/>';

echo $OUTPUT->footer();