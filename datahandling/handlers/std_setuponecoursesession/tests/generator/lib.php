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
 * @package  shophandlers_std_setuponecoursesession
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/products/products.controller.php');

/**
 * local_shop data generator class.
 */
class shophandler_std_setuponecoursesession_generator extends component_generator_base {

    /**
     * Create product
     * @param object $thecatalog
     * @param object $category
     * @param object $tax
     * @param object $params
     * @param array $data
     */
    public function create_product($thecatalog, $category, $tax, $data = null) {
        global $CFG;

        include_once($CFG->dirroot.'/local/shop/products/products.controller.php');

        static $prodix = 1;

        if (is_null($data)) {

            $requireddata = '[{"field":"requ1","label":"Requirement 1","type":"textfield",';
            $requireddata .= '"desc":"Testing collecting testfield","attrs":{"size":80}}, ';
            $requireddata .= '{"field":"requ2","label":"Requirement 2","type":"select",';
            $requireddata .= '"desc":"Testing colecting form select", "options":{"MOD1":"Model1","MOD2":"Model2"}}]';

            $data = (object) [
                'code' => 'TESTPROD',
                'name' => 'Test product',
                'description_editor' => [
                    'text' => '<p>Product for unit testing. Renewable, Seat allocatable, Multiple price (2 ranges), 
                        Automated on course session creation.</p>',
                    'format' => '1',
                    'itemid' => 0,
                ],

                'userid' => 0,
                'status' => 'AVAILABLE',
                'price1' => 10,
                'from1' => 0,
                'range1' => 5,
                'price2' => 20,
                'range2' => 0,
                'price3' => 0,
                'range3' => 0,
                'price4' => 0,
                'range4' => 0,
                'price5' => 0,
                'taxcode' => $tax->id,
                'stock' => 1000,
                'sold' => 0,
                'maxdeliveryquant' => 5,
                'onlyforloggedin' => 0,
                'password' => '',
                'categoryid' => $category->id,
                'setid' => 0,
                'showsnameinset' => 1,
                'showsdescriptioninset' => 1,

                'eula_editor' => [
                        'text' => '<p>Sales conditions</p>',
                        'format' => 1,
                        'itemid' => 0,
                ],

                'notes_editor' => [
                    'text' => '<p>Test notes</p>',
                    'format' => 1,
                    'itemid' => 0,
                ],

                'requireddata' => $requireddata,
                'enablehandler' => 'std_setuponecoursesession',
                'handlerparams' => 'coursename=TESTPROD',
                'quantaddressesusers' => 2,
                'renewable' => 1,
            ];
        }

        $controller = new \local_shop\backoffice\product_controller($thecatalog);
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }
}
