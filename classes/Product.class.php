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

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/ProductEvent.class.php');

class Product extends ShopObject {

    static $table = 'local_shop_product';

    // Build a full bill plus billitems.
    function __construct($idorrecord, $light = false) {
        global $DB;

        $config = get_config('local_shop');

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) return; // this builds a lightweight proxy of the Bill, without items
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
            $this->record->test = $config->test;

            $this->save();
        }
    }

    function delete() {
        // Delete all events linked to product
        $events = ProductEvent::get_instances(array('productid' => $this->id));
        if ($events) {
            foreach ($events as $e) {
                $e->delete();
            }
        }

        parent::delete();
    }

    /**
     * get info out of production data (in product)
     *
     */
    function extract_production_data() {

        $info = new \StdClass();

        if ($pairs = explode('&', $this->productiondata)) {
            foreach ($pairs as $pair) {
                list($key, $value) = explode('=', $pair);
                $info->$key = $value;
            }
        }

        return $info;
    }

    /*
     * Aggregates param arrays into an urlencoded string for storage into DB
     * @param array $data1 a stub of params as an array
     * @param array $data2 a stub of params as an array
     * @param array $data3 a stub of params as an array
     * @return string
     */
    static function compile_production_data($data1, $data2 = null, $data3 = null) {

        $pairs = array();
        foreach ($data1 as $key => $value) {
            $pairs[] = "$key=".urlencode($value);
        }

        // aggregates $data2 if given
        if (is_array($data2)) {
            foreach ($data2 as $key => $value) {
                $pairs[] = "$key=".urlencode($value);
            }
        }
    
        // aggregates $data3 if given
        if (is_array($data3)) {
            foreach ($data3 as $key => $value) {
                $pairs[] = "$key=".urlencode($value);
            }
        }

        return implode('&', $pairs);
    }

    static function count($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_count_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    static function get_instances_on_context($filter, $order = '', $limitfrom = 0, $limitnum = '') {
        global $DB;

        $filterclause = '';
        $params = array();
        if (!empty($filter)) {
            foreach ($filter as $k => $v) {
                $filterstrs[] = "$k = ?";
                $params[] = $v;
            }
            $filterclause = ' AND '.implode(' AND ', $filterstrs);
        }

        $sql = '
            SELECT
                p.*
            FROM
                {local_shop_catalogitem} ci,
                {local_shop_billitem} ibi,
                {local_shop_product} p
            LEFT JOIN
                {local_shop_billitem} cbi
            ON
                p.currentbillitemid = cbi.id
            WHERE
                p.catalogitemid = ci.id AND
                p.initialbillitemid = ibi.id
                '.$filterclause.'
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

    function get_instance_link() {
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
                $link = \html_writer::tag('a', format_string($coursecat->name), array('href' => $courseurl));
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
}