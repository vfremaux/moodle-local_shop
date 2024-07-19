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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

use StdClass;
use renderer_base;
use Templatable;
use local_shop\BillItem;

/**
 * A line of a online bill (usually a billitem)
 */
class front_bill_line implements Templatable {

    /** @var represented bill item */
    protected $billitem;

    /** @var array of rendering options */
    protected $options;

    /**
     * Base constructor
     * @param object $billitem
     * @param array $options
     */
    public function __construct(BillItem $billitem, $options) {
        $this->billitem = $billitem;
        $this->options = $options;
    }

    /**
     * Exporter for template
     * @param renderer_base $output unused
     * @SuppressWarnings(PHPMD.UnusedFormaParameter)
     */
    public function export_for_template(renderer_base $output /* unused */) {
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
