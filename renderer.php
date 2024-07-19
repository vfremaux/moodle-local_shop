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
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

use local_shop\Catalog;
use local_shop\Customer;
use local_shop\Shop;

/**
 * A general renderer for global parts of the moodle shop
 * It will provide generic functions that may be used in several services inside
 * the shop front and backoffice implementation.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class local_shop_renderer extends local_shop_base_renderer {

    /**
     * prints an owner menu and changes currently viewed owner if required
     * @param string $urlroot
     * @param string $activeowner
     */
    public function print_owner_menu($urlroot, $activeowner) {
        global $OUTPUT, $DB;

        $config = get_config('local_shop');

        if (empty($config->usedelegation)) {
            return;
        }

        $owners = $DB->get_records_select('local_shop_customer', " hasaccount > 0 ", [], 'hasaccount,firstname,lastname');

        $ownersmenu = [];
        if ($owners) {
            foreach ($owners as $accountid => $owner) {
                $ownersmenu[$accountid] = $owner->lastname.' '.$owner->firstname;
            }
        }

        $ownerlabel = get_string('currentowner', 'local_shop');

        if (count($owners) == 1) {
            $ownername = reset($owners);
            $output = $ownerlabel.': '.$ownername->lastname.' '.$ownername->firstname;
        } else {
            $select = new single_select(new moodle_url($urlroot), 'shopowner', $ownersmenu, $activeowner, null, 'selectowner');
            $select->label = $ownerlabel;
            $output = $OUTPUT->render($select);
        }

        $output = '<div class="shopownerselector">'.$output.'</div>';

        return $output;
    }

    /**
     * prints a customer menu and changes currently viewed owner if required
     * @param string $urlroot
     * @param arrayref &$customers
     * @param int $activecustomerid
     */
    public function print_customer_menu($urlroot, &$customers, $activecustomerid) {
        global $OUTPUT;

        $customersmenu = [];
        if ($customers) {
            foreach ($customers as $cid => $cu) {
                $customersmenu[$cid] = $cu->lastname.' '.$cu->firstname.' ('.$cu->city.') ['.$cu->country.']';
            }
        }

        $customerlabel = get_string('currentcustomer', 'local_shop');

        if (count($customers) == 1) {
            $defaultcustomer = array_pop($customers);
            $output = $customerlabel.': '.$defaultcustomer->lastname.' '.$defaultcustomer->firstname;
            $output .= ' ('.$defaultcustomer->city.') ['.$defaultcustomer->country.']';
        } else {
            $u = new moodle_url($urlroot);
            $select = new single_select($u, 'customer', $customersmenu, $activecustomerid, ['' => 'choosedots'], 'selectcustomer');
            $select->label = $customerlabel;
            $output = $OUTPUT->render($select);
        }

        $output = '<div class="shopcustomerselector">'.$output.'</div>';

        return $output;
    }

    /**
     * Renders a custom pager
     * @TODO : maybe could be replaced with moodle standard pager.
     * @param object $portlet an object with all data for pager
     */
    public function paging_results($portlet) {
        $str = '';
        if (empty($portlet->pagesize)) {
            $portlet->pagesize = 30;
        }
        if ($portlet->pagesize < $portlet->total) {
            $pages = ceil($portlet->total / $portlet->pagesize);
            $offset = optional_param('offset', 0, PARAM_INT);
            if ($offset > 0) {
                $pageoffset = $offset - $portlet->pageSize;
                $str .= '<a href="'.$portlet->url.'&offset='.$pageoffset.'">&lt;</a> - ';
            }
            $str .= '<span class="paging">';
            for ($i = 1; $i <= $pages; $i++) {
                if ($i == ($offset / $portlet->pagesize) + 1) {
                    $str .= ' <div class="shop-paging">'.$i.'</div> - ';
                } else {
                    $pageoffset = $portlet->pagesize * ($i - 1);
                    $str .= '<a class="paging" href="'.$portlet->url.'&offset='.$pageoffset.'">'.$i.'</a> - ';
                }
            }
            $str .= '</span>';
            if ($offset + $portlet->pagesize < $portlet->total) {
                $pageoffset = $offset + $portlet->pagesize;
                $nexturl = $portlet->url.'&offset='.$pageoffset;
                $str .= '<a href="'.$nexturl.'" >&gt;</a>';
            }
        }

        return $str;
    }

    /**
     * A widget showing links to available catalogs
     * @param mixed $url a string or a moodle_url as base url.
     */
    public function catalog_choice($url) {
        global $SESSION, $OUTPUT;

        $str = '';
        $catalogs = Catalog::get_instances();
        $catalogmenu = [];
        foreach ($catalogs as $c) {
            $catalogmenu[$c->id] = format_string($c->name);
        }
        $str .= $OUTPUT->single_select($url, 'catalogid', $catalogmenu, $SESSION->shop->catalogid);

        return $str;
    }

    /**
     * A chooser for switching shops
     * @param moodle_url $url base url
     * @param bool $chooseall if true, adds a choose all shops possibility
     * @param int $shopid the preselected shop. If null, taken from $SESSION
     */
    public function shop_choice($url, $chooseall = false, $shopid = null) {
        global $SESSION, $OUTPUT;

        $str = '';
        $shops = Shop::get_instances();
        $shopmenu = [];

        if ($chooseall) {
            $shopmenu[0] = get_string('allshops', 'local_shop');
        }

        foreach ($shops as $s) {
            $shopmenu[$s->id] = format_string($s->name);
        }
        if (is_null($shopid)) {
            $str .= $OUTPUT->single_select($url, 'shopid', $shopmenu, $SESSION->shop->shopid, null);
        } else {
            $str .= $OUTPUT->single_select($url, 'shopid', $shopmenu, $shopid, null);
        }

        return $str;
    }

    /** 
     * A selector to change current currency
     * @param string $current current currency
     * @param moodle_url $url the base url
     */
    public function currency_choice($current, $url) {
        global $OUTPUT;

        $currencies = shop_get_supported_currencies();

        $str = '';

        $str .= $OUTPUT->single_select($url, 'cur', $currencies, $current);

        return $str;
    }

    /** 
     * A selector to change current year time scope
     * @param int $current the current year
     * @param moodle_url $url the base url
     */
    public function year_choice($current, $url) {
        global $OUTPUT, $DB, $SESSION;

        if ($current) {
            // Register in user's session.
            $SESSION->shop->billyear = $current;
        }

        $firstyear = $DB->get_field('local_shop_bill', 'MIN(emissiondate)', []);
        $lastyear = $DB->get_field('local_shop_bill', 'MAX(emissiondate)', []);

        if (!$firstyear && !$lastyear) {
            return '';
        }

        $firstyear = date('Y', $firstyear);
        $lastyear = date('Y', $lastyear);
        $laststep = $lastyear - $firstyear;
        for ($i = 0; $i <= $laststep; $i++) {
            $years[$firstyear + $i] = $firstyear + $i;
        }

        $str = '';

        $str .= $OUTPUT->single_select($url, 'y', $years, $current);

        return $str;
    }

    /** 
     * A selector to change current month time scope
     * @param int $current the current month
     * @param moodle_url $url the base url
     */ 
    public function month_choice($current, $url) {
        global $OUTPUT, $SESSION;

        if ($current) {
            // Register in user's session.
            $SESSION->shop->billmonth = $current;
        }

        $monthnames = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];

        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = get_string($monthnames[$i - 1], 'local_shop');
        }

        $str = '';

        $str .= $OUTPUT->single_select($url, 'm', $months, $current);

        return $str;
    }

    /** 
     * A selector to change current customer in context
     * @param int $current the current customer id
     * @param moodle_url $url the base url
     */ 
    public function customer_choice($current, $url) {
        global $OUTPUT;

        $customers = Customer::get_instances_menu([], 'lastname, firstname');

        $str = '';

        $customers = ['' => get_string('allcustomers', 'local_shop')] + $customers;
        $attrs['label'] = get_string('customer', 'local_shop').': ';
        $str .= $OUTPUT->single_select($url, 'customerid', $customers, $current, null, null, $attrs);

        return $str;
    }

    /**
     * Main menu in shop index.
     * @param object $theshop the currently selected shop
     */
    public function main_menu($theshop) {

        $config = get_config('local_shop');

        $template = new StdClass;

        $template->supportsinstances = false;
        if (local_shop_supports_feature('shop/instances')) {
            $template->supportsinstances = true;
            $params = ['view' => 'viewAllShops', 'id' => $theshop->id];
            $template->allshopsurl = new moodle_url('/local/shop/pro/shop/view.php', $params);
        } else {
            $params = ['id' => $theshop->id, 'shopid' => $theshop->id];
            $template->shopsettingsurl = new moodle_url('/local/shop/shop/edit_shop.php', $params);
        }

        if (local_shop_supports_feature('shop/discounts')) {
            $template->supportsdiscounts = true;
            $params = ['view' => 'viewAllDiscounts', 'id' => $theshop->id];
            $template->discountsurl = new moodle_url('/local/shop/pro/discounts/view.php', $params);
        }

        $params = ['view' => 'viewAllBills', 'id' => $theshop->id];
        $template->billsurl = new moodle_url('/local/shop/bills/view.php', $params);
        $params = ['view' => 'viewAllProductInstances', 'id' => $theshop->id];
        $template->productsurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
        $params = ['view' => 'viewAllCustomers', 'id' => $theshop->id];
        $template->customersurl = new moodle_url('/local/shop/customers/view.php', $params);
        $params = ['view' => 'viewAllTaxes', 'id' => $theshop->id];
        $template->taxesurl = new moodle_url('/local/shop/taxes/view.php', $params);

        if (!empty($config->useshipping)) {
            $template->useshipping = true;
            $template->shippingurl = new moodle_url('/local/shop/shipzones/index.php', ['id' => $theshop->id]);
        }

        $template->traceurl = new moodle_url('/local/shop/front/scantrace.php', ['id' => $theshop->id]);

        if (has_capability('moodle/site:config', context_system::instance())) {
            $template->hassiteadmin = true;
            $template->settingsurl = new moodle_url('/admin/settings.php', ['section' => 'localsettingshop']);
        }

        $template->reseturl = new moodle_url('/local/shop/reset.php', ['id' => $theshop->id]);

        if (local_shop_supports_feature('shop/partners')) {
            $template->supportspartners = true;
            $params = ['id' => $theshop->id, 'view' => 'viewAllPartners'];
            $template->partnersurl = new moodle_url('/local/shop/pro/partners/view.php', $params);
        }

        return $this->output->render_from_template('local_shop/main_menu', $template);
    }

    /**
     * Generates a back button for the purchase process.
     */
    public function back_buttons() {
        global $OUTPUT;

        $str = '';

        $options['id'] = $this->theshop->id;
        $label = get_string('backtoshopadmin', 'local_shop');
        $str .= $OUTPUT->single_button(new moodle_url('/local/shop/index.php', $options), $label, 'get');
        $options['view'] = 'shop';
        $label = get_string('backtoshop', 'local_shop');
        $str .= $OUTPUT->single_button(new moodle_url('/local/shop/front/view.php', $options), $label, 'get');

        return $str;
    }

    /**
     * A chooser for the transaction backtrace
     * @TODO : shift to template
     * @param string $transid the currently selected transaction id
     */
    public function transaction_chooser($transid) {
        global $DB;

        $transids = $DB->get_records('local_shop_bill', null, 'id', 'transactionid, amount');
        $scanstr = get_string('scantrace', 'local_shop');

        $transidsmenu = [];
        if ($transids) {
            foreach ($transids as $trans) {
                $transidsmenu[$trans->transactionid] = $trans->transactionid.' ('.$trans->amount.')';
            }
        }

        $str = '';

        $str .= '<form name="transidform" method="POST" >';
        print_string('picktransactionid', 'local_shop');
        $str .= html_writer::select($transidsmenu, 'transid', $transid);
        $str .= '<input type="submit" name="g_btn" value="'.$scanstr.'" />';
        $str .= '</form>';

        return $str;
    }
}

