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
 * Screen for searching in product instances
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/purchasemanager/search.controller.php');
    $controller = new \local_shop\productinstances\search_controller($theshop);
    $productinstances = $controller->process($action);
}

$PAGE->requires->js('/local/shop/js/search.js');

echo $out;

echo $OUTPUT->heading(get_string('unitsearch', 'local_shop'), 3);

if (!empty($controller) && !empty($controller->criteria)) {
    $class = (empty($productinstances)) ? 'error' : 'success';
    echo $OUTPUT->notification($controller->criteria, $class);
}

if (empty($productinstances)) {
    print_string('nounits', 'local_shop');
} else {
    echo $OUTPUT->heading(get_string('results', 'local_shop'), 2);

    echo $OUTPUT->box(print_string('manyunitsasresult', 'local_shop'));

    echo $renderer->search_results($productinstances, $theshop);
}

$productinstancescount = $DB->count_records('local_shop_product');
echo $renderer->search_form($theshop, $productinstancescount);

echo "<center>";
$params = ['view' => 'viewAllProductInstances', 'shopid' => $theshop->id, 'customerid' => 0];
$returnurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
echo $OUTPUT->single_button($returnurl, get_string('backtounits', 'local_shop'));
echo "</center>";
