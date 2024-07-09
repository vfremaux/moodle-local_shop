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
 * Privacy Subsystem implementation for local shop.
 *
 * @package    local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\privacy;

use core_privacy\local\request\contextlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\writer;
use core_privacy\local\request\helper as request_helper;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\transform;
use context_system;

/**
 * Implementation of the privacy subsystem plugin provider for the forum activity module.
 *
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $items The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items) : collection {
        // The 'customer' table stores customer info related to some moodle users.
        $items->add_database_table('local_shop_customer', [
            'firstname' => 'privacy:metadata:shop_customer:firstname',
            'lastname' => 'privacy:metadata:shop_customer:lastname',
            'hasaccount' => 'privacy:metadata:shop_customer:hasaccount',
            'email' => 'privacy:metadata:shop_customer:email',
            'address' => 'privacy:metadata:shop_customer:address',
            'zip' => 'privacy:metadata:shop_customer:zip',
            'city' => 'privacy:metadata:shop_customer:city',
            'country' => 'privacy:metadata:shop_customer:country',
            'organisation' => 'privacy:metadata:shop_customer:organisation',
            'invoiceinfo' => 'privacy:metadata:shop_customer:invoiceinfo',
            'timecreated' => 'privacy:metadata:shop_customer:timecreated',
        ], 'privacy:metadata:shop_customer');

        // The 'local_customer_ownership' table stores the metadata about customers owned by some moodle users.
        $items->add_database_table('local_shop_customer_owner', [
            'userid' => 'privacy:metadata:customer_ownership:userid',
            'customerid' => 'privacy:metadata:customer_ownership:customerid',
        ], 'privacy:metadata:customer_ownership');

        // The 'local_shop_bills' table stores information about bills the users have.
        $items->add_database_table('local_shop_bill', [
            'shopid' => 'privacy:metadata:shop_bill:shopid',
            'userid' => 'privacy:metadata:shop_bill:userid',
            'idnumber' => 'privacy:metadata:shop_bill:idnumber',
            'ordering' => 'privacy:metadata:shop_bill:ordering',
            'customerid' => 'privacy:metadata:shop_bill:customerid',
            'invoiceinfo' => 'privacy:metadata:shop_bill:invoiceinfo',
            'title' => 'privacy:metadata:shop_bill:title',
            'worktype' => 'privacy:metadata:shop_bill:worktype',
            'status' => 'privacy:metadata:shop_bill:status',
            'remotestatus' => 'privacy:metadata:shop_bill:remotestatus',
            'emissiondate' => 'privacy:metadata:shop_bill:emissiondate',
            'lastactiondate' => 'privacy:metadata:shop_bill:lastactiondate',
            'assignedto' => 'privacy:metadata:shop_bill:assignedto',
            'timetodo' => 'privacy:metadata:shop_bill:timetodo',
            'untaxedamount' => 'privacy:metadata:shop_bill:untaxedamount',
            'taxes' => 'privacy:metadata:shop_bill:taxes',
            'amount' => 'privacy:metadata:shop_bill:amount',
            'currency' => 'privacy:metadata:shop_bill:currency',
            'convertedamount' => 'privacy:metadata:shop_bill:convertedamount',
            'transactionid' => 'privacy:metadata:shop_bill:transactionid',
            'onlinetransactionid' => 'privacy:metadata:shop_bill:onlinetransactionid',
            'expectedpaiment' => 'privacy:metadata:shop_bill:expectedpaiment',
            'paiedamount' => 'privacy:metadata:shop_bill:paiedamount',
            'paymode' => 'privacy:metadata:shop_bill:paymode',
            'ignoretax' => 'privacy:metadata:shop_bill:ignoretax',
            'productiondata' => 'privacy:metadata:shop_bill:productiondata',
            'paymentfee' => 'privacy:metadata:shop_bill:paymentfee',
            'productionfeedback' => 'privacy:metadata:shop_bill:productionfeedback',
        ], 'privacy:metadata:shop_bill');

        // The 'local_shop_bill_item' table stores the metadata about each item purchased in a bill.
        $items->add_database_table('local_shop_bill_item', [
            'billid' => 'privacy:metadata:shop_bill_item:billid',
            'ordering' => 'privacy:metadata:shop_bill_item:ordering',
            'type' => 'privacy:metadata:shop_bill_item:type',
            'itemcode' => 'privacy:metadata:shop_bill_item:itemcode',
            'catalogitem' => 'privacy:metadata:shop_bill_item:catalogitem',
            'abstract' => 'privacy:metadata:shop_bill_item:abstract',
            'description' => 'privacy:metadata:shop_bill_item:description',
            'delay' => 'privacy:metadata:shop_bill_item:delay',
            'unitcost' => 'privacy:metadata:shop_bill_item:unitcost',
            'quantity' => 'privacy:metadata:shop_bill_item:quantity',
            'totalprice' => 'privacy:metadata:shop_bill_item:totalprice',
            'taxcode' => 'privacy:metadata:shop_bill_item:taxcode',
            'bundleid' => 'privacy:metadata:shop_bill_item:bundleid',
            'customerdata' => 'privacy:metadata:shop_bill_item:customerdata',
            'productiondata' => 'privacy:metadata:shop_bill_item:productiondata',
        ], 'privacy:metadata:shop_bill_item');

        // The 'local_shop_product' table stores metadata about the product instances owned by users.
        $items->add_database_table('local_shop_product', [
            'customerid' => 'privacy:metadata:shop_product:customerid',
            'catalogitemid' => 'privacy:metadata:shop_product:catalogitemid',
            'initialbillitemid' => 'privacy:metadata:shop_product:initialbillitemid',
            'currentbillitemid' => 'privacy:metadata:shop_product:currentbillitemid',
            'contexttype' => 'privacy:metadata:shop_product:contexttype',
            'instanceid' => 'privacy:metadata:shop_product:instanceid',
            'startdate' => 'privacy:metadata:shop_product:startdate',
            'enddate' => 'privacy:metadata:shop_product:enddate',
            'reference' => 'privacy:metadata:shop_product:reference',
            'productiondata' => 'privacy:metadata:shop_product:productiondata',
            'extradata' => 'privacy:metadata:shop_product:extradata',
            'deleted' => 'privacy:metadata:shop_product:deleted',
        ], 'privacy:metadata:shop_product');

        // The 'local_shop_product_event' table stores information about product lifecycle owned by a user.
        $items->add_database_table('local_shop_product_event', [
            'productid' => 'privacy:metadata:shop_product_event:productid',
            'billitemid' => 'privacy:metadata:shop_product_event:billitemid',
            'eventtype' => 'privacy:metadata:shop_product_event:eventtype',
            'eventdata' => 'privacy:metadata:shop_product_event:eventdata',
            'datecreated' => 'privacy:metadata:shop_product_event:datecreated',
        ], 'privacy:metadata:shop_product_event');

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     * All the user data are in systemlevel context. We pass the user's context to find him in exports.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {

        $contextlist = new contextlist();
        $contextlist->add_system_context();
        $contextlist->add_user_context($userid);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        $customeraccount = $DB->get_record('local_shop_customer', ['hasaccount' => $userid]);
        self::export_customer_account($user, $customeraccount);
        $bills = $DB->get_records('local_shop_bill', ['customerid' => $customeraccount->id], 'ordering');
        if (!empty($bills)) {
            foreach ($bills as $bill) {
                self::export_bill($user, $bill);
                $items = $DB->get_records('local_shop_billitem', ['billid' => $bill->id], 'ordering');
                if (!empty($items)) {
                    foreach ($items as $item) {
                        self::export_bill_item($user, $item);
                    }
                }
            }
        }

        $products = $DB->get_records('local_shop_product', ['customerid' => $customeraccount->id], 'startdate');
        if (!empty($products)) {
            foreach ($products as $product) {
                self::export_product($user, $product);
                $events = $DB->get_records('local_shop_productevent', ['productid' => $product->id], 'datecreated');
                if (!empty($events)) {
                    foreach($events as $event) {
                        self::export_product_event($user, $event);
                    }
                }
            }
        }
    }

    /**
     * Export all customer data for the specified user.
     *
     * @param object $user The user who has the customer account.
     * @param object $recordobj The customer record.
     */
    protected static function export_customer_account($user, $recordobj) {
        global $DB;

        if (!$recordobj) {
            return;
        }

        // Necessary format transforms
        $recordobj->hasaccount = transform::user($recordobj->hasaccount);
        $recordobj->timecreated = transform::datetime($recordobj->timecreated);

        // Data about the record.
        writer::with_context(context_system::instance())->export_data([$recordobj->id], (object) $recordobj);
    }

    /**
     * Export data from a user bill.
     *
     * @param object $user The user who has the customer account.
     * @param object $recordobj The bill record.
     */
    protected static function export_bill($user, $recordobj) {
        global $DB;

        if (!$recordobj) {
            return;
        }

        $recordobj->userid = transform::user($recordobj->userid);
        $recordobj->emissiondate = transform::datetime($recordobj->emissiondate);
        $recordobj->lastactiondate = transform::datetime($recordobj->lastactiondate);
        $recordobj->assignedto = transform::user($recordobj->assignedto);
        $recordobj->ignoretax = transform::yesno($recordobj->ignoretax);

        // Data about the record.
        writer::with_context(context_system::instance())->export_data([$recordobj->id], (object)$recordobj);
    }

    /**
     * Export data from a user bill item.
     *
     * @param object $user The user who has the customer account.
     * @param object $recordobj The bill item record.
     */
    protected static function export_bill_item($user, $recordobj) {
        global $DB;

        if (!$recordobj) {
            return;
        }

        // Data about the record.
        writer::with_context(context_system::instance())->export_data([$recordobj->id], (object)$recordobj);
    }

    /**
     * Export a user owned product.
     *
     * @param object $user The user who has the customer account.
     * @param object $recordobj The product record.
     */
    protected static function export_product($user, $recordobj) {
        global $DB;

        if (!$recordobj) {
            return;
        }

        $recordobj->customerid = transform::user($recordobj->customerid);
        $recordobj->startdate = transform::datetime($recordobj->startdate);
        $recordobj->enddate = transform::datetime($recordobj->enddate);
        $recordobj->deleted = transform::yesno($recordobj->deleted);

        // Data about the record.
        writer::with_context(context_system::instance())->export_data([$recordobj->id], (object)$recordobj);
    }

    protected static function export_product_event($user, $recordobj) {
        global $DB;

        if (!$recordobj) {
            return;
        }

        $recordobj->datecreated = transform::datetime($recordobj->datecreated);

        // Data about the record.
        writer::with_context(context_system::instance())->export_data([$recordobj->id], (object)$recordobj);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * GRDP statement on shop records : There is a superseeding legal obligation to
     * keep track of all commercial data : France : Rec.30;Art.7(1)(c), Article L123-22 Code du commerce
     *
     * "Les documents comptables et les pièces justificatives sont conservés pendant dix ans."
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        assert(true);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * GRDP statement on shop records : There is a superseeding legal obligation to
     * keep track of all commercial data : France : Rec.30;Art.7(1)(c), Article L123-22 Code du commerce
     *
     * "Les documents comptables et les pièces justificatives sont conservés pendant dix ans."
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        assert(true);
    }
}
