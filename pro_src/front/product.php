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

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/pro/prolib.php');
require_once($CFG->dirroot.'/local/shop/pro/lib.php');

$promanager = \local_shop\pro_manager::instance();
if (!$promanager->require_pro('products/smarturls')) {
    throw new moodle_exception("Pro License Check Failed");
}

use local_shop\Catalog;
use local_shop\CatalogItem;
use local_shop\Category;

$SESSION->shop = null;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// We edit products within a catalog.
$itemid = optional_param('itemid', '', PARAM_INT);
$itemalias = optional_param('itemalias', '', PARAM_TEXT);

// Security.
$context = context_system::instance();
$PAGE->set_context($context);

$view = 'viewProductDetail';

// Make page header and navigation.

if (!empty($itemid)) {
    if (!$item = new CatalogItem($itemid)) {
        throw new moodle_exception("Unregistered product id.");
    }
    $url = new moodle_url('/local/shop/pro/front/productid/'.$itemid);
} else {
    if (!$item = CatalogItem::instance_by_seoalias($itemalias)) {
        throw new moodle_exception("Unknown product alias.");
    }
    $url = new moodle_url('/local/shop/pro/front/product/'.$itemalias);
}

$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(format_string($item->name));
if (!empty($item->seotitle)) {
    $PAGE->set_title($item->seotitle);
}
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('catalog', 'local_shop'));
$category = new Category($item->categoryid);
$PAGE->navbar->add(format_string($category->name), get_smart_category_url($theshop->id, $category, @$theblock->id, $byalias = true));
$PAGE->navbar->add(format_string($item->name));
$PAGE->set_pagelayout('admin');

// Make page content.
require($CFG->dirroot."/local/shop/products/{$view}.php");

// Make footer.
echo $OUTPUT->footer();