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

$itemid = required_param('itemid', PARAM_INT);

if (!has_capability('local/shop:accessallowners', $context)) {
    $shopowner = $USER->id;
} else {
    $shopowner = null;
}

$catalogitem = new CatalogItem($itemid);

echo $out;

echo $OUTPUT->heading(get_string('catalogitem', 'local_shop'));

$params = array('view' => 'viewProductDetail', 'id' => $theshop->id, 'catalogid' => $thecatalog->id, 'itemid' => $itemid);
$viewurl = new moodle_url('/local/shop/products/view.php', $params);
echo $renderer->category_chooser($viewurl);

echo $OUTPUT->heading(get_string('products', 'local_shop'));

echo $renderer->catalogitem_details($catalogitem);
