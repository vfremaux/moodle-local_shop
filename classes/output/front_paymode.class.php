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


class front_paymode implements \Templatable {

    protected $bill;

    public function __construct($bill) {
        $this->bill = $bill;
    }

    public function export_for_template(\renderer_base $output) {
        global $CFG;

        $afullbill = $this->bill;

        $template = new \StdClass;
        include_once($CFG->dirroot.'/local/shop/paymodes/'.$afullbill->paymode.'/'.$afullbill->paymode.'.class.php');

        $classname = 'shop_paymode_'.$afullbill->paymode;

        $pm = new $classname($afullbill->theshop);
        $template->paymode = $pm->print_name(true);

        return $template;
    }
}