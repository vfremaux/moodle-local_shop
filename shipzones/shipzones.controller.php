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
 * Controller for shipzones operations
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * MVC controller class
 */
class shipzones_controller {

    /** @var object Action data context */
    protected $data;

    /** @var bool Marks data has been loaded for action. */
    protected $received = false;

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function should get them from request
     */
    public function receive($cmd, $data = null) {

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new StdClass();
            $this->data->categoryid = optional_param('categoryid', 0, PARAM_INT);
        }

        switch ($cmd) {
            case 'edit': {
                // Rely on feeding directly with the data argument.
                break;
            }

            case 'deletezone' : {
                $this->data->zoneid = required_param('zoneid', PARAM_INT);
                break;
            }

            case 'deleteshipping' : {
                $this->data->shippingids = optional_param_array('shipid', [], PARAM_INT);
                break;
            }
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {

        if ($cmd == 'deletezone') {

            $zoneid = $this->data->zoneid;
            $zone = new CatalogShipZone($zoneid);
            $zone->delete();
        }

        if ($cmd == 'deleteshipping') {

            $shippingids = $this->data->shippingids;

            if (!empty($shippingids)) {
                foreach ($shippingids as $shipid) {
                    $shipping = new CatalogShipping($shipid);
                    $shipping->delete();
                }
            }
        }
    }
}
