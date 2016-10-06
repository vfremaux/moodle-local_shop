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
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * inspects instanciated products, get associated handlers and run cron function if exists
 * To lower the load of this review, product will NOT be considered after their enddate + 'one day' value
 * so designers must ensure "die time" handling is done in this delay.
 */
function local_shop_cron_task() {
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
