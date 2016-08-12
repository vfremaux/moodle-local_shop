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

namespace local_shop\back;

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


class unittests_controller {

    var $theshop;
    var $thecatalogue;
    var $theblock;

    function __construct($theShop, $theCatalogue, $theBlock) {
        $this->theshop = $theShop;
        $this->thecatalogue = $theCatalogue;
        $this->theblock = $theBlock;
    }

    function process($action) {
        global $CFG;

        /* 
        * Performs consistancy test on all selected products and produces a report about what is OK and what is wrong.
        * Only Catalog defined handler params are supported here. 
        */
        if ($action == 'test') {

            $selected = optional_param_array('sel', array(), PARAM_TEXT);

            include_once($CFG->dirroot.'/local/shop/datahandling/production.php');

            $messages = '';
            $errors = array();
            $warnings = array();

            $this->thecatalogue->get_all_products($products);
            $this->theshop->thecatalogue = $this->thecatalogue;
            produce_unittests($this->theshop, $products, $selected, $errors, $warnings, $messages);
            return array($errors, $warnings, $messages);
        }
    }
}