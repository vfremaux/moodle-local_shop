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
require_once($CFG->dirroot.'/backup/util/includes/restore_includes.php');
require_once($CFG->libdir.'/filestorage/tgz_packer.php');
require_once($CFG->dirroot.'/local/shop/compatlib.php');

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

define('SHOP_UNIT_EXPIRATION_FORECAST_DELAY1', DAYSECS * 30);
define('SHOP_UNIT_EXPIRATION_FORECAST_DELAY2', DAYSECS * 90);

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

    $fs = get_file_storage();
    $templatecontext = context_course::instance($courseid);

    // Alternatively and as last try standard backup.
    $backupfiles = $fs->get_area_files($templatecontext->id, 'backup', 'course', 0, 'timecreated', false);

    if (!empty($backupfiles)) {
        return array_pop($backupfiles);
    } else {
        // Try making a new one if missing.
        return shop_backup_for_template($courseid);
    }
}

/**
 * Make a course backup without user data and stores it in the course
 * backup area.
 */
function shop_backup_for_template($courseid, $options = array(), &$log = '') {
    global $CFG, $USER;

    $user = get_admin();

    include_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');

    $bc = new backup_controller(backup::TYPE_1COURSE, $courseid, backup::FORMAT_MOODLE,
                                backup::INTERACTIVE_NO, backup::MODE_GENERAL, $user->id);

    try {

        $coursecontext = context_course::instance($courseid);

        // Build default settings for quick backup.
        // Quick backup is intended for publishflow purpose.

        // Get default filename info from controller.
        $format = $bc->get_format();
        $type = $bc->get_type();
        $id = $bc->get_id();
        $users = $bc->get_plan()->get_setting('users')->get_value();
        $anonymised = $bc->get_plan()->get_setting('anonymize')->get_value();

        $settings = array(
            'users' => 0,
            'role_assignments' => 0,
            'user_files' => 0,
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'comments' => 0,
            'completion_information' => 0,
            'logs' => 0,
            'histories' => 0,
            'filename' => backup_plan_dbops::get_default_backup_filename($format, $type, $id, $users, $anonymised)
        );

        foreach ($settings as $setting => $configsetting) {
            if ($bc->get_plan()->setting_exists($setting)) {
                $bc->get_plan()->get_setting($setting)->set_value($configsetting);
            }
        }

        $bc->set_status(backup::STATUS_AWAITING);

        $bc->execute_plan();
        $results = $bc->get_results();
        // Convert user file in course file.
        $file = $results['backup_destination'];

        $fs = get_file_storage();

        $filerec = new StdClass();
        $filerec->contextid = $coursecontext->id;
        $filerec->component = 'backup';
        $filerec->filearea = 'course';
        $filerec->itemid = 0;
        $filerec->filepath = $file->get_filepath();
        $filerec->filename = $file->get_filename();

        if (!empty($options['clean'])) {
            if (!empty($options['verbose'])) {
                $log .= "Cleaning course backup area\n";
            }
            $fs->delete_area_files($coursecontext->id, 'backup', 'course');
        }

        if (!empty($options['verbose'])) {
            $log .= "Moving backup to course backup area\n";
        }
        $archivefile = $fs->create_file_from_storedfile($filerec, $file);

        // Remove user scope original file.
        $file->delete();

        return $archivefile;

    } catch (backup_exception $e) {
        return null;
    }
}


/**
 * generates a username from given identity
 * @param object $user a user record. 
 * @param bool $checkunique if true, generates indexed untill not found in DB.
 * @return a username
 */
