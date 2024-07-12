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

/*
 * @package    local_shop
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright  Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Ajax services for Local shop.
 */
require('../../../../config.php'); // May require level adjust.
require_once($CFG->dirroot.'/local/shop/pro/prolib.php');

$promanager = local_shop\pro_manager::instance();

// Nothing changes beyond this line.

$action = required_param('what', PARAM_TEXT);

// Security.
$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
$config = get_config($promanager::$component);

if ($action == 'license') {
    $customerkey = required_param('customerkey', PARAM_TEXT);
    $provider = required_param('provider', PARAM_TEXT);

    // Protects those screens whe do not have provider in the template.
    if (empty($provider) || $provider == 'undefined') {
        $provider = $config->licenseprovider;
    }

    shop_debug_trace('Firing '.$customerkey.' to provider '.$provider, SHOP_TRACE_DEBUG);

    $result = $promanager->set_and_check_license_key($customerkey, $provider);
    echo $result;
    die;
}