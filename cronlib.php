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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\cron;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\CatalogItem;
use local_shop\Customer;
use context_system;
use StdClass;

/**
 * A class to handle all cron jobs.
 */
class manager {

    /**
     * inspects instanciated products, get associated handlers and run cron function if exists
     * To lower the load of this review, product will NOT be considered after their enddate + 'one day' value
     * so designers must ensure "die time" handling is done in this delay.
     */
    public function cron_task() {
        global $CFG, $DB;

        $horizon = time() - DAYSECS;
        $sql = "
            SELECT
                p.id,
                p.startdate,
                p.enddate,
                p.customerid,
                c.hasaccount as userid,
                p.reference,
                ci.renewable,
                ci.name,
                ci.code,
                ci.enablehandler
            FROM
                {local_shop_product} p,
                {local_shop_customer} c,
                {local_shop_catalogitem} ci
            WHERE
                p.catalogitemid = ci.id AND
                p.customerid = c.id AND
                (p.enddate > $horizon OR p.enddate = 0)
        ";
        if ($eligibles = $DB->get_records_sql($sql)) {
            foreach ($eligibles as $p) {
                $phandlerclassfile = $p->enablehandler.'/'.$p->enablehandler.'.class.php';
                $phandlerclass = 'shop_handler_'.$p->enablehandler;
                if ($p->enablehandler) {
                    include_once($CFG->dirroot.'/local/shop/datahandling/handlers/'.$phandlerclassfile);
                    $handler = new $phandlerclass('');
                    if (method_exists($handler, 'cron')) {
                        $handler->cron($p);
                    }
                }
            }
        }
    }

    /**
     * Daily notifications by cron
     */
    public function notify_daily_task() {
        global $DB;

        // Notify expired from short time.
        $select = ' enddate >= ? AND enddate < ? AND deleted = 0 ';
        $params = [time(), time() + 3 * DAYSECS];
        $productinstances = $DB->get_records_select('local_shop_product', $select, $params);

        $instancereport = $this->compile_instances($productinstances);
        if ($instancereport) {
            $instancereporthtml = $this->compile_instances($productinstances, '<br/>');
            $a = format_string($SITE->shortname);
            $title = get_string('justexpired', 'local_shop', $a);
            $params = ['view' => 'viewAllProducts', 'id' => $theshop->id ?? 1];
            $managerurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
            $b = new StdClass;
            $b->url = $managerurl->out();
            $b->list = $instancereport;
            $text = get_string('justexpired_mail', 'local_shop', $b);
            $c = new StdClass;
            $c->url = $managerurl->out();
            $c->list = $instancereporthtml;
            $html = get_string('justexpired_html', 'local_shop', $c);
            $this->send_notification($title, $text, $html);
        }

        // Notify near to expire in week.
        $select = ' enddate >= ? AND enddate < ? AND deleted = 0 ';
        $params = [time() - WEEKSECS, time()];
        $productinstances = $DB->get_records_select('local_shop_product', $select, $params);

        $instancereport = $this->compile_instances($productinstances);
        if ($instancereport) {
            $instancereporthtml = $this->compile_instances($productinstances, '<br/>');
            $a = format_string($SITE->shortname);
            $title = get_string('neartoexpire', 'local_shop', $a);
            $params = ['view' => 'viewAllProducts', 'id' => 1];
            $managerurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
            $b = new StdClass();
            $b->url = $managerurl->out();
            $b->list = $instancereport;
            $text = get_string('neartoexpire_mail', 'local_shop', $b);
            $c = new StdClass();
            $c->url = $managerurl->out();
            $c->list = $instancereporthtml;
            $html = get_string('neartoexpire_html', 'local_shop', $c);
            $this->send_notification($title, $text, $html);
        }
    }

    /**
     * Weekly notifications.
     */
    public function notify_weekly_task() {
        global $DB, $SITE;

        // Notify expired from long time.
        $select = ' enddate >= ? AND enddate < ? AND deleted = 0 ';
        $params = [time(), time() + 3 * DAYSECS];
        $productinstances = $DB->get_records_select('local_shop_products', $select, $params);

        $instancereport = $this->compile_instances($productinstances);
        if ($instancereport) {
            $instancereporthtml = $this->compile_instances($productinstances, '<br/>');
            $a = format_string($SITE->shortname);
            $title = get_string('longtimeexpired', 'local_shop', $a);
            $params = ['view' => 'viewAllProducts', 'id' => 1];
            $managerurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
            $b = new StdClass();
            $b->url = $managerurl->out();
            $b->list = $instancereport;
            $text = get_string('longtimeexpired_mail', 'local_shop', $b);
            $c = new StdClass();
            $c->url = $managerurl->out();
            $c->list = $instancereporthtml;
            $html = get_string('longtimeexpired_html', 'local_shop', $c);
            $this->send_notification($title, $text, $html);
        }
    }

    /**
     * Process all instances. Optimized to build objects only once in memory,
     * then forget everything once done.
     * @param array $instances process instances by adding info in product objects.
     */
    function compile_instances($instances, $linesep = "\n") {

        $shops = [];
        $catalogs = [];
        $catalogitems = [];
        $customers = [];

        $instancerecs = [];

        if (!empty($instances)) {
            foreach ($instances as &$pi) {
                if (!array_key_exists($pi->catalogitemid, $catalogitems)) {
                    $ci = new CatalogItem($pi->catalogitemid, true); /* lightweight */
                } else {
                    $ci = $catalogitems[$pi->catalogitemid];
                }
                if (!array_key_exists($ci->catalogid, $catalogs)) {
                    $c = new Catalog($ci->catalogid, true); /* lightweight */
                } else {
                    $c = $catalogs[$ci->catalogid];
                }
                if (!array_key_exists($c->shopid, $shops)) {
                    $s = new Shop($c->shopid, true); /* lightweight */
                } else {
                    $s = $shops[$c->shopid];
                }
                if (!array_key_exists($pi->customerid, $customers)) {
                    $cu = new Customer($pi->customerid);
                } else {
                    $cu = $customers[$pi->customerid];
                }

                $instancerecs[] = $ci->code.' '.$ci->name.' / Instance : '.$pi->reference.' / Shop : '.$s->name.' / Customer : '.$cu->firstname.' '.$cu->lastname. '('.$cu->organisation.')';
            }

            return implode($linesep, $instancerecs);
        }

        return false;
    }

    /**
     * Send notifications to all sales admins.
     * @param string $title notification caption
     * @param string $str notification content
     */
    function send_notification($title, $str) {
        $admin = get_site_admin();
        $systemcontext = context_system::instance();
        $sales = get_users_by_capability($systemcontext, 'local/shop:salesadmin');
        foreach ($sales as $saleadmin) {
            email_to_user($saleadmin, $admin, $title, $str);
        }
    }
}
