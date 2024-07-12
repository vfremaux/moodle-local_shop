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
 * class for disconunt instances.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');

use StdClass;
use context_system;
use context;

class UserCapabilityDiscount extends Discount {

    /**
     * Checks user has capability.
     *
     * an institution matching the ruledata field.
     */
    public function check_applicability(&$bill = null) {
        global $USER;

        $this->checked = true;

        /*
         * Expects a structure as:
         * {"capability":"local/shop:discountagreed"}
         * {"capability":"local/shop:seconddiscountagreed"}
         * {"capability":"local/shop:thirddiscountagreed"}
         * but also any other capability works.
         * optionnaly:
         * {"capability":"local/shop:discountagreed", "contextid":"<id>"}
         */
        $config = json_decode($this->applydata);

        if (empty($config)) {
            return false;
        }

        if (empty($USER)) {
            return false;
        }

        if (empty($config->contextid)) {
            $context = context_system::instance();
        } else {
            $context = context::instance_by_id($config->contextid);
        }

        if (has_capability($config->capability, $context, $USER->id)) {
            $productiondata = new StdClass;
            $productiondata->code = 'UCAP'.$this->id;
            $productiondata->ratio = $this->ratio;
            $this->productiondata = $productiondata;
            return true;
        }

        return false;
    }

    /**
     * Each Discount subclass will emplement its own applicability algorithm for preview
     */
    public function preview_applicability() {
        return $this->check_applicability(null);
    }
}