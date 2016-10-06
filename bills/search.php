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

defined('MOODLE_INTERNAL') || die;

$action = optional_param('what', '', PARAM_TEXT);
if ($action != '') {
    include($CFG->dirroot.'/local/shop/bills/search.controller.php');
    $controller = new \local_shop\bills\search_controller($theshop);
    $bills = $controller->process($action);
}

$billcount = $DB->count_records('local_shop_bill');

echo $out;

?>
<script type="text/javascript">
function searchBy(criteria) {
    document.search.by.value = criteria;
    document.search.submit();
}
</script>

<?php

echo $OUTPUT->heading(get_string('billsearch', 'local_shop'), 3);

if (empty($bills)) {
    print_string('errorsearchbillfailed', 'local_shop');
} else {
    echo $OUTPUT->heading(get_string('results', 'local_shop'), 2);
    echo '<p>';
    print_string('manybillsasresult', 'local_shop');
    echo ':</p>';
    echo '<table width="100%">';
    foreach ($bills as $portlet) {
        include($CFG->dirroot.'/local/shop/lib/shortBillLine.php');
    }
    echo '</table>';
}
?>

<form name="search" action="#" method="get">
<input type="hidden" name="id" value="<?php p($blockinstance->id) ?>">
<input type="hidden" name="by" value="">
<input type="hidden" name="what" value="search">
<table>
<?php
if ($billcount == 0) {
?>
<tr>
    <td colspan="4" class="billRow">
    <?php print_string('nobills', 'local_shop') ?>
    </td>
</tr>
<?php
} else {
?>
    <tr>
        <td valign="top">
           <h2><?php print_string('searchby', 'local_shop') ?></h2>
        </td>
    </tr>
    <tr>
        <td align="center">
            <?php echo $OUTPUT->heading(get_string('uniquetransactionkey', 'local_shop'), 3) ?>
            <input type="text" name="billkey" style="font-family : 'Courier New', monospace ; width : 30em"><br/>
            <p class="smalltext"><?php print_string('searchforakeyinstructions', 'local_shop') ?>.
        </td>
    </tr>
    <tr>
         <td align="right">
             <a href="Javascript:searchBy('key');"><?php print_string('search') ?></a>
         </td>
    </tr>
    <tr>
        <td align="center">
            <?php echo $OUTPUT->heading(get_string('orclientname', 'local_shop'), 3) ?>
            <input type="text" name="customerName" width="50" maxlength="60"><br>
            <p class="smalltext"><?php print_string('customersnameonbill', 'local_shop') ?>.
        </td>
    </tr>
    <tr>
        <td align="right">
            <a href="Javascript:searchBy('name');"><?php print_string('search') ?></a>
        </td>
    </tr>
    <tr>
        <td align="center">
            <?php echo $OUTPUT->heading(get_string('orbillid', 'local_shop'), 3) ?>
            <input type="text" name="billid" width="5" maxlength="10"><br>
            <p class="smalltext"><?php print_string('billorderingnumber', 'local_shop') ?>.
        </td>
    </tr>
    <tr>
        <td align="right">
            <a href="Javascript:searchBy('id');"><?php print_string('search') ?></a>
        </td>
    </tr>
    <tr>
        <td align="center">
            <?php echo $OUTPUT->heading(get_string('oremissiondate', 'local_shop'), 3) ?>
            <?php print_string('from (date)', 'local_shop') ?>
            <input type="text" name="dateFrom" width="10" maxlength="10">
            <?php print_string('hour', 'local_shop') ?>
            <input type="text" name="timeFrom" width="10" maxlength="10">
            <?php print_string('until', 'local_shop') ?>
            <select name="during">
                <option value="h"><?php print_string('onehour', 'local_shop') ?></option>
                <option value="d"><?php print_string('oneday', 'local_shop') ?></option>
                <option value="10d" SELECTED ><?php print_string('tendays', 'local_shop') ?></option>
                <option value="m"><?php print_string('onemonth', 'local_shop') ?></option>
                <option value="3m"><?php print_string('threemonths', 'local_shop') ?></option>
            </select> <?php print_string('after', 'local_shop') ?>.<br>
            <p class="smalltext"><?php print_string('searchtimerange', 'local_shop') ?>.
        </td>
    </tr>
    <tr>
        <td align="right">
            <a href="Javascript:searchBy('date');"><?php print_string('search') ?></a>
        </td>
    </tr>
<?php
}
?>
</table>
</form>
<table>
</table>