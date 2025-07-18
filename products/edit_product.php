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
 * Edit a simple product
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
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
use local_shop\Category;

$PAGE->requires->jquery();
$PAGE->requires->js('/local/shop/extlib/js.js', true);
$PAGE->requires->js('/local/shop/js/shopadmin.js', true);
$PAGE->requires->js('/local/shop/js/shopadmin_late.js', false);
$PAGE->requires->js_call_amd('local_shop/editmetadata', 'init');

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

/*
 * Note thecatalog may be NOT the catalog associated to the updated product.
 */

$itemid = optional_param('itemid', 0, PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
$return = optional_param('return', 'back', PARAM_TEXT);

// Security.

$context = context_system::instance();

require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$params = ['itemid' => $itemid, 'categoryid' => $categoryid, 'return' => $return];
$url = new moodle_url('/local/shop/products/edit_product.php', $params);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

if ($itemid) {
    $item = new CatalogItem($itemid);
    $mform = new Product_Form($url, ['what' => 'edit', 'catalog' => $thecatalog]);
} else {
    $item = new CatalogItem(null);
    $mform = new Product_Form($url, ['what' => 'add', 'catalog' => $thecatalog]);
}

if ($return == 'back') {
    $params = ['view' => 'viewAllProducts', 'catalogid' => $thecatalog->id, 'categoryid' => $categoryid];
    $returnurl = new moodle_url('/local/shop/products/view.php', $params);
} else {
    $params = ['view' => 'shop', 'shopid' => $theshop->id, 'category' => $categoryid];
    $returnurl = new moodle_url('/local/shop/front/view.php', $params);
}

if ($mform->is_cancelled()) {
    redirect($returnurl);
}

if ($data = $mform->get_data()) {
    $controller = new \local_shop\backoffice\product_controller($thecatalog, $mform);
    $controller->receive('edit', $data);
    $controller->process('edit');
    redirect($returnurl);
}

if ($itemid) {
    $item = new CatalogItem($itemid);
    $itemrec = $item->record;

    // Replicates some attributes for variants.

    $handleropts['0'] = get_string('disabled', 'local_shop');
    $handleropts['1'] = get_string('dedicated', 'local_shop');
    $handleropts = array_merge($handleropts, shop_get_standard_handlers_options());

    $itemrec->catalogid = $thecatalog->id;
    $itemrec->codeshadow = $itemrec->code;
    $itemrec->enablehandlershadow = $handleropts[$itemrec->enablehandler];
    $itemrec->handlerparamsshadow = $itemrec->handlerparams;
    $category = new Category($itemrec->categoryid);
    $itemrec->categoryidshadow = $category->get_name();
    $itemrec->quantaddressesusersshadow = ($itemrec->quantaddressesusers) ? get_string('yes') : get_string('no');
    $itemrec->renewableshadow = ($itemrec->renewable) ? get_string('yes') : get_string('no');

    $itemrec->itemid = $itemid;
    $mform->set_data($itemrec);
} else {
    $item = new CatalogItem(null);
    $itemrec = $item->record;
    $itemrec->catalogid = $thecatalog->id;
    $itemrec->categoryid = optional_param('categoryid', 0, PARAM_INT);
    $mform->set_data($itemrec);
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_shop/metadata_edit_modal', new StdClass());
$mform->display();
echo $OUTPUT->footer();
