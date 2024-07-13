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
 * A tax instance applies for a country.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');

/**
 * Tax
 */
class Tax extends ShopObject {

    /** @var the storage table */
    protected static $table = 'local_shop_tax';

    /**
     * Constructor
     * @param mixed $idorrecord
     * @param bool $light if true builds a lightweight object
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
            $this->record->country = '';
            $this->record->title = '';
            $this->record->ratio = '';
            $this->record->formula = '';
        }
    }

    /**
     * Wrapper to ShopObject
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     */
    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    /**
     * Used by json ajax handler
     */
    public static function get_json_taxset() {
        global $DB;

        $taxarr = [];
        if ($taxes = $DB->get_records('local_shop_tax')) {
            foreach ($taxes as $tax) {
                $taxarr[$tax->id] = ['ratio' => $tax->ratio, 'formula' => str_replace('$', '', $tax->formula)];
            }
        }
        return json_encode($taxarr);
    }

    /**
     * Wrapper to ShopObject
     * @param array $filter
     * @param string $order
     * @param string $chooseopt text for "choose" option
     */
    public static function get_instances_menu($filter = [], $order = '', $chooseopt = 'choosedots') {
        return parent::_get_instances_menu(self::$table, $filter, $order, 'title', $chooseopt);
    }
}
