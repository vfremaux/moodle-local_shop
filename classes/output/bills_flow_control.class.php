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
 * Controller for bill state machine.
 *
 * @package     local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\output;

use StdClass;
use renderer_base;
use Templatable;

/**
 * Data recollection for bill changing states
 */
class bills_flow_control implements Templatable {

    /** @var The current status */
    protected $status;

    /** @var the base URL */
    protected $url;

    /**
     * Base constructor
     * @param string $status starting status
     * @param moodle_url $url
     */
    public function __construct($status, $url) {
        $this->status = $status;
        $this->url = $url;
    }

    /**
     * Exporter for renderer.
     * @param renderer_base $output unused
     */
    public function export_for_template(renderer_base $output /* unused */) {
        global $DB;

        $select = "
            element = 'bill' AND
            `tostate` = ?
            GROUP BY element,`fromstate`
        ";
        $froms = $DB->get_records_select('local_flowcontrol', $select, [$this->status]);

        $select = "
            element = 'bill' AND
            `fromstate` = ?
            GROUP BY element,`tostate`
        ";
        $tos = $DB->get_records_select('local_flowcontrol', $select, [$this->status]);

        $template = new StdClass();

        $template->statusstr = get_string($this->status, 'local_shop');
        $template->url = $this->url->out();

        if ($froms) {
            foreach ($froms as $from) {
                $fromtpl = new StdClass();
                $fromtpl->label = get_string($from->fromstate, 'local_shop');
                $fromtpl->fromstate = $from->fromstate;
                $template->froms[] = $fromtpl;
            }
        }

        if ($tos) {
            foreach ($tos as $to) {
                $totpl = new StdClass();
                $totpl->label = get_string($to->tostate, 'local_shop');
                $totpl->tostate = $to->tostate;
                $template->tos[] = $totpl;
            }
        }

        return $template;
    }
}
