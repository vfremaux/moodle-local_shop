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
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
 // TODO : check if still useful.
 
defined('MOODLE_INTERNAL') || die();

if ($cmd == 'updatecatalog') {
    $catalog->id = required_param('item', PARAM_INT);
    $catalog->name = required_param('name', PARAM_TEXT);
    $catalog->description = optional_param('description', '', PARAM_CLEANHTML);
    $catalog->linked = optional_param('linked', 'free', PARAM_ALPHA);
    $catalog->groupid = optional_param('groupid', 0, PARAM_INT);
    $DB->update_record('local_shop_catalog', $catalog);

    if ($catalog->linked != 'free') {
        if ($catalog->linked == 'master') {
            $groupidvalue = $id;
        } else if ($catalog->linked == 'slave') {
            $groupidvalue = $catalog->groupid;
        }
        $sql = "
           UPDATE
              {local_shop_catalog}
           SET
              groupid = '{$groupidvalue}'
           WHERE
              id = '{$catalog->id}'
        ";
        $DB->execute($sql);
    }

    redirect(new moodle_url('/local/shop/index.php'));
}
if ($cmd == 'deletecatalog') {
    $catalogid = required_param('catalogid', PARAM_INT);
    $catalogidlist = $catalogid;

    // If master catalog, must delete all slaves.
    include($CFG->dirroot.'/classes/Catalog.class.php');
    $thecatalog = new Catalog($catalogid);
    if ($thecatalog->ismaster) {
        $catalogids = $DB->get_records_select_menu('local_shop_catalog', " groupid = ? AND id != groupid ", array($catalogid), '', 'id,id');
        $catalogidlist = implode("','", array_values($catalogids));
    }
    // Deletes products entries in candidate catalogs.
    $DB->delete_records_select('local_shop_catalogitem', " id IN ('$catalogidlist') ");
    $DB->delete_records_select('local_shop_catalogcategory', " id IN ('$catalogidlist') ");
    $DB->delete_records_select('local_shop_catalog', " id IN ('$catalogidlist') ");

}