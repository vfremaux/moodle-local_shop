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

class UnconditionalDiscount extends Discount {

    /**
     * Each Discount subclass will emplement its own applicability algorithm
     */
    public function check_applicability(&$bill = null) {

        $this->checked = true;

        $productiondata = new StdClass;
        $productiondata->code = 'INC'.$this->id;
        $productiondata->ratio = $this->ratio;
        $this->productiondata = $productiondata;
        return true;
    }

    /**
     * Each Discount subclass will implement its own applicability algorithm for preview
     */
    public function preview_applicability() {
        return true;
    }

    /**
     * Unconditinal just prints the discount argument.
     */
    public function interactive_form() {
        $formtpl = new Stdclass;
        $formtpl->label = $this->argument;
        return $formtpl;
    }
}