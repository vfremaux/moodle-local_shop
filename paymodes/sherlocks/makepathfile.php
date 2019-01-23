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
 * @package    shopaymodes_sherlocks
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/paymodes/sherlocks/sherlocks.class.php');

require_capability('moodle/site:config', context_system::instance());

echo $OUTPUT->header();

if (empty($config->sherlocks_processor_type)) {
    set_config('sherlocks_processor_type', 32, 'local_shop');
}

$data->proctype = (!empty($config->sherlocks_processor_type)) ? $config->sherlocks_processor_type : '32';
$data->os = $CFG->os;

echo $OUTPUT->heading(get_string('generatingpathfile', 'shoppaymodes_sherlocks', $data));

$blockinstance = null;
$payhandler = new shop_paymode_sherlocks($blockinstance);
$payhandler->generate_pathfile();

echo $OUTPUT->footer();