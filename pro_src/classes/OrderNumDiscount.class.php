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
     * User Institution checks if the current customer identity is presenting.
     * If logged in, we can check in $bill attached customeruser.
     *
     * If not logged in we may fallback on the $SESSION purchase info container
     *
     * an institution matching the ruledata field.
     */
    public function check_applicability(&$bill = null) {
        global $SESSION;

        $orderquant = $this->applydata; // Expects a numnber of passed bills.

        if (isloggedin()) {
            $soldoutbillcount = Bill::count(['status' => SHOP_BILL_SOLDOUT, 'customerid' => $bill->customer->id]);
            $completebillcount = Bill::count(['status' => SHOP_BILL_COMPLETE, 'customerid' => $bill->customer->id]);

            if (($oldbillcount + $completebillcount) > $orderquant) {
                $this->checked = true;
                $productiondata = new StdClass;
                $productiondata->code = 'ODN'.$this->id;
                $productiondata->ratio = $this->ratio;
                $this->productiondata = $productiondata;
                return true;
            }
        }

        $this->checked = true;

        return false;
    }

    public function preview_applicability() {
        // to develop.
        return false;
    }
}