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
 * @package     local_shop
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Catalog;
use local_shop\Shop;
use local_shop\Bill;
use local_shop\CatalogItem;

define('PRODUCT_STANDALONE', 0);
define('PRODUCT_SET', 1);
define('PRODUCT_BUNDLE', 2);

define('PROVIDING_CUSTOMER_ONLY', 2);
define('PROVIDING_LOGGEDIN_ONLY', 1);
define('PROVIDING_BOTH', 0);
define('PROVIDING_LOGGEDOUT_ONLY', -1);

/*
 * what means the quantity ordered
 * SHOP_QUANT_NO_SEATS : will not ask for required seats to assign. Such product as physical
 * goods f.e., or unassigned seat packs
 * SHOP_QUANT_ONE_SEAT : products gives one seat only whatever the quantity ordered. Usually
 * quantity addresses another parameter such as course duration
 * SHOP_QUANT_AS_SEATS : products as many required seats as quantity requires. This is for
 * assignable seats.
 */
define('SHOP_QUANT_AS_SEATS', 2);
define('SHOP_QUANT_ONE_SEAT', 1);
define('SHOP_QUANT_NO_SEATS', 0);

/*
 * Known bill states.
 */
define('SHOP_BILL_WORKING', 'WORKING'); // An order is being prepared in the backoffice.
define('SHOP_BILL_PLACED', 'PLACED'); // An order has been placed online.
define('SHOP_BILL_PENDING', 'PENDING'); // An order is confirmed, waiting for paiment confirmation.
define('SHOP_BILL_PREPROD', 'PREPROD'); // An order has bypassed paiment check and is produced but not yet paied.
define('SHOP_BILL_SOLDOUT', 'SOLDOUT'); // An order has been paied.
define('SHOP_BILL_PARTIAL', 'PARTIAL'); // An order has beed partialy paied.
define('SHOP_BILL_COMPLETE', 'COMPLETE'); // An order is paied AND produced (final state).
define('SHOP_BILL_PAYBACK', 'PAYBACK'); // An order needs payback to customer.
define('SHOP_BILL_FAILED', 'FAILED'); // An order production has failed.
define('SHOP_BILL_REFUSED', 'REFUSED'); // An order could not conclude because payment failed or was rejected.
define('SHOP_BILL_CANCELLED', 'CANCELLED'); // An order has been cancelled after placement (or pending with no final resolution).

/**
 * gives all product status
 */
function shop_get_status() {
    $status = array(
                'PREVIEW' => get_string('PREVIEW', 'local_shop'),
                'AVAILABLE' => get_string('AVAILABLE', 'local_shop'),
                'AVAILABLEINTERNAL' => get_string('AVAILABLEINTERNAL', 'local_shop'),
                'SUSPENDED' => get_string('SUSPENDED', 'local_shop'),
                'PROVIDING' => get_string('PROVIDING', 'local_shop'),
                'ABANDONNED' => get_string('ABANDONNED', 'local_shop')
    );
    return $status;
}

/**
 * get a block instance for the shop
 */
function shop_get_block_instance($instanceid) {
    global $DB;

    if (!$instance = $DB->get_record('block_instances', array('id' => $instanceid))) {
        // Silently forget the block.
        return null;
    }
    if (!$theblock = block_instance('shop_access', $instance)) {
        print_error('errorbadblockinstance', 'local_shop');
    }
    return $theblock;
}

/**
 * examines the handler list in implementation and get the
 * emerging standard handlers. Standard (generic) handlers are
 * PHP clases that all start with STD_
 * @return an array of options for a select.
 */
function shop_get_standard_handlers_options() {

    $stdhandlers = array();
    $handlers = get_list_of_plugins('/local/shop/datahandling/handlers');
    foreach ($handlers as $h) {
        if (!preg_match('/^std|ext/', $h)) {
            continue;
        }
        $handlername = get_string('handlername', 'shophandlers_'.$h);
        $stdhandlers[$h] = get_string('generic', 'local_shop').' '.$handlername;
    }
    return $stdhandlers;
}

