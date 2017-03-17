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
 * local_shop tests of purchase chain
 *
 * @package    local_shop
 * @category   test
 * @copyright  2013 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/tests/generator/lib.php');
require_once($CFG->dirroot.'/local/shop/catalogs/catalogs.controller.php');
require_once($CFG->dirroot.'/local/shop/taxes/taxes.controller.php');

use \local_shop\Catalog;
use \local_shop\Shop;

// Get all front controllers.
$controllerfiles = glob($CFG->dirroot.'/local/shop/front/*.controller.php');
foreach ($controllerfiles as $c) {
    require_once($c);
}

/**
 *  tests class for local_shop.
 */
class local_shop_backoffice_testcase extends advanced_testcase {

    /**
     * Given an initialised shop with a TEST product, will run the entire
     * purchase controller chain using test payment method.
     * This test assumes we have a shop,purchasereqs,users,customer,order,payment,bill sequence
     *
     */
    public function test_backoffice() {
        global $DB, $SESSION;

        $config = get_config('local_shop');

        $this->resetAfterTest();

        // Setup moodle content environment.

        $category = $this->getDataGenerator()->create_category();
        $params = array('name' => 'Test course', 'shortname' => 'TESTPROD', 'category' => $category->id);
        $course = $this->getDataGenerator()->create_course($params);

        // Create customersupport default course.
        $params = array('name' => 'Test Customer Support course', 'shortname' => 'CUSTOMERSUPPORT', 'category' => $category->id);
        $customersupportcourse = $this->getDataGenerator()->create_course($params);

        // Setup the shop structure.

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        // Creating an managing shops.

        $shopcontroller = new \local_shop\backoffice\shop_controller();

        $data = array(
            'name' => 'Testshop',
            'description_editor' => array('text' => 'Testing backoffice',
                'format' => 1, 'itemid' => 0),
            'currency' => 'EUR',
            'customerorganisationrequired' => 1,
            'enduserorganisationrequired' => 1,
            'endusermobilephonerequired' => 1,
            'printtabbedcategories' => 1,
            'defaultcustomersupportcourse' => $customersupportcourse->id,
            'forcedownloadleaflet' => 1,
            'allowtax' => 1,
            'discountthreshold' => '1000',
            'discountrate' => '10',
            'discountrate2' => '13',
            'discountrate3' => '15',
            'eula_editor' => array('text' => 'General sales conditions',
                'format' => 1, 'itemid' => 0),
            'catalogid' => 0,
            'paymodes' => 'test',
            'defaultpaymode' => 'test',
            'navsteps' => $config->defaultnavsteps
        );

        $shopcontroller->receive('edit', $data);
        $shopinstance = $shopcontroller->process('edit');

        $this->assertTrue(!empty($shopinstance));
        $this->assertTrue($DB->count_records('local_shop') == 2);

        $data = array(
            'name' => 'Testshop2',
            'description_editor' => array('text' => 'Testing backoffice 2',
                'format' => 1, 'itemid' => 0),
            'currency' => 'EUR',
            'customerorganisationrequired' => 1,
            'enduserorganisationrequired' => 1,
            'endusermobilephonerequired' => 1,
            'printtabbedcategories' => 1,
            'defaultcustomersupportcourse' => $customersupportcourse->id,
            'forcedownloadleaflet' => 1,
            'allowtax' => 1,
            'discountthreshold' => '1000',
            'discountrate' => '10',
            'discountrate2' => '13',
            'discountrate3' => '15',
            'eula_editor' => array('text' => 'General sales conditions 2',
                'format' => 1, 'itemid' => 0),
            'catalogid' => 0,
            'paymodes' => 'test',
            'defaultpaymode' => 'test',
            'navsteps' => $config->defaultnavsteps
        );

        $shopcontroller->receive('edit', $data);
        $shopinstance = $shopcontroller->process('edit');

        $this->assertTrue(!empty($shopinstance));
        $this->assertTrue($DB->count_records('local_shop') == 3);

        $data = array('shopid' => $shopinstance->id);

        $shopcontroller->receive('delete', $data);
        $shopcontroller->process('delete');

        $this->assertTrue($DB->count_records('local_shop') == 2);

        // Testing tax controllers.

        $tax = $generator->create_tax();
        $taxcontroller = new \local_shop\backoffice\taxes_controller();

        $data = array(
            'country' => 'ES',
            'title' => 'IVA',
            'ratio' => '15',
            'formula' => '$ttc = $ht * (1 + ($rt / 100))'
        );
        $taxcontroller->receive('edit', $data);
        $tax = $taxcontroller->process('edit');
        $this->assertTrue($DB->count_records('local_shop_tax') == 2);

        $data = array('taxid' => $tax->id);
        $taxcontroller->receive('delete', $data);
        $tax = $taxcontroller->process('delete');
        $this->assertTrue($DB->count_records('local_shop_tax') == 1);

        // Test creating catalogs.

        // Keep unconnected while executing the purchase process.

    }

