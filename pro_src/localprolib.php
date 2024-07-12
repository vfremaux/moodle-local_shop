<?php
// This file is NOT part of Moodle - http://moodle.org/
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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

final class local_pro_manager {

    private static $component = 'local_shop';
    private static $componentpath = 'local/shop';

    /**
     * this adds additional settings to the component settings (generic part of the prolib system).
     * @param objectref &$admin
     * @param objectref &$settings
     */
    public static function add_settings(&$admin, &$settings) {
        global $CFG, $PAGE;

        if (local_shop_supports_feature('catalog/instances')) {
            $key = self::$component.'/usedelegation';
            $label = get_string('usedelegation', self::$component);
            $desc = get_string('configusedelegation', self::$component);
            $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, ''));

            $key = self::$component.'/useslavecatalogs';
            $label = get_string('useslavecatalogs', self::$component);
            $desc = get_string('configuseslavecatalogs', self::$component);
            $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, ''));

            $key = self::$component.'/userenewableproducts';
            $label = get_string('userenewableproducts', self::$component);
            $desc = get_string('configuserenewableproducts', self::$component);
            $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, ''));

            $key = self::$component.'/serviceproxykey';
            $label = get_string('serviceproxykey', self::$component);
            $desc = get_string('configserviceproxykey', self::$component);
            $settings->add(new \admin_setting_configtext($key, $label, $desc, ''));

            $key = self::$component.'/usesmarturls';
            $label = get_string('usesmarturls', self::$component);
            $desc = get_string('configusesmarturls', self::$component);
            $settings->add(new \admin_setting_configcheckbox($key, $label, $desc, ''));
        }
    }

    public static function add_bill_export_columns(&$columns) {

        // Start unshifting 'id', as 'id' needs be the first columns in query.
        $iddesc = array_shift($columns);

        // Push additional columns (reverse order).
        array_unshift($columns, array('name' => 'partnertag',
              'width' => 30,
              'format' => 'smalltext'));
        array_unshift($columns, array('name' => 'partnerkey',
              'width' => 15,
              'format' => 'smalltext'));

        // Push id descriptor back at start.
        array_unshift($columns, $iddesc);
    }

    public static function get_bill_export_query($yearclause, $monthclause, $statusclause, $shopclause) {

        $sql = "
            SELECT
                b.id,
                p.partnerkey,
                b.partnertag,
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
                GROUP_CONCAT(bi.abstract ORDER BY bi.ordering SEPARATOR ',') as itemnames,
                c.firstname,
                c.lastname,
                c.address,
                c.city,
                c.zip,
                c.country,
                c.email,
                c.hasaccount,
                u.username,
                u.institution,
                u.department
            FROM
                {local_shop_customer} c,
                {local_shop_billitem} bi,
                {user} u,
                {local_shop_bill} b
            LEFT JOIN
                {local_shop_partner} p
            ON
                b.partnerid = p.id
            WHERE
                bi.billid = b.id
                AND c.hasaccount = u.id
                AND b.customerid = c.id
                {$yearclause}
                {$monthclause}
                {$statusclause}
                {$shopclause}
            GROUP BY
                b.id
            ORDER BY
                b.ordering
         ";

        return $sql;
    }
}