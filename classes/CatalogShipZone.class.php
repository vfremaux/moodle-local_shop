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
 * A shipzone describes a geographic area where a shipping cost applies.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @todo : check class against shopobject model
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

/**
 * CatalogShipZone object is provided for direct Object Mapping of the _catalogshipzone database model
 * A Shipzone is reprensents a geographic area that may have influence on shipping calculation.
 */
class CatalogShipZone extends ShopObject {

    protected static $table = 'local_shop_catalogshipzone';

    public function __construct($idorrecord = '', $light = false) {

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($light) {
                // This builds a lightweight proxy of the Bill, without items.
                return;
            }
        } else {
            $this->record->catalogid = 0;
            $this->record->zonecode = '';
            $this->record->description = '';
            $this->record->billscopeamount = 0;
            $this->record->taxid = 0;
            $this->record->applicability = '';
        }
    }

    /**
     *
     */
    public function get_zones($catalogid = null) {
        global $DB;

        if ($catalogid) {
            $zonerecs = $DB->get_records(self::$table, array('catalogid' => $catalogid));
        } else {
            $zonerecs = $DB->get_records(self::$table, array());
        }

        $zones = array();
        if (!empty($zonerecs)) {
            foreach ($zonerecs as $zone) {
                $zones[$zone->id] = new CatalogShipZone($zone);
            }
        }

        return $zones;
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}