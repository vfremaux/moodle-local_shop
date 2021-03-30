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

/*
 * Standard upgrade handler.
 * @param int $oldversion
 */
function xmldb_local_shop_upgrade($oldversion = 0) {
    global $DB;

    $result = true;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016022900) {

        // Define table local_shop to be created.
        $table = new xmldb_table('local_shop');

        // Adding fields to table local_shop.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('descriptionformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('currency', XMLDB_TYPE_CHAR, '3', null, null, null, null);
        $table->add_field('customerorganisationrequired', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('enduserorganisationrequired', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('endusermobilephonerequired', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('printtabbedcategories', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('defaultcustomersupportcourse', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('forcedownloadleaflet', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('allowtax', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('eula', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('eulaformat', XMLDB_TYPE_INTEGER, '4', null, null, null, '0');
        $table->add_field('catalogid', XMLDB_TYPE_INTEGER, '4', null, null, null, '0');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '4', null, null, null, '0');

        // Adding keys to table local_shop.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('ix_unique_name', XMLDB_KEY_UNIQUE, array('name'));

        // Conditionally launch create table for local_shop.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2016022900, 'local', 'shop');
    }

    if ($oldversion < 2016083100) {

        // Define table local_shop to be created.
        $table = new xmldb_table('local_shop_catalogitem');

        $field = new xmldb_field('password');
        $field->set_attributes(XMLDB_TYPE_CHAR, '8', null, null, null, null, null, null, 'handlerparams');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016083100, 'local', 'shop');
    }

    if ($oldversion < 2016090800) {

        // Define table local_shop to be created.
        $table = new xmldb_table('local_shop');

        $field = new xmldb_field('discountthreshold');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, XMLDB_NOTNULL, null, 0, 'allowtax');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discountrate');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'discountthreshold');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discountrate2');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'discountrate');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discountrate3');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'discountrate2');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016090800, 'local', 'shop');
    }

    if ($oldversion < 2016090804) {
        // New version in version.php.
        // Purge a weird record.
        $DB->delete_records_select('capabilities', " name LIKE 'local/shop:%' AND component = 'local_block'" );

        upgrade_plugin_savepoint(true, 2016090804, 'local', 'shop');
    }

    if ($oldversion < 2016091000) {
        // New version in version.php.

        // Add field to local_shop_customer.
        $table = new xmldb_table('local_shop_customer');

        $field = new xmldb_field('invoiceinfo');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'hasaccount');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field to local_shop_customer.
        $table = new xmldb_table('local_shop_bill');

        $field = new xmldb_field('invoiceinfo');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'customerid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016091000, 'local', 'shop');
    }

    if ($oldversion < 2016092100) {
        // New version in version.php.

        // Add field to local_shop.
        $table = new xmldb_table('local_shop');

        $field = new xmldb_field('discountthreshold');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, null, null, 0, 'allowtax');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discountrate');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', null, null, null, 0, 'discountthreshold');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discountrate2');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', null, null, null, 0, 'discountrate');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discountrate3');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', null, null, null, 0, 'discountrate2');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016092100, 'local', 'shop');
    }

    if ($oldversion < 2016101500) {
        // New version in version.php.

        // Add field to local_shop_product.
        $table = new xmldb_table('local_shop_product');

        $field = new xmldb_field('deleted');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 1, null, null, null, null, 'productiondata');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field to local_shop_productevent.
        $table = new xmldb_table('local_shop_productevent');

        $field = new xmldb_field('eventtype');
        $field->set_attributes(XMLDB_TYPE_CHAR, 32, null, null, null, null, 'billitemid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2016101500, 'local', 'shop');
    }

    if ($oldversion < 2017111100) {
        // New version in version.php.

        // Add field to local_shop_catalog.
        $table = new xmldb_table('local_shop_catalog');

        $field = new xmldb_field('billfooter');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'countryrestrictions');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('billfooterformat');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 4, null, null, null, 0, 'billfooter');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2017111100, 'local', 'shop');
    }

    if ($oldversion < 2018011500) {
        // New version in version.php.

        // Define table local_shop to be created.
        $table = new xmldb_table('local_shop_paypal_ipn');

        // Adding fields to table local_shop.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('tnxid', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('transid', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('paypalinfo', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('result', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_shop.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('tnxid_unique_name', XMLDB_KEY_UNIQUE, array('tnxid'));
        $table->add_key('transid_unique_name', XMLDB_KEY_UNIQUE, array('transid'));

        // Conditionally launch create table for local_shop.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2018011500, 'local', 'shop');
    }

    if ($oldversion < 2018031100) {
        // Add field to local_shop_productevent.
        $table = new xmldb_table('local_shop_productevent');

        $field = new xmldb_field('eventdata');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'eventtype');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2018031100, 'local', 'shop');
    }

    if ($oldversion < 2018033000) {
        // Add extradata local_shop_product.
        $table = new xmldb_table('local_shop_product');

        $field = new xmldb_field('extradata');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'small', null, null, null, null, 'productiondata');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2018033000, 'local', 'shop');
    }

    if ($oldversion < 2019050301) {
        // Add extradata local_shop_product.
        $table = new xmldb_table('local_shop_bill');

        $field = new xmldb_field('test');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, 0, 'productionfeedback');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('partnerid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, 0, 'test');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('partnertag');
        $field->set_attributes(XMLDB_TYPE_CHAR, '16', null, null, null, null, 'partnerid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table local_shop_partner to be created.
        $table = new xmldb_table('local_shop_partner');

        // Adding fields to table local_shop_partner.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('shopid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('partnerkey', XMLDB_TYPE_CHAR, '16', null, XMLDB_NOTNULL, null, null);
        $table->add_field('referer', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);

        // Adding keys to table local_shop.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('tnxid_unique_partnerkey', XMLDB_KEY_UNIQUE, array('partnerkey'));

        // Conditionally launch create table for local_shop.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2019050301, 'local', 'shop');
    }

    if ($oldversion < 2020072900) {
        // New version in version.php.

        // Define table local_shop to be created.
        $table = new xmldb_table('local_shop_discount');

        // Adding fields to table local_shop_discount.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('shopid', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('argument', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('argumentformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '32', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ruledata', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('ratio', XMLDB_TYPE_NUMBER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('applyon', XMLDB_TYPE_CHAR, '8', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('applydata', XMLDB_TYPE_TEXT, 'small', null, null, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ordering', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('operator', XMLDB_TYPE_CHAR, '16', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '11', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_shop_discount.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_index('fk_local_shop_id_uniq_name', XMLDB_KEY_UNIQUE, array('shopid', 'name'));

        // Conditionally launch create table for local_shop.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2020072900, 'local', 'shop');
    }

    if ($oldversion < 2020102200) {
        // Add discount fields in local_shop_bill.
        $table = new xmldb_table('local_shop_bill');

        $field = new xmldb_field('discount');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, null, null, null, 'paiedamount');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('untaxeddiscount');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, null, null, null, 'discount');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('discounttaxes');
        $field->set_attributes(XMLDB_TYPE_NUMBER, '10', null, null, null, null, 'untaxeddiscount');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2020102200, 'local', 'shop');
    }

    if ($oldversion < 2020111500) {
        $table = new xmldb_table('local_shop_catalogitem');

        $field = new xmldb_field('idnumber');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, 'name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('local_shop_partner');

        $field = new xmldb_field('referer');
        $field->set_attributes(XMLDB_TYPE_CHAR, '255', null, null, null, null, 'partnerkey');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('partnersecretkey');
        $field->set_attributes(XMLDB_TYPE_CHAR, '32', null, null, null, null, 'referer');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // New version in version.php.
        upgrade_plugin_savepoint(true, 2020111500, 'local', 'shop');
    }

    return $result;
}