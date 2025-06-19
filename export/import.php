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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

// Get all the shop session context objects.

list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

$context = context_system::instance();

require_login();
require_capability('local/shop:salesadmin', $context);

$what = required_param('what', PARAM_TEXT);
$format = required_param('format', PARAM_TEXT);

if (file_exists($CFG->dirroot.'/local/shop/export/importers/import_'.$what.'.php')) {
    require_once($CFG->dirroot.'/local/shop/export/importers/import_'.$what.'.php');
} else {
    throw new moodle_exception(get_string('erroremptyimport', 'local_shop'));
}

if (file_exists($CFG->dirroot.'/local/shop/export/formats/import_'.$format.'.php')) {
    require_once($CFG->dirroot.'/local/shop/export/formats/import_'.$format.'.php');
} else {
    throw new moodle_exception(get_string('errorbadformatrenderer', 'local_shop'));
}

$parserclass = "shop_import_source_$what";
$parser = new $parserclass();
$data = $parser->read_data($params);

$injectorclass = "shop_import_$format";
$injector = new $injectorclass($data, $datadesc, ['addtimestamp' => 1]);

$PAGE->set_url();

echo $OUTPUT->header();

$injector->run();

echo $OUTPUT->footer();
