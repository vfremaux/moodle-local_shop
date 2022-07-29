<?php
<<<<<<< HEAD
=======
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
>>>>>>> MOODLE_40_STABLE

namespace local_shop\output;

class front_bill_line implements \Templatable {

    protected $billitem;
    protected $options;

    public function __construct($billitem, $options) {
        $this->billitem = $billitem;
        $this->options = $options;
    }

    public function export_for_template(\renderer_base $output) {
        $template = new \StdClass;

        $billitem = $this->billitem;
        $options = $this->options;
        $template->q = $billitem->quantity;
        $template->abstract = $billitem->abstract;
        if (!empty($options['description'])) {
            $template->description = $billitem->description;
        }
        $template->itemcode = $billitem->itemcode;
        $template->unitcost = sprintf('%0.2f', $billitem->unitcost);
        $template->totalprice = sprintf('%0.2f', $billitem->totalprice);

        return $template;
    }
}