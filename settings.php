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

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');

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

    $key = 'local_shop/defaultnavsteps';
    $label = get_string('defaultnavsteps', 'local_shop');
    $desc = get_string('configdefaultnavsteps', 'local_shop');
    $default = 'shop,purchaserequ,users,customer,order,payment,produce,invoice';
    $settings->add(new admin_setting_configtext($key, $label, $desc, $default, PARAM_TEXT, 80));

    $key = 'local_shop/defaultcurrency';
    $currencies = shop_get_supported_currencies();
    $label = get_string('defaultcurrency', 'local_shop');
    $desc = get_string('configdefaultcurrency', 'local_shop');
    $settings->add(new admin_setting_configselect($key, $label, $desc, 'EUR', $currencies));

    $key = 'local_shop/test';
    $label = get_string('testmode', 'local_shop');
    $desc = get_string('configtestmode', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    $key = 'local_shop/testoverride';
    $label = get_string('testoverride', 'local_shop');
    $desc = get_string('configtestoverride', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    $key = 'local_shop/maxitemsperpage';
    $label = get_string('maxitemsperpage', 'local_shop');
    $desc = get_string('configmaxitemsperpage', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/hideproductswhennotavailable';
    $label = get_string('hideproductswhennotavailable', 'local_shop');
    $desc = get_string('confighideproductswhennotavailable', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    $settings->add(new admin_setting_heading('discounts', get_string('discounts', 'local_shop'), ''));

    $key = 'local_shop/discountthreshold';
    $label = get_string('discountthreshold', 'local_shop');
    $desc = get_string('configdiscounttheshold', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 0, PARAM_INT));

    $key = 'local_shop/discountrate';
    $label = get_string('discountrate', 'local_shop');
    $desc = get_string('configdiscountrate', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 0, PARAM_INT));

    $key = 'local_shop/discountrate2';
    $label = get_string('discountrate2', 'local_shop');
    $desc = get_string('configdiscountrate2', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 0, PARAM_INT));

    $key = 'local_shop/discountrate3';
    $label = get_string('discountrate3', 'local_shop');
    $desc = get_string('configdiscountrate3', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 0, PARAM_INT));

    $settings->add(new admin_setting_heading('local_shop_vendor', get_string('vendorinfo', 'local_shop'), ''));

    $key = 'local_shop/sellername';
    $label = get_string('sellername', 'local_shop');
    $desc = get_string('configsellername', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/selleraddress';
    $label = get_string('selleraddress', 'local_shop');
    $desc = get_string('configselleraddress', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerzip';
    $label = get_string('sellerzip', 'local_shop');
    $desc = get_string('configsellerzip', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellercity';
    $label = get_string('sellercity', 'local_shop');
    $desc = get_string('configsellercity', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellercountry';
    $label = get_string('sellercountry', 'local_shop');
    $desc = get_string('configsellercountry', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellermail';
    $label = get_string('sellermail', 'local_shop');
    $desc = get_string('configsellermail', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellermailsupport';
    $label = get_string('sellermailsupport', 'local_shop');
    $desc = get_string('configsellermailsupport', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerphonesupport';
    $label = get_string('sellerphonesupport', 'local_shop');
    $desc = get_string('configsellerphonesupport', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerID';
    $label = get_string('sellerID', 'local_shop');
    $desc = get_string('configsellerID', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerbillingaddress';
    $label = get_string('sellerbillingaddress', 'local_shop');
    $desc = get_string('configsellerbillingaddress', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerbillingzip';
    $label = get_string('sellerbillingzip', 'local_shop');
    $desc = get_string('configsellerbillingzip', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerbillingcity';
    $label = get_string('sellerbillingcity', 'local_shop');
    $desc = get_string('configsellerbillingcity', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerbillingcountry';
    $label = get_string('sellerbillingcountry', 'local_shop');
    $desc = get_string('configsellerbillingcountry', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/sellerlogo';
    $label = get_string('sellerlogo', 'local_shop');
    $desc = get_string('configsellerlogo', 'local_shop');
    $settings->add(new admin_setting_configstoredfile($key, $label, $desc, 'shoplogo'));

    shop_paymode::shop_add_paymode_settings($settings);

    $key = 'local_shop/bankinginfo';
    $settings->add(new admin_setting_heading($key, get_string('bankinginfo', 'local_shop'), ''));

    $key = 'local_shop/banking';
    $label = get_string('banking', 'local_shop');
    $desc = get_string('configbanking', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/bankcode';
    $label = get_string('bankcode', 'local_shop');
    $desc = get_string('configbankcode', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/bankoffice';
    $label = get_string('bankoffice', 'local_shop');
    $desc = get_string('configbankoffice', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/bankaccount';
    $label = get_string('bankaccount', 'local_shop');
    $deszc = get_string('configbankaccount', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/bankaccountkey';
    $label = get_string('bankaccountkey', 'local_shop');
    $desc = get_string('configbankaccountkey', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/iban';
    $label = get_string('iban', 'local_shop');
    $desc = get_string('configiban', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/bic';
    $label = get_string('bic', 'local_shop');
    $desc = get_string('configbic', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/tvaeurope';
    $label = get_string('tvaeurope', 'local_shop');
    $desc = get_string('configtvaeurope', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

    $key = 'local_shop/apparence';
    $settings->add(new admin_setting_heading($key, get_string('apparence', 'local_shop'), ''));

    $key = 'local_shop/productimageheight';
    $label = get_string('productimageheight', 'local_shop');
    $desc = get_string('configproductimageheight', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 150, PARAM_INT));

    $key = 'local_shop/productimagewidth';
    $label = get_string('productimagewidth', 'local_shop');
    $desc = get_string('configproductimagewidth', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 150, PARAM_INT));

    $key = 'local_shop/productimagermargin';
    $label = get_string('productimagermargin', 'local_shop');
    $desc = get_string('configproductimagermargin', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 40, PARAM_INT));

    $key = 'local_shop/elementimageheight';
    $label = get_string('elementimageheight', 'local_shop');
    $desc = get_string('configelementimageheight', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 80, PARAM_INT));

    $key = 'local_shop/elementimagewidth';
    $label = get_string('elementimagewidth', 'local_shop');
    $desc = get_string('configelementimagewidth', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 80, PARAM_INT));

    $key = 'local_shop/elementimagermargin';
    $label = get_string('elementimagermargin', 'local_shop');
    $desc = get_string('configelementimagermargin', 'local_shop');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 25, PARAM_INT));

    $key = 'local_shop/experimental';
    $settings->add(new admin_setting_heading($key, get_string('experimental', 'local_shop'), ''));

    $key = 'local_shop/useshipping';
    $label = get_string('useshipping', 'local_shop');
    $desc = get_string('configuseshipping', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    $key = 'local_shop/usedelegation';
    $label = get_string('usedelegation', 'local_shop');
    $desc = get_string('configusedelegation', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    $key = 'local_shop/useslavecatalogs';
    $label = get_string('useslavecatalogs', 'local_shop');
    $desc = get_string('configuseslavecatalogs', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    $key = 'local_shop/userenewableproducts';
    $label = get_string('userenewableproducts', 'local_shop');
    $desc = get_string('configuserenewableproducts', 'local_shop');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, ''));

    if (local_shop_supports_feature('emulate/community')) {
        // This will accept any
        $settings->add(new admin_setting_heading('plugindisthdr', get_string('plugindist', 'report_trainingsessions'), ''));

        $key = 'report_trainingsessions/emulatecommunity';
        $label = get_string('emulatecommunity', 'report_trainingsessions');
        $desc = get_string('emulatecommunity_desc', 'report_trainingsessions');
        $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 0));
    } else {
        $label = get_string('plugindist', 'report_trainingsessions');
        $desc = get_string('plugindist_desc', 'report_trainingsessions');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }

}