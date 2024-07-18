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
 * Class for shipping definitions.
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
 * A catalogShippig object is a calculation object that applies for a given
 * product for a given geographic area.
 * the shipping applies either a value (simple way) indexed by quantity,
 * a formula may fix the shipping calculation, using three parameters a, b, c;
 */
class CatalogShipping extends ShopObject {

    /**
     * DB table (for ShopObject)
     */
    protected static $table = 'local_shop_catalogshipping';

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
            $this->record->productcode = '';
            $this->record->zoneid = 0;
            $this->record->value = 0;
            $this->record->formula = '';
            $this->record->a = 1;
            $this->record->b = 0;
            $this->record->c = 0;
        }
    }

    /**
     * Get products with shipping information.
     * @TODO : mature the shipping integration.
     * @param int $catalogid
     */
    public static function get_products_with_shipping($catalogid) {
        global $DB;

        $sql = "
            SELECT
                ci.id,
                ci.code,
                ci.shortname,
                ci.name,
                cs.*
            FROM
                {local_shop_catalogshipping} cs,
                {local_shop_catalogitem} ci
            WHERE
                ci.isset = 0 AND
                ci.catalogid = ? AND
                ci.code = cs.productcode
        ";

        return $DB->get_records($sql, [$catalogid]);
    }

    public static function count($filter) {
        parent::_count(self::$table, $filter);
    }

    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}
