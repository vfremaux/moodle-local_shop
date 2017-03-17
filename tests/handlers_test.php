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
 * local_shop tests of product handlers
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

use \local_shop\Catalog;
use \local_shop\Shop;

/**
 *  tests class for local_shop.
 */
class local_shop_handlers_testcase extends advanced_testcase {

    /**
     * Given an initialised shop with a TEST product, will run the entire
     * purchase controller chain using test payment method.
     * This test assumes we have a shop,purchasereqs,users,customer,order,payment,bill sequence
     */
    public function test_handlers() {
        global $DB, $SESSION;

        $this->resetAfterTest();

        // Setup moodle content environment.

        $category = $this->getDataGenerator()->create_category();
        $params = array('name' => 'Test course', 'shortname' => 'TESTPROD', 'category' => $category->id);
        $course = $this->getDataGenerator()->create_course($params);

        // Create customersupport default course.
        $params = array('name' => 'Test Customer Support course', 'shortname' => 'CUSTOMERSUPPORT', 'category' => $category->id);
        $customersupportcourse = $this->getDataGenerator()->create_course($params);

        $customer = array('firstname' => 'Test', 'lastname' => 'customer', 'email' => 'test.customer@foo.com');
        $customer = $this->getDataGenerator()->create_user($customer);
        $this->setUser($customer);

        // Setup the shop structure.

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $tax = $generator->create_tax();
        $shop = $generator->create_shop();
        $this->assertTrue(!empty($shop));

        $catalog = $generator->create_catalog();
        $this->assertTrue(!empty($catalog));

        // Bind catalog to shop.
        $shop->catalogid = $catalog->id;
        $shop->save(true);
        $this->assertTrue($catalog->id == $DB->get_field('local_shop', 'catalogid', array('id' => $shop->id)));

        $category = $generator->create_category($catalog);
        $this->assertTrue(!empty($category));

        // Connect with a fake customer user.

        $this->assertTrue($DB->record_exists('local_shop_catalogcategory', array('id' => $category->id)));

        // Fetch handlers.
        $pluginman = core_plugin_manager::instance();

        $allhandlers = array_keys($pluginman->get_plugins_of_type('shophandlers'));

        // Create products and trigger pre_pay and post_pay processes in name of customer.

        foreach ($allhandlers as $handlername) {
            try {
                $handlergenerator = $this->getDataGenerator()->get_plugin_generator('shophandler_'.$handlername);
            } catch (Exception $e) {
                // Ignore plugin that do not have sufficient environment.
                continue;
            }

            $product = $handlergenerator->create_product();
            $handler = $product->get_handler();

            $product->productiondata = $handlergenerator->test_productiondata();
            $product->customerdata = $handlergenerator->test_customerdata();

            // Just run it to check if execution errors.
            $handler->is_available($product);

            $handler->produce_prepay($product);

            $handler->produce_postpay($product);

            $handlergenerator->check_unittest();
        }
    }
}