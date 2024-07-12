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
 * @package         local_shop
 * @author          Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright       Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license         http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_shipping.class.php'); // Imports of Form shipping.
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipZone.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipping.class.php');

use local_shop\CatalogShipping;
use local_shop\CatalogShipZone;
use local_shop\CatalogItem;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$action = optional_param('what', '', PARAM_TEXT);
$zoneid = optional_param('zoneid', 0, PARAM_INT); // Will lock zoneid choice. Needs shippingid.
$productcode = optional_param('productcode', '', PARAM_TEXT); // Will lock productcode choice. Needs shippingid.
$shippingid = optional_param('shippingid', 0, PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/shipzones/edit_shipping.php', ['shippingid' => $shippingid]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('shippings', 'local_shop'));
$PAGE->set_pagelayout('admin');

$products = CatalogItem::get_instances_menu(['catalogid' => $thecatalog->id], 'name');
$zones = CatalogShipZone::get_instances_menu(['catalogid' => $thecatalog->id], 'zonecode');

if ($shippingid) {
    $shipping = new CatalogShipping($shippingid);
    $mform = new ProductShipping_Form('', ['what' => 'edit', 'products' => $products, 'shippingzones' => $zones]);
    $shippingrec = $shipping->record;
    $shippingrec->shippingid = $shippinggid;
    $shippingrec->id = $catalogid;
    $mform->set_data($shippingrec);
} else {
    $shipping = new CatalogShipping(null);
    $shippingrec = $shipping->record;
    $mform = new ProductShipping_Form('', ['what' => 'add', 'products' => $products, 'shippingzones' => $zones]);
    if ($zoneid) {
        $shippingrec->zoneid = $zoneid;
        $mform->freeze('zoneid');
    }
    if ($productcode) {
        $shippingrec->productcode = $productcode;
        $mform->freeze('productcode');
    }
    $mform->set_data($shippingrec);
}
if ($mform->is_cancelled()) {
     redirect(new moodle_url('/local/shop/shipzones/zoneindex.php', ['zoneid' => $zoneid]));
}

if ($data = $mform->get_data()) {
    $shipping->id = optional_param('shippingid', 0, PARAM_INT);
    $shipping->productcode = required_param('productcode', PARAM_TEXT);
    $shipping->zoneid = required_param('zoneid', PARAM_INT);
    $shipping->value = optional_param('value', '', PARAM_NUMBER);
    $shipping->formula = optional_param('formula', '', PARAM_TEXT);
    $shipping->a = optional_param('a', '', PARAM_NUMBER);
    $shipping->b = optional_param('b', '', PARAM_NUMBER);
    $shipping->c = optional_param('c', '', PARAM_NUMBER);

    if (empty($shipping->id)) {
        $shipping->id = $DB->insert_record('shop_catalogshipping', $shipping);
    } else {
        $DB->update_record('shop_catalogshipping', $shipping);
    }
     redirect(new moodle_url('/local/shop/shipzones/zoneindex.php', ['zoneid' => $zoneid]));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
