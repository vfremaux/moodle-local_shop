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

/**
 * Generator tests class for local_shop.
 */
class local_shop_generator_testcase extends advanced_testcase {

    public function test_create_shop() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $this->assertFalse($DB->record_exists('local_shop'));
        $shop = $this->getDataGenerator()->create_shop_instance();
        $this->assertTrue($DB->record_exists('local_shop', array('id' => $shop->id)));

        $shop->delete();
        $this->assertFalse($DB->record_exists('local_shop'));
    }

    public function test_create_catalog() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $this->assertFalse($DB->record_exists('local_shop'));
        $catalog = $this->getDataGenerator()->create_catalog_instance();
        $this->assertTrue($DB->record_exists('local_shop', array('id' => $catalog->id)));

        $catalog->delete();
        $this->assertFalse($DB->record_exists('local_shop'));
    }

    public function test_build_catalog() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $this->assertFalse($DB->record_exists('local_shop'));
        $catalog = $this->getDataGenerator()->create_catalog_instance();

        $categorydef = (object) array(
            'name' => 'Category 1',
            'parentid' => 0,
            'description_editor' => array('text' => 'Top category 1', 'format' => 1, 'itemid' => 0),
            'visible' => false,
        );
        $category1 = $this->getDataGenerator()->create_catalog_category($categorydef);

    }

}
