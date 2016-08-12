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

use local_shop\Catalog;

// get the block reference and key context.
list($theShop, $theCatalog, $theBlock) = shop_build_context();

// Get the block reference and key context.

$catalogid = required_param('id', PARAM_INT);
$view = optional_param('view', 'viewAllCustomers', PARAM_TEXT);

// Security.

$context = context_system::instance();
$PAGE->set_context($context);

require_login();
require_capability('local/shop:salesadmin', $context);
if (!preg_match('/viewAllCategories/', $view)) $view = 'viewAllCategories';

// Make page header and navigation.

$url = new moodle_url('/local/shop/products/category/view.php', array('id' => $catalogid));
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php', array('id' => $catalogid)));
$PAGE->navbar->add(get_string('category', 'local_shop'));

$renderer = shop_get_renderer('products');
$renderer->load_context($theShop, $theCatalog, $theBlock);

echo $OUTPUT->header();

// make page content
include $CFG->dirroot."/local/shop/products/category/$view.php";

// make footer
echo '<br/>';
echo $OUTPUT->footer();