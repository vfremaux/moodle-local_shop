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
 * Class representing a Customer
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

use moodle_url;

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

/**
 * A customer has customer info and MAYor MAY not be linked to a moodle user.
 * Generally, a customer having finalized a purchase will have an associated moodle account.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class Customer extends ShopObject {

    /**
     * DB table (for ShopObject)
     */
    public static $table = 'local_shop_customer';

    /**
     * Viewable url for this customer
     */
    public $url;

    /**
     * Constructor
     * @param mixed $idorrecord
     * @param bool $light
     */
    public function __construct($idorrecord, $light = false) {
        global $CFG;

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {

            $this->url = new moodle_url('local/shop/customer/view?php', ['view' => 'viewCustomer', 'id' => $this->record->id]);

            if ($light) {
                // This builds a lightweight proxy of the Bill, without items.
                return;
            }

            $this->bills = Bill::get_instances(['customerid' => $this->id], 'status ASC');
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->firstname = '';
            $this->record->lastname = '';
            $this->record->address = '';
            $this->record->zip = '';
            $this->record->email = '';
            $this->record->city = ''.$CFG->city ?? '';
            $this->record->country = ''.$CFG->country ?? '';
            $this->record->organisation = '';
            $this->record->hasaccount = 0;
            $this->record->timecreated = time();

            $this->bills = [];
        }
    }

    /**
     * Builds a customer object for the given moodle account.
     * @param int $userid
     */
    public static function instance_by_user($userid) {
        global $DB;

        $params = ['hasaccount' => $userid];
        if ($customerrec = $DB->get_record('local_shop_customer', $params)) {
            return new Customer($customerrec);
        }
        return null;
    }

    /**
     * Get customer's full printable name
     */
    public function fullname() {
        return $this->lastname.' '.$this->firstname;
    }

    /**
     * Customers should not be deleted if having bills attached. Only
     * manually created customers might be deleted.
     */
    public function delete(): void {
        $instances = Bill::get_instances(['userid' => $this->id]);
        if (empty($instances)) {
            parent::delete();
        }
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     */
    public static function count($filter, $order = 'lastname') {
        return parent::_count(self::$table, $filter, $order);
    }

    /**
     * Get instances for backoffice administration.
     * @param object $theshop
     */
    public static function get_instances_for_admin($theshop) {
        global $DB;

        $config = get_config('local_shop');

        $params = [];
        $shopclause = '';
        $catalogclause = '';
        if (!is_null($theshop)) {
            $shopclause = ' AND b.shopid = ? ';
            $params[] = $theshop->id;

            if ($theshop->catalogid) {
                $catalogclause = ' AND (sh.catalogid = ? OR sh.catalogid IS NULL) ';
                $params[] = $theshop->catalogid;
            }
        }

        $order = optional_param('order', 'c.id', PARAM_ALPHA);
        $dir = optional_param('dir', '', PARAM_ALPHA);

        $sql = "
            SELECT
               c.*,
               SUM( CASE WHEN (b.status = 'PLACED') THEN 1 ELSE 0 END) as placedcount,
               SUM( CASE WHEN (b.status = 'PENDING') THEN 1 ELSE 0 END) as pendingscount,
               SUM( CASE WHEN (b.status = 'SOLDOUT' OR b.status = 'COMPLETE') THEN 1 ELSE 0 END) as billcount,
               SUM(b.amount) as totalaccount
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
            ORDER BY
               $order $dir
        ";

        $offset = optional_param('offset', 0, PARAM_INT);
        $customers = $DB->get_records_sql($sql, $params, $offset, $config->maxitemsperpage);
        $customersarr = [];
        foreach ($customers as $c) {
            $customersarr[$c->id] = new Customer($c);
        }

        return $customersarr;
    }

    /**
     * Get instances
     * @param array $filter
     * @param string $order
     * @param string $dir 'ASC' por 'DESC'
     * @param int $limitfrom
     * @param int $limitnum
     */
    public static function get_instances_by_shop($filter, $order = 'c.lastname, c.firstname', $dir = "ASC", $limitfrom = 0, $limitnum = '') {
        global $DB;

        $params = [];
        $shopclause = '';
        $catalogclause = '';
        $filterclause = '';
        $filterclauses = [];

        $theshop = null;
        foreach ($filter as $n => $v) {
            if ($n == 'shopid') {
                if (!empty($filter['shopid'])) {
                    $theshop = new Shop($filter['shopid']);
                }
            } else {
                if ($v != '*' || $v == '') {
                    $filterclauses[] = " $n = ? ";
                    $params[] = $v;
                }
            }

            if (!empty($filterclauses)) {
                $filterclause = 'AND '.implode(' AND ', $filterclauses);
            }
        }

        if (!is_null($theshop)) {
            $shopclause = ' AND b.shopid = ? ';
            $params[] = $theshop->id;

            if ($theshop->catalogid) {
                $catalogclause = ' AND (sh.catalogid = ? OR sh.catalogid IS NULL) ';
                $params[] = $theshop->catalogid;
            }
        }

        if ($order == 'name') {
            $order = 'c.lastname, c.firstname';
        }

        $sql = "
            SELECT
               c.*,
               SUM( CASE WHEN (b.status = 'PLACED') THEN 1 ELSE 0 END) as placedcount,
               SUM( CASE WHEN (b.status = 'PENDING') THEN 1 ELSE 0 END) as pendingscount,
               SUM( CASE WHEN (b.status = 'SOLDOUT' OR b.status = 'COMPLETE') THEN 1 ELSE 0 END) as billcount,
               SUM(b.amount) as totalaccount
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
               $filterclause
            GROUP BY
               c.id
            ORDER BY
               $order $dir
        ";

        $customers = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        $customersarr = [];
        foreach ($customers as $c) {
            $customersarr[$c->id] = new Customer($c);
        }

        return $customersarr;
    }

    /**
     * Counts instances
     * @param array $filter
     */
    public static function count_instances_by_shop($filter) {
        global $DB;

        $params = [];
        $shopclause = '';
        $catalogclause = '';
        $filterclause = '';
        $filterclauses = [];

        $theshop = null;
        foreach ($filter as $n => $v) {
            if ($n == 'shopid') {
                if (!empty($filter['shopid'])) {
                    $theshop = new Shop($filter['shopid']);
                }
            } else {
                if ($v != '*' || $v == '') {
                    $filterclauses[] = " $n = ? ";
                    $params[] = $v;
                }
            }

            if (!empty($filterclauses)) {
                $filterclause = 'AND '.implode(' AND ', $filterclauses);
            }
        }

        if (!is_null($theshop)) {
            $shopclause = ' AND b.shopid = ? ';
            $params[] = $theshop->id;

            if ($theshop->catalogid) {
                $catalogclause = ' AND (sh.catalogid = ? OR sh.catalogid IS NULL) ';
                $params[] = $theshop->catalogid;
            }
        }

        $sql = "
            SELECT
               COUNT(DISTINCT c.id)
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
               $filterclause
        ";

        $numrecords = $DB->count_records_sql($sql, $params);

        return $numrecords;
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     * @param bool $light if true, retreives lightweight instances
     */
    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '', $light = false) {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum, $light);
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     */
    public static function count_instances($filter = []) {
        return parent::_count_instances(self::$table, $filter);
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     */
    public static function get_instances_menu($filter = [], $order = 'lastname, firstname') {
        return parent::_get_instances_menu(self::$table, $filter, $order, "CONCAT(lastname, ' ', firstname)");
    }

    /**
     * Tells if the customer has a moodle account.
     * @return bool
     */
    public static function has_account() {
        global $USER, $DB;

        return $DB->record_exists('local_shop_customer', ['hasaccount' => $USER->id]);
    }
}
