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

class Category extends ShopObject {

    protected static $table = 'local_shop_catalogcategory';

    protected $thecatalog;

    public function __construct($idorrecord, $light = false) {
        global $DB;

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) {
                return; // This builds a lightweight proxy of the Bill, without items.
            }
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->catalogid = $this->thecatalog->id;
            $this->record->name = get_string('newcategorylabel', 'local_shop');
            $this->record->description;
            $this->record->descriptionformat = FORMAT_HTML;
        }
    }

    public function get_branch() {
        global $DB;

        $branch = array($this->id);
        $parentid = $this->record->parentid;
        while ($parentid != 0) {
            $branch[] = $parentid;
            $parentid = $DB->get_field('local_shop_catalogcategory', 'parentid', array('id' => $parentid));
        }

        return $branch;
    }

    /**
     * Recurse down to fetch first deeper branch. Stops when no more childs are found.
     * @param int $catalogid
     * @param int $categoryid the current iteration parent
     */
    public static function get_first_branch($catalogid, $categoryid = 0) {
        global $DB;

        $branch = array();
        $recs = $DB->get_records('local_shop_catalogcategory', array('parentid' => $categoryid, 'catalogid' => $catalogid), 'sortorder', 'id,parentid', 0, 1);
        if ($recs) {
            $reckeys = array_keys($recs);
            $catid = array_shift($reckeys);
            $branch[] = $catid;
            $branch += self::get_first_branch($catalogid, $catid);
        }
        return $branch;
    }

    public function delete() {
        parent::delete();
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function count($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_count_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    /**
     * Geven a set of categories, and a current categoryid,
     * filter out all categories that are NOT parentable
     */
    public static function filter_parentable(&$categories, $currentcat = 0) {
        global $DB;

        if (empty($categories)) {
            return;
        }
        if (!$currentcat) {
            return;
        }

        foreach ($categories as $c) {

            if ($c->id == $currentcat) {
                unset($categories[$cid]);
                continue;
            }

            $cid = $c->id;
            if ($c->parentid) {
                while ($c->parentid) {
                    $c = $DB->get_record('local_shop_catalogcategory', array('id' => $c->parentid), 'id,parentid');
                    if ($c->id == $currentcat) {
                        unset($categories[$cid]);
                        break;
                    }
                }
            }
        }

    }
}