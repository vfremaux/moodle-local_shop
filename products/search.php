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

defined('MOODLE_INTERNAL') || die();

/**
 *
 * Search view for product management
 *
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

$action = optional_param('what', '', PARAM_TEXT);

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/products/products.controller.php');
    $controller = new product_controller();
    $results = $controller->process($action);
}

// print results

$renderer = shop_get_renderer('products');

echo $out;

echo $OUTPUT->heading(get_string('searchresults', 'local_shop'));

echo $renderer->products($results);
