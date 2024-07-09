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
 * Library for catalog items management
 *
 * @package    local_shop
 * @category   local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function shop_products_process_files($data, $context, $usercontext) {
    global $USER;

    $fs = get_file_storage();

    if (empty($usercontext)) {
        $usercontext = context_user::instance($USER->id);
    };

    $filepickeritemid = $data->grleaflet['leaflet'];
    if (!$fs->is_area_empty($usercontext->id, 'user', 'draft', $filepickeritemid, true)) {
        file_save_draft_area_files($filepickeritemid, $context->id, 'local_shop', 'catalogitemleaflet', $data->id);
    }

    if (!empty($data->grleaflet['clearleaflet'])) {
        $fs->delete_area_files($context->id, 'local_shop', 'catalogitemleaflet', $data->id);
    }

    $filepickeritemid = $data->grimage['image'];
    if (!$fs->is_area_empty($usercontext->id, 'user', 'draft', $filepickeritemid, true)) {
        file_save_draft_area_files($filepickeritemid, $context->id, 'local_shop', 'catalogitemimage', $data->id);
    }

    if (!empty($data->grimage['clearimage'])) {
        $fs->delete_area_files($context->id, 'local_shop', 'catalogitemimage', $data->id);
    }

    $filepickeritemid = $data->grthumb['thumb'];
    if (!$fs->is_area_empty($usercontext->id, 'user', 'draft', $filepickeritemid, true)) {
        file_save_draft_area_files($filepickeritemid, $context->id, 'local_shop', 'catalogitemthumb', $data->id);
    }

    if (!empty($data->grthumb['clearthumb'])) {
        $fs->delete_area_files($context->id, 'local_shop', 'catalogitemthumb', $data->id);
    }

    $filepickeritemid = $data->grunit['unit'];
    if (!$fs->is_area_empty($usercontext->id, 'user', 'draft', $filepickeritemid, true)) {
        file_save_draft_area_files($filepickeritemid, $context->id, 'local_shop', 'catalogitemunit', $data->id);
    }

    if (!empty($data->grunit['clearunit'])) {
        $fs->delete_area_files($context->id, 'local_shop', 'catalogitemunit', $data->id);
    }
}
