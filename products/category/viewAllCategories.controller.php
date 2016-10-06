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
namespace local_shop\products;

defined('MOODLE_INTERNAL') || die();

class category_controller {

    public function process($cmd) {
        global $DB;

        // Delete a category.
        if ($cmd == 'delete') {
            $categoryids = required_param_array('categoryids', PARAM_INT);
            $categoryidlist = implode("','", $categoryids);
            $DB->delete_records_select('local_shop_catalogcategory', " id IN ('$categoryidlist') ");

        } else if ($cmd == 'up') {
            // Raises a question in the list ****************.
            $cid = required_param('categoryid', PARAM_INT);

            shop_list_up($shop, $cid, 'local_shop_catalogcategory');

        } else if ($cmd == 'down') {
            // Lowers a question in the list ****************.
            $cid = required_param('categoryid', PARAM_INT);

            shop_list_down($shop, $cid, 'local_shop_catalogcategory');

        } else if ($cmd == 'show') {
            // Show a category ****************.
            $cid = required_param('categoryid', PARAM_INT);
            $DB->set_field('local_shop_catalogcategory', 'visible', 1, array('id' => $cid));

        } else if ($cmd == 'hide') {
            // Hide a category ****************.
            $cid = required_param('categoryid', PARAM_INT);
            $DB->set_field('local_shop_catalogcategory', 'visible', 0, array('id' => $cid));
        }
    }
}