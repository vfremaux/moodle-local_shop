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
 * Main paymode class
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_shop\Bill;
use local_shop\Shop;

/**
 * This abstract class emplements an object wrapper for a payment method
 * in the shop block
 *
 * All payment method should be a subclass of this class.
 * A payment method provides callbacks that execute specific code
 * for this payment mode.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
abstract class shop_paymode {

    /** @var string the plugin name */
    protected $name;

    /** @var bool is the plugin enabled */
    public $enabled;

    /** @var bool is the plugin overriden */
    protected $overridelocalconfirm;

    /** @var Shop the shop */
    protected $theshop;

    /** @var object internal config */
    protected $_config;

    /** @var bool After processing will tell if the transaction is handled in interactive mode. */
    public $interactive;

    /**
     * Constructor
     * @param string $name
     * @param Shop $shop
     * @param bool $enabled
     * @param bool $overridelocalconfirm
     */
    public function __construct(string $name, ?Shop $shop, bool $enabled = true, bool $overridelocalconfirm = false) {
        $this->name = $name;
        $this->theshop = $shop;
        $this->enabled = $enabled;
        $this->overridelocalconfirm = $overridelocalconfirm;
        $this->interactive = true;

        // Optimisation. Get it once per instance.
        $this->_config = get_config('local_shop');
    }

    /**
     * Is this plugin for immediate payment ?
     */
    public function is_instant_payment() {
        return false;
    }

    /**
     * Prints a payment portlet in an order form from the shoppingcart content in SESSION.
     * Shopping cart may be altered.
     */
    public abstract function print_payment_portlet();

    /**
     * Prints a payment info on an invoice.
     * @param Bill $billdata
     */
    public abstract function print_invoice_info(?Bill $billdata = null);

    /**
     * Prints a message when transaction is complete.
     */
    public abstract function print_complete();

    /**
     * Processes a payment return.
     */
    public abstract function process();

    /**
     * Processes a payment asynchronoous confirmation.
     */
    public abstract function process_ipn();

    /**
     * Provides global settings to add to shop settings when installed.
     * @param StdClass $settings
     */
    public abstract function settings($settings);

    /**
     * Provides global settings to add to shop settings when installed.
     * @param moodle_form $mform
     */
    public function add_instance_config(MoodleQuickForm $mform) {

        $isenabledvar = "enable".$this->get_name();

        $group[] = &$mform->createElement('checkbox', $isenabledvar);
        $group[] = &$mform->createElement('radio', 'defaultpaymode', '', '', $this->get_name());
        $label = get_string('enable'.$this->get_name(), 'shoppaymodes_'.$this->get_name());
        $mform->addGroup($group, 'paymode'.$this->get_name(), $label, ' '.get_string('isdefault', 'local_shop').' ', false);
    }

    /**
     * Accessor
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * printable name
     * @param bool $return
     * @return paymode name as a string
     */
    public function print_name($return = false) {
        if ($return) {
            return get_string('pluginname', 'shoppaymodes_'.$this->get_name());
        }
        echo get_string('pluginname', 'shoppaymodes_'.$this->get_name());
    }

    /**
     * Get a mail template.
     * @param string $mailtype
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get_mail($mailtype, $data) {
        assert(true);
    }

    /**
     * Tells if this paymode does not need interactive order confirm
     * this is the case for most instant online payment plugins
     * @return boolean
     */
    public function needslocalconfirm() {
        return !$this->overridelocalconfirm;
    }

    /**
     * This static function defers to each plugin the possibility to
     * catch a valid payment session identification in the query environement
     * depending on specific ways to interpret data return from remote
     * payment gateway. It returns all the technical components of a valid
     * running transaction and returns initialized paymode plugin for it.
     * @param string $transid placeholder to be resolved as transaction ID
     * @param string $cmd placeholder to be resolved as operation
     * @param string $paymode placeholder to be resolved as paymode name
     * @return the paymode plugin instance that fits the transaction
     * @todo : reconsider if usefull. Seems never used. 
     */
    public static function resolve_transaction_identification($transid, $cmd, $paymode) {
        global $DB;

        $plugins = self::shop_get_plugins(null);
        $transid = '';
        $cmd = '';

        foreach ($plugins as $plugin) {
            $plugin->identify_transaction($transid, $cmd);
            if (!empty($transid)) {
                $paymode = strtolower($DB->get_field('local_shop_bill', 'paymode', ['transactionid' => $transid]));
                if ($paymode != $plugin->get_name()) {
                    $transid = '';
                    $cmd = '';
                    throw new moodle_exception(get_string('paymodedonotmatchtoresponse', 'local_shop'));
                }
                // We have valid transid and cmd and paymode, so process it in controller.
                return [$plugin, $transid, $cmd];
            }
        }
        return null;
    }

    /**
     * get all payment plugins available in a shop.
     * @param Shop $shop
     * @return an array of paymode objects
     */
    public static function get_plugins(Shop $shop) {
        global $CFG;

        $plugins = get_list_of_plugins('/local/shop/paymodes', 'CVS');
        foreach ($plugins as $p) {
            include_once($CFG->dirroot.'/local/shop/paymodes/'.$p.'/'.$p.'.class.php');
            $classname = "shop_paymode_$p";
            $payments[$p] = new $classname($shop);
        }
        return $payments;
    }

    /**
     * Get all payment plugins list for choice.
     * @return an array for select
     */
    public static function get_list() {
        $paylist = [];
        $plugins = get_list_of_plugins('/local/shop/paymodes', 'CVS');
        foreach ($plugins as $p) {
            $paylist[$p] = get_string('pluginname', 'shoppaymodes_'.$p);
        }
        return $paylist;
    }

    /**
     * get one plugin instance by name (Factory)
     * @param Shop $shop the shop instance we are working on
     * @param string $paymentpluginname instance builder from name
     */
    public static function get_instance(Shop $shop, $paymentpluginname) {
        global $CFG;

        include_once($CFG->dirroot.'/local/shop/paymodes/'.$paymentpluginname.'/'.$paymentpluginname.'.class.php');
        $classname = 'shop_paymode_'.$paymentpluginname;
        $payment = new $classname($shop);
        return $payment;
    }

    /**
     * Aggregate gobal settings for all available paymodes
     * @param StdClass $settings the global settings
     */
    public static function shop_add_paymode_settings($settings) {
        global $CFG;

        $plugins = get_list_of_plugins('/local/shop/paymodes', 'CVS');
        foreach ($plugins as $p) {
            include_once($CFG->dirroot.'/local/shop/paymodes/'.$p.'/'.$p.'.class.php');
            $classname = "shop_paymode_$p";
            $shop = null;
            $pm = new $classname($shop);
            // No need of real shop instances here.
            if ($pm->enabled) {
                $pm->settings($settings);
            }
        }
    }
}
