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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
header("Content-type: text/javascript");
header("Cache-Control: No-cache");

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$context = context_system::instance();
$PAGE->set_context($context);

$categories = $thecatalog->get_categories();
$shopproducts = $thecatalog->get_all_products($categories);

$units = 0;
if (isset($SESSION->shoppingcart->order)) {
    foreach ($SESSION->shoppingcart->order as $shortname => $q) {
        $units += $q;
    }
}

// Calculates and updates the seat count.
$requiredroles = $thecatalog->check_required_roles();
$required = $thecatalog->check_required_seats();
$assigned = shop_check_assigned_seats($requiredroles);
$notassignedstr = str_replace("'", '\\\'', get_string('notallassigned', 'local_shop'));
$myorderstr = str_replace("'", '\\\'', get_string('emptyorder', 'local_shop'));
$invalidemailstr = get_string('invalidemail', 'local_shop');
