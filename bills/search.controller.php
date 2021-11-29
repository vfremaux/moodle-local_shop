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
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\bills;

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
            $billid = optional_param('billid', '', PARAM_INT);
            $billkey = optional_param('billkey', '', PARAM_TEXT);
            $customername = optional_param('customername', '', PARAM_TEXT);
            $datefromparam = optional_param('datefrom', '', PARAM_TEXT);
            $datefrom = strtotime($datefromparam);
            $during = optional_param('during', '', PARAM_INT);

            switch ($by) {
                case 'id':
                    $whereclause = " b.id = ? ";
                    $this->criteria = "Bill ID = $billid ";
                    $params[] = $billid;
                    break;
                case "name":
                    $whereclause = " UPPER(c.lastname) LIKE UPPER(?) OR UPPER(username) LIKE UPPER(?)";
                    $this->criteria = "Customer last name or associated username starts with $customername ";
                    $params[] = $customername.'%';
                    $params[] = $customername.'%';
                    break;
                case "key":
                    $whereclause = " UPPER(transactionid) LIKE UPPER(?) ";
                    $this->criteria = "Transaction id name starts with $billkey ";
                    $params[] = $billkey.'%';
                    break;
                case "date":
                    $whereclause = " emissiondate > ? ";
                    $this->criteria = "emission date > $datefrom";
                    $params[] = $datefrom;

                    if (!empty($during)) {
                        $dateto = $datefrom + HOURSECS * $during;
                        $whereclause .= " AND emissiondate < ? ";
                        $this->criteria = "emission date > $datefrom and emission date < $dateto";
                        $params[] = $dateto;
                    }
                    break;
                default:
                    $error = true;
            }
            if (!$error) {
                $sql = "
                   SELECT
                      b.*,
                      c.firstname,
                      c.lastname,
                      u.username
                   FROM
                      {local_shop_bill} as b,
                      {local_shop_customer} as c
                   LEFT JOIN
                      {user} as u
                   ON
                       u.id = c.hasaccount
                   WHERE
                      b.customerid = c.id AND
                      $whereclause
                ";

                if ($bills = $DB->get_records_sql($sql, $params)) {

                    if (count($bills) == 1) {
                        $billrecord = array_pop($bills);
                        $billid = $billrecord->id;
                        // One only result. Switch directly to intranet/bills/viewBill with adequate Id.
                        $params = array('view' => 'viewBill', 'id' => $this->theshop->id, 'billid' => $billid);
                        redirect(new moodle_url('/local/shop/bills/view.php', $params));
                    }
                    return $bills;
                }
            }
        }
    }
}