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

class Tax extends ShopObject {

    static $table = 'local_shop_tax';

    function __construct($idorrecord, $light = false) {
        global $DB;

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) return; // this builds a lightweight proxy of the Bill, without items
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->country = '';
            $this->record->title = '';
            $this->record->ratio = '';
            $this->record->formula = '';
        }
    }

    function delete() {
        parent::delete();
    }

    static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    /**
     * Used by json ajax handler
     */
    static function get_json_taxset() {
        global $DB;

        $taxarr = array();
        if ($taxes = $DB->get_records('local_shop_tax')) {
            foreach ($taxes as $tax) {
                $taxarr[$tax->id] = array('ratio' => $tax->ratio, 'formula' => str_replace('$', '', $tax->formula));
            }
        }
        return json_encode($taxarr);
    }
}