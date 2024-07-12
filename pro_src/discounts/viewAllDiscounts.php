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
 * List view of discounts.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');

use local_shop\Discount;

$action = optional_param('what', '', PARAM_TEXT);

ini_set('memory_limit', '512M');

if (!empty($action)) {
    include_once($CFG->dirroot.'/local/shop/pro/discounts/discounts.controller.php');
    $controller = new \local_shop\backoffice\discounts_controller();
    $controller->receive($action);
    $controller->process($action);
}

$order = optional_param('order', 'lastname', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);
$offset = optional_param('offset', 0, PARAM_INT);

$params = ['view' => 'viewAllDiscounts', 'order' => $order, 'dir' => $dir];
$url = new moodle_url('/local/shop/pro/discounts/view.php', $params);

$discountscount = $DB->count_records('local_shop_discount', []); // Eliminate tests.
$config = get_config('local_shop');

$discounts = Discount::get_instances_for_admin($theshop->id);

echo $out;

echo $mainrenderer->shop_choice($url, true);

echo $OUTPUT->heading(get_string('discounts', 'local_shop'), 1);

if (empty($discounts)) {
    echo $OUTPUT->notification(get_string('nodiscounts', 'local_shop'));
} else {
    echo $renderer->discounts($discounts);
}

$portlet = new StdClass();
$portlet->url = $url;
$portlet->total = $discountscount;
$portlet->pagesize = $config->maxitemsperpage;
echo $mainrenderer->paging_results($portlet);

echo $renderer->discount_view_links();
