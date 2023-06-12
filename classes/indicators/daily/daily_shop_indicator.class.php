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
 * @author Valery Fremaux valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_zabbix
 * @category report
 */
namespace report_zabbix\indicators;

use moodle_exception;
use coding_exception;
use StdClass;

require_once($CFG->dirroot.'/report/zabbix/classes/indicator.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

class daily_shop_indicator extends zabbix_indicator {

    static $submodes = 'dailybills,dailyplaced,dailysoldout,dailycomplete,dailycancelled,dailypending,bills,placed,soldout,complete,cancelled,pending,categories,activeproducts,dailyamount';

    public function __construct() {
        parent::__construct();
        $this->key = 'moodle.shop';
    }

    /**
     * Return all available submodes
     * return array of strings
     */
    public function get_submodes() {
        return explode(',', self::$submodes);
    }

    /**
     * the function that contains the logic to acquire the indicator instant value.
     * @param string $submode to target an aquisition to an explicit submode, elsewhere 
     */
    public function acquire_submode($submode) {
        global $DB;

        if(!isset($this->value)) {
            $this->value = new Stdclass;
        }

        if (is_null($submode)) {
            $submode = $this->submode;
        }

        $now = time();
        $horizon = $now - DAYSECS;

        switch ($submode) {

            case 'dailybills': {
                $this->value->$submode = $DB->count_records_select('local_shop_bill', ' emissiondate > ? ', [$horizon]);
                break;
            }
            case 'dailyplaced': {
                $select = ' emissiondate > ? and status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [$horizon, SHOP_BILL_PLACED]);
                break;
            }

            case 'dailysoldout': {
                $select = ' emissiondate > ? and status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [$horizon, SHOP_BILL_SOLDOUT]);
                break;
            }

            case 'dailycomplete': {
                $select = ' emissiondate > ? and status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [$horizon, SHOP_BILL_COMPLETE]);
                break;
            }

            case 'dailypending': {
                $select = ' emissiondate > ? and status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [$horizon, SHOP_BILL_PENDING]);
                break;
            }

            case 'dailycancelled': {
                $select = ' emissiondate > ? and status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [$horizon, SHOP_BILL_CANCELLED]);
                break;
            }

            case 'bills': {
                // Counts all bills in all shops.
                $this->value->$submode = $DB->count_records('local_shop_bill', []);
                break;
            }

            case 'placed': {
                $select = ' status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [SHOP_BILL_PLACED]);
                break;
            }

            case 'soldout': {
                $select = ' status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [SHOP_BILL_SOLDOUT]);
                break;
            }

            case 'complete': {
                $select = ' status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [SHOP_BILL_COMPLETE]);
                break;
            }

            case 'cancelled': {
                $select = ' status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [SHOP_BILL_CANCELLED]);
                break;
            }

            case 'pending': {
                $select = ' status = ? ';
                $this->value->$submode = $DB->count_records_select('local_shop_bill', $select, [SHOP_BILL_PENDING]);
                break;
            }

            case 'categories': {
                // Will report only exposed categories having available products, or hidden with 
                // at least one internally available product.
                $sql = "
                    SELECT
                        COUNT(DISTINCT cc.id) as catcount
                    FROM
                        {local_shop_catalogcategory} cc,
                        {local_shop_catalogitem} ci
                    WHERE
                        ci.categoryid = cc.id AND
                        ((cc.visible = 1 AND ci.status = 'AVAILABLE') OR
                        (ci.status = 'AVAILABLEINTERNAL'))
                ";
                $catcount = $DB->count_records_sql($sql, []);
                $this->value->$submode = 0 + $catcount;
                break;
            }

            case 'activeproducts': {
                // Product counting : pure item, element of sets, or bundle
                list($insql, $inparams) = $DB->get_in_or_equal(['AVAILABLE', 'AVAILABLEINTERNAL']);

                $sql = "
                    SELECT
                        COUNT(DISTINCT ci.id) as pcount
                    FROM
                        {local_shop_catalogitem} ci,
                        {local_shop_catalogcategory} cc
                    WHERE
                        ci.categoryid = cc.id AND
                        ((cc.visible = 1 AND ci.status = 'AVAILABLE') OR
                        (ci.status = 'AVAILABLEINTERNAL')) AND
                        setid = 0
                ";
                $pcount = $DB->count_records_sql($sql, []);
                $this->value->$submode = 0 + $pcount;
                break;
            }

            case 'dailyamount': {
                list($insql, $inparams) = $DB->get_in_or_equal([SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE]);
                $sql = "
                    SELECT
                        sum(amount) as dailyamount
                    FROM
                        {local_shop_bill}
                    WHERE
                        status $insql AND
                        emissiondate >= ?
                ";
                $inparams[] = $horizon;
                $dailyamount = $DB->get_record_sql($sql, $inparams);
                $this->value->$submode = 0 + $dailyamount->dailyamount;
                break;
            }

            default: {
                if ($CFG->debug == DEBUG_DEVELOPER) {
                    throw new coding_exception("Indicator has a submode that is not handled in aquire_submode().");
                }
            }
        }
    }
}