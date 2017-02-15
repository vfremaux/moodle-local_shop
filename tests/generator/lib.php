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
 * local_shop data generator.
 *
 * @package    local_shop
 * @category   test
 * @copyright  2016 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/shop/reset.controller.php');
require_once($CFG->dirroot.'/local/shop/shop/shops.controller.php');
require_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');
require_once($CFG->dirroot.'/local/shop/products/products.controller.php');
require_once($CFG->dirroot.'/local/shop/taxes/taxes.controller.php');
require_once($CFG->dirroot.'/local/shop/products/category/viewAllCategories.controller.php');

/**
 * local_shop data generator class.
 */
class local_shop_generator extends component_generator_base {

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {

        // Empties the whole shop system using reset controller.
        $data = new Stdclass;
        $data->bills = true;
        $data->customers = true;
        $data->catalogs = true;

        $controller = new \local_shop\backoffice\reset_controller();
        $controller->receive('reset', $data);
        $controller->process('reset');

        parent::reset();
    }

    public function create_shop($data = null) {
        static $shopix = 1;

        $config = get_config('local_shop');

        if (is_null($data)) {
            $data = (object) array(
                'name' => 'Test shop '.$shopix,
                'description_editor' => array('text' => 'Shop instance for tests', 'format' => 1, 'itemid' => 0),
                'catalogid' => 0,
                'navsteps' => $config->defaultnavsteps,
                'allowtax' => true,
                'discountthreshold' => 1000,
                'discountrate' => 5,
                'discountrate2' => 10,
                'discountrate3' => 15,
                'currency' => 'EUR',
                'defaultpaymode' => 'test',
                /* 'paymode<paymodename>' => 'Boolean, one per enabled paymode', */
                'paymodetest' => true,
                'forcedownloadleaflet' => false,
                'customerorganisationrequired' => true,
                'enduserorganisationrequired' => true,
                'endusermobilephonerequired' => true,
                'printtabbedcategories' => true,
                'defaultcustomersupportcourse' => 0,
                'eula_editor' => array('text' => 'Test shop eulas', 'format' => 1, 'itemid' => 0),
            );
        }

        $controller = new \local_shop\backoffice\shop_controller();
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }

    public function create_tax($data = null) {

        $taxix = 1;

        if (is_null($data)) {
            $data = (object) array(
                'title' => 'Test VAT '.$taxix,
                'ratio' => '10.0',
                'country' => 'NZ',
                'formula' => '$ttc * $rt',
            );
        }

        $controller = new \local_shop\backoffice\taxes_controller();
        $controller->receive('edit', $data);
        return $controller->process('edit');

    }

    public function create_catalog($data = null) {

        static $catalogix = 1;

        if (is_null($data)) {
            $data = (object) array(
                'name' => 'Test catalog '.$catalogix,
                'description_editor' => array('text' => 'Catalog instance for tests', 'format' => 1, 'itemid' => 0),
                'salesconditions_editor' => array('text' => 'Catalog sales conditions', 'format' => 1, 'itemid' => 0),
                'countryrestrictions' => '',
                'linked' => 'free',
                'groupid' => 0,
            );
        }

        $controller = new \local_shop\backoffice\catalog_controller();
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }

    public function create_category($thecatalog, $data = null) {
        global $CFG;

        include_once($CFG->dirroot.'/local/shop/products/category/viewAllCategories.controller.php');

        static $catix = 1;

        if (is_null($data)) {
            $data = (object) array(
                'name' => 'Test product category '.$catix,
                'description_editor' => array('text' => 'Product category for tests', 'format' => 1, 'itemid' => 0),
                'parentid' => 0,
                'visible' => 1,
            );
        }

        $controller = new \local_shop\backoffice\category_controller($thecatalog);
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }

    public function create_product($thecatalog, $category, $tax, $data = null) {
        global $CFG;

        include_once($CFG->dirroot.'/local/shop/products/products.controller.php');

        static $prodix = 1;

        if (is_null($data)) {

            $requireddata = '[{"field":"requ1","label":"Requirement 1","type":"textfield",';
            $requireddata .= '"desc":"Testing collecting testfield","attrs":{"size":80}}, ';
            $requireddata .= '{"field":"requ2","label":"Requirement 2","type":"select",';
            $requireddata .= '"desc":"Testing colecting form select", "options":{"MOD1":"Model1","MOD2":"Model2"}}]';

            $data = (object) array(
                'code' => 'TESTPROD',
                'name' => 'Test product',
                'description_editor' => array(
                    'text' => '<p>Product for unit testing. Renewable, Seat allocatable, Multiple price (2 ranges), Automated on course session creation.</p>',
                    'format' => '1',
                    'itemid' => 0,
                ),

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

                'eula_editor' => array (
                        'text' => '<p>Sales conditions</p>',
                        'format' => 1,
                        'itemid' => 0,
                ),

                'notes_editor' => array (
                    'text' => '<p>Test notes</p>',
                    'format' => 1,
                    'itemid' => 0,
                ),

                'requireddata' => $requireddata,
                'enablehandler' => 'std_setuponecoursesession',
                'handlerparams' => 'coursename=TESTPROD',
                'quantaddressesusers' => 2,
                'renewable' => 1,
            );
        }

        $controller = new \local_shop\backoffice\product_controller($thecatalog);
        $controller->receive('edit', $data);
        return $controller->process('edit');
    }

    /**
     * Create a set of catalogs for testing.
     */
    public function create_catalogs() {
        global $DB;

        $this->reset();

        $catalogs = array(
          array('id' => '1','name' => 'Base catalog','description' => '<p>Catalog for independant products<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '0','countryrestrictions' => ''),
          array('id' => '2','name' => 'Master catalog 1','description' => '<p>Master products 1<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '2','countryrestrictions' => ''),
          array('id' => '3','name' => 'Master catalog 2','description' => '<p>Master records 2<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '3','countryrestrictions' => ''),
          array('id' => '4','name' => 'Slave catalog 1 - 1','description' => '<p>slave to master 1<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '2','countryrestrictions' => ''),
          array('id' => '5','name' => 'Slave 1 - 2','description' => '<p>Slave to master 1<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '2','countryrestrictions' => ''),
          array('id' => '6','name' => 'Slave catalog 2 - 1','description' => '<p>Slave of Master 2<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '3','countryrestrictions' => ''),
          array('id' => '7','name' => 'Master to delete','description' => '<p>To delete<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '7','countryrestrictions' => ''),
          array('id' => '8','name' => 'Master to delete w slaves','description' => '<p>Master with slaves to delete<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '8','countryrestrictions' => ''),
          array('id' => '9','name' => 'Slave to master to delete','description' => '<p>Slave to master to delete<br></p>','descriptionformat' => '1','salesconditions' => '','salesconditionsformat' => '1','groupid' => '8','countryrestrictions' => '')
        );

        foreach ($catalogs as $c) {
            $cobj = (object)$c;
            unset($cobj->id);
            $DB->insert_records('local_shop_catalog', $cobj);
        }
    }
}