/**
 * A base class to centralize all common things
 */
class local_shop_base_renderer extends \plugin_renderer_base {

    // Context references.
    /**
     * The initial block instance from where we got access to the shop.
     * **DEPRECATED** : This seems not a very operable context data to use.
     */
    protected $theblock;

    /**
     * the currently active shop, i.e. public front end that is the actual context of navigation.
     */
    protected $theshop;

    /**
     * the currently displaying product catalog.
     */
    protected $thecatalog;

    /**
     * The current catalog category to display, for some renderers.
     * Captures an URL param to tell other renderers where we are.
     */
    protected $categoryid;

    /**
     * the generic $OUTPUT
     */
    protected $output;

    /**
     * this is to cope with subrenderers standards
     * @TODO : reshape renderers into core fashion subrenderers.
     */
    public function __construct() {
        global $OUTPUT;

        $this->output = $OUTPUT;
    }

    /**
     * Loads the renderer with contextual objects. Most of the renderer function need
     * at least a shop instance.
     * @param objectref &$theshop
     * @param arrayref &$thecatalog
     * @param objectref &$theblock
     */
    public function load_context(&$theshop, &$thecatalog, &$theblock = null) {

        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;

        if (!empty($this->theblock->instance->id)) {
            $this->context = context_block::instance($this->theblock->instance->id);
            $this->theblock->id = $this->theblock->instance->id;
        } else {
            $this->context = context_system::instance();
            $this->theblock = new Stdclass();
            $this->theblock->id = 0;
        }

        if (empty($this->categoryid)) {
            $this->categoryid = optional_param('categoryid', 0, PARAM_INT);
        }
    }

