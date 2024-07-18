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
 * A set is a group of products that are displayed alltogether.
 * this is an helper class.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

/**
 * A Set is a set of products that may propose variants for a single product type.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class Set extends ShopObject {

    /**
     * DB table (for ShopObject)
     */
    protected static $table = 'local_shop_catalogitem';

    /**
     * Constructor
     * @param mixed $idorrecord
     * @param bool $light lightweight object (without categories) if true.
     */
    public function __construct($recordorid, $light = false) {
        parent::__construct($recordorid, self::$table);
    }

    /**
     * Get the accurate price against quantity ranges.
     * @param int $q
     */
    public function get_price($q) {
        if ($this->catalogitem->range1) {
            if ($q < $this->catalogitem->range1) {
                return $this->catalogitem->price1;
            }
            if ($this->catalogitem->range2) {
                if ($q < $this->catalogitem->range2) {
                    return $this->catalogitem->price2;
                }
                if ($this->catalogitem->range3) {
                    if ($q < $this->catalogitem->range3) {
                        return $this->catalogitem->price3;
                    }
                    if ($this->catalogitem->range4) {
                        if ($q < $this->catalogitem->range4) {
                            return $this->catalogitem->price4;
                        }
                        if ($this->catalogitem->range4) {
                            if ($q < $this->catalogitem->range4) {
                                return $this->catalogitem->price4;
                            } else {
                                return $this->catalogitem->price5;
                            }
                        } else {
                            return $this->catalogitem->price4;
                        }
                    } else {
                        return $this->catalogitem->price4;
                    }
                } else {
                    return $this->catalogitem->price3;
                }
            } else {
                return $this->catalogitem->price2;
            }
        } else {
            return $this->catalogitem->price1;
        }
    }

    /**
     * Get full set taxed prince
     * @param int $q
     * @param int $taxid
     */
    public function get_taxed_price($q, $taxid) {
        global $DB, $CFG;
        static $taxcache;

        if (!isset($taxcache)) {
            $taxcache = [];
        }

        if (!array_key_exists($taxid, $taxcache)) {
            if ($taxcache[$taxid] = $DB->get_record('local_shop_tax', ['id' => $taxid])) {
                if (empty($taxcache[$taxid]->formula)) {
                    $taxcache[$taxid]->formula = '$ttc = $ht';
                }
            } else {
                return $this->get_price($q);
            }
        }
        $in['ht'] = $this->get_price($q);
        $in['tr'] = $taxcache[$taxid]->ratio;
        require_once($CFG->dirroot.'/local/shop/extlib/extralib.php');
        $result = evaluate(\core_text::strtolower($taxcache[$taxid]->formula).';', $in, 'ttc');
        return $result['ttc'];
    }
}
