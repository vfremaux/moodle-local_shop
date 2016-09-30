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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/local/shop/paymodes/paymode.class.php';
require_once $CFG->dirroot.'/local/shop/locallib.php';

// Settings default init.
if (is_dir($CFG->dirroot.'/local/adminsettings')) {
    // Integration driven code.
    require_once($CFG->dirroot.'/local/adminsettings/lib.php');
    list($hasconfig, $hassiteconfig, $capability) = local_adminsettings_access();
} else {
    // Standard Moodle code.
    $capability = 'moodle/site:config';
    $hasconfig = $hassiteconfig = has_capability($capability, context_system::instance());
}

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_shop', get_string('pluginname', 'local_shop'));
    $ADMIN->add('localplugins', $settings);

    $gotobackofficestr = get_string('gotobackoffice', 'local_shop');
    $content = "<a href=\"{$CFG->wwwroot}/local/shop/index.php\">$gotobackofficestr</a>";
    $settings->add(new admin_setting_heading('backoffice', get_string('backoffice', 'local_shop'), $content));

    $settings->add(new admin_setting_heading('globalsettings', get_string('globalsettings', 'local_shop'), ''));

    $label = get_string('defaultnavsteps', 'local_shop');
    $desc = get_string('configdefaultnavsteps', 'local_shop');
    $default = 'shop,purchaserequ,users,customer,order,payment,produce,invoice';
    $settings->add(new admin_setting_configtext('local_shop/defaultnavsteps', $label, $desc, $default, PARAM_TEXT, 80));

    $currencies = shop_get_supported_currencies();
    $label = get_string('defaultcurrency', 'local_shop');
    $desc = get_string('configdefaultcurrency', 'local_shop');
    $settings->add(new admin_setting_configselect('local_shop/defaultcurrency', $label, $desc, 'EUR', $currencies));

    $label = get_string('testmode', 'local_shop');
    $desc = get_string('configtestmode', 'local_shop');
    $settings->add(new admin_setting_configcheckbox('local_shop/test', $label, $desc, ''));

    $label = get_string('testoverride', 'local_shop');
    $desc = get_string('configtestoverride', 'local_shop');
    $settings->add(new admin_setting_configcheckbox('local_shop/testoverride', $label, $desc, ''));

    $label = get_string('useshipping', 'local_shop');
    $desc = get_string('configuseshipping', 'local_shop');
    $settings->add(new admin_setting_configcheckbox('local_shop/useshipping', $label, $desc, ''));

    $label = get_string('usedelegation', 'local_shop');
    $desc = get_string('configusedelegation', 'local_shop');
    $settings->add(new admin_setting_configcheckbox('local_shop/usedelegation', $label, $desc, ''));

    $label = get_string('maxitemsperpage', 'local_shop');
    $desc = get_string('configmaxitemsperpage', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/maxitemsperpage', $label, $desc, '', PARAM_TEXT));

    $label = get_string('hideproductswhennotavailable', 'local_shop');
    $desc = get_string('confighideproductswhennotavailable', 'local_shop');
    $settings->add(new admin_setting_configcheckbox('local_shop/hideproductswhennotavailable', $label, $desc, ''));

    $settings->add(new admin_setting_heading('discounts', get_string('discounts', 'local_shop'), ''));

    $label = get_string('discountthreshold', 'local_shop');
    $desc = get_string('configdiscounttheshold', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/discountthreshold', $label, $desc, 0, PARAM_INT));

    $label = get_string('discountrate', 'local_shop');
    $desc = get_string('configdiscountrate', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/discountrate', $label, $desc, 0, PARAM_INT));

    $label = get_string('discountrate2', 'local_shop');
    $desc = get_string('configdiscountrate2', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/discountrate2', $label, $desc, 0, PARAM_INT));

    $label = get_string('discountrate3', 'local_shop');
    $desc = get_string('configdiscountrate3', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/discountrate3', $label, $desc, 0, PARAM_INT));

    $settings->add(new admin_setting_heading('local_shop_vendor', get_string('vendorinfo', 'local_shop'), ''));

    $label = get_string('sellername', 'local_shop');
    $desc = get_string('configsellername', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellername', $label, $desc, '', PARAM_TEXT));

    $label = get_string('selleraddress', 'local_shop');
    $desc = get_string('configselleraddress', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/selleraddress', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerzip', 'local_shop');
    $desc = get_string('configsellerzip', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerzip', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellercity', 'local_shop');
    $desc = get_string('configsellercity', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellercity', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellercountry', 'local_shop');
    $desc = get_string('configsellercountry', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellercountry', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellermail', 'local_shop');
    $desc = get_string('configsellermail', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellermail', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellermailsupport', 'local_shop');
    $desc = get_string('configsellermailsupport', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellermailsupport', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerphonesupport', 'local_shop');
    $desc = get_string('configsellerphonesupport', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerphonesupport', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerID', 'local_shop');
    $desc = get_string('configsellerID', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerID', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerbillingaddress', 'local_shop');
    $desc = get_string('configsellerbillingaddress', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerbillingaddress', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerbillingzip', 'local_shop');
    $desc = get_string('configsellerbillingzip', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerbillingzip', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerbillingcity', 'local_shop');
    $desc = get_string('configsellerbillingcity', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerbillingcity', $label, $desc, '', PARAM_TEXT));

    $label = get_string('sellerbillingcountry', 'local_shop');
    $desc = get_string('configsellerbillingcountry', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/sellerbillingcountry', $label, $desc, '', PARAM_TEXT));

    shop_paymode::shop_add_paymode_settings($settings);

    $settings->add(new admin_setting_heading('local_shop/bankinginfo', get_string('bankinginfo', 'local_shop'), ''));

    $label = get_string('banking', 'local_shop');
    $desc = get_string('configbanking', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/banking', $label, $desc, '', PARAM_TEXT));

    $label = get_string('bankcode', 'local_shop');
    $desc = get_string('configbankcode', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/bankcode', $label, $desc, '', PARAM_TEXT));

    $label = get_string('bankoffice', 'local_shop');
    $desc = get_string('configbankoffice', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/bankoffice', $label, $desc, '', PARAM_TEXT));

    $label = get_string('bankaccount', 'local_shop');
    $deszc = get_string('configbankaccount', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/bankaccount', $label, $desc, '', PARAM_TEXT));

    $label = get_string('bankaccountkey', 'local_shop');
    $desc = get_string('configbankaccountkey', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/bankaccountkey', $label, $desc, '', PARAM_TEXT));

    $label = get_string('iban', 'local_shop');
    $desc = get_string('configiban', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/iban', $label, $desc, '', PARAM_TEXT));

    $label = get_string('bic', 'local_shop');
    $desc = get_string('configbic', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/bic', $label, $desc, '', PARAM_TEXT));

    $label = get_string('tvaeurope', 'local_shop');
    $desc = get_string('configtvaeurope', 'local_shop');
    $settings->add(new admin_setting_configtext('local_shop/tvaeurope', $label, $desc, '', PARAM_TEXT));

}