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
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/renderer.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');

use local_shop\Shop;
use local_shop\Product;
use local_shop\BillItem;
use local_shop\CatalogItem;

/**
 * Renderer for product instances backoffice.
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
class shop_purchasemanager_renderer extends local_shop_base_renderer {

    /**
     * Displays a single product instance admin line.
     * @param array $productinstances a full Product instance.
     * @param array $viewparams contextual query params from the view.
     * @param int $customerid
     * @param int $shopowner
     */
    public function productinstance_admin_form($productinstances, $viewparams = [], $customerid = 0, $shopowner = 0) {

        $this->check_context();

        $template = new StdClass;

        $template->selstr = get_string('sel', 'local_shop');
        $template->imagestr = '';
        $template->billstr = get_string('bill', 'local_shop');
        $template->codestr = get_string('code', 'local_shop');
        $template->designationstr = get_string('designation', 'local_shop');
        $template->renewablestr = get_string('renewable', 'local_shop');
        $template->contexttypestr = get_string('contexttype', 'local_shop');
        $template->instancestr = get_string('instance', 'local_shop');
        $template->purchasedpricestr = get_string('purchasedprice', 'local_shop');
        $template->referencestr = get_string('reference', 'local_shop');
        $template->startdatestr = get_string('startdate', 'local_shop');
        $template->enddatestr = get_string('enddate', 'local_shop');

        $template->customerid = $customerid;
        $template->shopowner = $shopowner;

        $template->formurl = new moodle_url('/local/shop/purchasemanager/view.php');

        foreach (array_values($productinstances) as $productinstance) {
            $producttpl = new StdClass;
            $billitem = null;
            if ($productinstance->initialbillitemid) {
                if (\local_shop\ShopObject::exists($productinstance->initialbillitemid, 'billitem')) {
                    $billitem = new BillItem($productinstance->initialbillitemid, false, []);
                }
            }
            if ($productinstance->currentbillitemid) {
                if (\local_shop\ShopObject::exists($productinstance->currentbillitemid, 'billitem')) {
                    $currentbillitem = new BillItem($productinstance->currentbillitemid, false, []);
                    $pix = $this->output->pix_icon('bill', '', 'local_shop');
                    $params = ['view' => 'viewBill', 'billid' => $currentbillitem->billid];
                    $linkurl = new moodle_url('/local/shop/bills/view.php', $params);
                    $attrs = ['target' => 'blank'];
                    $producttpl->currentbilllink = html_writer::link($linkurl, $pix, $attrs);
                }
            }
            $product = new CatalogItem($productinstance->catalogitemid);

            $totals = [
                'expired' => 0,
                'expiring' => 0,
                'ending' => 0,
                'pending' => 0,
                'running' => 0
            ];
            $producttpl->statusclass = '';
            $params = ['view' => 'viewProductDetail', 'itemid' => $product->id];
            $producturl = new moodle_url('/local/shop/products/view.php', $params);
            $producttpl->code = '<a href="'.$producturl.'">'.$product->code.'</a>';
            $producttpl->designation = format_string($product->name);
            $producttpl->reference = $productinstance->reference;
            $producttpl->extradata = $this->process_extradata($productinstance);
            $producttpl->renewable = ($product->renewable) ? get_string('yes') : '';

            if ($productinstance->enddate) {
                $producttpl->pend = date('Y/m/d H:i', $productinstance->enddate);
            } else {
                $producttpl->pend = 'N.C.';
            }

            $producttpl->pstart = date('Y/m/d H:i', $productinstance->startdate);
            $statusclass = $this->get_productinstance_running_status($productinstance, $totals);
            $producttpl->statusclass = $statusclass;

            if (has_capability('local/shop:salesadmin', context_system::instance())) {
                $producttpl->selcheckbox = '<input type="checkbox" id="purchase-select-'.$productinstance->id.'" class="purchase-selects unfiltered" name="productids" value="'.$productinstance->id.'" />';
            }
            $producttpl->id = $productinstance->id;
            $producttpl->thumburl = $product->get_thumb_url();
            $producttpl->contexttype = get_string($productinstance->contexttype, 'local_shop');
            $producttpl->instancelink = $productinstance->get_instance_link();
            $producttpl->unitcost = ($billitem) ? $billitem->unitcost : 'N.C.';
            $producttpl->currency = $this->theshop->get_currency();

            $producttpl->commands = $this->get_product_commands($productinstance, $viewparams);

            $template->products[] = $producttpl;
        }

        return $this->output->render_from_template('local_shop/purchaselist', $template);
    }

    /**
     * Given a product instance, returns the product status CSS class to apply.
     * @param Product $productinstance
     * @param array &$totals
     */
    protected function get_productinstance_running_status(Product $productinstance, &$totals) {

            $now = time();

            if ($productinstance->enddate) {
                if ($now > $productinstance->enddate) {
                    // Expired.
                    $statusclass = 'cs-product-expired';
                    $totals['expired']++;
                } else if ($now > $productinstance->enddate - SHOP_UNIT_EXPIRATION_FORECAST_DELAY2) {
                    // Expiring.
                    $statusclass = 'cs-product-expiring';
                    $totals['expiring']++;
                } else if ($now > $productinstance->enddate - SHOP_UNIT_EXPIRATION_FORECAST_DELAY1) {
                    // Near to Expiring.
                    $statusclass = 'cs-product-ending';
                    $totals['ending']++;
                } else if ($now < $productinstance->startdate) {
                    // Pending.
                    $statusclass = 'cs-product-pending';
                    $totals['pending']++;
                } else {
                    // Running.
                    $statusclass = 'cs-product-running';
                    $totals['running']++;
                }
            } else {
                // Running.
                $statusclass = 'cs-product-running';
                $totals['running']++;
            }
        return $statusclass;
    }

    /**
     * Builds the command set.
     * @param Product $productinstance
     * @param array $viewparams
     */
    protected function get_product_commands(Product $productinstance, $viewparams) {

        $commands = '';

        if (has_capability('local/shop:salesadmin', context_system::instance())) {
            if ($productinstance->deleted) {
                // Only delete soft deleted products.
                $pix = $this->output->pix_icon('t/delete', get_string('delete'), 'moodle');
                $params = ['what' => 'delete',
                           'productids[]' => $productinstance->id,
                           'sesskey' => sesskey()];
                           $params = array_merge($params, $viewparams);
                $deleteurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
                $commands .= '<a href="'.$deleteurl.'" title="'.get_string('delete').'">'.$pix.'</a>';
            }

            if ($productinstance->deleted) {
                $title = get_string('softrestore', 'local_shop');
                $pix = $this->output->pix_icon('t/go', $title, 'moodle');
            } else {
                $title = get_string('softdelete', 'local_shop');
                $pix = $this->output->pix_icon('t/stop', $title, 'moodle');
            }
            $params = ['what' => 'softdelete',
                            'set[]' => $productinstance->id,
                            'sesskey' => sesskey()];
                            $params = array_merge($params, $viewparams);
            $deleteurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);
            $commands .= '&nbsp;<a href="'.$deleteurl.'" title="'.$title.'">'.$pix.'</a>';

            if (local_shop_supports_feature('products/editable')) {
                $pix = $this->output->pix_icon('t/edit', get_string('edit'), 'moodle');
                $params = ['instanceid' => $productinstance->id,
                                'sesskey' => sesskey()];
                $linkurl = new moodle_url('/local/shop/pro/purchasemanager/edit_instance.php', $params);
                $commands .= '&nbsp;<a href="'.$linkurl.'">'.$pix.'</a>';
            }
        }
        return $commands;
    }

    /**
     * Extracts some extra metadata if config requires.
     * @param Product $productinstance
     */
    protected function process_extradata(Product $productinstance) {
        $config = get_config('local_shop');

        if (!empty($config->extradataonproductinstances)) {

            $extrajson = $productinstance->extradata;
            if (empty($extrajson)) {
                return;
            }

            $extradata = json_decode($productinstance->extradata);

            $extrafields = preg_split('/[\s,]+/', $config->extradataonproductinstances);
            $fieldsarr = [];
            foreach ($extrafields as $field) {
                if (isset($extradata->$field)) {
                    $fieldsarr[] = "$field: ".$extradata->$field; 
                }
            }
            return implode('<br/>', $fieldsarr);
        }

        return '';
    }

    /**
     * Print all search options in product instances.
     * @param object $mainrenderer the shop main renderer for global functions
     */
    public function productinstances_options($mainrenderer) {

        $dir = optional_param('dir', 'ASC', PARAM_TEXT);
        $sortorder = optional_param('sortorder', 'id', PARAM_TEXT);
        $customerid = optional_param('customerid', 0, PARAM_INT);
        $contexttype = optional_param('contexttype', '*', PARAM_TEXT);
        $productstate = optional_param('productstate', '*', PARAM_TEXT);
        $quicksearchfilter = optional_param('quicksearchfilter', '*', PARAM_TEXT);
        $shopid = optional_param('shopid', 0, PARAM_INT);

        $template = new StdClass();

        $params = [
            'view' => 'viewAllProductInstances',
            'dir' => $dir,
            'order' => $sortorder,
            'contexttype' => $contexttype,
            'customerid' => $customerid,
            'shopid' => $shopid,
            'quicksearchfilter' => $quicksearchfilter
        ];

        $url = new moodle_url('/local/shop/purchasemanager/view.php', $params);
        $url->remove_params('shopid');
        $template->shopselect = $mainrenderer->shop_choice($url, true, $shopid);

        $url = new moodle_url('/local/shop/purchasemanager/view.php', $params);
        $url->remove_params('customerid');
        $template->customerselect = $mainrenderer->customer_choice($customerid, $url, true);

        $url = new moodle_url('/local/shop/purchasemanager/view.php', $params);
        $url->remove_params('producttype');
        $template->contextselect = $this->contexttypes($contexttype, $url, true);

        $url = new moodle_url('/local/shop/purchasemanager/view.php', $params);
        $url->remove_params('productstate');
        $template->productstate = $this->productstates($productstate, $url, true);

        $url = new moodle_url('/local/shop/purchasemanager/view.php', $params);
        $url->remove_params('quicksearchfilter');
        $template->quicksearch = $this->quicksearch($quicksearchfilter, $url);

        $params = ['view' => 'search'];
        $template->searchurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);

        return $this->output->render_from_template('local_shop/productinstances_options', $template);
    }

    /**
     * Provides a selector for filtering context types
     * @param string $current
     * @param moodle_url|string $url
     */
    protected function contexttypes($current, $url) {
        global $DB;

        $sql = "
            SELECT DISTINCT
                contexttype
            FROM
                {local_shop_product}
        ";
        $typesarr = $DB->get_records_sql($sql);

        $str = '';

        $types = ['' => get_string('alltypes', 'local_shop')];
        foreach (array_keys($typesarr) as $type) {
            $types[$type] = get_string($type, 'local_shop');
        }

        $attrs['label'] = get_string('contexttype', 'local_shop').': ';
        $str .= $this->output->single_select($url, 'contexttype', $types, $current, null, null, $attrs);

        return $str;
    }

    /**
     * Provides a simple text widget for filtering the screen by text
     * @param string $current
     * @param moodle_url|string $url
     */
    protected function quicksearch($current, $url) {

        $template = new StdClass();
        $template->url = $url;
        $template->current = $current;

        return $this->output->render_from_template('local_shop/purchasemanager_quick_search', $template);
    }

    /**
     * Provides a selector for filtering product by state
     * @param string $current
     * @param moodle_url|string $url
     */
    protected function productstates($current, $url) {

        $str = '';
        $states = [
            '*' => get_string('allstates', 'local_shop'),
            'cs-product-expired' => get_string('productexpired', 'local_shop'),
            'cs-product-expiring' => get_string('productexpiring', 'local_shop'),
            'cs-product-ending' => get_string('productending', 'local_shop'),
            'cs-product-pending' => get_string('productpending', 'local_shop'),
            'cs-product-running' => get_string('productrunning', 'local_shop')
        ];

        $attrs['label'] = get_string('productstate', 'local_shop').': ';
        $str .= $this->output->single_select($url, 'productstate', $states, $current, null, null, $attrs);

        return $str;
    }

    /**
     * Search in products
     * @param object $blockinstance
     * @param int $unitcount
     */
    public function search_form($blockinstance, $unitcount) {

        try {
            $outputclass = 'productinstances_search_form';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\productinstances_search_form($blockinstance, $unitcount);
            $template = $tpldata->export_for_template($this->output);
            return $this->output->render_from_template('local_shop/productinstances_search_form', $template);
        } catch (Exception $e) {
            throw new moodle_exception("Missing output class $outputclass");
        }
    }

    /**
     * Results of search
     * @param array $results
     */
    public function search_results($results) {
        $template = new StdClass();
        $odd = 0;
        foreach ($results as $unit) {
            $unittpl = new StdClass();
            $product = Product::instance_by_reference($unit->reference, false);
            $unittpl->lineclass = ($odd) ? 'r0' : 'r1';
            $odd = ($odd + 1) % 2;

            $unittpl->reference = $product->reference;

            $unittpl->startdate = ($product->startdate) ? userdate($product->startdate) : 'N.C.';
            $unittpl->enddate = ($product->enddate) ? userdate($product->enddate) : 'N.C.';

            $totals = [
                'expired' => 0,
                'expiring' => 0,
                'ending' => 0,
                'pending' => 0,
                'running' => 0
            ];
            $statusclass = $this->get_productinstance_running_status($product, $totals);
            $unittpl->statusclass = $statusclass;

            $unittpl->contexttype = $product->contexttype;

            // Note : as internal record values are protected. We must pass them to a public object.
            $unittpl->c = new StdClass();
            $unittpl->c->url = $product->customer->url;
            $unittpl->c->firstname = $product->customer->firstname;
            $unittpl->c->lastname = $product->customer->lastname;
            $unittpl->c->organisation = $product->customer->organisation;
            $params = ['view' => 'showAllProductInstances', 'customerid' => $product->customer->id];
            $unittpl->c->unitsurl = new moodle_url('/local/shop/purchasemanager/view.php', $params);

            $unittpl->ci = new StdClass();
            $unittpl->ci->url = $product->catalogitem->url;
            $unittpl->ci->name = format_string($product->catalogitem->name);
            $unittpl->ci->shortname = $product->catalogitem->shortname;

            $unittpl->hasbill = $product->hasbill;
            if ($unittpl->hasbill) {
                $unittpl->b = new StdClass();
                $unittpl->b->url = $product->currentbillitem->bill->url;
                $unittpl->b->id = $product->currentbillitem->bill->id;
            }

            $unittpl->commands = $this->get_product_commands($product, []);

            $template->units[] = $unittpl;
        }

        return $this->output->render_from_template('local_shop/productinstances_search_results', $template);
    }

    public function add_instance_button($theshop, $shopowner = 0, $customerid = 0) {
        global $USER;

        $contextsystem = context_system::instance();

        if (($shopowner == $USER->id) || has_capability('local/shop:accessallowners', $contextsystem)) {
            $params = ['shopid' => $theshop->id, 'customerid' => $customerid, 'instanceid' => 0];
            $addurl = new moodle_url('/local/shop/pro/purchasemanager/edit_instance.php', $params);
            return $this->output->single_button($addurl, get_string('newproduct', 'local_shop'));
        }
    }

    public function selection_tools($customerid) {
        $template = new StdClass;
        $template->customerid = $customerid;
        $template->quicksearchfilter = optional_param('quicksearchfilter', '', PARAM_TEXT);

        return $this->output->render_from_template('local_shop/purchasemanager_selection_tools', $template);
    }
}