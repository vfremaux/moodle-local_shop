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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\CatalogItem;

// Do NOT rely on the caller script, as it might reside in Pro zone.
list($theshop, $thecatalog, $theblock) = shop_build_context();
$renderer = shop_get_renderer('products');
$renderer->load_context($theshop, $thecatalog, $theblock);

$itemid = optional_param('itemid', '', PARAM_INT);
$itemalias = optional_param('itemalias', '', PARAM_TEXT);
if (!empty($itemid)) {
    if (!$catalogitem = new CatalogItem($itemid)) {
        throw new moodle_exception("Unregistered product id.");
    }
} else {
    if (!$catalogitem = CatalogItem::instance_by_seoalias($itemalias)) {
        throw new moodle_exception("Unknown product alias.");
    }
}

$context = $PAGE->context;
if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = $USER->id;
} else {
    $shopowner = null;
}

if (local_shop_supports_feature('products/smarturls')) {
    include_once($CFG->dirroot.'/local/shop/pro/lib.php');
    local_shop_setup_seo_overrides($catalogitem);
}

$PAGE->requires->js_call_amd('local_shop/productdetail', 'init');

$out = $OUTPUT->header();
echo $out;

echo $OUTPUT->heading(get_string('catalogitem', 'local_shop'));

// $params = array('view' => 'viewProductDetail', 'id' => $theshop->id, 'catalogid' => $thecatalog->id, 'itemid' => $itemid);
// $viewurl = new moodle_url('/local/shop/products/view.php', $params);
// echo $renderer->category_chooser($viewurl);

echo $renderer->catalogitem_details($catalogitem);
