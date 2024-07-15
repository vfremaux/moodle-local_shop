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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\back;

use StdClass;
use coding_exception;

/**
 * Controller for unit tests
 */
class unittests_controller {

    /** @var object Action data context */
    protected $data;

    /** @var bool Marks data has been loaded for action. */
    protected $received;

    /** @var object the shop in context */
    protected $theshop;

    /** @var object the shop catalog in context */
    protected $thecatalog;

    /** @var object the shop_access block (optional) */
    protected $theblock;

    /**
     * Constructor
     * @param object $theshop
     * @param object $thecatalog
     * @param object $theblock
     */
    public function __construct($theshop, $thecatalog, $theblock = null) {
        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;
    }

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function should get them from request
     */
    public function receive($cmd, $data = []) {

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            return;
        } else {
            $this->data = new StdClass();
        }

        switch ($cmd) {
            case 'test' :
                $this->data->selected = optional_param_array('sel', [], PARAM_TEXT);
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $CFG;

        if (!$this->received) {
            throw new coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        /*
         * Performs consistancy test on all selected products and produces a report about what is OK and what is wrong.
         * Only Catalog defined handler params are supported here.
         */
        if ($cmd == 'test') {

            include_once($CFG->dirroot.'/local/shop/datahandling/production.php');

            $messages = [];
            $errors = [];
            $warnings = [];

            $this->thecatalog->get_all_products_for_admin($products);
            $this->theshop->thecatalogue = $this->thecatalog;
            produce_unittests($this->theshop, $products, $this->data->selected, $errors, $warnings, $messages);
            return [$errors, $warnings, $messages];
        }
    }
}
