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
 * Manage files attachements in bill.
 *
 * @package   local_shop
 * @category  local
 * @copyright 2010 Valery Fremaux <valery.fremaux@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/pro/forms/form_bill_attachement.php');
require_once($CFG->dirroot.'/repository/lib.php');

$id = required_param('id', PARAM_INT); // Shop id.
$billid = required_param('billid', PARAM_INT);

// Security.

require_login();
$context = context_system::instance();
require_capability('local/shop:salesadmin', $context);
// TODO : upgrade this security check when subdelegated sales are possible.

$title = get_string('attachfiles', 'local_shop');

$url = new moodle_url('/local/shop/pro/bills/attachto.php', ['billid' => $billid, 'id' => $id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->navbar->add(get_string('pluginname', 'local_shop'), $CFG->wwwroot.'/local/shop/index.php');
$PAGE->navbar->add(get_string('bill', 'local_shop'));
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('user-files');

$options = array('subdirs' => false, 'maxbytes' => -1, 'maxfiles' => -1, 'accepted_types' => 'pdf,jpg,png,doc,docx,xls,xlsx,odt', 'areamaxbytes' => -1);

file_prepare_standard_filemanager($data, 'files', $options, $context, 'local_shop', 'billfiles', $billid);

$mform = new bill_attachement_form($url, array('options' => $options));

$returnurl = new moodle_url('/local/shop/bills/view.php', ['view' => 'viewBill', 'billid' => $billid]);
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($formdata = $mform->get_data()) {
    $formdata = file_postupdate_standard_filemanager($formdata, 'files', $options, $context, 'local_shop', 'billfiles', $billid);
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->box_start('generalbox');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
