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
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/local/shop/tests/generator/lib.php');

/**
 * Generator tests class for local_shop.
 * @covers \local_shop_generator
 */
class generator_test extends advanced_testcase {

    /**
     * Test create shop
     */
    public function test_create_shop() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        // There is one default shop at install time.
        $this->assertTrue(1 == $DB->count_records('local_shop', []));
        $shop = $generator->create_shop();
        $this->assertTrue($DB->record_exists('local_shop', ['id' => $shop->id]));

        $shop->delete();
        $this->assertTrue(1 == $DB->count_records('local_shop', []));
    }

    /**
     * Test create catalog
     */
    public function test_create_catalog() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $this->assertTrue(0 == $DB->count_records('local_shop_catalog', []));
        $catalog = $generator->create_catalog();
        $this->assertTrue($DB->record_exists('local_shop_catalog', ['id' => $catalog->id]));

        $catalog->delete();
        $this->assertTrue(0 == $DB->count_records('local_shop_catalog', []));
    }

    /**
     * Test tax creation
     */
    public function test_create_tax() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $this->assertTrue(0 == $DB->count_records('local_shop_tax', []));
        $tax = $generator->create_tax();
        $this->assertTrue($DB->record_exists('local_shop_tax', ['id' => $tax->id]));

        $tax->delete();
        $this->assertTrue(0 == $DB->count_records('local_shop_tax', []));
    }

    /**
     * Test categor creation
     */
    public function test_create_category() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator()->get_plugin_generator('local_shop');

        $catalog = $generator->create_catalog();

        $this->assertTrue(0 == $DB->count_records('local_shop_catalogcategory', []));
        $cat = $generator->create_category($catalog);
        $this->assertTrue(!empty($cat));
        $this->assertTrue($DB->record_exists('local_shop_catalogcategory', ['id' => $cat->id]));

        $cat->delete();
        $this->assertTrue(0 == $DB->count_records('local_shop_catalogcategory', []));
    }
}
