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

// Get all the shop session context objects.
list($theshop, $thecatalog, $theblock) = shop_build_context();

// Security.

$context = context_system::instance();

require_login();
require_capability('local/shop:salesadmin', $context);

$what = required_param('what', PARAM_TEXT);
$format = required_param('format', PARAM_TEXT);

if (file_exists($CFG->dirroot.'/local/shop/export/extractors/export_'.$what.'.php')) {
    require_once($CFG->dirroot.'/local/shop/export/extractors/export_'.$what.'.php');
} else {
    print_error('erroremptyexport', 'local_shop');
}

if (file_exists($CFG->dirroot.'/local/shop/export/formats/export_'.$format.'.php')) {
    require_once($CFG->dirroot.'/local/shop/export/formats/export_'.$format.'.php');
} else {
    print_error('errorbadformatrenderer', 'local_shop');
}

$extractorclass = "shop_export_source_$what";
$extractor = new $extractorclass();
$params = new StdClass;
$params->catalogid = $thecatalog->id;
$datadesc = $extractor->get_data_description($params);
$data = $extractor->get_data($params);

$rendererclass = "shop_export_$format";
$renderer = new $rendererclass($data, $datadesc, array('addtimestamp' => 1));
$renderer->open_export();
$renderer->render();
$renderer->close_export();