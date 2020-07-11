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

defined('MOODLE_INTERNAL') || die();

class bills_search_form implements \Templatable {

    protected $billcount;

    protected $theshop;

    public function __construct($theshop, $billcount) {
        $this->billcount = $billcount;
        $this->theshop = $theshop;
    }

    public function export_for_template(\renderer_base $output) {

        $template = new \StdClass();
        $template->shopid = $this->theshop->id;
        $template->sesskey = sesskey();
 
        if ($this->billcount) {
            $template->hasbills = true;
        } else {
            $template->nobillsnotification = $output->notification('nobills', 'local_shop');
        }

        return $template;
    }
}

