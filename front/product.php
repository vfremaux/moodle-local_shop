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

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;

$SESSION->shop = null;

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// We edit products within a catalog.
$itemid = required_param('itemid', PARAM_INT);

// Security.
$context = context_system::instance();
$PAGE->set_context($context);

$view = 'viewProductDetail';

// Make page header and navigation.

$url = new moodle_url('/local/shop/front/product.php', ['id' => $theshop->id, 'itemid' => $itemid]);
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('catalog', 'local_shop'));
$PAGE->set_pagelayout('admin');

$out = $OUTPUT->header();

$renderer = shop_get_renderer('products');
$renderer->load_context($theshop, $thecatalog, $theblock);

// Make page content.
require($CFG->dirroot."/local/shop/products/{$view}.php");

// Make footer.
echo $OUTPUT->footer();