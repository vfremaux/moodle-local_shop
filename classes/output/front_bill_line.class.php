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
 * Data for bill line in front office.
 *
 * @package     local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\output;

use Stdclass;
use renderer_base;

class front_bill_line implements \Templatable {

    /**
     * represented bill item
     */
    protected $billitem;

    /**
     * Rendering options
     */
    protected $options;

    /**
     * Base constructor
     * @param object $billitem
     * @param array $options
     */
    public function __construct($billitem, $options) {
        $this->billitem = $billitem;
        $this->options = $options;
    }

    /**
     * Exporter for template
     * @param renderer_base $output
     */
    public function export_for_template(renderer_base $output) {
        $template = new StdClass();

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