function shop_generate_username($user, $checkunique = false) {
    global $DB;

    if (empty($user)) {
        debugging("Empty user");
        return;
    }
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

    if ($checkunique) {
        $ix = '';
        $usernamebase = $username;

        while ($DB->record_exists('user', array('username' => $username, 'deleted' => 0))) {
            if ($ix == '') {
                $ix = 1;
            } else {
                $ix = $ix + 1;
            }
            $username = $usernamebase.$ix;
        }
    }

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
            shortname REGEXP '^{$basename}_[[0-9]]+$'
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
 * Make a silent restore of the template into the target category and enrol user as teacher inside
 * if reqested.
 * @param object $archivefile a moodle file containing the mbz.
 * @param object $data a course record where the fullname, shortname, description and idnumber can be overriden from
 */
function shop_restore_template($archivefile, $data) {
    global $USER, $CFG, $DB;

    // Let the site admin performing the restore.
    $user = get_admin();

    $contextid = context_system::instance()->id;
    $component = 'local_coursetemplates';
    $filearea = 'temp';
    $itemid = $uniq = 9999999 + rand(0, 100000);
    $tempdir = $CFG->tempdir."/backup/$uniq";

    if (!is_dir($tempdir)) {
        mkdir($tempdir, 0777, true);
    }

    if (!$archivefile->extract_to_pathname(new tgz_packer(), $tempdir)) {
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('error');
        echo $OUTPUT->notification(get_string('restoreerror', 'local_coursetemplates'));
        echo $OUTPUT->box_end();
        echo $OUTPUT->continue_button($url);
        echo $OUTPUT->footer();
        die;
    }

    // Transaction.
    $transaction = $DB->start_delegated_transaction();

    // Create new course.
    $categoryid = $data->category; // A categoryid.
    $userdoingtherestore = $user->id; // E.g. 2 == admin.
    $newcourseid = restore_dbops::create_new_course('', '', $categoryid);

    // Restore backup into course.
    $controller = new restore_controller($uniq, $newcourseid,
        backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $userdoingtherestore,
        backup::TARGET_NEW_COURSE );
    $controller->execute_precheck();
    $controller->execute_plan();

    // Commit.
    $transaction->allow_commit();

    // Update names.
    if ($newcourse = $DB->get_record('course', array('id' => $newcourseid))) {
        $newcourse->fullname = $data->fullname;
        $newcourse->shortname = $data->shortname;
        $newcourse->idnumber = $data->idnumber;
        if (!empty($data->summary)) {
            $newcourse->summary = $data->summary;
        }
        $DB->update_record('course', $newcourse);
    }

    return $newcourseid;
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
 * @param string $output the destination trace (empty or 'mail')
 * @param string $dest for mail trace, the destination user of the mail.
 */
function shop_trace_open($str, $output, $dest) {
    global $CFG;
    static $iter = 0;

    $iter++;

    $date = new DateTime();
    $u = microtime(true);
    $u = sprintf('%03d', floor(($u - floor($u)) * 1000)); // Millisecond.

    if (empty($output)) {
        if (!empty($CFG->merchanttrace)) {
            fputs($CFG->merchanttrace, "-- ".$date->format('Y-n-d H:i:s').' '.$u." -".$iter."-  ".$str."\n");
        }
    } else if ($output == 'mail') {
        if (!empty($CFG->merchantmailtrace)) {
            fputs($CFG->merchantmailtrace, "-- ".$date->format('Y-n-d H:i:s').' '.$u." -".$iter."-\n");
            fputs($CFG->merchantmailtrace, "MailTo: ".$dest->email."\n");
            fputs($CFG->merchantmailtrace, "MailContent:\n");
            fputs($CFG->merchantmailtrace, "@@@@@@@@\n");
            fputs($CFG->merchantmailtrace, $str."\n");
            fputs($CFG->merchantmailtrace, "@@@@@@@@\n");
        }
    }
}

/**
 * write to the trace
 */
function shop_trace($str, $output = '', $dest = null) {
    global $CFG;

    if (empty($output)) {
        if (!empty($CFG->merchanttrace)) {
            shop_trace_open($str, $output);
            return;
        }
    } else if ($output == 'mail') {
        if (empty($dest)) {
            throw new coding_exception("Shop mail trace needs the destination user to be set");
        }
        if (!empty($CFG->merchantmailtrace)) {
            shop_trace_open($str, $output);
            return;
        }
    }

    if (shop_open_trace($output)) {
        shop_trace_open($str, $output, $dest);
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
        $SESSION->shop->shopid = 1;
    }

    $shopid = optional_param('shopid', @$SESSION->shop->shopid, PARAM_INT);

    if ($shopid) {
        try {
            $theshop = new Shop($shopid);
            $SESSION->shop = $theshop;
            $SESSION->shop->catalogid = $theshop->catalogid;
        } catch (Exception $e) {
            print_error('objecterror', 'local_shop', $e->getMessage());
        }
    } else {
        // Shopid is null. get lowest available shop as default.
        $shops = $DB->get_records('local_shop', array(), 'id', '*', 0, 1);
        if ($shop = array_pop($shops)) {
            $theshop = new Shop($shop->id);
            $SESSION->shop = $theshop;
            $SESSION->shop->catalogid = $theshop->catalogid;
        }
    }

    if (!$theshop) {
        // No shops available at all. Redirect to shop management.
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
    $timemark = sprintf('%f', microtime(true) + rand(0, 32));
    $seed = crypt($timemark, 'MOODLE_SHOP');
    $seedstr = base64_encode($seed);
    $transid = strtoupper(substr($seedstr, 0, 30));
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
            $DB->update_record($table, $rec);
            $ix++;
        }
    }
}

function shop_get_enabled_paymodes($theshop) {
    global $USER;

    $config = get_config('local_shop');

    $paymodes = get_list_of_plugins('/local/shop/paymodes');
    $systemcontext = context_system::instance();

    $availables = array();

    \local_shop\Shop::expand_paymodes($theshop);
    foreach ($paymodes as $var) {

        $paymodeplugin = shop_paymode::get_instance($theshop, $var);

        // User must be allowed to use non immediate payment methods.

        $instant = $paymodeplugin->is_instant_payment();

        if (!$instant) {
            if (!has_capability('local/shop:paycheckoverride', $systemcontext) &&
                !has_capability('local/shop:usenoninstantpayments', $systemcontext) && !$config->testoverride) {
                continue;
            }
        }

        if (!empty($USER->realuser)) {
            $isrealadmin = has_capability('moodle/site:config', $systemcontext, $USER->realuser);
        } else {
            $isrealadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);
        }

        if ($var == 'test') {
            if (!$isrealadmin) {
                continue;
            }
        } else {
            if ($config->test && $instant) {
                if (empty($config->testoverride)) {
                    if (!isloggedin()) {
                        continue;
                    }
                }
            }
        }

        $isenabledvar = "enable$var";
        $check = $theshop->{$isenabledvar};
        if ($check) {
            $availables[] = $var;
        }
    }

    return $availables;
}

