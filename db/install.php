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

function xmldb_local_shop_install() {
    global $DB;

    $flowcodes = array(
        array('element' => 'order',
              'fromstate' => 'WORKING',
              'tostate' => 'RECEIVED'),
        array('element' => 'order',
              'fromstate' => 'RECEIVED',
              'tostate' => 'EXAMINED'),
        array('element' => 'order',
              'fromstate' => 'EXAMINED',
              'tostate' => 'QUANTIFIED'),
        array('element' => 'order',
              'fromstate' => 'RECEIVED',
              'tostate' => 'QUANTIFIED'),
        array('element' => 'order',
              'fromstate' => 'QUANTIFIED',
              'tostate' => 'ANSWERED'),
        array('element' => 'order',
              'fromstate' => 'ANSWERED',
              'tostate' => 'EXECUTING'),
        array('element' => 'order',
              'fromstate' => 'EXECUTING',
              'tostate' => 'CANCELLED'),
        array('element' => 'order',
              'fromstate' => 'ANSWERED',
              'tostate' => 'CANCELLED'),
        array('element' => 'order',
              'fromstate' => 'ANSWERED',
              'tostate' => 'WORKING'),
        array('element' => 'order',
              'fromstate' => 'RECEIVED',
              'tostate' => 'CANCELLED'),

        // Bills.

        array('element' => 'bill',
              'fromstate' => 'PLACED',
              'tostate' => 'PENDING'),
        array('element' => 'bill',
              'fromstate' => 'PLACED',
              'tostate' => 'PREPROD'),
        array('element' => 'bill',
              'fromstate' => 'PLACED',
              'tostate' => 'SOLDOUT'),

        array('element' => 'bill',
              'fromstate' => 'PAYBACK',
              'tostate' => 'CANCELLED'),

        array('element' => 'bill',
              'fromstate' => 'WORKING',
              'tostate' => 'PLACED'),

        array('element' => 'bill',
              'fromstate' => 'WORKING',
              'tostate' => 'PENDING'),
        array('element' => 'bill',
              'fromstate' => 'WORKING',
              'tostate' => 'PARTIAL'),
        array('element' => 'bill',
              'fromstate' => 'WORKING',
              'tostate' => 'CANCELLED'),

        array('element' => 'bill',
              'fromstate' => 'PENDING',
              'tostate' => 'CANCELLED'),
        array('element' => 'bill',
              'fromstate' => 'PENDING',
              'tostate' => 'REFUSED'),
        array('element' => 'bill',
              'fromstate' => 'PENDING',
              'tostate' => 'SOLDOUT'),

        array('element' => 'bill',
              'fromstate' => 'PREPROD',
              'tostate' => 'COMPLETE'),
        array('element' => 'bill',
              'fromstate' => 'PREPROD',
              'tostate' => 'PARTIAL'),

        array('element' => 'bill',
              'fromstate' => 'PARTIAL',
              'tostate' => 'COMPLETE'),
        array('element' => 'bill',
              'fromstate' => 'PARTIAL',
              'tostate' => 'PAYBACK'),

        array('element' => 'bill',
              'fromstate' => 'SOLDOUT',
              'tostate' => 'COMPLETE'),
        array('element' => 'bill',
              'fromstate' => 'SOLDOUT',
              'tostate' => 'PAYBACK'),

        // Post online payment failure resolution.
        array('element' => 'bill',
              'fromstate' => 'REFUSED',
              'tostate' => 'PENDING'),
        array('element' => 'bill',
              'fromstate' => 'REFUSED',
              'tostate' => 'SOLDOUT'),
    );

    foreach ($flowcodes as $fc) {
        $DB->insert_record('local_flowcontrol', (object)$fc);
    }

    // Create the teacherowner role if absent.
    if (!$DB->record_exists('role', array('shortname' => 'courseowner'))) {
        $courseownerid = create_role(get_string('courseowner', 'local_shop'), 'courseowner', str_replace("'", "\\'", get_string('courseownerdesc', 'local_shop')), 'editingteacher');
        set_role_contextlevels($courseownerid, array(CONTEXT_COURSECAT, CONTEXT_COURSE));
        $editingteacher   = $DB->get_record('role', array('shortname' => 'editingteacher'));
        role_cap_duplicate($editingteacher, $courseownerid);
    }

    // Create the categoryowner role if absent.
    if (!$DB->record_exists('role', array('shortname' => 'categoryowner'))) {
        $categoryownerid = create_role(get_string('categoryowner', 'local_shop'), 'categoryowner', str_replace("'", "\\'", get_string('categoryownerdesc', 'local_shop')), 'coursecreator');
        set_role_contextlevels($categoryownerid, array(CONTEXT_COURSECAT));
        $coursecreator   = $DB->get_record('role', array('shortname' => 'coursecreator'));
        role_cap_duplicate($coursecreator, $categoryownerid);
    }

    // Create the sales manager role if absent.
    if (!$DB->record_exists('role', array('shortname' => 'sales'))) {
        $salesroleid = create_role(get_string('salesrolename', 'local_shop'), 'sales', str_replace("'", "\\'", get_string('salesroledesc', 'local_shop')), '');
        set_role_contextlevels($salesroleid, array(CONTEXT_BLOCK,CONTEXT_SYSTEM));
    }

    // Create first catalog for default shop.
    $catalog = new StdClass;
    $catalog->name = get_string('defaultcatalogname', 'local_shop');
    $catalog->description = get_string('defaultcatalogdescription', 'local_shop');
    $catalog->descriptionformat = FORMAT_HTML;
    $catalog->salesconditions = '';
    $catalog->salesconditionsformat = FORMAT_HTML;
    $catalog->groupid = 0;
    $catalog->countryrestrictions = '';
    $catalog->id = $DB->insert_record('local_shop_catalog', $catalog);

    // Create first default shop of id 1 to rattach all bloc data.
    $shop = new StdClass();
    $shop->name = "(Default)";
    $shop->description = '';
    $shop->descriptionformat = FORMAT_HTML;
    $shop->currency = 'EUR';
    $shop->customerorganisationrequired = 1;
    $shop->enduserorganisationrequired = 1;
    $shop->endusermobilephonerequired = 1;
    $shop->printtabbedcategories = 1;
    $shop->defaultcustomersupportcourse = 0;
    $shop->forcedownloadleaflet = 1;
    $shop->allowtax = 1;
    $shop->eula = '';
    $shop->eulaformat = FORMAT_HTML;
    $shop->catalogid = $catalog->id;
    $shop->paymodes = '';
    $shop->defaultpaymode = '';
    $shop->sortorder = 1;
    $shop->id = $DB->insert_record('local_shop', $shop);

    // Copy old data model from block model.
    $dbman = $DB->get_manager();
    $table = new xmldb_table('block_shop_catalog');
    if ($dbman->table_exists($table)) {

        if ($catalogs = $DB->get_records('block_shop_catalog')) {
            foreach ($catalogs as $c) {
                $DB->insert_record('local_shop_catalog', $c);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_bill');
        if ($bills = $DB->get_records('block_shop_bill')) {
            foreach ($bills as $b) {
                $b->shopid = $shop->id;
                $DB->insert_record('local_shop_bill', $b);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_billitem');
        if ($billitems = $DB->get_records('block_shop_billitem')) {
            foreach ($billitems as $bi) {
                $DB->insert_record('local_shop_billitem', $bi);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_catalogcategory');
        if ($catalogcategories = $DB->get_records('block_shop_catalogcategory')) {
            foreach ($catalogcategories as $cc) {
                $DB->insert_record('local_shop_catalogcategory', $cc);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_catalogitem');
        if ($catalogitems = $DB->get_records('block_shop_catalogitem')) {
            foreach ($catalogitems as $ci) {
                $DB->insert_record('local_shop_catalogitem', $ci);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_catalogshipping');
        if ($catalogshippings = $DB->get_records('block_shop_catalogshipping')) {
            foreach ($catalogshippings as $sh) {
                $DB->insert_record('local_shop_catalogshipping', $sh);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_catalogshipzone');
        if ($shippingzones = $DB->get_records('block_shop_catalogshipzone')) {
            foreach ($shippingzones as $sz) {
                $DB->insert_record('local_shop_catalogshipzone', $sz);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_customer');
        if ($customers = $DB->get_records('block_shop_customer')) {
            foreach ($customers as $cu) {
                $DB->insert_record('local_shop_customer', $cu);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_customer_owner');
        if ($dbman->table_exists($table)) {
            // Some older version may not have this table.
            if ($owners = $DB->get_records('block_shop_customer_owner')) {
                foreach ($owners as $ow) {
                    $DB->insert_record('local_shop_customer_owner', $ow);
                }
            }
            $dbman->drop_table($table);
        }

        $table = new xmldb_table('block_shop_product');
        if ($products = $DB->get_records('block_shop_product')) {
            foreach ($products as $pr) {
                $DB->insert_record('local_shop_product', $pr);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_productevent');
        if ($productevents = $DB->get_records('block_shop_productevent')) {
            foreach ($productevents as $ev) {
                $DB->insert_record('local_shop_productevent', $ev);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('block_shop_tax');
        if ($taxes = $DB->get_records('block_shop_tax')) {
            foreach ($taxes as $t) {
                $DB->insert_record('local_shop_tax', $t);
            }
        }
        $dbman->drop_table($table);

        $table = new xmldb_table('flowcontrol');
        if ($fcs = $DB->get_records('flowcontrol')) {
            foreach ($fcs as $fc) {
                if (!$DB->record_exists('local_flowcontrol', array('element' => $fc->element, 'fromstate' => $fc->fromstate, 'tostate' => $fc->tostate))) {
                    $DB->insert_record('local_flowcontrol', $fc);
                }
            }
        }
        $dbman->drop_table($table);
    }

    return true;
}