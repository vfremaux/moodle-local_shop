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
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/externallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/externallib.php');

use \local_shop\Shop;
use \local_shop\Catalog;
use \local_shop\Catalogitem;
use \local_shop\Category;

class local_shop_external_extended extends local_shop_external {

    /**
     * Validates all inpout params and change or remap values if required.
     */
    protected function validate_shop_parameters($requs, &$parameters) {
        global $DB;

        $TYPES = array(
            'plain' => PRODUCT_STANDALONE,
            'set' => PRODUCT_SET,
            'bundle' => PRODUCT_BUNDLE,
            '*' => '*',
        );

        parent::validate_parameters($requs, $parameters);

        if (array_key_exists('shopid', $parameters)) {
            $params = array('id' => $parameters['shopid']);
            if (!$DB->record_exists('local_shop', $params)) {
                throw new invalid_parameter_exception("No such shop");
            }
        }

        if (array_key_exists('catalogid', $parameters)) {
            $params = array('id' => $parameters['catalogid']);
            if (!$DB->record_exists('local_shop_catalog', $params)) {
                throw new invalid_parameter_exception("No such catalog");
            }
        }

        if (array_key_exists('catalogcategoryid', $parameters)) {
            $params = array('id' => $parameters['catalogcategoryid']);
            if (!$DB->record_exists('local_shop_catalogcategory', $params)) {
                throw new invalid_parameter_exception("No such catalog category");
            }
        }

        if (array_key_exists('itemid', $parameters)) {

            if (!array_key_exists('itemidsource', $parameters)) {
                throw new invalid_parameter_exception("Missing item id source");
            }

            if (!in_array($parameters['itemidsource'], array('id', 'code'))) {
                throw new invalid_parameter_exception("Not valid id source");
            }

            if ($parameters['itemidsource'] == 'id') {
                $params = array('id' => $parameters['itemid']);
            } else if ($parameters['itemidsource'] == 'code') {
                $params = array('code' => $parameters['itemid']);
            }
            if (!$rec = $DB->get_record('local_shop_catalogitem', $params)) {
                throw new invalid_parameter_exception("No such catalog item");
            } else {
                $parameters['itemid'] = $rec->id;
            }
        }

        if (array_key_exists('categoryid', $parameters)) {
            if ($parameters['categoryid'] == 0) {
                $parameters['categoryid'] = '*'; // Catch all.
            }
        }

        if (array_key_exists('type', $parameters)) {
            if (!in_array($parameters['type'], array('*', 'plain', 'set', 'bundle'))) {
                throw new invalid_parameter_exception("Invalid product type");
            }

            $parameters['type'] = $TYPES[$parameters['type']];
        }
    }

    // Get content.

    /**
     * Get shop info
     *
     * @param string $shopid the shop id.
     *
     * @return external_description
     */
    public static function get_shop($shopid) {
        global $DB;

        $parameters = array(
            'shopid' => $shopid,
        );
        self::validate_shop_parameters(self::get_shop_parameters(), $parameters);

        $shop = new Shop($parameters['shopid']);

        return $shop->export_to_ws();
    }

    /**
     * Get catalog info
     *
     * @param string $catalogid the catalog id.
     *
     * @return external_description
     */
    public static function get_catalog($catalogid) {
        global $DB;

        $parameters = array(
            'catalogid' => $catalogid,
        );
        self::validate_shop_parameters(self::get_catalog_parameters(), $parameters);

        $catalog = new Catalog($parameters['catalogid']);

        return $catalog->export_to_ws();
    }

    /**
     * Get catalog info
     *
     * @param string $categoryid the catalog category id.
     *
     * @return external_description
     */
    public static function get_catalogcategory($categoryid) {
        global $DB;

        $parameters = array(
            'categoryid' => $categoryid,
        );
        self::validate_shop_parameters(self::get_catalogcategory_parameters(), $parameters);

        $category = new Category($parameters['categoryid']);

        return $category->export_to_ws();
    }

    /**
     * Get catalog info
     *
     * @param string $itemidsource the catalog item source field for id.
     * @param string $itemid the catalog item id.
     * @param string $q the required quantity for princing.
     * @param string $withsubs if true, will complete the sub products if a set or a bundle.
     *
     * @return external_description
     */
    public static function get_catalogitem($itemidsource, $itemid, $q /* , $withsubs */) {
        global $DB;

        $parameters = array(
            'itemidsource' => $itemidsource,
            'itemid' => $itemid,
            'q' => $q,
            /* 'withsubs' => $withsubs, */
        );
        self::validate_shop_parameters(self::get_catalogitem_parameters(), $parameters);

        $catalogitem = new CatalogItem($parameters['itemid']);

        $withsubs = 0; // Reserved to future use if needed.
        return $catalogitem->export_to_ws($q, $withsubs);
    }

    /**
     * Get catalog info
     *
     * @param string $cid the catalog id.
     *
     * @return external_description
     */
    public static function get_catalogitems($catalogid, $categoryid, $status, $type, $q /* , $withsubs */) {
        global $DB;

        $parameters = array(
            'catalogid' => $catalogid,
            'categoryid' => $categoryid,
            'status' => $status,
            'type' => $type,
            'q' => $q,
            /* 'withsubs' => $withsubs, */
        );
        self::validate_shop_parameters(self::get_catalogitems_parameters(), $parameters);

        $withsubs = 0; // Reserved to future use if needed.

        $results = array();
        $params = array('catalogid' => $parameters['catalogid'],
                        'categoryid' => $parameters['categoryid'],
                        'status' => $status,
                        'isset' => $parameters['type']);
        $items = CatalogItem::get_instances($params);
        if (!empty($items)) {
            foreach ($items as $item) {
                $results[] = $item->export_to_ws($q, $withsubs);
            }
        }

        return $results;
    }
}