/**
 * given an url encoded param input from a catalog item handler paramstring, make a clean object with it
 * The object will be tranmitted to core handler callbacks to help product to be handled.
 * @param string $catalogitemcode product code name in catalog.
 * @return an object with params as object fields.
 */
function shop_decode_params($catalogitemcode) {
    global $DB;

    $paramstring = $DB->get_field('local_shop_catalogitem', 'handlerparams', array('code' => "$catalogitemcode"));
    if (empty($paramstring)) {
        return null;
    }
    $params = array();
    $paramelements = explode('&', $paramstring);
    foreach ($paramelements as $elm) {
        list($key, $value) = explode('=', $elm);
        if (empty($key)) {
            // Ignore bad formed.
            continue;
        }
        $params[$key] = $value;
    }
    return $params;
}

/**
 * checks for an existing backup
 * @see exists in other components (enrol/sync, publishflow) but no consensus yet to centralize
 * @param int $courseid the course for which seeking for backup consistant file
 * @return full path of the backyp file on disk.
 */
function shop_delivery_check_available_backup($courseid) {
    global $CFG, $DB;

    $realpath = false;
    // Calculate the archive pattern.
    $course = $DB->get_record('course', array('id' => $courseid));
    // Calculate the backup word.
    $backupword = backup_get_backup_string($course);
    // Calculate the date recognition/capture patterns.
    $backupdatepattern = '([0-9]{8}-[0-9]{4})';
    // Calculate the shortname.
    $backupshortname = clean_filename($course->shortname);
    if (empty($backupshortname) or $backupshortname == '_' ) {
        $backupshortname = $course->id;
    } else {
        // Get rid of all version information for searching archive.
        $backupshortname = preg_replace('/(_(\d+))+$/' , '', $backupshortname);
    }
    // Calculate the final backup filename.
    // The backup word.
    $backuppattern = $backupword."-";
    // The shortname.
    $backuppattern .= preg_quote(moodle_strtolower($backupshortname)).".*-";
    // The date format.
    $backuppattern .= $backupdatepattern;
    // The extension.
    $backuppattern .= "\\.zip";
    /*
     * Get the last backup in the proper location
     * backup must have moodle backup filename format
     */
    $realdir = $CFG->dataroot.'/'.$courseid.'/backupdata';
    if (!file_exists($realdir)) {
        return false;
    }
    if ($dir = opendir($realdir)) {
        $archives = array();
        while ($entry = readdir($dir)) {
            if (preg_match("/^$backuppattern\$/", $entry, $matches)) {
                $archives[$matches[1]] = "{$realdir}/{$entry}";
            }
        }
        if (!empty($archives)) {
            // Sorts reverse the archives so we can get the latest.
            krsort($archives);
            $archnames = array_values($archives);
            $realpath->path = $archnames[0];
            $realpath->dir = $realdir;
        }
    }
    return $realpath;
}

/**
 * generates a usrname from given identity
 * @param object $user a user record
 * @return a username
 */
function shop_generate_username($user) {
    $firstname = $user->firstname;
    $lastname = $user->lastname;

    $firstname = strtolower($firstname);
    $firstname = str_replace('\'', '', $firstname);
    $firstname = preg_replace('/\s+/', '-', $firstname);
    $lastname = strtolower($lastname);
    $lastname = str_replace('\'', '', $lastname);
    $lastname = preg_replace('/\s+/', '-', $lastname);
    $username = $firstname.'.'.$lastname;
    $username = str_replace('é', 'e', $username);
    $username = str_replace('è', 'e', $username);
    $username = str_replace('ê', 'e', $username);
    $username = str_replace('ë', 'e', $username);
    $username = str_replace('ö', 'o', $username);
    $username = str_replace('ô', 'o', $username);
    $username = str_replace('ü', 'u', $username);
    $username = str_replace('û', 'u', $username);
    $username = str_replace('ù', 'u', $username);
    $username = str_replace('î', 'i', $username);
    $username = str_replace('ï', 'i', $username);
    $username = str_replace('à', 'a', $username);
    $username = str_replace('â', 'a', $username);
    $username = str_replace('ç', 'c', $username);
    $username = str_replace('ñ', 'n', $username);
    return $username;
}

