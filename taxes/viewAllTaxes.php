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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Tax;
use local_shop\CatalogItem;

$action = optional_param('what', '', PARAM_TEXT);
if (!empty($action)) {
    include($CFG->dirroot.'/local/shop/taxes/taxes.controller.php');
    $controller = new taxes_controller();
    $controller->process($action);
}

$order = optional_param('order', 'country', PARAM_TEXT);
$dir = optional_param('dir', 'ASC', PARAM_TEXT);
$offset = optional_param('offset', 0, PARAM_INT);

$url = new moodle_url('/local/shop/taxes/view.php', array('view' => 'viewAllTaxes', 'order' => $order, 'dir' => $dir));

$taxescount = $DB->count_records_select('local_shop_tax', " UPPER(title) NOT LIKE 'test%' "); // Eliminate tests.

$taxes = Tax::get_instances();

echo $OUTPUT->heading(get_string('taxes', 'local_shop'), 1);

if (empty($taxes)) {
    echo $OUTPUT->box(get_string('notaxes', 'local_shop'));
} else {
    $namestr = get_string('nametax', 'local_shop');
    $countrystr = get_string('countrytax', 'local_shop');
    $ratiostr = get_string('ratiotax', 'local_shop');
    $formulastr = get_string('taxformula', 'local_shop');
    $countproductsstr = get_string('countproducts', 'local_shop');

    $table = new html_table();
    $table->head = array("<b>$namestr</b>",
                         "<b>$countrystr</b>",
                         "<b>$ratiostr</b>",
                         "<b>$formulastr</b>",
                         "<b>$countproductsstr</b>",
                         '');
    $table->width = '100%';
    $table->align = array('left', 'left', 'center', 'left', 'center', 'right');
    foreach ($taxes as $t) {
        $row = array();
        $row[] = format_string($t->title);
        $row[] = $t->country;
        $row[] = $t->ratio;
        $row[] = $t->formula;

        $pcount = 0 + CatalogItem::count(array('taxcode' => $t->id));
        $row[] = $pcount;

        $params = array('taxid' => $t->id, 'what' => 'updatetax');
        $editurl = new moodle_url('/local/shop/taxes/edit_tax.php', $params);
        $commands = '<a href="'.$editurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" /></a>';

        if ($pcount == 0) {
            $params = array('view' => 'viewAllTaxes',
                            'order' => $order,
                            'dir' => $dir,
                            'taxid' => $t->id,
                            'what' => 'delete');
            $deleteurl = new moodle_url('/local/shop/taxes/view.php', $params);
            $commands .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
        }
        $row[] = '<div class="shop-line-commands">'.$commands.'</div>';

        $table->data[] = $row;
    }

    echo html_writer::table($table);
}

$addtaxstr = get_string('newtax', 'local_shop');
$addurl = new moodle_url('/local/shop/taxes/edit_tax.php');
echo '<div class="addlink"><a href="'.$addurl.'">'.$addtaxstr.'</a>';