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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

use StdClass;
use moodle_exception;

/**
 * A shop object is a generic object that has record in DB
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class ShopObject {

    /**
     * DB storage table name
     */
    protected static $table;

    /** 
     * Rehydrated record from DB.
     */
    protected $record;

    /**
     * Constructor
     * @param mixed $recordorid
     * @param string $recordtable
     */
    public function __construct($recordorid, $recordtable) {
        global $DB;

        self::$table = $recordtable;

        if (empty($recordorid)) {
            $this->record = new StdClass();
            $this->record->id = 0;
        } else if (is_numeric($recordorid)) {
            $this->record = $DB->get_record(self::$table, ['id' => $recordorid]);
            if (!$this->record) {
                throw new moodle_exception('Missing record exception in table '.self::$table." for ID $recordorid ");
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
            throw new moodle_exception("empty object");
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
     * Checks if an object instance exists in storage.
     * @param int $id
     * @param string $table
     */
    public static function exists($id, $table = '') {
        global $DB;

        if (empty($table)) {
            if (empty(self::$table)) {
                throw new coding_exception("Shop object exists :Internal table should have been initialized. Review coding.");
            } else {
                $table = self::$table;
            }
        } else {
            $table = 'local_shop_'.$table;
        }

        return $DB->record_exists($table, ['id' => $id]);
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

    /**
     * Deletes the object in DB
     */
    public function delete(): void {
        global $DB;

        $class = get_called_class();

        // Finally delete record.
        $DB->delete_records($class::$table, ['id' => $this->id]);
    }

    /**
     * Counts records in a scope given by filter
     * @param string $table
     * @param array $filter
     * @return int;
     */
    static protected function _count($table, $filter = []): int {
        global $DB;

        return 0 + $DB->count_records($table, $filter);
    }

    /**
     * Get instances of the object. If some filtering is needed, override
     * this method providing a filter as input.
     * @param array $filter an array of specialized field filters
     * @return array of object instances keyed by primary id.
     */
    static protected function _get_instances($table, $filter = [], $order = '',
                                             $fields = '*', $limitfrom = 0, $limitnum = '', $light = false, $internalrefs = []) {
        global $DB;

        $params = [];
        $sql = "SELECT ";
        $sql .= $fields;
        $sql .= " FROM {{$table}} ";
        if (!empty($filter)) {
            $sql .= " WHERE ";
            $wheres[] = ' 1 = 1 ';
            foreach ($filter as $cond => $value) {
                if ($value === '*') {
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
        $instances = [];
        if ($records) {
            $class = get_called_class();
            foreach ($records as $rec) {
                $instances[$rec->id] = new $class($rec, $light, $internalrefs);
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
    static protected function _count_instances($table, $filter = [],
                                               $limitfrom = 0, $limitnum = '') {
        global $DB;

        $params = [];
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
     * @param string $table the storing table
     * @param string $field what field to sum on.
     * @param array $filter an array of specialized field filters
     * @return a single scalar summed value.
     */
    static protected function _sum($table, $field, $filter = []) {
        global $DB;

        $params = [];
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
     * @param string $table the storing table
     * @param array $filter an array of specialized field filters
     * @param string $order
     * @param string $namefield
     * @param string $chooseopt
     * @return array of object instances keyed by primary id.
     */
    static protected function _get_instances_menu($table, $filter = [], $order = '', $namefield = 'name', $chooseopt = 'choosedots') {
        global $DB;

        $menurecords = $DB->get_records_menu($table, $filter, $order, 'id,'.$namefield);
        if (empty($chooseopt)) {
            $instancemenu = [];
        } else {
            if ($chooseopt == 'choosedots') {
                $instancemenu = [0 => get_string('choosedots')];
            } else {
                $instancemenu = [0 => get_string($chooseopt, 'local_shop')];
            }
        }
        if ($menurecords) {
            foreach ($menurecords as $id => $name) {
                $instancemenu[$id] = format_string($name);
            }
        }
        return $instancemenu;
    }

    /**
     * Get instances of the object. If some filtering is needed, override
     * this method providing a filter as input.
     * @param string $table the effective class db table
     * @param string $field the list driving field
     * @param array $values an array of values the filed must match
     * @param string $order order clause
     * @param string $fields fields to extract
     * @param bool $light do we want lightweight instances
     * @param array $internarefs
     * @return array of key/name pairs by primary id.
     */
    static protected function _get_instances_list($table, $field, array $values, $order = '', $fields = '*',
                                                                            $light = false, $internalrefs = []) {
        global $DB;

        $listrecords = $DB->get_records_list($table, $field, $values, $order, $fields);

        $instances = [];
        if ($listrecords) {
            $class = get_called_class();
            foreach ($listrecords as $rec) {
                $instances[$rec->id] = new $class($rec, $light, $internalrefs);
            }
        }
        return $instances;
    }

    /**
     * Exports to YML format
     * @param int $level the indent level
     */
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
