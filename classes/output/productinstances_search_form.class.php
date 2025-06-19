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
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use renderer_base;
use Templatable;
use StdClass;
use local_shop\Shop;

/**
 * Search form
 */
class productinstances_search_form implements Templatable {

    /** @var counter */
    protected $unitcount;

    /** @var the shop in context */
    protected $theshop;

    /**
     * Constructor
     * @param Shop $theshop
     * @param int $unitcount
     */
    public function __construct(Shop $theshop, $unitcount) {
        $this->unitcount = $unitcount;
        $this->theshop = $theshop;
    }

    /**
     * Exporter for template
     * @param renderer_base $output
     */
    public function export_for_template(renderer_base $output) {

        $template = new StdClass();
        $template->shopid = $this->theshop->id;
        $template->sesskey = sesskey();
 
        if ($this->unitcount) {
            $template->hasinstances = true;
        } else {
            $template->noinstancessnotification = $output->notification('nounits', 'local_shop');
        }

        return $template;
    }
}
