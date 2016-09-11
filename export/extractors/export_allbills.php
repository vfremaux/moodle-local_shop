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

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class shop_export_source_allbills {

    function get_data_description(&$params) {
        global $DB;

        $catalogue = $DB->get_record('local_shop_catalog', array('id' => $params->config->catalogue));
        $desc['filename'] = get_string('allbillsfile', 'local_shop', $catalogue->name);
        $desc['title'] = get_string('allbills', 'local_shop');
        $desc['colheadingformat'] = 'bold';
        $desc['columns'] = array(
            array('name' => 'transactionid',
                  'width' => 40,
                  'format' => 'smalltext'),
            array('name' => 'onlinetransactionid',
                  'width' => 40,
                  'format' => 'smalltext'),
            array('name' => 'idnumber',
                  'width' => 10,
                  'format' => 'smalltext'),
            array('name' => 'title',
                  'width' => 40,
                  'format' => 'smalltext'),
            array('name' => 'worktype',
                  'width' => 15,
                  'format' => 'smalltext'),
            array('name' => 'status',
                  'width' => 10,
                  'format' => 'smalltext'),
            array('name' => 'emissiondate',
                  'width' => 15,
                  'format' => 'date'),
            array('name' => 'lastactiondate',
                  'width' => 15,
                  'format' => 'date'),
            array('name' => 'untaxedamount',
                  'width' => 10,
                  'format' => 'float'),
            array('name' => 'taxes',
                  'width' => 10,
                  'format' => 'float'),
            array('name' => 'amount',
                  'width' => 10,
                  'format' => 'float'),
            array('name' => 'items',
                  'width' => 60,
                  'format' => 'smalltext'),
            array('name' => 'firstname',
                  'width' => 20,
                  'format' => 'smalltext'),
            array('name' => 'lastname',
                  'width' => 20,
                  'format' => 'smalltext'),
            array('name' => 'address',
                  'width' => 40,
                  'format' => 'smalltext'),
            array('name' => 'city',
                  'width' => 15,
                  'format' => 'smalltext'),
            array('name' => 'zip',
                  'width' => 8,
                  'format' => 'smalltext'),
            array('name' => 'country',
                  'width' => 8,
                  'format' => 'smalltext'),
            array('name' => 'email',
                  'width' => 20,
                  'format' => 'smalltext'),
            array('name' => 'hasaccount',
                  'width' => 0, /* ignore */
                  'format' => 'smalltext'),
            array('name' => 'username',
                  'width' => 20,
                  'format' => 'smalltext'),
        );
        return array($desc);
    }

    /**
    *
    */
    function get_data(&$params) {
        global $DB, $CFG;

        $sql = "
            SELECT
                b.transactionid,
                b.onlinetransactionid,
                b.idnumber,
                b.title,
                b.worktype,
                b.status,
                b.emissiondate,
                b.lastactiondate,
                b.untaxedamount,
                b.taxes,
                b.amount,
                GROUP_CONCAT(bi.itemcode ORDER BY bi.ordering SEPARATOR ',') as items,
                c.firstname,
                c.lastname,
                c.address,
                c.city,
                c.zip,
                c.country,
                c.email,
                c.hasaccount,
                u.username
            FROM
                {local_shop_bill} b,
                {local_shop_billitem} bi,
                {local_shop_catalogitem} ci,
                {local_shop_customer} c
            LEFT JOIN
                {user} u
            ON
                c.hasaccount = u.id
            WHERE
                bi.billid = b.id AND
                b.customerid = c.id AND
                ci.code = bi.itemcode AND
                ci.catalogid = {$params->config->catalogue}
            GROUP BY
                b.id
            ORDER BY
                b.ordering
        ";
        $data = $DB->get_records_sql($sql);

        return array($data);
    }
}