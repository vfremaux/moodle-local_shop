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

require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

defined('MOODLE_INTERNAL') || die();

use local_shop\Tax;

class front_taxes implements \Templatable {

    protected $taxes;

    protected $finaltaxestotal;

    protected $theshop;

    public function __construct($taxes, $finaltaxestotal, $theshop) {
        $this->taxes = $taxes;
        $this->finaltaxestotal = $finaltaxestotal;
        $this->theshop = $theshop;
    }

    public function export_for_template(\renderer_base $output) {
        global $OUTPUT;

        $template = new \StdClass;

        $template->currency = $this->theshop->get_currency('symbol');

        if (!empty($this->taxes)) {

            $template->hastaxlines = true;
            $template->taxheading = $OUTPUT->heading(get_string('taxes', 'local_shop'), 2, '', 'invoice-taxes');

            foreach ($this->taxes as $tcode => $tamount) {
                if ($tcode == 0) {
                    continue;
                }
                $tax = new Tax($tcode);
                $taxlinetpl = new \StdClass;
                $taxlinetpl->taxtitle = $tax->title;
                $taxlinetpl->taxratio = $tax->ratio;
                $taxlinetpl->taxamount = sprintf("%0.2f", round($tamount, 2));
                $template->taxlines[] = $taxlinetpl;
            }

            $template->totaltaxes = sprintf("%0.2f", round($this->finaltaxestotal, 2));
        }

        return $template;
    }
}
