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
 * local_shop/handler data generator.
 *
 * @package  shophandler_std_openltiaccess
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/tests/generator/shophandler_generator_base.php');
require_once($CFG->dirroot.'/local/shop/products/products.controller.php');

/**
 * Data generator class for tests.
 */
class shophandler_std_openltiaccess_generator extends shophandler_generator_base {

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG;

        if (!is_dir($CFG->dirroot.'/local_ltiprovider')) {
            throw new Exception("Missing plugins for this handler");
        }
    }

    /**
     * Create product
     * @param object $thecatalog
     * @param object $category
     * @param object $tax
     * @param object $params
     * @param array $data
     */
    public function create_product($thecatalog, $category, $tax, $params, $data = null) {

        static $prodix = 1;

        if (is_null($data)) {
            $data = [
            ];
        }

        $controller = new \local_shop\backoffice\product_controller($thecatalog);
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }
}
