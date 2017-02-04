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

/**
 * This is part of the dual release distribution system.
 * Tells wether a feature is supported or not. Gives back the 
 * implementation path where to fetch resources.
 * @param string $feature a feature key to be tested.
 */
function local_shop_supports_feature($feature) {
    global $CFG;
    static $supports;

    if (!isset($supports)) {
        $supports = array(
            'pro' => array(
                'handlers' => array('fullstack'),
                'paymodes' => array('fullstack'),
            ),
            'community' => array(
                'handlers' => array('basic'),
                'paymodes' => array('basic'),
            ),
        );
    }

    // Check existance of the 'pro' dir in plugin.
    if (is_dir(__DIR__.'/pro')) {
        if ($feature == 'emulate/community') {
            return 'pro';
        }
        if (empty($config->emulatecommunity)) {
            $versionkey = 'pro';
        } else {
            $versionkey = 'community';
        }
    } else {
        $versionkey = 'community';
    }

    list($feat, $subfeat) = explode('/', $feature);

    if (!array_key_exists($feat, $supports[$versionkey])) {
        return false;
    }

    if (!in_array($subfeat, $supports[$versionkey][$feat])) {
        return false;
    }

    return $versionkey;
}

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

        if ((!$oldfile = $fs->get_file_by_hash(sha1($oldpath))) || $oldfile->is_directory()) {
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
    send_stored_file($file, 0, 0, true, $options); // Download MUST be forced - security!
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