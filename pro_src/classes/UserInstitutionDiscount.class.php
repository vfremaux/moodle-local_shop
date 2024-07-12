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

class UserInstitutionDiscount extends Discount {

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

        $this->checked;

        if (empty($bill->customeruser)) {
            // unlogged and unregistered yet user. Has no institution known.
            return false;
        }

        $acceptedinstitutions = explode(',', $this->ruledata);
        foreach ($acceptedinstitutions as $inst) {
            $inst = trim($inst);

            if (empty($inst)) {
                // Case of accidental null length input.
                continue;
            }

            if (isloggedin()) {
                if (trim($bill->customeruser->institution) == $inst) {
                    // First true matches the rule.
                    $productiondata = new StdClass;
                    $productiondata->code = 'UINST'.$this->id;
                    $productiondata->ratio = $this->ratio;
                    $this->productiondata = $productiondata;
                    return true;
                }
            } else {
                if (empty($SESSION->shoppingcart->customerinfo['organisation'])) {
                    continue;
                }
                if (trim($SESSION->shoppingcart->customerinfo['organisation']) == $inst) {
                    // First true matches the rule.
                    $productiondata = new StdClass;
                    $productiondata->code = 'UINST'.$this->id;
                    $productiondata->ratio = $this->ratio;
                    $this->productiondata = $productiondata;
                    return true;
                }
            }
        }

        return false;
    }

    public function preview_applicability() {
        global $SESSION, $USER;

        $acceptedinstitutions = explode(',', $this->ruledata);
        foreach ($acceptedinstitutions as $inst) {
            $inst = trim($inst);

            if (empty($inst)) {
                // Case of accidental null length input.
                continue;
            }

            if (isloggedin()) {
                if (trim($USER->institution) == $inst) {
                    // First true matches the rule.
                    return true;
                }
            } else {
                if (empty($SESSION->shoppingcart->customerinfo['organisation'])) {
                    continue;
                }
                if (trim($SESSION->shoppingcart->customerinfo['organisation']) == $inst) {
                    // First true matches the rule.
                    return true;
                }
            }
        }

        return false;
    }
}