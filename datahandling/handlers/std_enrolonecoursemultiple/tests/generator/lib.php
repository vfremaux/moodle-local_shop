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
 * @package     local_shop
 * @subpackage  shophandler_std_enrolonecoursemultiple
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/tests/generator/shophandler_generator_base.php');
require_once($CFG->dirroot.'/local/shop/products/products.controller.php');

/**
 * Data generator class for tests.
 * This class has no override of test_customerdata nor test_productiondata as enrolling the current
 * user in the product level defined targets.
 */
class shophandler_std_enrolonecourse_generator extends shophandler_generator_base {

    /**
     * Create product
     * @param object $thecatalog
     * @param object $category
     * @param object $tax
     * @param object $params
     * @param array $data
     */
    public function create_product($thecatalog, $category, $tax, $params, $data = null) {
        global $CFG;

        static $prodix = 1;

        if (is_null($data)) {

            $handlerparams = 'courseid='.$params['course'].'&role=student&duration=365&customersupport=CUSTOMERSUPPORT';
            $requireddata = '';

            $data = (object) [
                'code' => 'TESTPRODENROLONECOURSE',
                'name' => 'Test product for enrolling in a course',
                'description_editor' => [
                    'text' => '<p>Product for unit testing. Single price, Automated on enrolling in a course.</p>',
                    'format' => '1',
                    'itemid' => 0,
                ],

                'userid' => 0,
                'status' => 'AVAILABLE',
                'price1' => 30,
                'from1' => 0,
                'range1' => 0,
                'price2' => 0,
                'range2' => 0,
                'price3' => 0,
                'range3' => 0,
                'price4' => 0,
                'range4' => 0,
                'price5' => 0,
                'taxcode' => $tax->id,
                'stock' => 0,
                'sold' => 0,
                'maxdeliveryquant' => 1,
                'onlyforloggedin' => 0,
                'password' => '',
                'categoryid' => $category->id,
                'setid' => 0,
                'showsnameinset' => 0,
                'showsdescriptioninset' => 0,

                'eula_editor' => [
                        'text' => '<p>Sales conditions / Creating course</p>',
                        'format' => 1,
                        'itemid' => 0,
                ],

                'notes_editor' => [
                    'text' => '<p>Test notes / Creating course</p>',
                    'format' => 1,
                    'itemid' => 0,
                ],

                'requireddata' => $requireddata,
                'enablehandler' => 'std_createcourse',
                'handlerparams' => $handlerparams,
                'quantaddressesusers' => 0,
                'renewable' => 0,
            ];
        }

        $controller = new \local_shop\backoffice\product_controller($thecatalog);
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }
}
