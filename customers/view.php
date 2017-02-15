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
 * Master view for customer management
 *
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Shop;

// Get the shop reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$view = optional_param('view', 'viewAllCustomers', PARAM_TEXT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

if (!preg_match('/viewAllCustomers|viewCustomer/', $view) ||
        !file_exists($CFG->dirroot."/local/shop/customers/{$view}.php")) {
    print_error('errorbadview', 'local_shop');
}

// Make page header and navigation.

$url = new moodle_url('/local/shop/customers/view.php', array('view' => $view));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('customers', 'local_shop'));

if ($view == 'viewCustomer') {
    $PAGE->navbar->add(get_string('customer', 'local_shop'));
}

// Getting needed renderers.
$mainrenderer = $PAGE->get_renderer('local_shop');
$renderer = shop_get_renderer('customers');
$renderer->load_context($theshop, $thecatalog, $theblock);

// Make page content.

$out = $OUTPUT->header();

require($CFG->dirroot.'/local/shop/customers/'.$view.'.php');

echo $OUTPUT->footer();