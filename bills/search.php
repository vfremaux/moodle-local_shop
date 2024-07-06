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
 * Search engine in bills.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/bills/search.controller.php');
    $controller = new \local_shop\bills\search_controller($theshop);
    $bills = $controller->process($action);
}

$PAGE->requires->js('/local/shop/js/search.js');

echo $out;

echo $OUTPUT->heading(get_string('billsearch', 'local_shop'), 3);

if (!empty($controller) && !empty($controller->criteria)) {
    $class = (empty($bills)) ? 'error' : 'success';
    echo $OUTPUT->notification($controller->criteria, $class);
}

if (empty($bills)) {
    print_string('nobills', 'local_shop');
} else {
    echo $OUTPUT->heading(get_string('results', 'local_shop'), 2);

    echo $OUTPUT->box(print_string('manybillsasresult', 'local_shop'));

    echo $renderer->search_results($bills, $theshop);
}

$billcount = $DB->count_records('local_shop_bill');
echo $renderer->search_form($theshop, $billcount);
