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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2016 Valery Fremaux (http://www.activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/lib/externallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Shop;
use local_shop\Catalog;
use local_shop\Catalogitem;

class local_shop_external extends external_api {

    /**
     * Validates all inpout params and change or remap values if required.
     */
    protected function validate_shop_parameters($requs, $params) {
        global $DB;

        $types = [
            'plain' => SHOP_PRODUCT,
            'set' => SHOP_SET,
            'bundle' => SHOP_BUNDLE,
        ];

        parent::validate_parameters($requs, $params);

        if (array_key_exists('shopid', $params)) {
            $params = ['id' => $params['shopid']];
            if (!$DB->record_exists('local_shop', $params)) {
                throw new ParameterException("No such shop");
            }
        }

        if (array_key_exists('type', $params)) {
            if (!in_array($params['type'], ['plain', 'set', 'bundle'])) {
                throw new ParameterException("Invalid product type");
            }

            $parameter['type'] = $types[$parameter['type']];
        }
    }

    // Get content.

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_shop_parameters() {
        $desc = 'Id of the shop';
        return new external_function_parameters(
            [
                'shopid' => new external_value(PARAM_INT, $desc),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_catalog_parameters() {
        $desc = 'Id of the catalog';
        return new external_function_parameters(
            [
                'catalogid' => new external_value(PARAM_INT, 'Catalog id'),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_catalogcategory_parameters() {
        $desc = 'Id of the category';
        return new external_function_parameters(
            [
                'categoryid' => new external_value(PARAM_INT, 'Category id'),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_catalogitem_parameters() {
        $desc1 = 'Source field of the catalog item';
        $desc2 = 'Id of the catalogitem, depending on required source ';
        $desc3 = 'Quantity required for pricing';
        $desc4 = 'Output subrecords';
        return new external_function_parameters(
            [
                'itemidsource' => new external_value(PARAM_ALPHA, $desc1),
                'itemid' => new external_value(PARAM_TEXT, $desc2),
                'q' => new external_value(PARAM_INT, $desc3),
            ]
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_catalogitems_parameters() {
        $desc1 = 'Catalog ID';
        $desc2 = 'Category id, "*" for any';
        $desc3 = 'Status filter, such as AVAILABLE or "*" for any';
        $desc4 = 'Product type such as : plain, set or bundle or "*" for any';
        $desc5 = 'Quantity required for pricing';
        $desc6 = 'Output subrecords';
        return new external_function_parameters(
            [
                'catalogid' => new external_value(PARAM_INT, $desc1),
                'categoryid' => new external_value(PARAM_INT, $desc2),
                'status' => new external_value(PARAM_TEXT, $desc3),
                'type' => new external_value(PARAM_TEXT, $desc4),
                'q' => new external_value(PARAM_INT, $desc5), 
            ]
        );
    }

    /**
     * Get shop info
     *
     * @param string $shopid the shop id.
     *
     * @return external_description
     */
    public static function get_shop($shopid) {
        global $CFG;

        if (local_shop_supports_feature('api/ws')) {
            include_once($CFG->dirroot.'/local/shop/pro/externallib.php');
            return local_shop_external_extended::get_shop($shopid);
        }

        throw new moodle_exception('WS Not available in this distribution');
    }

    /**
     * Get catalog info
     *
     * @param string $catalogid the catalog id.
     *
     * @return external_description
     */
    public static function get_catalog($catalogid) {
        global $CFG;

        if (local_shop_supports_feature('api/ws')) {
            include_once($CFG->dirroot.'/local/shop/pro/externallib.php');
            return local_shop_external_extended::get_catalog($catalogid);
        }

        throw new moodle_exception('WS Not available in this distribution');
    }

    /**
     * Get catalog info
     *
     * @param string $categoryid the catalog category id.
     *
     * @return external_description
     */
    public static function get_catalogcategory($categoryid) {
        global $CFG;

        if (local_shop_supports_feature('api/ws')) {
            include_once($CFG->dirroot.'/local/shop/pro/externallib.php');
            return local_shop_external_extended::get_catalogcategory($categoryid);
        }

        throw new moodle_exception('WS Not available in this distribution');
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
        global $CFG;

        if (local_shop_supports_feature('api/ws')) {
            include_once($CFG->dirroot.'/local/shop/pro/externallib.php');
            return local_shop_external_extended::get_catalogitem($itemidsource, $itemid, $q);
        }

        throw new moodle_exception('WS Not available in this distribution');
    }

    /**
     * Get catalog info
     *
     * @param string $cid the catalog id.
     *
     * @return external_description
     */
    public static function get_catalogitems($catalogid, $categoryid, $status, $type, $q /* , $withsubs */) {
        global $CFG;

        if (local_shop_supports_feature('api/ws')) {
            include_once($CFG->dirroot.'/local/shop/pro/externallib.php');
            return local_shop_external_extended::get_catalogitems($catalogid, $categoryid, $status, $type, $q);
        }

        throw new moodle_exception('WS Not available in this distribution');
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_shop_returns() {
        return new external_single_structure(
             [
                'id' => new external_value(PARAM_INT, 'Shop id'),
                'name' => new external_value(PARAM_TEXT, 'Shop name'),
                'catalogid' => new external_value(PARAM_TEXT, 'Master shop catalog'),
                'description' => new external_value(PARAM_TEXT, 'Shop description'),
                'allowtax' => new external_value(PARAM_INT, 'Do the shop apply VAT tax'),
                'eulas' => new external_value(PARAM_TEXT, 'Shop eulas'),
                'paymodes' => new external_value(PARAM_TEXT, 'Enabled paymodes'),
                'defaultpaymode' => new external_value(PARAM_TEXT, 'Default paymode'),
            ]
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_catalog_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Catalog id'),
                'name' => new external_value(PARAM_TEXT, 'Catalog name'),
                'description' => new external_value(PARAM_TEXT, 'Catalog description'),
                'salesconditions' => new external_value(PARAM_TEXT, 'Catalog Eulas'),
                'countryrestrictions' => new external_value(PARAM_TEXT, 'Countries deserved'),
                'categories' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'id' => new external_value(PARAM_INT, 'Category id'),
                            'name' => new external_value(PARAM_TEXT, 'Category name'),
                        ]
                    )
                ),
            ]
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_catalogcategory_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Category id'),
                'catalogid' => new external_value(PARAM_INT, 'Catalog id'),
                'name' => new external_value(PARAM_TEXT, 'Category name'),
                'description' => new external_value(PARAM_TEXT, 'Category description'),
                'visible' => new external_value(PARAM_INT, 'Is category visible'),
            ]
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_catalogitem_returns() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'Item id'),
                'catalogid' => new external_value(PARAM_INT, 'Catalog id'),
                'categoryid' => new external_value(PARAM_INT, 'Item category id'),
                'code' => new external_value(PARAM_TEXT, 'Item code'),
                'shortname' => new external_value(PARAM_TEXT, 'Item shortname (for web UI)'),
                'name' => new external_value(PARAM_TEXT, 'Item name'),
                'description' => new external_value(PARAM_TEXT, 'Item description'),
                'eulas' => new external_value(PARAM_TEXT, 'Item eulas'),
                'notes' => new external_value(PARAM_TEXT, 'Item notes'),
                'type' => new external_value(PARAM_TEXT, 'Item type, plain, set or bundle'),
                'status' => new external_value(PARAM_TEXT, 'Item status'),
                'unitcost' => new external_value(PARAM_TEXT, 'Unit cost for input quantity'),
                'tax' => new external_value(PARAM_TEXT, 'Tax cost'),
                'requireddata' => new external_value(PARAM_TEXT, 'Required data from the front customer'),
                'leafleturl' => new external_value(PARAM_TEXT, 'Leaflet url'),
                'thumburl' => new external_value(PARAM_TEXT, 'Thumb url'),
                'imageurl' => new external_value(PARAM_TEXT, 'Image url'),
            ]
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function get_catalogitems_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Item id'),
                    'catalogid' => new external_value(PARAM_INT, 'Item catalog id'),
                    'categoryid' => new external_value(PARAM_INT, 'Item category id'),
                    'code' => new external_value(PARAM_TEXT, 'Item code'),
                    'shortname' => new external_value(PARAM_TEXT, 'Item shortname (for web UI)'),
                    'name' => new external_value(PARAM_TEXT, 'Item name'),
                    'description' => new external_value(PARAM_TEXT, 'Item description'),
                    'eulas' => new external_value(PARAM_TEXT, 'Item eulas'),
                    'notes' => new external_value(PARAM_TEXT, 'Item notes'),
                    'type' => new external_value(PARAM_TEXT, 'Item type, plain, set or bundle'),
                    'status' => new external_value(PARAM_TEXT, 'Item status'),
                    'unitcost' => new external_value(PARAM_TEXT, 'Unit cost for input quantity'),
                    'tax' => new external_value(PARAM_TEXT, 'Tax cost'),
                    'requireddata' => new external_value(PARAM_TEXT, 'Required data from the front customer'),
                    'leafleturl' => new external_value(PARAM_TEXT, 'Leaflet url'),
                    'thumburl' => new external_value(PARAM_TEXT, 'Thumb url'),
                    'imageurl' => new external_value(PARAM_TEXT, 'Image url'),
                ]
            )
        );
    }
}

