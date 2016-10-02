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
defined('MOODLE_INTERNAL') || die();

function xmldb_local_shop_uninstall() {
    global $DB;

    $editingteacherid   = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));
    $courseownerid   = $DB->get_field('role', 'id', array('shortname' => 'courseowner'));
    $coursecreatorid   = $DB->get_field('role', 'id', array('shortname' => 'coursecreator'));
    $categoryownerid   = $DB->get_field('role', 'id', array('shortname' => 'categoryowner'));
    // Remap all teacherowner assignments to editingteacher.
    $sql = "
        UPDATE
            {role_assignment}
        SET
            roleid = $editingteacherid
        WHERE
            roleid = $courseownerid
    ";
    $DB->execute($sql);

    // Remap all categoryowner assignments to coursecreator.
    $sql = "
        UPDATE
            {role_assignment}
        SET
            roleid = $coursecreatorid
        WHERE
            roleid = $categoryownerid
    ";
    $DB->execute($sql);

    // Delete the teacherowner role if absent.
    $courseownerrole = $DB->get_record('role', array('name' => 'courseowner'));
    $categoryownerrole = $DB->get_record('role', array('name' => 'categoryowner'));
    $salesmanagerrole = $DB->get_record('role', array('name' => 'sales'));
    delete_role($courseownerrole->id);
    delete_role($categoryownerrole->id);
    delete_role($salesmanagerrole->id);
}