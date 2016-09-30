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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

// Get the block reference and key context.

$view = optional_param('view', '', PARAM_TEXT);
$id = optional_param('shopid', '', PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/shop/view.php', array('view' => $view, 'shopid' => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php', array('id' => $id)));
$PAGE->navbar->add(get_string('shops', 'local_shop'), new moodle_url('/local/shop/shop/view.php', array('view' => 'viewAllShops')));
$PAGE->set_pagelayout('admin');

if ($view == 'viewShop') {
    $id = required_param('shopid', PARAM_INT); // just for bocking
    $shop = new Shop($id);
    $PAGE->navbar->add(get_string('shop', 'local_shop'), $shop->name);
}

$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);

echo $OUTPUT->header();

// Make page content.

include_once($CFG->dirroot."/local/shop/shop/{$view}.php");

// Make footer.

echo $OUTPUT->footer();