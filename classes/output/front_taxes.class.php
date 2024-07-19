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

require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use StdClass;
use local_shop\Tax;
use local_shop\Shop;
use Templatable;
use renderer_base;

/**
 * Taxes
 */
class front_taxes implements Templatable {

    /** @var array of taxlines */
    protected $taxes;

    /** @var the shop in context */
    protected $theshop;

    /**
     * Constructor
     * @param array $taxes
     * @param Shop $theshop
     */
    public function __construct($taxes, Shop $theshop) {
        $this->taxes = $taxes;
        $this->theshop = $theshop;
    }

    /**
     * Exporter for template
     * @param renderer_base $output unused
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function export_for_template(renderer_base $output /* unused */) {

        $template = new StdClass();

        if (!empty($this->taxes)) {

            $finaltaxestotal = 0;
            $template->hastaxlines = true;
            $currency = $this->theshop->get_currency('symbol');

            foreach ($this->taxes as $tcode => $tamount) {
                if ($tcode == 0) {
                    continue;
                }
                $tax = new Tax($tcode);
                $taxlinetpl = new StdClass;
                $taxlinetpl->taxtitle = $tax->title;
                $taxlinetpl->taxratio = $tax->ratio;
                // Get discounts tax out if exists.
                $finaltaxestotal += $tamount;
                $taxlinetpl->taxamount = sprintf("%0.2f", round($tamount, 2)).' '.$currency;
                $template->taxlines[] = $taxlinetpl;
            }

            $template->totaltaxes = sprintf("%0.2f", round($finaltaxestotal, 2)).' '.$currency;
        }

        return $template;
    }
}
