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

defined('MOODLE_INTERNAL') || die();

class front_order_line implements \Templatable {

    protected $catalogitem;

    protected $theshop;

    protected $options;

    protected $q;

    public function __construct($catalogitem, $q, $theshop, $options) {
        $this->catalogitem = $catalogitem;
        $this->theshop = $theshop;
        $this->options = $options;
        $this->q = $q;
    }

    public function export_for_template(\renderer_base $output) {

        $template = new \StdClass;

        $theshop = $this->theshop;
        $catalogitem = $this->catalogitem;
        $options = $this->options;

        $template->currency = $theshop->get_currency('symbol');
        $template->itemname = $catalogitem->name;
        $template->abstract = '';
        if (!empty($options['description'])) {
            $template->abstract .= $catalogitem->description;
        }
        if (!empty($options['notes'])) {
            $template->abstract .= '<br/>'.$catalogitem->notes;
        }
        $template->itemcode = $catalogitem->code;
        $template->taxedprice = sprintf('%.2f', $catalogitem->get_taxed_price($this->q));
        $template->q = $this->q;
        $template->totaltaxedprice = sprintf('%.2f', $catalogitem->get_taxed_price($this->q) * $this->q);

        return $template;
    }
}