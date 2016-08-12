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

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class shipzones_controller {

    function process($cmd) {

        if ($cmd == 'deletezone') {

            $zoneid = required_param('zoneid', PARAM_INT);
            $zone = new CatalogShipZone($zoneid);
            $zone->delete();
        }

        if ($cmd == 'deleteshipping') {

            $shippingids = optional_param_array('shipid', array(), PARAM_INT);

            if (!empty($shippingids)) {
                foreach ($shippingids as $shipid) {
                    $shipping = new CatalogShipping($shipid);
                    $shipping->delete();
                }
            }
        }
    }
}