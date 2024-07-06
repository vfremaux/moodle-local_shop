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
 * Forum external functions and service definitions.
 *
 * @package    local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$functions = [

    'local_shop_get_shop' => [
        'classname' => 'local_shop_external',
        'methodname' => 'get_shop',
        'classpath' => 'local/shop/externallib.php',
        'description' => 'Get a shop instance description',
        'type' => 'read',
        'capabilities' => 'local/shop:export',
    ],

    'local_shop_get_catalog' => [
        'classname' => 'local_shop_external',
        'methodname' => 'get_catalog',
        'classpath' => 'local/shop/externallib.php',
        'description' => 'Get a catalog instance description',
        'type' => 'read',
        'capabilities' => 'local/shop:export',
    ],

    'local_shop_get_catalogcategory' => [
        'classname' => 'local_shop_external',
        'methodname' => 'get_catalogcategory',
        'classpath' => 'local/shop/externallib.php',
        'description' => 'Get a catalog category instance description',
        'type' => 'read',
        'capabilities' => 'local/shop:export',
    ],

    'local_shop_get_catalogitem' => [
        'classname' => 'local_shop_external',
        'methodname' => 'get_catalogitem',
        'classpath' => 'local/shop/externallib.php',
        'description' => 'Get a catalog item description',
        'type' => 'read',
        'capabilities' => 'local/shop:export',
    ],

    'local_shop_get_catalogitems' => [
        'classname' => 'local_shop_external',
        'methodname' => 'get_catalogitems',
        'classpath' => 'local/shop/externallib.php',
        'description' => 'Get catalog item list',
        'type' => 'read',
        'capabilities' => 'local/shop:export',
    ],
];

$services = [
    'Moodle Shop Definition Access API' => [
        'functions' => [
            'local_shop_get_shop',
            'local_shop_get_catalog',
            'local_shop_get_catalogcategory',
            'local_shop_get_catalogitem',
            'local_shop_get_catalogitems',
        ], // Web service function names.
        'requiredcapability' => 'local/shop:export',
        'restrictedusers' => 1,
        'enabled' => 0, // Used only when installing the services.
    ],
];