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
 * Diplays per product the programmed zone shippings
 */

require('../../../config.php');

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogShipping.class.php');

use local_shop\Catalog;
use local_shop\CatalogItem;
use local_shop\CatalogShipping;

$catalogid = required_param('id', PARAM_INT);
$catalogitemid = required_param('catalogitemid', PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

try {
    $thecatalog = new Catalog($catalogid);
    if ($thecatalog->isslave) {
        $thecatalog = new Catalog($thecatalog->groupid);
        $catalogid = $thecatalog->groupid;
    }
} catch (Exception $e) {
    throw new moodle_exception(get_string('objecterror', 'local_shop', $e->message));
}

try {
    $catalogitem = new CatalogItem($catalogitemid);
} catch (Exception $e) {
    throw new moodle_exception(get_string('objecterror', 'local_shop', $e->message));
}

$renderer = shop_get_renderer('shipzones');
$renderer->load_context($theshop, $thecatalog, $theblock);

if ($allitemswithshipping = CatalogShipping::get_products_with_shipping($catalogid)) {
    foreach ($allitemswithshipping as $ci) {
        $itemoptions[$ci->id] = '['.$ci->shortname.'] '.$ci->name;
    }
    echo html_writer::select($itemoptions, 'catalogitemid', $catalogitemid, array('' => CHOOSEDOTS));
} else {
    echo $OUTPUT->notification(get_string('noshippings', 'local_shop'));
}

if (!$itemshippingzones = $catalogitem->get_shipping_zones($catalogitemid)) {
    echo $OUTPUT->notification(get_string('noshippedproducts', 'local_shop'));
} else {
    $renderer->catalogitem_shippings_zones($itemshippingzones);
}

echo $OUTPUT->footer();