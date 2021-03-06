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
 * local_shop generator tests
 *
 * @package    local_shop
 * @category   test
 * @copyright  2013 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/local/shop/tests/generator/lib.php');

/**
 * Generator tests class for local_shop.
 */
class local_shop_generator_testcase extends advanced_testcase {

    public function test_create_shop() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        // There is one default shop at install time.
        $this->assertTrue(1 == $DB->count_records('local_shop', array()));
        $shop = $generator->create_shop();
        $this->assertTrue($DB->record_exists('local_shop', array('id' => $shop->id)));

        $shop->delete();
        $this->assertTrue(1 == $DB->count_records('local_shop', array()));
    }

    public function test_create_catalog() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $this->assertTrue(0 == $DB->count_records('local_shop_catalog', array()));
        $catalog = $generator->create_catalog();
        $this->assertTrue($DB->record_exists('local_shop_catalog', array('id' => $catalog->id)));

        $catalog->delete();
        $this->assertTrue(0 == $DB->count_records('local_shop_catalog', array()));
    }

    public function test_create_tax() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $this->assertTrue(0 == $DB->count_records('local_shop_tax', array()));
        $tax = $generator->create_tax();
        $this->assertTrue($DB->record_exists('local_shop_tax', array('id' => $tax->id)));

        $tax->delete();
        $this->assertTrue(0 == $DB->count_records('local_shop_tax', array()));
    }

    public function test_create_category() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $catalog = $generator->create_catalog();

        $this->assertTrue(0 == $DB->count_records('local_shop_catalogcategory', array()));
        $cat = $generator->create_category($catalog);
        $this->assertTrue(!empty($cat));
        $this->assertTrue($DB->record_exists('local_shop_catalogcategory', array('id' => $cat->id)));

        $cat->delete();
        $this->assertTrue(0 == $DB->count_records('local_shop_catalogcategory', array()));
    }
}
