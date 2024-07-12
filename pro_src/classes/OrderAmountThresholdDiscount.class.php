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

class OrderNumDiscount extends Discount {

    /**
     * Checks global undiscounted amount threshold.
     *
     * an institution matching the ruledata field.
     */
    public function check_applicability(&$bill = null) {

        $orderamountthreshold = $this->applydata; // Expects a numnber of passed bills.

        if ($bill->amount > $orderamountthreshold) {
            return true;
        }

        return false;
    }

    public function preview_applicability() {
        // TODO :  to develop, check in session.
        return false;
    }
}