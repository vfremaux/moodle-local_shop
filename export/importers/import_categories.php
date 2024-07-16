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
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class shop_import_categories {

    /** @var string table name */
    protected $table;

    /** @var object data object */
    protected $data;

    /** @var array overrides declarations */
    protected $overrides;

    /**
     * Constructor
     * @param string $table
     * @param object $data
     */
    public function __construct($table, $data) {
        $this->table = $table;
        $this->data = $data;
        $this->overrides = [];
    }

    /**
     * Overrides accessor
     * @param array $overrides 
     */
    public function set_overrides($overrides) {
        $this->overrides = $overrides;
    }

    /**
     * Import data
     */
    public function import() {
        global $DB;

        foreach ($this->data as $object) {

            $results = [];

            foreach ($this->overrides as $ovk => $ovv) {
                $object->$ovk = $ovv;
                $object->id = $DB->insert_record($this->table, $object);
                $results[$object->id] = $object;
            }
        }

        return $results;
    }
}
