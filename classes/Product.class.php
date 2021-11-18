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
 * A product is the concrete realisation of a catalogitem.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

class Product extends ShopObject {

    protected static $table = 'local_shop_product';

    /**
     * Build a full product instance.
     * @param mixed $idorrecord an integer product id or the full database record
     * @param bool $light future use. Switch to light proxy object initialisation.
     */
    public function __construct($idorrecord, $light = false) {

        $config = get_config('local_shop');

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) {
                // This builds a lightweight proxy of the Bill, without items.
                return;
            }
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->catalogitemid = 0;
            $this->record->initialbillitemid = 0;
            $this->record->currentbillitemid = 0;
            $this->record->customerid = 0;
            $this->record->contexttype = '';
            $this->record->instanceid = 0;
            $this->record->startdate = time();
            $this->record->enddate = 0;
            $this->record->reference = '';
            $this->record->productiondata = '';
            $this->record->extradata = '';
            $this->record->test = $config->test;

            $this->save();
        }
    }

    public static function instance_by_reference($reference, $light = false) {
        global $DB;

        if ($productrec = $DB->get_record('local_shop_product', array('reference' => $reference))) {
            return new Product($productrec, $light);
        }

        return null;
    }

    /**
     * Full deletes the product instance with all product events
     */
    public function delete() {
        // Dismount product effect in Moodle using delete method of the attached product handler.
        list($handler, $method) = $this->get_handler_info(null);

        if (!is_null($handler)) {
            $handler->delete($this);
        }

        // Delete all events linked to product.
        $events = ProductEvent::get_instances(array('productid' => $this->id));
        if ($events) {
            foreach ($events as $e) {
                $e->delete();
            }
        }

        parent::delete();
    }

    public function soft_delete() {
        $this->record->deleted = 1;
        $this->save(true);

        // Dismount product effect in Moodle using soft_delete method of the attached product handler.
        list($handler, $method) = $this->get_handler_info(null);

        if (!is_null($handler)) {
            $handler->soft_delete($this);
        }

        // Record an event.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = 0;
        $productevent->datecreated = $now = time();
        $productevent->eventtype = 'delete';
        $productevent->save();
    }

    public function soft_restore() {
        $this->record->deleted = 0;
        $this->save(true);

        // Restores product effect in Moodle using soft_restore method of the attached product handler.
        list($handler, $method) = $this->get_handler_info(null);

        if (!is_null($handler)) {
            $handler->soft_restore($this);
        }

        // Record an event.
        $productevent = new ProductEvent(null);
        $productevent->productid = $product->id;
        $productevent->billitemid = 0;
        $productevent->datecreated = $now = time();
        $productevent->eventtype = 'restore';
        $productevent->save();
    }

    /**
     * get info out of extra data (in product)
     * @return an object
     */
    public function get_extra_data() {

        $info = null;

        if (!empty($this->extradata)) {
            $info = json_decode($this->extradata);
        }

        return $info;
    }

    /**
     * get info out of extra data (in product)
     * @return an object
     */
    public function set_extra_data($data) {
        $this->extradata = json_encode($data);
        $this->save();
    }

    /**
     * Aggregates param arrays into an urlencoded string for storage into DB
     * @param array $data1 a stub of params as an array
     * @param array $data2 a stub of params as an array
     * @param array $data3 a stub of params as an array
     * @return string
     */
    public static function compile_production_data($data1, $data2 = null, $data3 = null) {

        $pairs = array();
        foreach ($data1 as $key => $value) {
            $pairs[] = "$key=".urlencode($value);
        }

        // Aggregates $data2 if given.
        if (is_array($data2)) {
            foreach ($data2 as $key => $value) {
                $pairs[] = "$key=".urlencode($value);
            }
        }

        // Aggregates $data3 if given.
        if (is_array($data3)) {
            foreach ($data3 as $key => $value) {
                $pairs[] = "$key=".urlencode($value);
            }
        }

        return implode('&', $pairs);
    }

    /**
     * Defers to underlying catalogitem the request for info about handler
     */
    public function get_handler_info($method, $type = 'postprod') {
        if (CatalogItem::exists($this->record->catalogitemid)) {
            $ci = new CatalogItem($this->record->catalogitemid);
            return $ci->get_handler_info($method, $type);
        }
        return [null, null];
    }

    public static function count($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_count_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function get_instances_on_context($filter, $order = '', $limitfrom = 0, $limitnum = '') {
        global $DB;

        $filterclause = '';
        $params = array();
        if (!empty($filter)) {
            foreach ($filter as $k => $v) {
                $filterstrs[] = " $k = ? ";
                $params[] = $v;
            }
            $filterclause = ' AND '.implode(' AND ', $filterstrs);
        }

        $orderclause = '';
        if (!empty($order)) {
            $orderclause = " ORDER BY $order ";
        }

        $sql = '
            SELECT
                p.*
            FROM
                {local_shop_catalogitem} ci,
                {local_shop_product} p
            LEFT JOIN
                {local_shop_billitem} ibi
            ON
                p.initialbillitemid = ibi.id
            LEFT JOIN
                {local_shop_billitem} cbi
            ON
                p.currentbillitemid = cbi.id
            WHERE
                p.catalogitemid = ci.id
                '.$filterclause.'
            '.$orderclause.'
        ';

        $records = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);

        $results = array();
        if (!empty($records)) {
            foreach ($records as $rid => $rec) {
                $results[$rid] = new Product($rec);
            }
        }

        return $results;
    }

    /**
     * Provides a direct link to the concerned instance for a product.
     * This may depend on the context type addressed by the product instance.
     * @return string a moodle url.
     */
    public function get_instance_link() {
        global $DB;

        $link = '';

        switch ($this->contexttype) {
            case 'enrol':
                $sql = "
                    SELECT
                       e.courseid,
                       c.shortname,
                       c.fullname
                    FROM
                        {user_enrolments} ue,
                        {enrol} e,
                        {course} c
                    WHERE
                        ue.enrolid = e.id AND
                        ue.id = ? AND
                        e.courseid = c.id AND
                        e.enrol = 'manual'
                ";
                if ($enrol = $DB->get_record_sql($sql, array($this->instanceid))) {
                    $courseurl = new \moodle_url('/course/view.php', array('id' => $enrol->courseid));
                    $link = \html_writer::tag('a', format_string($enrol->fullname), array('href' => $courseurl));
                }
                break;

            case 'course':
                $course = $DB->get_record('course', array('id' => $this->instanceid));
                $courseurl = new \moodle_url('/course/view.php', array('id' => $course->id));
                $link = \html_writer::tag('a', format_string($enrol->fullname), array('href' => $courseurl));
                break;

            case 'coursecat':
                $coursecat = $DB->get_record('course_categories', array('id' => $this->instanceid));
                $coursecaturl = new \moodle_url('/course/management.php', array('categoryid' => $coursecat->id));
                $link = \html_writer::tag('a', format_string($coursecat->name), array('href' => $coursecaturl));
                break;

            case 'attempt':
                $cm = $DB->get_record('course_module', array('id' => $this->instanceid));
                $module = $DB->get_record('module', array('id' => $cm->moduleid));
                $activity = $DB->get_record($module->name, array('id' => $cm->instanceid));
                $activityurl = new \moodle_url('/mod/'.$module->name.'/view.php', array('id' => $cm->id));
                $link = \html_writer::tag('a', format_string($activity->name), array('href' => $activityurl));
        }

        return $link;
    }

    /**
     * Retreives the current billitem object. The current bill item is the
     * most recently involved billitem in the product instance.
     * @return a BillItem object
     */
    public function get_current_billitem() {
        return new BillItem($this->currentbillitemid);
    }
}