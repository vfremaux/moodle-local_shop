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

namespace local_shop\output;

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

defined('MOODLE_INTERNAL') || die();

use Templatable;
use StdClass;
use local_shop\Bill;
use renderer_base;

/**
 * Paymode info
 */
class front_paymode implements Templatable {

    /** @var the bill */
    protected $bill;

    /**
     * Constructor
     * @param Bill $bill
     */
    public function __construct(Bill $bill) {
        $this->bill = $bill;
    }

    /**
     * Exporter for template
     * @param renderer_base $output unused
     */
    public function export_for_template(renderer_base $output /* unused */) {
        global $CFG;

        $afullbill = $this->bill;

        $template = new StdClass();
        include_once($CFG->dirroot.'/local/shop/paymodes/'.$afullbill->paymode.'/'.$afullbill->paymode.'.class.php');

        $classname = 'shop_paymode_'.$afullbill->paymode;

        $pm = new $classname($afullbill->theshop);
        $template->paymode = $pm->print_name(true);

        return $template;
    }
}
