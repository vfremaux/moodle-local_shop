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
 * An abstract comon class for all front controllers.
 *
 * @package   local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

use context_block;
use context_system;

abstract class front_controller_base {

    /** @var Shop the current shop in shop context */
    protected $theshop;

    /** @var the current catalog used in shop context */
    protected $thecatalog;

    /** @var the block (shop_access) that parks the entry point */
    protected $theblock;

    /** @var the general front renderer instance */
    protected $renderer;

    /** @var object Action data context */
    protected $data;

    /** @var bool Marks data has been loaded for action. */
    protected $received;

    /** @var context attached to the transaction, block or system context **/
    protected $context;

    /**
     * Constructor
     * @param Shop $theshop
     * @param Catalog $thecatalog
     * @param object $theblock the block instance, may be null
     */
    public function __construct($theshop, $thecatalog, $theblock = null) {

        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;

        if (!empty($theblock->instance->id)) {
            $this->context = context_block::instance($theblock->instance->id);
        } else {
            $this->context = context_system::instance();
        }

        $this->renderer = shop_get_renderer();
        $this->renderer->load_context($theshop, $thecatalog, $theblock);
    }

    /**
     * Receives data from a data source, or get data from query input
     * if not feed from the arguments (empty array).
     * @param string $cmd
     * @param array $data
     */
    abstract public function receive($cmd, $data = []);

    /**
     * Process processes received data (assumes data has been received and loaded
     * into controller instance. A processor should NEVER receive data from direct
     * query decoding function such as required_param or optional_param, nor
     * directly read from any input global such as $_GET or $_POST.
     * @param string $cmd
     */
    abstract public function process($cmd);
}
