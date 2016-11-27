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
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/products/lib.php');
require_once($CFG->dirroot.'/local/shop/products/products.controller.php');
require_once($CFG->dirroot.'/local/shop/forms/form_product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Catalog;
use local_shop\CatalogItem;

$PAGE->requires->jquery();
$PAGE->requires->js('/local/shop/extlib/js.js', true);
$PAGE->requires->js('/local/shop/js/shopadmin.js', true);
$PAGE->requires->js('/local/shop/js/shopadmin_late.js', false);

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$itemid = optional_param('itemid', 0, PARAM_INT);

// Security.

$context = context_system::instance();

require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/products/edit_product.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($itemid) {
    $item = new CatalogItem($itemid);
    $mform = new Product_Form($url, array('what' => 'edit', 'catalog' => $thecatalog));
    $itemrec = $item->record;
    $itemrec->itemid = $itemid;
    $mform->set_data($itemrec);
} else {
    $item = new CatalogItem(null);
    $mform = new Product_Form($url, array('what' => 'add', 'catalog' => $thecatalog));
    $itemrec = $item->record;
    $itemrec->categoryid = optional_param('categoryid', 0, PARAM_INT);
    $mform->set_data($itemrec);
}

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
}

if ($data = $mform->get_data()) {

    $controller = new \local_shop\backoffice\product_controller($thecatalog);
    $controller->receive('edit', $data);
    $controller->process('edit');

    redirect(new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();