    /**
     * checks if context has been initialized. This is a developer utility to catch
     * uninitialized code locations.
     */
    public function check_context() {
        if (empty($this->theshop) || empty($this->thecatalog)) {
            throw new coding_exception('the renderer is not ready for use. Load a shop and a catalog before calling.');
        }
    }

    /**
     * Prints the reference time relative to UTC time.
     */
    public function reference_time() {
        $str = '';

        $str .= '<br/>';
        $str .= '<div class="reference-time">';
        $str .= '<b>UTC :</b> '.gmdate('Y/m/d H:i:s'). ' <b>Local :</b> '.date('Y/m/d H:i:s');
        $str .= '</div>';
        $str .= '<br/>';

        return $str;
    }

    /**
     *
     */
    public function print_screen_button() {
        $template = new StdClass();
        return $this->output->render_from_template('local_shop/commons_print_screen_button', $template);
    }

    /**
     * Renders a template by string with the given context.
     *
     * The provided data needs to be array/stdClass made up of only simple types.
     * Simple types are array,stdClass,bool,int,float,string
     *
     * @param string $templatestring a mustache template from a string variable.
     * @param array|stdClass $context Context containing data for the template.
     * @return string|boolean
     */
    public function render_from_string($templatestring, $context) {

        $mustache = $this->get_mustache();
        $loader = new Mustache_Loader_StringLoader();
        $mustache->setLoader($loader);

        try {
            // Grab a copy of the existing helper to be restored later.
            $uniqidhelper = $mustache->getHelper('uniqid');
        } catch (Mustache_Exception_UnknownHelperException $e) {
            // Helper doesn't exist.
            $uniqidhelper = null;
        }

        /*
         * Provide 1 random value that will not change within a template
         * but will be different from template to template. This is useful for
         * e.g. aria attributes that only work with id attributes and must be
         * unique in a page.
         */
        $mustache->addHelper('uniqid', new \core\output\mustache_uniqid_helper());

        $renderedtemplate = $mustache->render($templatestring, $context);

        /*
         * If we had an existing uniqid helper then we need to restore it to allow
         * handle nested calls of render_from_template.
         */
        if ($uniqidhelper) {
            $mustache->addHelper('uniqid', $uniqidhelper);
        }

        return $renderedtemplate;
    }

    /**
     * Set the current page for pagers
     * @param int $page
     */
    public function set_page($page) {
        $this->page = $page;
    }
}