function shop_has_enabled_paymodes($theshop) {
    $availables = shop_get_enabled_paymodes($theshop);
    return !empty($availables);
}

function shop_get_bill_tabs($total, $fullview) {

    $view = optional_param('view', '', PARAM_TEXT);
    $cur = optional_param('cur', 'EUR', PARAM_TEXT);
    $url = new moodle_url('/local/shop/bills/view.php', array('view' => $view));
    $nopaging = optional_param('nopaging', '0', PARAM_BOOL);

    $rows = array();

    if ($total->WORKING) {
        $label = get_string('bill_WORKINGs', 'local_shop');
        $rows[0][] = new tabobject('WORKING', "$url&status=WORKING&cur=$cur&nopaging=$nopaging", $label.' ('.$total->WORKING.')');
    }

    if ($fullview) {
        $label = get_string('bill_PLACEDs', 'local_shop');
        $rows[0][] = new tabobject('PLACED', "$url&status=PLACED&cur=$cur&nopaging=$nopaging", $label.' ('.$total->PLACED.')');

        $label = get_string('bill_PENDINGs', 'local_shop');
        $rows[0][] = new tabobject('PENDING', "$url&status=PENDING&cur=$cur&nopaging=$nopaging", $label.' ('.$total->PENDING.')');
    }

    $label = get_string('bill_SOLDOUTs', 'local_shop');
    $rows[0][] = new tabobject('SOLDOUT', "$url&status=SOLDOUT&cur=$cur&nopaging=$nopaging", $label.' ('.$total->SOLDOUT.')');

    $label = get_string('bill_COMPLETEs', 'local_shop');
    $rows[0][] = new tabobject('COMPLETE', "$url&status=COMPLETE&cur=$cur&nopaging=$nopaging", $label.' ('.$total->COMPLETE.')');

    if ($fullview) {
        $label = get_string('bill_CANCELLEDs', 'local_shop');
        $rows[0][] = new tabobject('CANCELLED', "$url&status=CANCELLED&cur=$cur&nopaging=$nopaging", $label.' ('.$total->CANCELLED.')');

        $label = get_string('bill_FAILEDs', 'local_shop');
        $rows[0][] = new tabobject('FAILED', "$url&status=FAILED&cur=$cur&nopaging=$nopaging", $label.' ('.$total->FAILED.')');
    }

    if ($total->PAYBACK) {
        $label = get_string('bill_PAYBACKs', 'local_shop');
        $rows[0][] = new tabobject('PAYBACK', "$url&status=PAYBACK&cur=$cur&nopaging=$nopaging", $label.' ('.$total->PAYBACK.')');
    }

    if ($fullview) {
        $label = get_string('bill_ALLs', 'local_shop');
        $rows[0][] = new tabobject('ALL', "$url&status=ALL&cur=$cur&nopaging=$nopaging", $label.' ('.$total->ALL.')');
    }

    return $rows;
}

