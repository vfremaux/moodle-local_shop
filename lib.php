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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/compatlib.php');

/**
 * This is part of the dual release distribution system.
 * Tells wether a feature is supported or not. Gives back the
 * implementation path where to fetch resources.
 * @param string $feature a feature key to be tested.
 */
function local_shop_supports_feature($feature = null) {
    global $CFG;
    static $supports;

    if (!during_initial_install()) {
        $config = get_config('local_shop');
    }

    if (!isset($supports)) {
        $supports = [
            'pro' => [
                'handlers' => ['fullstack'],
                'paymodes' => ['fullstack'],
                'catalog' => ['instances'],
                'shop' => ['instances', 'partners', 'discounts'],
                'products' => ['editable', 'smarturls'],
                'docgen' => ['pdf'],
                'api' => ['ws'],
                'bill' => ['attachements', 'lettering'],
            ],
            'community' => [
                'handlers' => ['basic'],
                'paymodes' => ['basic'],
            ],
        ];
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

    if (empty($feature)) {
        // Just return version.
        return $versionkey;
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

/**
 * checks in file storage if a leaflet document exists for an item.
 * TODO : move it to CatalogItem class
 * @param int $itemid
 */
function local_shop_has_leaflet($itemid) {
    $fs = get_file_storage();
    $context = context_system::instance();
    return !$fs->is_area_empty($context->id, 'local_shop', 'catalogitemleaflet', $itemid);
}

/**
 * Standard pluginfile
 * @param object $course
 * @param int $cmid
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 */
function local_shop_pluginfile($course, $cmid, $context, $filearea, $args, $forcedownload, array $options = []) {
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

    if ($filearea != 'shoplogo') {
        if (!$record = $DB->get_record($areas[$filearea], ['id' => $itemid])) {
            return false;
        }
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
    return [
        'description' => 'local_shop',
        'eula' => 'local_shop',
        'catalogdescription' => 'local_shop_catalog',
        'catalogbillfooter' => 'local_shop_catalog',
        'catalogitemdescription' => 'local_shop_catalogitem',
        'catalogitemnotes' => 'local_shop_catalogitem',
        'catalogitemthumb' => 'local_shop_catalogitem',
        'catalogitemimage' => 'local_shop_catalogitem',
        'catalogitemunit' => 'local_shop_catalogitem',
        'catalogitemeula' => 'local_shop_catalogitem',
        'catalogitemleaflet' => 'local_shop_catalogitem',
        'shoplogo' => 'shoplogo',
        'categorydescription' => 'local_shop_catalogcategory',
    ];
}

/**
 * Get a subrenderer instance from a shop submodule. The function recognizes
 * any pro override or pro location before failing over to community renderers.
 * @param string $module
 */
function shop_get_renderer($module = 'front') {
    global $CFG, $PAGE, $OUTPUT;

    // Get the base renderer, switching to pro extension if possible.
    if ($module == 'base') {
        if (!local_shop_supports_feature('catalog/instances')) {
            return $PAGE->get_renderer('local_shop');
        } else {
            include_once($CFG->dirroot.'/local/shop/pro/renderer.php');
            $renderer = new local_shop_renderer_extended($PAGE, '');
            $renderer->set_output($OUTPUT); // This is to comply general renderer model.
            return $renderer;
        }
    }

    // Get other modules.
    $slashedmodule = '/'.$module;

    if (file_exists($CFG->dirroot."/local/shop/pro{$slashedmodule}/renderer.php") &&
            local_shop_supports_feature() == 'pro') {
        // Do we know something about "pro" version ?
        include_once($CFG->dirroot."/local/shop/pro{$slashedmodule}/renderer.php");
        $class = "shop_{$module}_renderer_extended";
        return new $class();
    }

    include_once($CFG->dirroot."/local/shop{$slashedmodule}/renderer.php");
    $class = "shop_{$module}_renderer";
    return new $class();
}
