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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
use local_shop\Shop;

$renderer = shop_get_renderer('shop');
$mainrenderer = $PAGE->get_renderer('local_shop');

$sortorder = optional_param('order', 'id', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);
$action = optional_param('what', '', PARAM_TEXT);
$cur = optional_param('cur', '', PARAM_TEXT);

if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/shop/shops.controller.php');
    $controller = new local_shop\backoffice\shop_controller();
    $controller->receive($action);
    $controller->process($action);
}

$params = array('view' => 'viewAllShops', 'id' => $id, 'dir' => $dir, 'order' => $sortorder);
$url = new moodle_url('/local/shop/shop/view.php', $params);
$mainrenderer->currency_choice($cur, $url);

echo $OUTPUT->heading_with_help(get_string('shops', 'local_shop'), 'shops', 'local_shop');

if (!empty($cur)) {
    $filter = array('currency' => $cur);
} else {
    $filter = array();
}

if (!$shops = Shop::get_instances($filter, "$sortorder $dir")) {
    echo $OUTPUT->notification(get_string('noshops', 'local_shop'));
}

// Print shops.
echo $renderer->shops($shops);

$newshopurl = new moodle_url('/local/shop/shop/edit_shop.php');

echo '<div id="local-shop-shop-new new-link">';
echo html_writer::link($newshopurl, get_string('newshop', 'local_shop'));
echo '</div>';