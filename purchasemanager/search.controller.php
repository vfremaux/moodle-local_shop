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
 * controller for searching in products
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\productinstances;

defined('MOODLE_INTERNAL') || die;

use \moodle_url;

class search_controller {

    protected $theshop;

    public $criteria;

    public function __construct($theshop) {
        $this->theshop = $theshop;
    }

    public function process($cmd) {
        global $DB;

        if ($cmd == 'search') {
            $error = false;
            $by = optional_param('by', '', PARAM_TEXT);
            $unitid = optional_param('unitid', '', PARAM_INT);
            $reference = optional_param('reference', '', PARAM_TEXT);
            $customername = optional_param('customername', '', PARAM_TEXT);
            $datefromparam = optional_param('datefrom', '', PARAM_TEXT);
            $datefrom = strtotime($datefromparam);

            switch ($by) {
                case 'id':
                    $whereclause = " p.id = ? ";
                    $this->criteria = "Product ID = $unitid ";
                    $params[] = $unitid;
                    break;
                case "name":
                    $whereclause = " UPPER(c.lastname) LIKE UPPER(?) OR UPPER(username) LIKE UPPER(?)";
                    $this->criteria = "Customer last name or associated username starts with $customername ";
                    $params[] = $customername.'%';
                    $params[] = $customername.'%';
                    break;
                case "reference":
                    $whereclause = " UPPER(reference) LIKE UPPER(?) ";
                    $this->criteria = "Product unit reference contains $reference ";
                    $params[] = '%'.$reference.'%';
                    break;
                case "date":
                    $whereclause = " startdate >= ? ";
                    $this->criteria = "Start date > $datefrom";
                    $params[] = $datefrom;
                    break;
                default:
                    $error = true;
            }
            if (!$error) {
                $sql = "
                   SELECT
                      p.*
                   FROM
                      {local_shop_product} as p
                   WHERE
                      $whereclause
                ";

                if ($units = $DB->get_records_sql($sql, $params)) {
                    return $units;
                }

                return [];
            }
        }
    }
}