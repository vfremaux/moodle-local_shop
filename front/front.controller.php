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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

abstract class front_controller_base {

    protected $theshop;

    protected $thecontroller;

    protected $theblock;

    /**
     * The received data
     */
    protected $data;

    protected $context;

    public function __construct(&$theshop, &$thecatalog, &$theblock = null) {
        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;

        if (!empty($theblock->instance->id)) {
            $this->context = \context_block::instance($theblock->instance->id);
        } else {
            $this->context = \context_system::instance();
        }
    }

    /**
     * Receives data from a data source, or get data from query input
     * if not feed from the arguments (empty array).
     */
    abstract public function receive($cmd, $data = array());

    /**
     * Process processes received data (assumes data has been received and loaded 
     * into controller instance. A processor should NEVER receive data from direct
     * query decoding function such as required_param or optional_param, nor
     * directly read from any input global such as $_GET or $_POST.
     */
    abstract public function process($cmd);
}