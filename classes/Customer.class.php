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

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

class Customer extends ShopObject {

    static $table = 'local_shop_customer';

    function __construct($idorrecord, $light = false) {
        global $DB, $CFG;

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) return; // this builds a lightweight proxy of the customer, without moodle user data

            $this->bills = Bill::get_instances(array('customerid' => $this->id), 'status ASC');
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->firstname = '';
            $this->record->lastname = '';
            $this->record->address = '';
            $this->record->zip = '';
            $this->record->email = '';
            $this->record->city = ''.@$CFG->city;
            $this->record->country = ''.@$CFG->country;
            $this->record->organisation = '';
            $this->record->hasaccount = 0;
            $this->record->timecreated = time();

            $this->bills = array();
        }
    }

    function fullname() {
        return $this->lastname.' '.$this->firstname;
    }

    /**
     * customers should not be deleted if having bills attached. Only 
     * manually created customers might be deleted.
     */
    function delete() {
        $instances = Bill::get_intances(array('userid' => $this->id));
        if (empty($instances)) {
            parent::delete();
        }
    }

    static function count($filter) {
        return parent::_count(self::$table, $filter);
    }

    static function get_instances_for_admin($theshop) {
        global $DB;

        $config = get_config('local_shop');

        $params = array();
        $shopclause = '';
        $catalogclause = '';
        if (!is_null($theshop)) {
            $shopclause = ' AND b.shopid = ? ';
            $params[] = $theshop->id;

            if ($theshop->catalogid) {
                $catalogclause = ' AND sh.catalogid = ? ';
                $params[] = $theshop->catalogid;
            }
        }

        $sql = "
            SELECT 
               c.*,
               COUNT(b.id) as billCount,
               SUM(b.amount) as totalAccount
            FROM 
               {local_shop_customer} as c
            LEFT JOIN 
               {local_shop_bill} as b
            ON
               b.customerid = c.id
               $shopclause
            LEFT JOIN
               {local_shop} sh
            ON
               sh.id = b.shopid
            WHERE
               UPPER(c.email) NOT LIKE 'TEST%'
               $catalogclause
            GROUP BY 
               c.id
        ";

        $order = optional_param('order', '', PARAM_ALPHA);
        $dir = optional_param('dir', '', PARAM_ALPHA);
        $offset = optional_param('offest', '', PARAM_ALPHA);
        $customers = $DB->get_records_sql($sql, $params, $offset, $config->maxitemsperpage);
        $customersarr = array();
        foreach($customers as $c) {
            $customersarr[$c->id] = new Customer($c);
        }

        return $customersarr;
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}