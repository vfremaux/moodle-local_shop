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
 *
 * Wrappers for interfadcing with other components in Moodle
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/locallib.php');

/**
 * checks if there are any catalogs available
 * @return boolean
 */
function local_shop_has_shops() {
    global $DB;

    // Silently tries.
    $shops = 0;
    try {
        $shops = $DB->count_records('local_shop');
    } catch (Exception $e) {
        // Just ignore errors.
        return false;
    }

    return $shops > 0;
}

/**
 * Searches in the shop for a product matching the shortname, or
 * having a handler that matches the shortname.
 */
function local_shop_related_product($courseorid, $shopid = 0) {
    global $DB;

    $candidateproduct = null;

    if (is_numeric($courseorid)) {
        $shortname = $DB->get_field('course', 'shortname', ['id' => $courseorid]);
    } else {
        $shortname = $courseorid->code;
    }

    $sql = '
        SELECT
            ci.*
        FROM
            {local_shop_catalogitem} ci,
            {local_shop_catalog} c
        WHERE
            ci.catalogid = c.id AND
            ci.status = "AVAILABLE"
    ';
    $sql .= \local_shop\Catalog::get_isloggedin_sql('ci');

    $sql .= " AND shortname = ? ";
    $params = ['shortname' => $shortname];
    if ($shopid) {
        $sql .= " AND c.shopid = ? ";
        $params['shopid'] = $shopid;
    }
    $directproducts = $DB->get_records_sql($sql, $params);
    if (!empty($directproducts)) {
        $candidate = array_shift($directproducts);
        $candidateproduct = new \local_shop\CatalogItem($candidate->id);
        $candidateproduct->check_availability();
        if (!$candidateproduct->available) {
            $candidateproduct = null;
        }
    }

    if (empty($candidateproduct)) {

        // Indirect products are not named with the shortname, but allow single enrol in the course.
        $select = '
            (handlerparams LIKE ? OR handlerparams LIKE ?) AND
            enablehandler = "std_enrolonecourse" AND
            status = "AVAILABLE"
        ';
        $select .= \local_shop\Catalog::get_isloggedin_sql('');

        $params = ['%coursename='.$shortname, '%coursename='.$shortname.'&%'];
        if ($indirectproducts = $DB->get_records_select('local_shop_catalogitem', $select, $params)) {
            $candidate = array_shift($indirectproducts);
            $candidateproduct = new \local_shop\CatalogItem($candidate->id);
            $candidateproduct->check_availability();
            if (!$candidateproduct->available) {
                $candidateproduct = null;
            }
        }
    }

    return $candidateproduct;
}