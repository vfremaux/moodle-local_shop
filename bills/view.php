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
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use \local_shop\Shop;
use \local_shop\Catalog;
use \local_shop\Bill;

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

$PAGE->requires->js('/local/shop/js/bills.js');

$view = optional_param('view', '', PARAM_TEXT);
$billid = optional_param('billid', '', PARAM_TEXT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/bills/view.php', array('view' => $view));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$viewurl = new moodle_url('/local/shop/bills/view.php', array('view' => 'viewAllBills'));
$PAGE->navbar->add(get_string('bills', 'local_shop'), $viewurl);
$PAGE->set_pagelayout('admin');

if ($view == 'viewBill') {
    $billidnumber = $billid;
    if ($idnumber = $DB->get_field('local_shop_bill', 'idnumber', array('id' => $billid))) {
        $billidnumber .= " {$idnumber}";
    }
    $PAGE->navbar->add(get_string('bill', 'local_shop', $billidnumber));

    $url = new moodle_url('/local/shop/bills/view.php', array('id' => $theshop->id, 'view' => 'viewBill', 'billid' => $billid));
} else {
    $url = new moodle_url('/local/shop/bills/view.php', array('id' => $theshop->id, 'view' => $view));
}

$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));

$renderer = shop_get_renderer('bills');
$renderer->load_context($theshop, $thecatalog, $theblock);
$mainrenderer = $PAGE->get_renderer('local_shop');
$mainrenderer->load_context($theshop, $thecatalog, $theblock);

$out = $OUTPUT->header();

// Make page content.

require($CFG->dirroot."/local/shop/bills/{$view}.php");

echo '<center>';
echo $mainrenderer->back_buttons();
echo '</center>';

echo $OUTPUT->footer();