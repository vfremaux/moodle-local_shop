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
    $settings->add(new admin_setting_heading('backoffice', get_string('backoffice', 'local_shop'), "<a href=\"{$CFG->wwwroot}/local/shop/index.php\">$gotobackofficestr</a>"));

    $settings->add(new admin_setting_heading('globalsettings', get_string('globalsettings', 'local_shop'), ''));

    $settings->add(new admin_setting_configtext('local_shop/defaultnavsteps', get_string('defaultnavsteps', 'local_shop'),
                       get_string('configdefaultnavsteps', 'local_shop'), 'shop,purchaserequ,users,customer,order,payment,produce,invoice', PARAM_TEXT, 80));

    $currencies = shop_get_supported_currencies();
    $settings->add(new admin_setting_configselect('local_shop/defaultcurrency', get_string('defaultcurrency', 'local_shop'),
                       get_string('configdefaultcurrency', 'local_shop'), 'EUR', $currencies));

    $settings->add(new admin_setting_configcheckbox('local_shop/test', get_string('testmode', 'local_shop'),
                       get_string('configtestmode', 'local_shop'), ''));

    $settings->add(new admin_setting_configcheckbox('local_shop/testoverride', get_string('testoverride', 'local_shop'),
                       get_string('configtestoverride', 'local_shop'), ''));

    $settings->add(new admin_setting_configcheckbox('local_shop/useshipping', get_string('useshipping', 'local_shop'),
                       get_string('configuseshipping', 'local_shop'), ''));

    $settings->add(new admin_setting_configcheckbox('local_shop/usedelegation', get_string('usedelegation', 'local_shop'),
                       get_string('configusedelegation', 'local_shop'), ''));

    $settings->add(new admin_setting_configtext('local_shop/maxitemsperpage', get_string('maxitemsperpage', 'local_shop'),
                        get_string('configmaxitemsperpage', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox('local_shop/hideproductswhennotavailable', get_string('hideproductswhennotavailable', 'local_shop'),
                        get_string('confighideproductswhennotavailable', 'local_shop'), ''));

    $settings->add(new admin_setting_heading('discounts', get_string('discounts', 'local_shop'), ''));

    $settings->add(new admin_setting_configtext('local_shop/discountthreshold', get_string('discountthreshold', 'local_shop'),
                       get_string('configdiscounttheshold', 'local_shop'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_shop/discountrate', get_string('discountrate', 'local_shop'),
                       get_string('configdiscountrate', 'local_shop'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_shop/discountrate2', get_string('discountrate2', 'local_shop'),
                       get_string('configdiscountrate2', 'local_shop'), 0, PARAM_INT));

    $settings->add(new admin_setting_configtext('local_shop/discountrate3', get_string('discountrate3', 'local_shop'),
                       get_string('configdiscountrate3', 'local_shop'), 0, PARAM_INT));

    $settings->add(new admin_setting_heading('local_shop_vendor', get_string('vendorinfo', 'local_shop'), ''));

    $settings->add(new admin_setting_configtext('local_shop/sellername', get_string('sellername', 'local_shop'),
                       get_string('configsellername', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/selleraddress', get_string('selleraddress', 'local_shop'),
                       get_string('configselleraddress', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerzip', get_string('sellerzip', 'local_shop'),
                       get_string('configsellerzip', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellercity', get_string('sellercity', 'local_shop'),
                       get_string('configsellercity', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellercountry', get_string('sellercountry', 'local_shop'),
                       get_string('configsellercountry', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellermail', get_string('sellermail', 'local_shop'),
                       get_string('configsellermail', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellermailsupport', get_string('sellermailsupport', 'local_shop'),
                       get_string('configsellermailsupport', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerphonesupport', get_string('sellerphonesupport', 'local_shop'),
                       get_string('configsellerphonesupport', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerID', get_string('sellerID', 'local_shop'),
                       get_string('configsellerID', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerbillingaddress', get_string('sellerbillingaddress', 'local_shop'),
                       get_string('configsellerbillingaddress', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerbillingzip', get_string('sellerbillingzip', 'local_shop'),
                       get_string('configsellerbillingzip', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerbillingcity', get_string('sellerbillingcity', 'local_shop'),
                       get_string('configsellerbillingcity', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/sellerbillingcountry', get_string('sellerbillingcountry', 'local_shop'),
                       get_string('configsellerbillingcountry', 'local_shop'), '', PARAM_TEXT));

    shop_paymode::shop_add_paymode_settings($settings);

    $settings->add(new admin_setting_heading('local_shop/bankinginfo', get_string('bankinginfo', 'local_shop'), ''));

    $settings->add(new admin_setting_configtext('local_shop/banking', get_string('banking', 'local_shop'),
                       get_string('configbanking', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/bankcode', get_string('bankcode', 'local_shop'),
                       get_string('configbankcode', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/bankoffice', get_string('bankoffice', 'local_shop'),
                       get_string('configbankoffice', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/bankaccount', get_string('bankaccount', 'local_shop'),
                       get_string('configbankaccount', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/bankaccountkey', get_string('bankaccountkey', 'local_shop'),
                       get_string('configbankaccountkey', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/iban', get_string('iban', 'local_shop'),
                       get_string('configiban', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/bic', get_string('bic', 'local_shop'),
                       get_string('configbic', 'local_shop'), '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_shop/tvaeurope', get_string('tvaeurope', 'local_shop'),
                        get_string('configtvaeurope', 'local_shop'), '', PARAM_TEXT));

}