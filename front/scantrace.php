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

/**
 * This script is a simple tool for salesadmins for 
 * extracting and inspecting a transaton backtrace.
 * It is provided for problem or claim solving.
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use \local_shop\Shop;

$transid = optional_param('transid', '', PARAM_TEXT);
$id = required_param('id', PARAM_INT);
$theShop = new Shop($id);

// Security.
$context = context_system::instance();
require_capability('local/shop:salesadmin', $context);

$url = new moodle_url('/local/shop/front/scantrace.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('scantrace', 'local_shop'));

$scanstr = get_string('scantrace', 'local_shop');
$transids = $DB->get_records('local_shop_bill', null, 'id', 'transactionid, amount');

echo $OUTPUT->box_start();

echo '<form name="transidform" method="POST" >';
print_string('pastetransactionid', 'local_shop');
echo '<input type="text" name="transid" size="40" />';
echo '<input type="submit" name="g_btn" value="'.$scanstr.'" />';
echo '</form>';

echo get_string('or', 'local_shop');

echo '<form name="transidform" method="POST" >';

print_string('picktransactionid', 'local_shop');
echo '<select name="transid" />';

foreach ($transids as $trans) {
    echo '<option value="'.$trans->transactionid.'" >'.$trans->transactionid.' ('.$trans->amount.')</option>';
}

echo '</select>';
echo '<input type="submit" name="g_btn" value="'.$scanstr.'" />';
echo '</form>';
echo $OUTPUT->box_end();

if ($transid) {
    $tracecontent = file($CFG->dataroot.'/merchant_trace.log');
    $trace = preg_grep("/\\[$transid\\]/", $tracecontent);
    if ($trace) {
        echo '<pre>';
        foreach ($trace as $tr) {
            echo $tr;
        }
        echo '</pre>';
    } else {
        print_string('notrace', 'local_shop');
    }
}
echo '<br/>';
echo '<center>';
$options['id'] = $theShop->id;
echo $OUTPUT->single_button(new moodle_url('/local/shop/index.php', $options), get_string('backtoshopadmin', 'local_shop'), 'get');
$options['view'] = 'shop';
echo $OUTPUT->single_button(new moodle_url('/local/shop/front/view.php', $options), get_string('backtoshop', 'local_shop'), 'get');
echo '</center>';

echo $OUTPUT->footer();