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
 * A category organises catalog items in a catalog.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');

/**
 * A catalog category has sub categories and / or catalog items.
 */
class Category extends ShopObject {

    /**
     * DB table (for ShopObject)
     */
    protected static $table = 'local_shop_catalogcategory';

    /**
     * The owning Catalog
     */
    protected $thecatalog;

    /**
     * Constructor
     * @param mixed $idorrecord
     * @param bool $light
     */
    public function __construct($idorrecord, $light = false) {

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) {
                // This builds a lightweight proxy of the Bill, without items.
                return;
            }
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->catalogid = $this->thecatalog->id;
            $this->record->name = get_string('newcategorylabel', 'local_shop');
            $this->record->description;
            $this->record->descriptionformat = FORMAT_HTML;
            $this->record->visible = true;
        }
    }

    /**
     * Get the category name
     */
    public function get_name() {
        return format_string($this->record->name);
    }

    /**
     * Get the category's parent name
     */
    public function get_parent_name() {
        global $DB;

        if ($this->perentid) {
            return format_string($DB->get_field('shop_catalog_category', 'name', ['id' => $this->parentid]));
        }
    }

    /**
     * This get all the upper branch from the current category up to the root.
     * @return an array of cat ids from the current on to the top.
     */
    public function get_branch() {
        global $DB;

        $branch = [$this->id];
        $parentid = $this->record->parentid;
        while ($parentid != 0) {
            $branch[] = $parentid;
            $parentid = $DB->get_field('local_shop_catalogcategory', 'parentid', ['id' => $parentid]);
        }

        return $branch;
    }

    /**
     * Export the category in YML format
     * @param int $level
     */
    public function export($level = 0) {

        $indent = str_repeat('    ', $level);

        $yml = '';
        $yml .= $indent.'category:'."\n";

        $level ++;
        $yml .= parent::export($level);
        $level--;

        return $yml;
    }

    /**
     * Is this category empty ?
     * @return boolean
     */
    public function is_empty() {
        global $DB;

        return !$DB->count_records('local_shop_catalogitem', ['categoryid' => $this->id]);
    }

    /**
     * Get the first non empty subcategory
     */
    public function get_first_non_empty_child() {
        global $DB;

        $sql = "
            SELECT
                cc.id,
                cc.name
            FROM
                {local_shop_catalogcategory} cc,
                {local_shop_catalogitem} ci
            WHERE
                cc.id = ci.categoryid AND
                cc.parentid = ?
            ORDER BY
                cc.sortorder
        ";

        if ($firstcat = $DB->get_records_sql($sql, [$this->id], 0, 1)) {
            $firstcatobj = array_shift($firstcat);
            return $firstcatobj->id;
        }

        return 0;
    }

    /**
     * Export for web services
     */
    public function export_to_ws() {
        $export = new Stdclass();

        $export->id = $this->record->id;
        $export->catalogid = $this->record->catalogid;
        $export->name = format_string($this->record->name);
        $export->description = format_text($this->record->description, $this->record->descriptionformat);
        $export->visible = $this->record->visible;

        return $export;
    }

    /**
     * Recurse down to fetch first deeper branch. Stops when no more childs are found.
     * @param int $catalogid
     * @param int $categoryid the current iteration parent
     * @return a branch list of categoryids from bottom to top formed with first category at each level.
     */
    public static function get_first_branch($catalogid, $categoryid = 0) {
        global $DB;

        $branch = [];
        $params = ['parentid' => $categoryid, 'catalogid' => $catalogid];
        // Get the first rec in order and follow th path.
        $recs = $DB->get_records('local_shop_catalogcategory', $params, 'sortorder', 'id, parentid', 0, 1);
        if ($recs) {
            $reckeys = array_keys($recs);
            $catid = array_shift($reckeys);
            $branch[] = $catid;
            $branch += self::get_first_branch($catalogid, $catid);
        }
        return $branch;
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * àparam int $limitnum
     */
    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     */
    public static function get_instances_menu($filter = [], $order = '') {
        return parent::_get_instances_menu(self::$table, $filter, $order, "name");
    }

    /**
     * Searches a catalogitem instance that matches a seoalias 
     * @param string $alias The catalogitem seoalias, should be unique if defined.
     */
    public static function instance_by_seoalias($alias) {
        global $DB;

        if (empty($alias)) {
            return null;
        }

        $intanceid = $DB->get_field('local_shop_catalogcategory', 'id', ['seoalias' => $alias]);
        if (!$intanceid) {
            return null;
        }

        return new Category($intanceid);
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     */
    public static function count($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_count_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    /**
     * Given a set of categories, and a current categoryid,
     * filter out all categories that are NOT parentable
     * @param arrayref $categories
     * @param int $currentcatid
     */
    public static function filter_parentable(&$categories, $currentcatid = 0) {
        global $DB;

        if (empty($categories)) {
            return;
        }

        if (!$currentcatid) {
            return;
        }

        foreach ($categories as $c) {

            $cid = $c->id;

            if ($c->id == $currentcatid) {
                unset($categories[$cid]);
                continue;
            }

            if ($c->parentid) {
                while ($c->parentid) {
                    $c = $DB->get_record('local_shop_catalogcategory', ['id' => $c->parentid], 'id,parentid');
                    if ($c->id == $currentcatid) {
                        unset($categories[$cid]);
                        break;
                    }
                }
            }
        }
    }
}
