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

/**
 * A Set is a set of products that may propose variants for a single product type.
 */
class Set extends ShopObject {

    static $table = 'local_shop_catalogitem';

    public function __construct($recordorid) {
        parent::__construct($recordorid, self::$table);
    }

    // Get the accurate price against quantity ranges.
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

    public function get_taxed_price($q, $tax) {
        static $TAXCACHE;
        global $DB;

        if (!isset($TAXCACHE)) {
            $TAXCACHE = array();
        }
        if (!array_key_exists($taxid, $TAXCACHE)) {
            if ($TAXCACHE[$taxid] = $DB->get_record('local_shop_tax', array('id' => $taxid))) {
                if (empty($TAXCACHE[$taxid]->formula)) $TAXCACHE[$taxid]->formula = '$TTC = $HT';
            } else {
                return $htprice;
            }
        }
        $HT = $this->get_price($q);
        $TR = $TAXCACHE[$taxid]->ratio;
        eval($TAXCACHE[$taxid]->formula.';');
        return $TTC;
    }
}