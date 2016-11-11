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
class local_shop_purchase_testcase extends advanced_testcase {

    /**
     * Given an initialised shop with a TEST product, will run the entire
     * purchase controller chain using test payment method.
     * This test assumes we have a shop,purchasereqs,users,customer,order,payment,bill sequence
     *
     */
    public function test_purchase() {
        global $DB, $SESSION;

        $this->resetAfterTest();

        // Setup moodle content environment.

        $category = $this->getDataGenerator()->create_category();
        $params = array('name' => 'Test course', 'shortname' => 'TESTPROD', 'category' => $category->id);
        $course = $this->getDataGenerator()->create_course($params);

        // Setup the shop structure.

        $generator = new local_shop_generator();

        $tax = $generator->create_tax();
        $shop = $generator->create_shop();
        $catalog = $generator->create_catalog();
        $category = $generator->create_category();
        $product = $generator->create_product($catalog, $category, $tax);

        // Keep unconnected while executing the purchase process.

        $this->assertTrue($DB->record_exists('local_shop'));

        // Take first available shop.
        $shops = $DB->get_record('local_shop', array(), 'id', '*', 0, 1);
        $shop = array_shift($shops);

        // Start setting purchase order in session.

        // Run shop controller, with an imported order.
        $controller = new \local_shop\front\shop_controller();
        $order = array('TESTPROD' => 10);
        $controller->receive('import', $order);
        $controller->process('import');
        $this->assertTrue(@$SESSION->shoppingcart->order->testproduct == 10);

        // Test order cleanup.
        $controller->receive('clearall', $order);
        $controller->process('clearall');
        $this->assertTrue(empty($SESSION->shoppingcart));

        // Run shop controller, with an imported order.
        $order = array('TESTPROD' => 5);
        $controller->receive('import', $order);
        $controller->process('import');

        // Simulate nav to users controller, with an imported order.
        $controller->receive('navigate', $order);
        $controller->process('navigate');
        $this->assertTrue(isset($SESSION->shoppingcart->finaltaxedtotal));

        // Run purchaserequ controller.
        $controller = new \local_shop\front\purchaserequ_controller();
        $collected = array('TESTPROD/requ1'.'0' => 'value1');
        $collected = array('TESTPROD/requ2'.'0' => 'value2');
        $controller->receive('collect', $collected);
        $controller->process('collect');
        $this->assertTrue($SESSION->shoppingcart->customerdata['TESTPROD']['requ1'][0] == 'value1');
        $this->assertTrue($SESSION->shoppingcart->customerdata['TESTPROD']['requ2'][0] == 'value2');

        // Simulate nav to users controller, with an imported order.
        $controller->receive('navigate', $collected);
        $controller->process('navigate');

        // Run users controller.
        $controller = new \local_shop\front\users_controller();
        // Add participants.
        $pts = array(
            (object) array('firstname' => 'Paul', 'lastname' => 'Teacher', 'email' => 'paul.teacher@foo.com', 'city' => 'COMMENY'),
            (object) array('firstname' => 'John', 'lastname' => 'Learn1', 'email' => 'john.learn1@foo.com', 'city' => 'COMMENY'),
            (object) array('firstname' => 'Pete', 'lastname' => 'Learn2', 'email' => 'pete.learn2@foo.com', 'city' => 'COMMENY'),
            (object) array('firstname' => 'Lara', 'lastname' => 'Learn3', 'email' => 'lara.learn3@foo.com', 'city' => 'COMMENY'),
            (object) array('firstname' => 'Sarah', 'lastname' => 'Learn4', 'email' => 'sarah.learn4@foo.com', 'city' => 'COMMENY'),
            (object) array('firstname' => 'To', 'lastname' => 'Delete', 'email' => 'to.delete@foo.com', 'city' => 'COMMENY'),
        );
        $i = 1;
        foreach ($pts as $pt) {
            $jspt = json_encode($pt);
            $data = array('participant' => json_encode($jspt));
            $controller->receive('addparticipant', $data);
            $controller->process('addparticipant');
            $this->assertTrue(count($SESSION->shoppingcart->participants) == $i);
            $i++;
        }

        // Test participant deletion.
        $i--;
        $data = array('participant' => 'to.delete@foo.com');
        $controller->receive('deleteparticipant', $data);
        $controller->process('deleteparticipant');
        $this->assertTrue(count($SESSION->shoppingcart->participants) == $i);

        // Test execution of assignlists with no data.
        $controller->receive('assignlistobj', $data);
        $controller->process('assignlistobj');
        $controller->receive('assignalllistobj', $data);
        $controller->process('assignalllistobj');

        // Assign roles.
        $roles = array('teacher', 'student', 'student', 'student', 'student');
        $i = 0;
        foreach ($pts as $pt) {
            $data = array('participantid' => $pt->email, 'role' => $roles[$i], 'shortname' => 'testproduct');
            $controller->receive('addassign', $data);
            $controller->process('addassign');
            $i++;
            $this->assertTrue(@$SESSION->shoppingcart->assigns == $i);
        }

        // Test assign delete on last. (using last pt).
        $i--;
        $data = array('participantid' => $pt->email, 'role' => $roles[$i], 'shortname' => 'testproduct');
        $controller->receive('addassign', $data);
        $controller->process('addassign');
        $this->assertTrue(@$SESSION->shoppingcart->assigns == $i);

        // Reassigning last.
        $data = array('participantid' => $pt->email, 'role' => $roles[$i], 'shortname' => 'testproduct');
        $controller->receive('addassign', $data);
        $controller->process('addassign');
        $i++;
        $this->assertTrue(@$SESSION->shoppingcart->assigns == $i);

        // Test execution of assignlist with data.
        $controller->receive('assignlistobj', $data);
        $controller->process('assignlistobj');
        $controller->receive('assignalllistobj', $data);
        $controller->process('assignalllistobj');

        // Run customer controller.
        $controller = new \local_shop\front\customer_controller();
        $data = array(
            'usedistinctinvoiceinfo' => 1,
            'customerinfo' => array(
                'firstname' => 'Stephen',
                'lastname' => 'Customer',
                'organisation' => 'MyLearningFactory',
                'address' => '40, Grande Rue',
                'city' => 'COMMENY',
                'zip' => '95450',
                'country' => 'FR',
                'email' => 'stephen.customer@foo.com',
            ),
            'invoiceinfo' => array(
                'firstname' => 'Stephen',
                'lastname' => 'Customer',
                'organisation' => 'MyLearningFactory',
                'department' => 'Moodle Devs',
                'address' => '40, Grande Rue',
                'city' => 'COMMENY',
                'zip' => '95450',
                'country' => 'FR',
                'vatcode' => 'FR9998887776666',
                'email' => 'stephen.pro.customer@foo.com',
            ),
        );
        $controller->receive('navigate', $data);
        $return = $controller->process('navigate');
        // No expected return here. Data should be ok.
        $this->assertTrue(empty($return));
        $customer = $DB->get_record('local_shop_customer', array('email' => 'stephen.customer@foo.com'));
        $this->assertTrue(!empty($customer));

        // Run order controller.
        $controller = new \local_shop\front\order_controller();
        $data = array('paymode' => 'test');
        $controller->receive('navigate', $data);
        $return = $controller->process('navigate');
        $this->assertTrue($return instanceof moodle_url);

        $billnum = $DB->count_records('local_shop_bill', array('shopid' => $shop->id));
        $this->assertTrue($billnum == 0);

        // Run payment controller to place the order.
        $controller = new \local_shop\front\payment_controller();
        $controller->receive('place', $data);
        $return = $controller->process('place');
        $this->assertTrue(empty($return));

        $billnum = $DB->count_records('local_shop_bill', array('shopid' => $shop->id));
        $this->assertTrue($billnum == 1);

        // Get the first bill.
        $bills = $DB->get_records('local_shop_bill', array(), 'id', '*', 0, 1);
        $bill = array_shift($bills);
        $this->assertTrue($bill->customerid == $customerid);
        $this->assertTrue($bill->shopid == $shop->id);

        $this->assertTrue($DB->record_exists('local_shop_billitem', array('billid' => $bill->id, 'itemcode' => 'TESTPROD')));

        // Navigate and pay with test payment.
        $controller->receive('navigate');
        $return = $controller->process('navigate');

        $bill = $DB->get_record('local_shop_bill', array('id' => $bill->id));
        $this->assertTrue($bill->status == SHOP_BILL_SOLDOUT);
    }
}