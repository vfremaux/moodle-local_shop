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
 * Entry point for vieing shop instances
 * @package    local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/products/lib.php');

// Get the block reference and key context.

$view = optional_param('view', '', PARAM_TEXT);

// Security.

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/instances/view.php');
$PAGE->set_url($url);

$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('catalog', 'local_shop'));
$PAGE->set_pagelayout('admin');

$out = $OUTPUT->header();

$renderer = shop_get_renderer('instances');
$mainrenderer = $PAGE->get_renderer('local_shop');

// Make page content.
require($CFG->dirroot."/local/shop/instances/{$view}.php");

// Make footer.
echo $OUTPUT->footer();