/**
 * generates a suitable shortname based on user's username
 * @param object $user a user record
 * @return a user shortname that is known having NOT been used yet
 */
function shop_generate_shortname($user) {
    global $DB;

    $username = str_replace('.', '', $user->username);
    $basename = strtoupper(substr($username, 0, 8));
    $sql = "
        SELECT
            shortname,
            shortname
        FROM
            {course}
        WHERE
            shortname REGEXP '^{$basename}_[[:digit:]]+$'
        ORDER BY
            shortname
    ";
    if (!$used = $DB->get_records_sql($sql)) {
        return $basename.'_1';
    } else {
        $last = array_pop($used);
        preg_match('/^$basename(\\d+)$/', $last, $matches);
        $lastid = $matches[1] + 1;
        return $basename.'_'.$lastid;
    }
}

/**
 * create a course from a template
 */
function shop_create_course_from_template($templatepath, $courserec) {
    if (empty($courserec->password)) {
        $courserec->password = '';
    }
    if (empty($courserec->fullname)) {
        $courserec->fullname = '';
    }
    if (empty($courserec->shortname)) {
        print_error('errorprograming'); // Should NEVER happen... shortname needs to be resolved before creating.
    }
    if (empty($courserec->idnumber)) {
        $courserec->idnumber = '';
    }
    if (empty($courserec->lang)) {
        $courserec->lang = '';
    }
    if (empty($courserec->lang)) {
        $courserec->lang = '';
    }
    if (empty($courserec->theme)) {
        $courserec->theme = '';
    }
    if (empty($courserec->cost)) {
        $courserec->cost = '';
    }

    // First creation of record before restoring.
    if (!$courserec->id = $DB->insert_record('course', $courserec)) {
        return;
    }
    create_context(CONTEXT_COURSE, $courserec->id);
    import_backup_file_silently($templatepath, $courserec->id, true, false, array('restore_course_files' => 1));

    /*
     * this part forces some course attributes to override the given attributes in template
     * temptate attributes might come from the backup instant and are not any more consistant.
     * As importing a course needs a real course to exist before importing, it is not possible
     * to preset those attributes and expect backup will not overwrite them.
     * conversely, precreating the coure with some attributes setup might give useful default valies that
     * are not present in the backup.
     * override necessary attributes from original courserec.
     */
    $DB->update_record('course', $courserec);
    return $courserec->id;
}

/**
 * Create category with the given name and parentID returning a category ID
 */
function shop_fast_make_category($catname, $description = '', $catparent = 0) {
    global $DB;

    $cname = mysqli_real_escape_string($catname);
    // Check if a category with the same name and parent ID already exists.
    if ($cat = $DB->get_field_select('course_categories', 'id', " name = '$cname' AND parent = $catparent ")) {
        return false;
    } else {
        if (!$parent = $DB->get_record('course_categories', array('id' => $catparent))) {
            $parent->path = '';
            $parent->depth = 0;
            $catparent = 0;
        }
        $cat = new StdClass;
        $cat->name = $cname;
        $cat->description = $description;
        $cat->parent = $catparent;
        $cat->sortorder = 999;
        $cat->coursecount = 0;
        $cat->visible = 1;
        $cat->depth = $parent->depth + 1;
        $cat->timemodified = time();
        if ($cat->id = $DB->insert_record('course_categories', $cat)) {
            // Must post update.
            $cat->path = $parent->path.'/'.$cat->id;
            $DB->update_record('course_categories', $cat);
            // We must make category context.
            create_context(CONTEXT_COURSECAT, $cat->id);
            return $cat->id;
        } else {
            return false;
        }
    }
}

