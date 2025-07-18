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
 * An utility to manage the storage of a list in DB.
 * @package local_shop
 * @author Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @contributors LUU Tao Meng, So Gerard (parts of treelib.php), Guillaume Magnien, Olivier Petit
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

use StdClass;

/**
 * Library of list dedicated operations when stored in DB.
 * @suppressWarnings(PHPMD.ShortMethodName)
 */
class DBListUtils {

    /** @var The table containing the list */
    protected $table;

    /** @var The ordering field */
    protected $field;

    /** @var Context params to find the list in the table. */
    protected $params;

    /**
     * Constructor
     * @param string $table the table storing list items
     * @param string $field the ordering field  name
     * @param array $params the filtering context to get instances of a single list
     */
    public function __construct($table, $field, $params) {
        $this->table = $table;
        $this->field = $field;
        $this->params = $params;
    }

    /**
     * Pushes up an item in his own list context
     * @param int $id;
     */
    public function up($id) {
        global $DB;

        $res = $DB->get_record($this->table, ['id' => $id]);
        if (!$res) {
            return;
        }

        $field = $this->field;

        if ($res->$field > 1) {

            $selects = [];
            if (!empty($this->params)) {
                foreach ($this->params as $key => $value) {
                    $selects[] = " $key = ? ";
                    $sqlparams[] = $value;
                }
            }
            $selects[] = " $field = ? ";

            $newordering = $res->$field - 1;
            $sqlparams[] = $newordering;
            $select = implode(' AND ', $selects)." ORDER BY {$field}";
            if ($resid = $DB->get_field_select($this->table, 'id', $select, $sqlparams)) {
                // Swapping.
                $object = new StdClass();
                $object->id = $resid;
                $object->ordering = $res->ordering;
                $DB->update_record($this->table, $object);
            }

            $object = new StdClass();
            $object->id = $id;
            $object->ordering = $newordering;
            $DB->update_record($this->table, $object);
        }
    }

    /**
     * Pulls down an item in his own list context
     * @param int $id;
     */
    public function down($id) {
        global $DB;

        $field = $this->field;

        $selects = [];
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $selects[] = " $key = ? ";
                $sqlparams[] = $value;
            }
        }
        $select = implode(' AND ', $selects);
        $maxordering = $DB->get_field_select($this->table, " MAX({$field}) ", $select, $sqlparams);

        $selects[] = " $field = ? ";

        $res = $DB->get_record($this->table, ['id' => $id]);

        if ($res->$field < $maxordering) {
            $newordering = $res->$field + 1;
            $sqlparams[] = $newordering;
            $select = implode(' AND ', $selects);
            if ($resid = $DB->get_field_select($this->table, 'id', $select, $sqlparams)) {
                // Swapping.
                $object = new StdClass;
                $object->id = $resid;
                $object->$field = $res->$field;
                $DB->update_record($this->table, $object);
            }

            $object = new StdClass;
            $object->id = $id;
            $object->$field = $newordering;
            $DB->update_record($this->table, $object);
        }
    }

    /** 
     * Get the actual max ordering value
     * @return int
     */
    public function get_max_ordering(): int {
        global $DB;

        $field = $this->field;
        $lastordering = $DB->get_field($this->table, "MAX($field)", $this->params);
        return 0 + $lastordering;
    }

    /**
     * Reorder the list in the context. Ensures linear holeless ordering.
     * this is mostly a "repair" function.
     * @param int $from
     */
    public function reorder($from = 1) {
        global $DB;

        $allrecs = $DB->get_records($this->table, $this->params, $this->field);
        if (!empty($allrecs)) {
            foreach ($allrecs as $rec) {
                $DB->set_field($this->table, $this->field, $from, ['id' => $rec->id]);
                $from++;
            }
        }
    }

    /**
     * Get Up front link for pushing up.
     * @param int $id
     * @param int $ordering
     * @param miwed $baseurl
     */
    public function get_up_cmd($id, $ordering, $baseurl) {
        global $OUTPUT;

        $baseurl->params(['what' => 'up', 'id' => $id]);

        if ($ordering > 1) {
            return '<a href="'.$baseurl.'">'.$OUTPUT->pix_icon('t/up', '', 'moodle').'</a>';
        } else {
            return '<span class="shadowed">'.$OUTPUT->pix_icon('t/up', '', 'moodle').'</span>';
        }
    }

    /**
     * Get Up front link for pulling down.
     * @param int $id
     * @param int $ordering
     * @param miwed $baseurl
     */
    public function get_down_cmd($id, $ordering, $baseurl) {
        global $OUTPUT;

        $baseurl->params(['what' => 'down', 'id' => $id]);

        if ($ordering < $this->get_max_ordering()) {
            return '<a href="'.$baseurl.'">'.$OUTPUT->pix_icon('t/down', '', 'moodle').'</a>';
        } else {
            return '<span class="shadowed">'.$OUTPUT->pix_icon('t/down', '', 'moodle').'</span>';
        }
    }
}