function shop_get_bill_filtering() {
    global $SESSION;

    $y = optional_param('y', 0 + @$SESSION->shop->billyear, PARAM_INT);
    $m = optional_param('m', 0 + @$SESSION->shop->billmonth, PARAM_INT);
    $customerid = optional_param('customerid', 0 + @$SESSION->shop->customerid, PARAM_INT);
    $SESSION->shop->billyear = $y;
    $SESSION->shop->billmonth = $m;
    $SESSION->shop->customerid = $customerid;
    if (local_shop_supports_feature('shop/partners')) {
        $p = optional_param('p', 0 + @$SESSION->shop->partnerid, PARAM_INT);
        $SESSION->shop->partnerid = $p;
    }

    $shopid = optional_param('shopid', 0, PARAM_INT);
    $status = optional_param('status', 'COMPLETE', PARAM_TEXT);
    $cur = optional_param('cur', 'EUR', PARAM_TEXT);
    $nopaging = optional_param('nopaging', 0, PARAM_BOOL);

    if ($shopid) {
        $filter['shopid'] = $shopid;
    }
    if ($status != 'ALL') {
        $filter['status'] = $status;
    }
    if (!empty($cur)) {
        $filter['currency'] = $cur;
    }
    if (!empty($y)) {
        $filter['YEAR(FROM_UNIXTIME(emissiondate))'] = $y;
    }
    if (!empty($m)) {
        $filter['MONTH(FROM_UNIXTIME(emissiondate))'] = $m;
    }
    if (!empty($customerid)) {
        $filter['customerid'] = $customerid;
    }

    $filterclause = '';
    $filterclause = " AND currency = '{$cur}' ";
    if ($shopid) {
        $filterclause .= " AND shopid = '{$shopid}' ";
    }
    if ($y) {
        $filterclause .= " AND YEAR(FROM_UNIXTIME(emissiondate)) = '{$y}' ";
    }
    if ($m) {
        $filterclause .= " AND MONTH(FROM_UNIXTIME(emissiondate)) = '{$m}' ";
    }
    if ($customerid) {
        $filterclause .= " AND customerid = '{$customerid}' ";
    }

    if (local_shop_supports_feature('shop/partners')) {
        if (!empty($p)) {
            $filter['partnerid'] = $p;
            $filterclause .= " AND partnerid = '{$p}' ";
        }
    }

    $urlfilter = "y=$y&m=$m&status=$status&shopid=$shopid&cur=$cur&nopaging=$nopaging";
    if (local_shop_supports_feature('shop/partners')) {
        $urlfilter = "p=$p&".$urlfilter;
    }

    return array($filter, $filterclause, $urlfilter);
}

function shop_get_customer_filtering() {
    global $SESSION;

    $shopid = optional_param('shopid', 0, PARAM_INT);
    $nopaging = optional_param('nopaging', 0, PARAM_BOOL);

    $filter = [];
    if ($shopid) {
        $filter['shopid'] = $shopid;
    }

    $filterclause = '';
    if ($shopid) {
        $filterclause .= " AND shopid = '{$shopid}' ";
    }

    $urlfilter = "shopid=$shopid&nopaging=$nopaging";

    return array($filter, $filterclause, $urlfilter);
}

/**
 * to fix some windows issues with strftime.
 */
function local_shop_strftimefixed($format, $timestamp=null) {
    global $CFG;

    if ($timestamp === null) $timestamp = time();

    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $format = preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);

        // Be carefull windows only uses a 2 letters locale.
        $locale = setlocale(LC_ALL, $CFG->lang);

        // This has been seen on some Win2012 server environments where the fr locale comes out in latin or Windows encding.
        return utf8_encode(strftime($format, $timestamp));
    }

    return strftime($format, $timestamp);
}

function shop_load_output_class($classname) {
    global $CFG;

    $parts = explode('\\', $classname);
    $classname = array_pop($parts);

    $classpath = $CFG->dirroot.'/local/shop/classes/output/'.$classname.'.class.php';
    include_once($classpath);
}