/**
 * background style switch
 */
function shop_switch_style($reset = 0) {
    static $style;

    if ($reset) {
        $style = 'odd';
    }
    if ($style == 'odd') {
        $style = 'even';
    } else {
        $style = 'odd';
    }
    return $style;
}

/**
 * opens a trace file
 * IMPORTANT : check very carefully the path and name of the file or it might destroy
 * some piece of code. Do NEVER use in production systems unless hot case urgent tracking
 */
function shop_open_trace($output) {
    global $CFG;

    if (empty($output)) {
        $file = 'merchant_trace.log';
    } else if ($output == 'mail') {
        $file = 'merchant_mail_trace.log';
    }

    if (empty($stream)) {
        $stream = fopen($CFG->dataroot.'/'.$file, 'a');
    }

    if (empty($output)) {
        $CFG->merchanttrace = $stream;
    } else if ($output == 'mail') {
        $CFG->merchantmailtrace = $stream;
    }

    return !is_null($stream);
}

/**
 * closes an open trace
 */
function shop_close_trace($output) {
    global $CFG;

    if (empty($output)) {
        if (!empty($CFG->merchanttrace)) {
            @fclose($CFG->merchanttrace);
            $CFG->merchanttrace = null;
        }
    } else if ($output == 'mail') {
        if (!empty($CFG->merchantmailtrace)) {
            @fclose($CFG->merchantmailtrace);
            $CFG->merchantmailtrace = null;
        }
    }

}

/**
 * outputs into an open trace (ligther than debug_trace)
 * @param string $str
 */
function shop_trace_open($str, $output) {
    global $CFG;

    $date = new DateTime();
    $u = microtime(true);
    $u = sprintf('%03d', floor(($u - floor($u)) * 1000)); // Millisecond.

    if (empty($output)) {
        if (!empty($CFG->merchanttrace)) {
            fputs($CFG->merchanttrace, "-- ".$date->format('Y-n-d H:i:s').' '.$u." --  ".$str."\n");
        }
    } else if ($output == 'mail') {
        if (!empty($CFG->merchantmailtrace)) {
            fputs($CFG->merchantmailtrace, "-- ".$date->format('Y-n-d H:i:s').' '.$u." --  ".$str."\n");
        }
    }
}

/**
 * write to the trace
 */
function shop_trace($str, $output = '') {
    global $CFG;

    if (empty($output)) {
        if (!empty($CFG->merchanttrace)) {
            shop_trace_open($str, $output);
            return;
        }
    } else if ($output == 'mail') {
        if (!empty($CFG->merchantmailtrace)) {
            shop_trace_open($str, $output);
            return;
        }
    }

    if (shop_open_trace($output)) {
        shop_trace_open($str, $output);
        shop_close_trace($output);
    }
}

/**
 * Deprectated : use class method instead
 */
function shop_calculate_taxed($htprice, $taxid) {
    static $taxcache;
    global $DB, $CFG;

    if (!isset($taxcache)) {
        $taxcache = array();
    }
    if (!array_key_exists($taxid, $taxcache)) {
        if ($taxcache[$taxid] = $DB->get_record('local_shop_tax', array('id' => $taxid))) {
            if (empty($taxcache[$taxid]->formula)) {
                $taxcache[$taxid]->formula = '$ttc = $ht';
            }
        } else {
            return $htprice;
        }
    }
    $in['ht'] = $htprice;
    $in['tr'] = $taxcache[$taxid]->ratio;
    require_once($CFG->dirroot.'/local/shop/extlib/extralib.php');
    $result = evaluate(\core_text::strtolower($taxcache[$taxid]->formula).';', $in, 'ttc');
    return $result['ttc'];
}

/**
 * International currencies
 * from http://fr.wikipedia.org/wiki/ISO_4217
 */
