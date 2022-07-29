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

require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

use \local_shop\Bill;
use \local_shop\BillItem;

// We needs them later in this script.
$relocated = optional_param('relocated', '', PARAM_TEXT);
$z = optional_param('z', '', PARAM_TEXT);

/* perform local commands on orderitems */
$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include_once($CFG->dirroot.'/local/shop/bills/bills.controller.php');
    $controller = new \local_shop\backoffice\bill_controller($theshop, $thecatalog, $theblock);
    $controller->receive($action);
    $controller->process($action);
}

$afullbill = new Bill($billid); // Complete bill data.

$PAGE->requires->js_call_amd('local_shop/bills', 'init');

echo $out;
echo $OUTPUT->box_start('', 'billpanel');

/*
echo '<form name="selection" action="'.$url.'" method="get">';
echo '<input type="hidden" name="what" value="" />';
echo '<input type="hidden" name="items" value="" />';
echo '</form>';
*/

echo $renderer->bill_header($afullbill, $url);

echo $renderer->customer_info($afullbill, true);

echo $OUTPUT->heading(get_string('order', 'local_shop'), 2);

echo '<table class="generaltable" width="100%">';
if (count($afullbill->items) == 0) {
    echo $renderer->no_items();
} else {
    echo $renderer->billitem_line(null);
    if ($afullbill->items) {
        foreach ($afullbill->items as $portlet) {
            if (($action == 'relocating') && ($portlet->ordering <= $z)) {
                echo $renderer->relocate_box($portlet->id, $portlet->ordering, $z, $relocated);
            }
            if (($action != 'relocating') || ($portlet->id != $relocated)) {
                echo $renderer->billitem_line($portlet);
            }
            if (($action == 'relocating') && ($portlet->ordering > $z)) {
                echo $renderer->relocate_box($portlet->id, $portlet->ordering, $z, $relocated);
            }
        }
    }
}
echo '</table>';

echo $renderer->full_bill_totals($afullbill);
echo $renderer->full_bill_taxes($afullbill, $theshop);
echo $renderer->bill_footer($afullbill);

echo $renderer->flow_controller($afullbill->status, $url);
<<<<<<< HEAD
=======
echo $renderer->ownership($afullbill);
>>>>>>> MOODLE_40_STABLE

echo $renderer->attachments($afullbill);

echo $renderer->bill_controls($afullbill);

echo $OUTPUT->box_end();