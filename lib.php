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
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function local_shop_has_leaflet($itemid) {

    $fs = get_file_storage();
    $context = context_system::instance();
    return !$fs->is_area_empty($context->id, 'local_shop', 'catalogitemleaflet', $itemid);

}

function local_shop_pluginfile($course, $birecord, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $areas = local_shop_get_file_areas();

    // Filearea must contain a real area.
    if (!isset($areas[$filearea])) {
        return false;
    }

    $itemid = (int)array_shift($args);

    if (!$record = $DB->get_record($areas[$filearea], array('id' => $itemid))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_shop/$filearea/$itemid/$relativepath";

    $oldpath = "/$context->id/block_shop/$filearea/$itemid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
        // Try getting an old file when shop was a block.

        if (!$oldfile = $fs->get_file_by_hash(sha1($oldpath)) or $oldfile->is_directory()) {
            return false;
        }

        $filerec = new Stdclass;
        $filerec->contextid = $context->id;
        $filerec->component = 'local_shop';
        $filerec->filearea = $filearea;
        $filerec->itemid = $itemid;
        $filerec->filepath = str_replace('//', '/', '/'.dirname($relativepath).'/');
        $filerec->filename = basename($relativepath);

        $file = $fs->create_file_from_storedfile($filerec, $oldfile);
    } else {
        if ($file->is_directory()) {
            return false;
        }
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}

/**
 * maps back all used fileareas to the table where the
 * item entity resides
 */
function local_shop_get_file_areas() {
    return array(
        'catalogdescription' => 'local_shop_catalog',
        'catalogsalesconditions' => 'local_shop_catalog',
        'catalogitemdescription' => 'local_shop_catalogitem',
        'catalogitemnotes' => 'local_shop_catalogitem',
        'catalogitemthumb' => 'local_shop_catalogitem',
        'catalogitemimage' => 'local_shop_catalogitem',
        'catalogitemunit' => 'local_shop_catalogitem',
        'catalogitemleaflet' => 'local_shop_catalogitem',

        'categorydescription' => 'local_shop_catalogcategory',
    );
}

/**
 * Get a subrenderer instance from a shop submodule
 * @param string $module
 */
function shop_get_renderer($module = 'front') {
    global $CFG;

    $slashedmodule = '/'.$module;

    include_once($CFG->dirroot."/local/shop{$slashedmodule}/renderer.php");

    $class = "shop_{$module}_renderer";

    return new $class();
}