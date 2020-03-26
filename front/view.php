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
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Shop;
use local_shop\Catalog;

$PAGE->requires->jquery();
$PAGE->requires->js('/local/shop/js/form_protection.js.php');
$PAGE->requires->js('/local/shop/front/js/order.js');
$PAGE->requires->js('/local/shop/js/bootstrap_3.4.1.js');

$PAGE->requires->css('/local/shop/stylesdyn.php');
$PAGE->requires->css('/local/shop/css/bootstrap_3.4.1.css');

$config = get_config('local_shop');

$category = optional_param('category', 0, PARAM_ALPHA);

// Get block information.

// Get the block reference and key context.
list($theshop, $thecatalog, $theblock) = shop_build_context();

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

// $PAGE->requires->js('/local/shop/front/js/front.js.php?id='.$theshop->id);
$params = ['shopid' => $theshop->id,
           'units' => $units,
           'required' => $required,
           'assigned' => $assigned];
$PAGE->requires->js_call_amd('local_shop/front', 'init', array($params));

$view = optional_param('view', $theshop->get_starting_step(), PARAM_ALPHA);

$context = context_system::instance();

if ($view == 'shop') {
    $PAGE->requires->js('/local/shop/js/fancybox/source/jquery.fancybox.pack.js?v=2.1.5');
    $PAGE->requires->css('/local/shop/js/fancybox/source/jquery.fancybox.css?v=2.1.5');
}

// Make page header.
$url = new moodle_url('/local/shop/front/view.php', array('view' => $view, 'category' => $category));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('shop', 'local_shop'));
$PAGE->set_cacheable(false);

// Add a forced shop_total block at right if necessary.

if (!isloggedin()) {
    $USER = $DB->get_record('user', array('username' => 'guest'));
}

if (empty($config->sellername)) {
    print_error('errornoselleridentity', 'local_shop');
}

$out = $OUTPUT->header();

$renderer = shop_get_renderer('front');
$renderer->load_context($theshop, $thecatalog, $theblock);

// Fetch view.
if (is_readable($CFG->dirroot."/local/shop/front/{$view}.php")) {
    include($CFG->dirroot."/local/shop/front/{$view}.php");
} else {
    print_error('errormissingview', 'local_shop');
}

if ($view == 'shop') {
    echo '
        <script type="text/javascript">
            $(document).ready(function() {
                $(".fancybox").fancybox();
            });
        </script>
    ';
}

echo $OUTPUT->footer();