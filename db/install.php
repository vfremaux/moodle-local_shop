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
 * Plugin post install sequence
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post install function.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(ExcessiveMethodLength)
 */
function xmldb_local_shop_install() {
    global $DB, $CFG;

    $flowcodes = [
        [
            'element' => 'order',
            'fromstate' => 'WORKING',
            'tostate' => 'RECEIVED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'RECEIVED',
            'tostate' => 'EXAMINED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'EXAMINED',
            'tostate' => 'QUANTIFIED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'RECEIVED',
            'tostate' => 'QUANTIFIED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'QUANTIFIED',
            'tostate' => 'ANSWERED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'ANSWERED',
            'tostate' => 'EXECUTING',
        ],
        [
            'element' => 'order',
            'fromstate' => 'EXECUTING',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'ANSWERED',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'order',
            'fromstate' => 'ANSWERED',
            'tostate' => 'WORKING',
        ],
        [
            'element' => 'order',
            'fromstate' => 'RECEIVED',
            'tostate' => 'CANCELLED',
        ],

        // Bills.

        [
            'element' => 'bill',
            'fromstate' => 'PLACED',
            'tostate' => 'PENDING',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PLACED',
            'tostate' => 'PREPROD',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PLACED',
            'tostate' => 'SOLDOUT',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PLACED',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PAYBACK',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'WORKING',
            'tostate' => 'PLACED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'WORKING',
            'tostate' => 'PENDING',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'WORKING',
            'tostate' => 'PARTIAL',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'WORKING',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PENDING',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PENDING',
            'tostate' => 'REFUSED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PENDING',
            'tostate' => 'SOLDOUT',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PREPROD',
            'tostate' => 'COMPLETE',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PREPROD',
            'tostate' => 'PARTIAL',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PARTIAL',
            'tostate' => 'COMPLETE',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'PARTIAL',
            'tostate' => 'PAYBACK',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'SOLDOUT',
            'tostate' => 'COMPLETE',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'SOLDOUT',
            'tostate' => 'PAYBACK',
        ],

        // Post online payment failure resolution.
        [
            'element' => 'bill',
            'fromstate' => 'REFUSED',
            'tostate' => 'PENDING',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'REFUSED',
            'tostate' => 'SOLDOUT',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'FAILED',
            'tostate' => 'CANCELLED',
        ],
        [
            'element' => 'bill',
            'fromstate' => 'FAILED',
            'tostate' => 'SOLDOUT',
        ],
    ];

    foreach ($flowcodes as $fc) {
        $DB->insert_record('local_flowcontrol', (object)$fc);
    }

    // Create the teacherowner role if absent.
    if (!$DB->record_exists('role', ['shortname' => 'courseowner'])) {
        $rolestr = get_string('courseowner', 'local_shop');
        $roledesc = get_string('courseowner_desc', 'local_shop');
        $courseownerid = create_role($rolestr, 'courseowner', str_replace("'", "\\'", $roledesc), 'editingteacher');
        set_role_contextlevels($courseownerid, [CONTEXT_COURSECAT, CONTEXT_COURSE]);
        $editingteacher   = $DB->get_record('role', ['shortname' => 'editingteacher']);
        role_cap_duplicate($editingteacher, $courseownerid);
    }

    // Create the categoryowner role if absent.
    if (!$DB->record_exists('role', ['shortname' => 'categoryowner'])) {
        $rolestr = get_string('categoryowner', 'local_shop');
        $roledesc = get_string('categoryowner_desc', 'local_shop');
        $categoryownerid = create_role($rolestr, 'categoryowner', str_replace("'", "\\'", $roledesc), 'coursecreator');
        set_role_contextlevels($categoryownerid, [CONTEXT_COURSECAT]);
        $coursecreator   = $DB->get_record('role', ['shortname' => 'coursecreator']);
        role_cap_duplicate($coursecreator, $categoryownerid);
    }

    // Create the sales manager role if absent.
    if (!$DB->record_exists('role', ['shortname' => 'sales'])) {
        $rolestr = get_string('salesrolename', 'local_shop');
        $roledesc = get_string('salesrole_desc', 'local_shop');
        $salesroleid = create_role($rolestr, 'sales', str_replace("'", "\\'", $roledesc), '');
        set_role_contextlevels($salesroleid, [CONTEXT_BLOCK, CONTEXT_SYSTEM]);
    }

    // Create the sales manager role if absent.
    if (!$DB->record_exists('role', ['shortname' => 'customer'])) {
        $rolestr = get_string('customerrolename', 'local_shop');
        $roledesc = get_string('customerrole_desc', 'local_shop');
        $customerroleid = create_role($rolestr, 'customer', str_replace("'", "\\'", $roledesc), '');
        set_role_contextlevels($customerroleid, [CONTEXT_COURSE, CONTEXT_SYSTEM]);
    }

    // Create first catalog for default shop.
    $catalog = new StdClass;
    $catalog->name = get_string('defaultcatalogname', 'local_shop');
    $catalog->description = get_string('defaultcatalogdescription', 'local_shop');
    $catalog->descriptionformat = FORMAT_HTML;
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
                $params = ['element' => $fc->element, 'fromstate' => $fc->fromstate, 'tostate' => $fc->tostate];
                if (!$DB->record_exists('local_flowcontrol', $params)) {
                    $DB->insert_record('local_flowcontrol', $fc);
                }
            }
        }
        $dbman->drop_table($table);
    }

    // Register zabbix indicators if installed.
    if (is_dir($CFG->dirroot.'/report/zabbix')) {
        include_once($CFG->dirroot.'/report/zabbix/xlib.php');
        report_zabbix_register_plugin('local', 'shop');
    }

    return true;
}