function shop_get_supported_currencies() {
    static $currencies;

    if (!isset($currencies)) {
        $currencies = array('EUR' => get_string('EUR', 'local_shop'), // Euro.
                            'CHF' => get_string('CHF', 'local_shop'), // Swiss franc.
                            'USD' => get_string('USD', 'local_shop'), // US dollar.
                            'CAD' => get_string('CAD', 'local_shop'), // Canadian dollar.
                            'AUD' => get_string('AUD', 'local_shop'), // Australian dollar.
                            'GPB' => get_string('GPB', 'local_shop'), // English pound.
                            'TRY' => get_string('TRY', 'local_shop'), // Turkish pound.
                            'PLN' => get_string('PLN', 'local_shop'), // Zloty (Poland).
                            'RON' => get_string('RON', 'local_shop'), // Roman leu.
                            'ILS' => get_string('ILS', 'local_shop'), // Shekel.
                            'KRW' => get_string('KRW', 'local_shop'), // Won (corea).
                            'JPY' => get_string('JPY', 'local_shop'), // Yen (japan).
                            'TND' => get_string('TND', 'local_shop'), // Dinar (Tunisian, internal market).
                            'MAD' => get_string('MAD', 'local_shop'), // Dinar (Marocco, internal market).
                      );
    }
    return $currencies;
}

/**
 * Builds the full memmory context from incoming params and
 * session state.
 * @returns three object refs if they are buildable, null for other.
 */
function shop_build_context() {
    global $SESSION, $DB;

    $theshop = new Shop(null);

    if (!isset($SESSION->shop)) {
        $SESSION->shop = new StdClass;
    }

    $SESSION->shop->shopid = optional_param('shopid', @$SESSION->shop->shopid, PARAM_INT);
    if ($SESSION->shop->shopid) {
        try {
            $theshop = new Shop($SESSION->shop->shopid);
        } catch (Exception $e) {
            print_error('objecterror', 'local_shop', $e->getMessage());
        }
    } else {
        // Shopid is null. get lowest available shop as default.
        $shops = $DB->get_records('local_shop', array(), 'id', '*', 0, 1);
        if ($shop = array_pop($shops)) {
            $theshop = new Shop($shop->id);
        }
    }

    if (!$theshop) {
        // No shops available at all. Redirect o shop management.
        redirect(new moodle_url('/local/shop/shop/view.php', array('view' => 'viewAllShops')));
    }

    /*
     * Logic : forces session catalog to be operative,
     * Defaults to the current shop bound catalog.
     */
    $SESSION->shop->catalogid = optional_param('catalogid', @$SESSION->shop->catalogid, PARAM_INT);
    if (empty($SESSION->shop->catalogid) || !$DB->record_exists('local_shop_catalog', array('id' => $SESSION->shop->catalogid))) {
        // If no catalog defined in session or catalog is missing after deletion.
        if ($theshop->id) {
            // ... If we have a shop take the catalog of this shop ...
            try {
                $thecatalog = new Catalog($theshop->catalogid);
                $SESSION->shop->catalogid = $thecatalog->id;
            } catch (Exception $e) {
                print_error('objecterror', 'local_shop', $e->getMessage());
            }
        }
    }
    try {
        $thecatalog = new Catalog($SESSION->shop->catalogid);
    } catch (Exception $e) {
        if (preg_match('/local\/shop\/index.php/', $_SERVER['PHP_SELF'])) {
            unset($SESSION->shop->catalogid);
            redirect(me());
        } else {
            print_error('objecterror', 'local_shop', $e->getMessage());
        }
    }

    $theblock = null;
    $SESSION->shop->blockid = optional_param('blockid', @$SESSION->shop->blockid, PARAM_INT);
    if (!empty($SESSION->shop->blockid)) {
        $theblock = shop_get_block_instance($SESSION->shop->blockid);
    }
    return array($theshop, $thecatalog, $theblock);
}

/**
 * get all users that have sales role for the given block context
 * @param int $blockid the shop block instance id
 * @return an array of user records or false
 */