    /**
     * Tries to test several combinations of catalogs.
     *
     */
    public function test_catalogs() {
        global $DB, $SESSION;

        // Setup the shop structure.

        $this->resetAfterTest();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $tax = $generator->create_tax();
        $shop = $generator->create_shop();

        $catalogcontroller = new \local_shop\backoffice\catalog_controller();

        $data = array(
            'name' => 'TestCatalog',
            'description_editor' => array('text' => 'Catalog for tests',
                'format' => 1, 'itemid' => 0),
            'salesconditions_editor' => array('text' => 'Catalog sales conditions',
                'format' => 1, 'itemid' => 0),
            'groupid' => 0,
            'countryrestrictions' => 'FR,BE',
            'linked' => 'free'
        );

        $catalogcontroller->receive('edit', $data);
        $catalog = $catalogcontroller->process('edit');
        $this->assertTrue(!empty($catalog));
        $this->assertTrue($DB->count_records('local_shop_catalog') == 1);

        $data = array('catalogid' => $catalog->id);

        $catalogcontroller->receive('deletecatalog', $data);
        $catalog = $catalogcontroller->process('deletecatalog');
        $this->assertTrue($DB->count_records('local_shop_catalog') == 0);

        $data = array(
            'name' => 'TestCatalog',
            'description_editor' => array('text' => 'Catalog for tests',
                'format' => 1, 'itemid' => 0),
            'salesconditions_editor' => array('text' => 'Catalog sales conditions',
                'format' => 1, 'itemid' => 0),
            'countryrestrictions' => 'FR,BE',
            'linked' => 'master'
        );

        $catalogcontroller->receive('edit', $data);
        $catalog = $catalogcontroller->process('edit');

        $data = array(
            'name' => 'TestCatalogS1',
            'description_editor' => array('text' => 'Slave Catalog 1 for tests',
                'format' => 1, 'itemid' => 0),
            'salesconditions_editor' => array('text' => 'Slave Catalog 1 sales conditions',
                'format' => 1, 'itemid' => 0),
            'groupid' => $catalog->id,
            'countryrestrictions' => 'UK',
            'linked' => 'slave'
        );

        $catalogcontroller->receive('edit', $data);
        $catalog1 = $catalogcontroller->process('edit');

        $this->assertTrue(!empty($catalog1));
        $this->assertTrue($DB->count_records('local_shop_catalog') == 2);

        $data = array(
            'name' => 'TestCatalogS2',
            'description_editor' => array('text' => 'Slave Catalog 2 for tests',
                'format' => 1, 'itemid' => 0),
            'salesconditions_editor' => array('text' => 'Slave Catalog 2 sales conditions',
                'format' => 1, 'itemid' => 0),
            'groupid' => $catalog->id,
            'countryrestrictions' => 'US',
            'linked' => 'slave'
        );

        $catalogcontroller->receive('edit', $data);
        $catalog2 = $catalogcontroller->process('edit');

        $this->assertTrue(!empty($catalog2));
        $this->assertTrue($DB->count_records('local_shop_catalog') == 3);

        $cats = $DB->get_records('local_shop_catalog');

        $members = $catalog->get_group_members();
        $this->assertTrue(count($members) == 3);
        $this->assertTrue(in_array($catalog1->id, $members));
        $this->assertTrue(in_array($catalog2->id, $members));
    }
}