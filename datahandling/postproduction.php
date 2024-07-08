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
 * BillItem postproduction
 *
 * @package    local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * This file is a library for postproduction handling. Post production
 * occurs when a customer wants to perform some action upon a registered
 * product.
 *
 * Postproduction has two parts :
 *
 * 1. Displaying a parameter selection form for getting data required to perform action
 * 2. Executing the controller for that form
 *
 * GUI to access to product postpord commands
 * is mainly given by the local_shop_products block.
 * Controller will check for handler name and calls the handler postprod methods as
 * required. Any method that is provided to user to launch will need to be implemented as
 * a postprod_xxxxxx() method in the handler class.
 * calls (urls) to a postprod command should be of the form :
 * <moodleroot>/local/shop/datahandling/postproduction.php?method=<methodname>&pid=<productid>&<otherparams>
 *
 * Securtity concerns : all handler method MUST check the $pid belongs to connected user. This can be centralized here
 * before invoking the class method
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Product;
use local_shop\CatalogItem;
use local_shop\Customer;

$id = required_param('id', PARAM_INT); // The course ID.
$productid = required_param('pid', PARAM_INT);
$method = required_param('method', PARAM_TEXT);

try {
    $product = new Product($productid);
} catch (Exception $e) {
    throw new moodle_exception(get_string('objecterror', 'local_shop', $e->get_message()));
}

$customer = new Customer($product->customerid);

try {
    $catalogitem = new CatalogItem($product->catalogitemid);
} catch (Exception $e) {
    throw new moodle_exception(get_string('objecterror', 'local_shop', $e->get_message()));
}

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

$params = ['id' => $id, 'pid' => $productid, 'method' => $method];
$url = new moodle_url('/local/shop/datahandling/postproduction.php', $params);
$PAGE->set_url($url);

// Security.

$context = context_course::instance($id);
$PAGE->set_context($context);
require_course_login($course);

if ($customer->hasaccount != $USER->id && !has_capability('local/shop:salesadmin', $context)) {
    throw new moodle_exception(get_string('notowner', 'local_shop'));
}

// Page setup.

$PAGE->set_title(get_string('shop', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('postproduction', 'local_shop'));
$PAGE->set_pagelayout('admin');

$productinfo = $product->extract_production_data();
list($handler, $methodname) = $product->get_handler_info($method);

if (is_null($handler) || is_null($methodname)) {
    $mess = "Moodle shop could not find valuable information in product or catalog item. this is probably a coding issue.";
    throw new moodle_exception($mess);
}

$productinfo->url = $url;

$courseurl = new moodle_url('/course/view.php', ['id' => $id]);

if ($confirm = optional_param('confirm', false, PARAM_TEXT)) {
    $handler->{$methodname}($product, $productinfo);
    redirect($courseurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('productoperation', 'local_shop'));
$handler->{$methodname}($product, $productinfo);
echo $OUTPUT->confirm(get_string('confirmoperation', 'local_shop'), $url.'&confirm=1', $courseurl);
echo $OUTPUT->footer();
