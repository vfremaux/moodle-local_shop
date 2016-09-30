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
require_once($CFG->dirroot.'/local/shop/forms/form_tax.class.php'); //imports of Form tax

// Get the block reference and key context.

$taxid = optional_param('taxid', 0, PARAM_INT);

// Security.

$context = context_system::instance();
require_login();
require_capability('local/shop:salesadmin', $context);

// Make page header and navigation.

$url = new moodle_url('/local/shop/taxes/edit_tax.php', array('taxid' => $taxid));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_shop'));
$PAGE->set_heading(get_string('pluginname', 'local_shop'));
$PAGE->navbar->add(get_string('salesservice', 'local_shop'), new moodle_url('/local/shop/index.php'));
$PAGE->navbar->add(get_string('taxes', 'local_shop'));

if ($taxid) {
    $tax = $DB->get_record('local_shop_tax', array('id' => $taxid));
    $mform = new Tax_Form('', array('what' => 'edit'));
    $tax->taxid = $taxid;
    unset($tax->id);
    $mform->set_data($tax);
} else {
    $mform = new Tax_Form('', array('what' => 'add'));
}

if ($mform->is_cancelled()) {
     redirect(new moodle_url('/local/shop/taxes/view.php', array('view' => 'viewAllTaxes')));
}
if ($data = $mform->get_data()) {
    if (empty($data->taxid)) {
        $data->id = $DB->insert_record('local_shop_tax', $data);
    } else {
        $data->id = $data->taxid;
        unset($data->taxid);
        $DB->update_record('local_shop_tax', $data);
    }
    redirect(new moodle_url('/local/shop/taxes/view.php', array('view' => 'viewAllTaxes')));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();