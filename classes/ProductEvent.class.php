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
 * A product has a lifecycle in which some events can occur.
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
 * A product event stores an event in the Product's lifecycle
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class ProductEvent extends ShopObject {

    /** @var DB table (for ShopObject) */
    protected static $table = 'local_shop_productevent';

    /**
     * Constructor
     * @param mixed $idorrecord
     * @param bool $light lightweight object (without categories) if true.
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
            $this->record->productid = 0;
            $this->record->billitemid = 0;
            $this->record->eventtype = 'billed';
            $this->record->datecreated = time();
        }
    }

    /**
     * ShopObject wrapper
     * @param array $filter
     * @param string $order
     * @param string $fields
     * @param int $limitfrom
     * @param int $limitnum
     */
    public static function get_instances($filter = [], $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }
}