function shop_get_sales_managers($blockid) {
    global $DB;

    $salesrole = $DB->get_record('role', array('shortname' => 'sales'));
    $blockcontext = context_block::instance($blockid);
    $sql = "
        SELECT DISTINCT
            u.*
        FROM
            {user} u,
            {role_assignments} ra
        WHERE
            u.id = ra.userid AND
            roleid = $salesrole->id AND
            contextid = $blockcontext->id
    ";
    return $DB->get_records_sql($sql);
}

/**
 * gives status list for bills :
 * PLACED : This is the first state;
 * PENDING : Payment has been initiated, but we do not have yet any evidence of it. This can be online
 * payment startup or offline method.
 * PAYBACK : The bill needs to be payback to the customer.
 * PARTIAL : The bill has received partial (not complete) payment.
 * SOLDOUT : The bill has received complete payment. It can be processed for production
 * CANCELLED : The bill has been cancelled, after placing but before payment
 * RECOVERING : The bill has payment issues and was not honored.
 * COMPLETE : The bill has been fully processed for production.
 *
 * Special state :
 *
 * WORKING : A manual bill edition starts in working state before being placed
 */
function shop_get_bill_states() {
    static $status;

    if (!isset($status)) {
        $status = array ('PLACED' => 'PLACED',
                         'PENDING' => 'PENDING',
                         'PAYBACK' => 'PAYBACK',
                         'PARTIAL' => 'PARTIAL',
                         'SOLDOUT' => 'SOLDOUT',
                         'RECOVERING' => 'RECOVERING',
                         'CANCELLED' => 'CANCELLED',
                         'COMPLETE' => 'COMPLETE',
                         'WORKING' => 'WORKING');
    }
    return $status;
}

/**
 * For further purposes
 *
 */
function shop_get_bill_worktypes() {
    static $worktypes;

    if (!isset($worktypes)) {
        $worktypes = array ( 'PROD' => 'PROD',
                            'PACK' => 'PACK',
                            'OTHER' => 'OTHER' );
    }
    return $worktypes;
}

/**
 * ensures a transaction id is unique.
 */
function shop_get_transid() {
    global $DB;

    // Seek for a unique transaction ID.
    $transid = strtoupper(substr(base64_encode(crypt(microtime() + rand(0, 32), 'MOODLE_SHOP')), 0, 30));
    while ($DB->record_exists('local_shop_bill', array('transactionid' => $transid))) {
        $transid = strtoupper(substr(base64_encode(crypt(microtime() + rand(0, 32), 'MOODLE_SHOP')), 0, 30));
    }
    return $transid;
}

/**
 * Pursuant a table has a sortorder field, pulls down an item in a specific select context.
 * @param array $context
 */
function shop_list_up($selectcontext, $itemid, $table) {
    global $DB;

    $item = $DB->get_record($table, array('id' => $itemid));
    $selectcontext['sortorder'] = $item->sortorder + 1;
    if (!$nextitem = $DB->get_record($table, $selectcontext)) {
        // Cannot go up. Last one.
        return;
    }
    $nextitem->sortorder--;
    $item->sortorder++;
    $DB->update_record($table, $item);
    $DB->update_record($table, $nextitem);
}

function shop_list_down($selectcontext, $itemid, $table) {
    global $DB;

    $item = $DB->get_record($table, array('id' => $itemid));
    if ($item->sortorder <= 1) {
        // Cannot go down. First one.
        return;
    }
    $selectcontext['sortorder'] = $item->sortorder - 1;
    $previtem = $DB->get_record($table, $selectcontext);
    $previtem->sortorder++;
    $item->sortorder--;
    $DB->update_record($table, $item);
    $DB->update_record($table, $previtem);
}

function shop_list_reorder($selectcontext, $table) {
    global $DB;

    $allrecs = $DB->get_records($table, $selectcontext, 'sortorder', 'id, sortorder');
    if ($allrecs) {
        $ix = 1;
        foreach ($allrecs as $rec) {
            $rec->sortorder = $ix;
            $DB->update_record($rec);
            $ix++;
        }
    }
}