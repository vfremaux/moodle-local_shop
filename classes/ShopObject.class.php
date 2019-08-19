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
 * the common base class for all shop objects.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

/**
 * A shop object is a generic object that has record in DB
 */
class ShopObject {

    protected static $table;

    protected $record;

    public function __construct($recordorid, $recordtable) {
        global $DB;

        self::$table = $recordtable;

        if (empty($recordorid)) {
            $this->record = new \StdClass;
            $this->record->id = 0;
        } else if (is_numeric($recordorid)) {
            $this->record = $DB->get_record(self::$table, array('id' => $recordorid));
            if (!$this->record) {
                throw new \Exception('Missing record exception in table '.self::$table." for ID $recordorid ");
            }
        } else {
            $this->record = $recordorid;
        }
    }

    /**
     * magic getter
     * @param string $field
     */
    public function __get($field) {

        // Return raw record.
        if ($field == 'record') {
            return $this->record;
        }

        // Object field value will always prepend on deeper representation.
        if (isset($this->$field)) {
            return $this->$field;
        }

        if (isset($this->record->$field)) {
            return $this->record->$field;
        }
    }

    /**
     * magic setter. This allows not polluting DB records with ton
     * of irrelevant members
     * @param string $field
     * @param mixed $value
     */
    public function __set($field, $value) {

        if (empty($this->record)) {
            throw new \Exception("empty object");
        }

        if (property_exists($this->record, $field)) {
            if (method_exists($this, '_magic_set_'.$field)) {
                $fname = '_magic_set_'.$field;
                return $this->$fname($value);
            }
            $this->record->$field = $value;
        } else {
            $this->$field = $value;
        }

        return true;
    }

    /**
     * generic saving
     */
    public function save() {
        global $DB;

        $class = get_called_class();

        if (empty($this->record->id)) {
            $this->record->id = $DB->insert_record($class::$table, $this->record);
        } else {
            $DB->update_record($class::$table, $this->record);
        }
        return $this->record->id;
    }

    public function delete() {
        global $DB;

        $class = get_called_class();

        // Finally delete record.
        $DB->delete_records($class::$table, array('id' => $this->id));
    }

    static protected function _count($table, $filter = array()) {
        global $DB;

        return $DB->count_records($table, $filter);
    }

    /**
     * Get instances of the object. If some filtering is needed, override
     * this method providing a filter as input.
     * @param array $filter an array of specialized field filters
     * @return array of object instances keyed by primary id.
     */
    static protected function _get_instances($table, $filter = array(), $order = '',
                                             $fields = '*', $limitfrom = 0, $limitnum = '', $light = false) {
        global $DB;

        $params = array();
        $sql = "SELECT ";
        $sql .= $fields;
        $sql .= " FROM {{$table}} ";
        if (!empty($filter)) {
            $sql .= " WHERE ";
            foreach ($filter as $cond => $value) {
                if ($value == '*') {
                    continue;
                }
                $wheres[] = "$cond = ? ";
                $params[] = $value;
            }
            $sql .= implode(' AND ', $wheres);
        }
        if (!empty($order)) {
            $sql .= " ORDER BY $order ";
        }

        $records = $DB->get_records_sql($sql, $params, $limitfrom, $limitnum);
        $instances = array();
        if ($records) {
            $class = get_called_class();
            foreach ($records as $rec) {
                $instances[$rec->id] = new $class($rec, $light);
            }
        }

        return $instances;
    }

    /**
     * Get instances of the object. If some filtering is needed, override
     * this method providing a filter as input.
     * @param array $filter an array of specialized field filters
     * @return array of object instances keyed by primary id.
     */
    static protected function _count_instances($table, $filter = array(), $order = '', $fields = '*',
                                               $limitfrom = 0, $limitnum = '') {
        global $DB;

        $params = array();
        $sql = "SELECT COUNT(*) FROM {{$table}} ";
        if (!empty($filter)) {
            $sql .= " WHERE ";
            foreach ($filter as $cond => $value) {
                $wheres[] = "$cond = ? ";
                $params[] = $value;
            }
            $sql .= implode(' AND ', $wheres);
        }
        if (!empty($order)) {
            $sql .= " ORDER BY $order ";
        }

        $recordscount = $DB->count_records_sql($sql, $params, $limitfrom, $limitnum);

        return $recordscount;
    }

    /**
     * Sum calculable fields of object instances. If some filtering is needed, override
     * this method providing a filter as input.
     * @param array $filter an array of specialized field filters
     * @param string $field what field to sum on.
     * @return a single scalar summed value.
     */
    static protected function _sum($table, $field, $filter = array()) {
        global $DB;

        $params = array();
        $sql = "SELECT SUM({$field}) as summed FROM {{$table}} ";
        if (!empty($filter)) {
            $sql .= " WHERE ";
            foreach ($filter as $cond => $value) {
                if ($value == '*') {
                    continue;
                }
                $wheres[] = "$cond = ? ";
                $params[] = $value;
            }
            $sql .= implode(' AND ', $wheres);
        }

        $sumresult = $DB->get_record_sql($sql, $params);

        return 0 + $sumresult->summed;
    }

    /**
     * Get instances of the object. If some filtering is needed, override
     * this method providing a filter as input.
     * @param array $filter an array of specialized field filters
     * @return array of object instances keyed by primary id.
     */
    static protected function _get_instances_menu($table, $filter = array(), $order = '', $namefield = 'name', $chooseopt = 'choosedots') {
        global $DB;

        $menurecords = $DB->get_records_menu($table, $filter, $order, 'id,'.$namefield);
        if (empty($chooseopt)) {
            $instancemenu = array();
        } else {
            if ($chooseopt == 'choosedots') {
                $instancemenu = array(0 => get_string('choosedots'));
            } else {
                $instancemenu = array(0 => get_string($chooseopt, 'local_shop'));
            }
        }
        if ($menurecords) {
            foreach ($menurecords as $id => $name) {
                $instancemenu[$id] = format_string($name);
            }
        }
        return $instancemenu;
    }

    protected function export($level = 0) {

        $indent = str_repeat('    ', $level);

        $yml = '';
        if (!empty($this->record)) {
            foreach ($this->record as $key => $value) {
                $yml .= $indent.$key.': '.$value."\n";
            }
        }

        return $yml;
    }
}