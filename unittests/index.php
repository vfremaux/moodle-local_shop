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

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/unittests/index.controller.php');

use local_shop\Shop;

// get all the shop session context objects
list($theShop, $theCatalog, $theBlock) = shop_build_context();

// Get block information.

$selected = array();

// Security.

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/shop:salesadmin', $context);

$action = optional_param('what', '', PARAM_ALPHA); // the action command

if ($action) {
    include_once($CFG->dirroot.'/local/shop/unittests/index.controller.php');
    $controller = new \local_shop\back\unittests_controller($theShop, $theCatalog, $theBlock);
    $controller->receive($action);
    list($errors, $warnings, $messages) = $controller->process($action);
}

// make page header.

$url = new moodle_url('/local/shop/unittests/index.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('catalogues', 'local_shop'));
$PAGE->navbar->add($theCatalog->name, new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts')));
$PAGE->navbar->add(get_string('unittests', 'local_shop'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('unittests', 'local_shop'));

$warningstr = get_string('warning', 'local_shop');
$errorstr = get_string('error', 'local_shop');
$messagestr = get_string('message', 'local_shop');

echo '<center>';

if ($productline = $theCatalog->get_products()) {
    $testtable = new html_table();

    $productcodestr = get_string('code', 'local_shop');
    $productnamestr = get_string('name', 'local_shop');
    $productdescstr = get_string('description');
    $productparamsstr = get_string('handlerparams', 'local_shop');
    $productrequirementsstr = get_string('requiredparams', 'local_shop');

    $testtable->width = "100%"; 
    $testtable->size = array('5%', '10%', '20%', '25%', '20%', '20%'); 
    $testtable->head = array("", "<b>$productcodestr</b>", "<b>$productnamestr</b>", "<b>$productdescstr</b>", "<b>$productparamsstr</b>", "<b>$productrequirementsstr</b>");

    foreach ($productline as $productcode => $catalogitem) {
        $presel = (in_array($productcode, $selected)) ? ' checked="checked" ' : '' ; 
        $selbox = '<input type="checkbox" name="sel[]" value="'.$productcode.'" '.$presel.' >';
        $producturl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $catalogitem->id));
        $productlink = '<a href="'.$producturl.'">'.format_string($catalogitem->name).'</a>';
        $testtable->data[] = array($selbox, $productcode, $productlink, $catalogitem->description, '<b>'.$catalogitem->enablehandler.'</b><br/>'.$catalogitem->get_serialized_handlerparams(), $catalogitem->requireddata);
    }

    echo $OUTPUT->box_start('generalbox');
    echo '<form name="testform" action="" method="post">';
    echo '<input type="hidden" name="what" value="test"/>';

    // we need write the table by ourselves... 
    echo '<table width="'.$testtable->width.'" class="generaltable">';
    $i = 0;
    echo '<tr valign="top" class="row">';
    foreach ($testtable->head as $col) {
        echo '<th class="header c'.$i.'" width="'.$testtable->size[$i].'">'.$testtable->head[$i].'</th>';
        $i++;
    }
    echo '</tr>';
    $j = 0;
    foreach ($testtable->data as $row) {
        echo '<tr valign="top" class="row r'.$j.'">';
        $i = 0;
        foreach ($row as $cell) {
            if ($i == 1) {
                $itemcode = $cell;
            }
            echo '<td class="cell c'.$i.'" width="'.$testtable->size[$i].'">'.$cell.'</td>';
            $i++;
        }
        echo '</tr>';

        if ($action == 'test') {
            if (!empty($messages)) {
                if (array_key_exists($itemcode, $messages)) {
                    echo '<tr valign="top" class="row r'.$j.'">';
                    echo '<td colspan="6">';
                    foreach ($messages[$itemcode] as $message) {
                        echo '<li class="shop-message-result">'.$messagestr.$message.'</li>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            }

            if (!empty($errors)) {
                if (array_key_exists($itemcode, $errors)) {
                    echo '<tr valign="top" class="row r'.$j.'">';
                    echo '<td colspan="6">';
                    foreach ($errors[$itemcode] as $error) {
                        echo '<li class="shop-error-result">'.$errorstr.$error.'</li>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
            }

            if (!empty($warnings)) {
                if (array_key_exists($itemcode, $warnings)) {
                    echo '<tr valign="top" class="row r'.$j.'">';
                    echo '<td colspan="6">';
                        foreach ($warnings[$itemcode] as $warning) {
                            echo '<li class="shop-warning-result">'.$warningstr.$warning.'</li>';
                        }
                    echo '</td>';
                    echo '</tr>';
                }
            }
        }

        $j = ($j + 1) % 2;
    }

    echo '</table>';
    echo '<br/>';
    echo '<input type="submit" name="go_test" value="'.get_string('gotest', 'local_shop').'" />';
    echo '</form >';
    echo '<br/>';
    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->box_start('generalbox');
    echo get_string('noproducts', 'local_shop');
    echo $OUTPUT->continue_button(new moodle_url('/local/shop/index.php'));
    echo $OUTPUT->box_end('generalbox');
}

echo '</center>';
echo $OUTPUT->footer();