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
 * Version details.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2016 onwards Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2024053103; // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2022112801; // Requires this Moodle version.
$plugin->component = 'local_shop'; // Full name of the plugin (used for diagnostics).
$plugin->release = '4.1.0 (Build 2024053103)';
$plugin->maturity = MATURITY_STABLE;
if (function_exists('local_shop_supports_feature') && local_shop_supports_feature() === 'pro') {
    $plugin->dependencies = array('auth_ticket' => '2012060400', 'local_vfcore' => 2024053100);
} else {
    $plugin->dependencies = array('auth_ticket' => '2012060400');
}
$plugin->supported = [401, 402];

// Non moodle attributes.
$plugin->codeincrement = '4.1.0023';
$plugin->privacy = 'dualrelease';
$plugin->prolocations = array(
    'datahandling/handlers/std_addtrainingcredits',
    'datahandling/handlers/std_addquizattempts',
    'datahandling/handlers/std_createcategory',
    'datahandling/handlers/std_createcourse',
    'datahandling/handlers/std_createvinstance',
    'datahandling/handlers/std_generateseats',
    'datahandling/handlers/std_openltiaccess',
    'datahandling/handlers/std_enrolonecoursemultiple',
    'datahandling/handlers/std_setuponecoursesession',
    'datahandling/handlers/std_unlockpdcertificate',
    'datahandling/handlers/std_registeredproduct',
    'datahandling/handlers/std_prorogate',
    'paymodes/mercanet',
    'paymodes/sherlocks',
    'paymodes/systempay',
    'paymodes/ogone',
    'paymodes/paypalapi',
    'paymodes/paybox',
    'paymodes/publicmandate',
    'paymodes/stripe_checkout',
);