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

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/forms/form_shippingzone.class.php'); // imports of Form shipping
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipZone.class.php');

use local_shop\Catalog;
use local_shop\CatalogShipZone;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$action = optional_param('what', '', PARAM_TEXT);
$zoneid = optional_param('item', 0, PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/shipzones/edit_shippingzone.php', array('item' => $zoneid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
if ($thecatalog) {
    $PAGE->navbar->add(format_string($thecatalog->name));
}
$PAGE->navbar->add(get_string('shipzones', 'local_shop'));
$PAGE->set_pagelayout('admin');

if ($zoneid) {
    $zone = new CatalogShipZone($zoneid);
    $mform = new ShippingZone_Form('', array('what' => 'edit'));
    $zonerec = $zone->record;
    $zonerec->zoneid = $zoneid;
    $mform->set_data($zonerec);
} else {
    $mform = new ShippingZone_Form('', array('what' => 'add'));
    $zone = new CatalogShipZone();
    $zonerec = $zone->record;
    $mform->set_data($zonerec);
}
if ($mform->is_cancelled()) {
     redirect(new moodle_url('/local/shop/shipzones/index.php'));
}

if ($data = $mform->get_data()) {
    $data->id = $data->zoneid;
    unset($data->zoneid);
    if ($thecatalog) {
        $data->catalogid = $thecatalog->id;
    } else {
        $data->catalogid = 0; // In first times, shoul not be used.
    }
    $zone = new CatalogShipZone($data);
    $zone->save();
    redirect(new moodle_url('/local/shop/shipzones/index.php'));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();