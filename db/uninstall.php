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
 * Pre uninstall sequence
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Pre plugin uninstall
 */
function xmldb_local_shop_uninstall() {
    global $DB;

    $editingteacherid   = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
    $courseownerid   = $DB->get_field('role', 'id', ['shortname' => 'courseowner']);
    $coursecreatorid   = $DB->get_field('role', 'id', ['shortname' => 'coursecreator']);
    $categoryownerid   = $DB->get_field('role', 'id', ['shortname' => 'categoryowner']);

    // Remap all teacherowner assignments to editingteacher.
    $sql = "
        UPDATE
            {role_assignments}
        SET
            roleid = $editingteacherid
        WHERE
            roleid = $courseownerid
    ";
    $DB->execute($sql);

    // Remap all categoryowner assignments to coursecreator.
    $sql = "
        UPDATE
            {role_assignments}
        SET
            roleid = $coursecreatorid
        WHERE
            roleid = $categoryownerid
    ";
    $DB->execute($sql);

    // Delete the teacherowner role if absent.
    $courseownerrole = $DB->get_record('role', ['name' => 'courseowner']);
    $categoryownerrole = $DB->get_record('role', ['name' => 'categoryowner']);
    $salesmanagerrole = $DB->get_record('role', ['name' => 'sales']);
    delete_role($courseownerrole->id);
    delete_role($categoryownerrole->id);
    delete_role($salesmanagerrole->id);
}
