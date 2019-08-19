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
 * Privacy Subsystem implementation for mod_forum.
 *
 * @package    mod_forum
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\privacy;

use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\deletion_criteria;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\helper as request_helper;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the forum activity module.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin currently implements the original plugin\provider interface.
    \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection     $items The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
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
     *
     * In the case of forum, that is any forum where the user has made any post, rated any content, or has any preferences.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {

        $ratingsql = \core_rating\privacy\provider::get_sql_join('rat', 'mod_forum', 'post', 'p.id', $userid);
        // Fetch all forum discussions, and forum posts.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {forum} f ON f.id = cm.instance
             LEFT JOIN {forum_discussions} d ON d.forum = f.id
             LEFT JOIN {forum_posts} p ON p.discussion = d.id
             LEFT JOIN {forum_digests} dig ON dig.forum = f.id AND dig.userid = :digestuserid
             LEFT JOIN {forum_subscriptions} sub ON sub.forum = f.id AND sub.userid = :subuserid
             LEFT JOIN {forum_track_prefs} pref ON pref.forumid = f.id AND pref.userid = :prefuserid
             LEFT JOIN {forum_read} hasread ON hasread.forumid = f.id AND hasread.userid = :hasreaduserid
             LEFT JOIN {forum_discussion_subs} dsub ON dsub.forum = f.id AND dsub.userid = :dsubuserid
             {$ratingsql->join}
                 WHERE (
                    p.userid        = :postuserid OR
                    d.userid        = :discussionuserid OR
                    dig.id IS NOT NULL OR
                    sub.id IS NOT NULL OR
                    pref.id IS NOT NULL OR
                    hasread.id IS NOT NULL OR
                    dsub.id IS NOT NULL OR
                    {$ratingsql->userwhere}
                )
        ";
        $params = [
            'modname'           => 'forum',
            'contextlevel'      => CONTEXT_MODULE,
            'postuserid'        => $userid,
            'discussionuserid'  => $userid,
            'digestuserid'      => $userid,
            'subuserid'         => $userid,
            'prefuserid'        => $userid,
            'hasreaduserid'     => $userid,
            'dsubuserid'        => $userid,
        ];
        $params += $ratingsql->params;

        $contextlist = new \core_privacy\local\request\contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $user = $contextlist->get_user();
        $userid = $user->id;

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT
                    c.id AS contextid,
                    f.*,
                    cm.id AS cmid,
                    dig.maildigest,
                    sub.userid AS subscribed,
                    pref.userid AS tracked
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {forum} f ON f.id = cm.instance
             LEFT JOIN {forum_digests} dig ON dig.forum = f.id AND dig.userid = :digestuserid
             LEFT JOIN {forum_subscriptions} sub ON sub.forum = f.id AND sub.userid = :subuserid
             LEFT JOIN {forum_track_prefs} pref ON pref.forumid = f.id AND pref.userid = :prefuserid
                 WHERE (
                    c.id {$contextsql}
                )
        ";

        $params = [
            'digestuserid'  => $userid,
            'subuserid'     => $userid,
            'prefuserid'    => $userid,
        ];
        $params += $contextparams;

        // Keep a mapping of forumid to contextid.
        $mappings = [];

        $forums = $DB->get_recordset_sql($sql, $params);
        foreach ($forums as $forum) {
            $mappings[$forum->id] = $forum->contextid;

            $context = \context::instance_by_id($mappings[$forum->id]);

            // Store the main forum data.
            $data = request_helper::get_context_data($context, $user);
            writer::with_context($context)
                ->export_data([], $data);
            request_helper::export_context_files($context, $user);

            // Store relevant metadata about this forum instance.
            static::export_digest_data($userid, $forum);
            static::export_subscription_data($userid, $forum);
            static::export_tracking_data($userid, $forum);
        }
        $forums->close();

        if (!empty($mappings)) {
            // Store all discussion data for this forum.
            static::export_discussion_data($userid, $mappings);

            // Store all post data for this forum.
            static::export_all_posts($userid, $mappings);
        }
    }

    /**
     * Store all information about all discussions that we have detected this user to have access to.
     *
     * @param   int         $userid The userid of the user whose data is to be exported.
     * @param   array       $mappings A list of mappings from forumid => contextid.
     * @return  array       Which forums had data written for them.
     */
    protected static function export_discussion_data(int $userid, array $mappings) {
        global $DB;

        // Find all of the discussions, and discussion subscriptions for this forum.
        list($foruminsql, $forumparams) = $DB->get_in_or_equal(array_keys($mappings), SQL_PARAMS_NAMED);
        $sql = "SELECT
                    d.*,
                    g.name as groupname,
                    dsub.preference
                  FROM {forum} f
                  JOIN {forum_discussions} d ON d.forum = f.id
             LEFT JOIN {groups} g ON g.id = d.groupid
             LEFT JOIN {forum_discussion_subs} dsub ON dsub.discussion = d.id AND dsub.userid = :dsubuserid
             LEFT JOIN {forum_posts} p ON p.discussion = d.id
                 WHERE f.id ${foruminsql}
                   AND (
                        d.userid    = :discussionuserid OR
                        p.userid    = :postuserid OR
                        dsub.id IS NOT NULL
                   )
        ";

        $params = [
            'postuserid'        => $userid,
            'discussionuserid'  => $userid,
            'dsubuserid'        => $userid,
        ];
        $params += $forumparams;

        // Keep track of the forums which have data.
        $forumswithdata = [];

        $discussions = $DB->get_recordset_sql($sql, $params);
        foreach ($discussions as $discussion) {
            // No need to take timestart into account as the user has some involvement already.
            // Ignore discussion timeend as it should not block access to user data.
            $forumswithdata[$discussion->forum] = true;
            $context = \context::instance_by_id($mappings[$discussion->forum]);

            // Store related metadata for this discussion.
            static::export_discussion_subscription_data($userid, $context, $discussion);

            $discussiondata = (object) [
                'name' => format_string($discussion->name, true),
                'pinned' => transform::yesno((bool) $discussion->pinned),
                'timemodified' => transform::datetime($discussion->timemodified),
                'usermodified' => transform::datetime($discussion->usermodified),
                'creator_was_you' => transform::yesno($discussion->userid == $userid),
            ];

            // Store the discussion content.
            writer::with_context($context)
                ->export_data(static::get_discussion_area($discussion), $discussiondata);

            // Forum discussions do not have any files associately directly with them.
        }

        $discussions->close();

        return $forumswithdata;
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * GRDP statement on shop records : There is a superseeding legal obligation to
     * keep track of all commercial data : France : Rec.30;Art.7(1)(c), Article L123-22 Code du commerce
     *
     * "Les documents comptables et les pièces justificatives sont conservés pendant dix ans."
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * GRDP statement on shop records : There is a superseeding legal obligation to
     * keep track of all commercial data : France : Rec.30;Art.7(1)(c), Article L123-22 Code du commerce
     *
     * "Les documents comptables et les pièces justificatives sont conservés pendant dix ans."
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
    }
}
