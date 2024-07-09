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

require_once($CFG->dirroot.'/local/shop/renderer.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Shop;
use local_shop\Tax;
use local_shop\Category;
use local_shop\CatalogItem;

/**
 * Renderer for catalog items management
 */
class shop_products_renderer extends local_shop_base_renderer {

    /**
     * Prints shop header
     */
    public function shop_header() {
        global $SESSION;
        $theshop = new Shop($SESSION->shop->shopid);

        $template = new StdClass;
        $template->name = $theshop->name;
        $template->description = format_text($theshop->description, $theshop->descriptionformat);

        return $this->output->render_from_template('local_shop/products_shopheader', $template);
    }

    /**
     * Prints catalog header
     */
    public function catalog_header() {

        $this->check_context();

        $template = new StdClass;

        $template->catalogname = format_string($this->thecatalog->name);

        if ($this->thecatalog->ismaster) {
            $template->ismaster = true;
        } else if ($this->thecatalog->isslave) {
            $template->isslave = true;
        } else {
            $template->isstandalone = true;
        }

        $template->description = format_string($this->thecatalog->description);
        $template->shops = 0 + Shop::count(['catalogid' => $this->thecatalog->id]);
        $shopinstances = Shop::get_instances(['catalogid' => $this->thecatalog->id]);
        foreach ($shopinstances as $s) {
            $shoptpl = new StdClass;
            $shoptpl->id = $s->id;
            $shoptpl->maincatid = $s->catalogid;
            $shoptpl->name = $s->name;
            $template->shopdata[] = $shoptpl;
        }

        return $this->output->render_from_template('local_shop/products_catalogheader', $template);
    }

    /**
     * Prints a backoffice admin line for a product
     */
    public function product_admin_line($product) {
        global $OUTPUT;

        $this->check_context();
        $shopid = $this->theshop->id;

        $template = new StdClass;

        if (is_null($product)) {
            $template->header = true;
            $template->helpcodeicon = $this->output->help_icon('helpcode', 'local_shop');
            $template->helpshortnameicon = $this->output->help_icon('helpshortname', 'local_shop');
        } else {
            $template->header = false;
            $template->pricelines = [];
            $prices = $product->get_printable_prices();
            foreach ($prices as $key => $price) {
                $pricelinetpl = new StdClass;
                $pricelinetpl->pricekey = $key;
                $pricelinetpl->price = $price;
                $template->pricelines[] = $pricelinetpl;
            }

            $taxedpricelines = [];
            $prices = $product->get_printable_prices(true);
            foreach ($prices as $key => $price) {
                $pricelinetpl = new StdClass;
                $pricelinetpl->pricekey = $key;
                $pricelinetpl->price = $price;
                $template->taxedpricelines[] = $pricelinetpl;
            }

            $template->id = $product->id;
            $template->statusclass = strtolower($product->status);
            $template->slaveclass  = (!$this->thecatalog->isslave || (@$product->masterrecord == 0)) ? '' : 'engraved slaved';

            $template->thumburl = '';
            // Get Handler guessed image.
            list($handler, $unusedmethod) = $product->get_handler_info('get_alternative_thumbnail_url', '');
            if (!empty($handler)) {
                $template->thumburl = $handler->get_alternative_thumbnail_url($product);
            }

            $thumburloverride = $product->get_thumb_url(!empty($template->thumburl));
            if (!empty($thumburloverride)) {
                $template->thumburl = $thumburloverride;
            }
            if (empty($template->thumburl)) {
                // Get the absolute default as last chance.
                $template->thumburl = $product->get_thumb_url(false);
            }

            $template->code = $product->code;
            $template->shortname = $product->shortname;
            $template->name = format_string($product->name);
            if ($product->enablehandler) {
                $template->enablehandler = $product->enablehandler;
            }

            $tax = new Tax($product->taxcode);
            $template->taxtitle = $tax->title;
            $template->taxcode = $product->taxcode;
            $template->status = get_string($product->status, 'local_shop');
            $template->maxdeliveryquant = $product->maxdeliveryquant;
            $template->sold = $product->sold;
            $template->stock = $product->stock;
            $template->renewable = ($product->renewable) ? get_string('yes') : '';
            switch ($product->quantaddressesusers) {

                case SHOP_QUANT_NO_SEATS:
                    $template->quantaddressusers = get_string('no');
                    break;

                case SHOP_QUANT_ONE_SEAT:
                    $template->quantaddressusers = get_string('oneseat', 'local_shop');
                    break;

                case SHOP_QUANT_AS_SEATS:
                    $template->quantaddressusers = get_string('yes');
                    break;
            }

            $cmds = '';
            if (!$this->thecatalog->isslave || (@$product->masterrecord == 0)) {
                // We cannot edit master records ghosts from the slave catalog.
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'toset',
                    'itemid' => $product->id,
                    'categoryid' => $this->categoryid,
                ];
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('toset', get_string('toset', 'local_shop'), 'local_shop').'</a> ';

                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'tobundle',
                    'itemid' => $product->id,
                    'categoryid' => $this->categoryid,
                ];
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('tobundle', get_string('tobundle', 'local_shop'), 'local_shop').'</a> ';

                // We cannot edit master records ghosts from the slave catalog.
                $params = ['shopid' => $shopid, 'itemid' => $product->id, 'categoryid' => $this->categoryid];
                $editurl = new moodle_url('/local/shop/products/edit_product.php', $params);
                $cmds .= '<a href="'.$editurl.'">'.$OUTPUT->pix_icon('t/edit', get_string('edit')).'</a> ';

                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'clone',
                    'itemid' => $product->id,
                    'categoryid' => $this->categoryid,
                ];
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/copy', get_string('copy')).'</a> ';

                $deletestr = get_string('deleteproduct', 'local_shop');
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'delete',
                    'itemid' => $product->id,
                    'categoryid' => $this->categoryid,
                ];
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '&nbsp;<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/delete', $deletestr).'</a>';
            }

            $createlocalstr = get_string('addoverride', 'local_shop');
            $deletelocalversionstr = get_string('deleteoverride', 'local_shop');

            if ($this->thecatalog->isslave) {
                if ($product->masterrecord == 1) {
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid, 
                        'what' => 'makecopy',
                        'itemid' => $product->id,
                        'catalogid' => $this->thecatalog->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('copy', $createlocalstr, 'local_shop');
                    $cmds .= '&nbsp;<a href="'.$cmdurl.'">'.$pixicon.'</a>';
                } else {
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid, 
                        'what' => 'freecopy',
                        'itemid' => $product->id,
                        'catalogid' => $this->thecatalog->id,
                        'categoryid' => $this->categoryid
                    ];
                    $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('uncopy', $deletelocalversionstr, 'local_shop');
                    $cmds .= '&nbsp;<a href="'.$cmdurl.'">'.$pixicon.'</a>';
                }
            }
            $template->controls = $cmds;
        }

        return $this->output->render_from_template('local_shop/products_product_admin_line', $template);
    }

    /**
     * Prints an administration line for a product set
     * @param Set $set;
     */
    public function set_admin_line(Set $set) {
        global $OUTPUT;

        $this->check_context();
        $shopid = $this->theshop->id;

        $hassubs = count($set->elements);

        $slaveclass = (!$this->thecatalog->isslave || (@$set->masterrecord == 1)) ? 'master' : 'slave';

        $statusclass = strtolower($set->status);

        $template = new Stdclass;
        $template->statusclass = $statusclass;
        $template->slaveclass = $slaveclass;
        $template->id = $set->id;

        $template->thumburl = $set->get_thumb_url(true);
        if (empty($template->thumburl)) {
            $template->thumburl = \local_shop\compat::pix_url('productset', 'local_shop');
        }
        $template->code = $set->code;
        $template->shortname = $set->shortname;
        $template->name = format_string($set->name);

        $cmds = '';
        if (!$this->thecatalog->isslave || (@$set->masterrecord == 0)) {
            // We cannot edit master records ghosts from the slave catalog.
            $params = ['shopid' => $shopid, 'itemid' => $set->id, 'categoryid' => $this->categoryid];
            $editseturl = new moodle_url('/local/shop/products/edit_set.php', $params);
            $pixicon = $OUTPUT->pix_icon('t/edit', get_string('editset', 'local_shop'), 'moodle');
            $cmds .= '<a href="'.$editseturl.'">'.$pixicon.'</a>';

            if ($hassubs) {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'unlink',
                    'itemid' => $set->id,
                    'categoryid' => $this->categoryid,
                ];
                $unlinkurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('unlinkcontent', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$unlinkurl.'">'.$pixicon.'</a>';
            } else {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'delete',
                    'itemid' => $set->id,
                    'categoryid' => $this->categoryid,
                ];
                $deleteurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('delete');
                $pixicon = $OUTPUT->pix_icon('t/delete', $linklbl, 'moodle');
                $cmds .= '&nbsp;<a href="'.$deleteurl.'">'.$pixicon.'</a>';
            }
        }

        if ($this->thecatalog->isslave) {
            if ($set->masterrecord == 1) {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'makecopy',
                    'itemid' => $set->id,
                    'categoryid' => $this->categoryid,
                ];
                $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('addoverride', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';
            } else {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'freecopy',
                    'itemid' => $set->id,
                    'categoryid' => $this->categoryid,
                ];
                $uncopyurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('deleteoverride', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('uncopy', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$uncopyurl.'">'.$pixicon.'</a>';
            }
        }
        $template->controls = $cmds;

        if ($hassubs) {
            $template->subs = $this->set_admin_elements($set);
        } else {
            $template->subs = $this->output->notification(get_string('noproductinset', 'local_shop'));
        }

        return $this->output->render_from_template('local_shop/products_set_admin_line', $template);
    }

    /**
     * Prints an admin line for a bundle
     * @param CatalogItem $bundle
     */
    public function bundle_admin_line(CatalogItem $bundle) {
        global $OUTPUT;

        $this->check_context();
        $shopid = $this->theshop->id;

        $hassubs = count($bundle->elements);

        $slaveclass = (!$this->thecatalog->isslave || (@$bundle->masterrecord == 1)) ? 'master' : 'slaved';

        $statusclass = strtolower($bundle->status);

        $template = new StdClass;
        $template->statusclass = $statusclass;
        $template->slaveclass = $slaveclass;
        $template->id = $bundle->id;
        $template->engravedclass = ((@$bundle->masterrecord == 0) ? '' : 'engraved');
        $template->thumburl = $bundle->get_thumb_url(true);
        if (empty($template->thumburl)) {
            $template->thumburl = \local_shop\compat::pix_url('productbundle', 'local_shop');
        }
        $template->code = $bundle->code;
        $template->shortname = $bundle->shortname;
        $template->name = format_string($bundle->name);

        $template->price1 = sprintf("%.2f", round($bundle->price1, 2));
        $template->taxcode = $bundle->taxcode;

        $template->bundleTTCPrice = sprintf("%.2f", round($bundle->bundleTTCPrice, 2));
        $template->status = get_string($bundle->status, 'local_shop');
        $template->maxdeliveryquant = $bundle->maxdeliveryquant;
        $template->sold = $bundle->sold;
        $template->stock = $bundle->stock;
        $template->renewable = ($bundle->renewable) ? get_string('yes') : get_string('no');

        $cmds = '';
        if (!$this->thecatalog->isslave || (@$bundle->masterrecord == 0)) {
            // We cannot edit master records ghosts from the slave catalog.
            $params = ['shopid' => $shopid, 'itemid' => $bundle->id, 'categoryid' => $this->categoryid];
            $editurl = new moodle_url('/local/shop/products/edit_bundle.php', $params);
            $linklbl = get_string('editbundle', 'local_shop');
            $pixicon = $OUTPUT->pix_icon('t/edit', $linklbl, 'moodle');
            $cmds .= '<a href="'.$editurl.'">'.$pixicon.'</a>';

            if ($hassubs) {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'unlink',
                    'itemid' => $bundle->id,
                    'categoryid' => $this->categoryid,
                ];
                $viewurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('unlinkcontent', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$viewurl.'">'.$pixicon.'</a>';
            } else {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'delete',
                    'itemid' => $bundle->id,
                    'categoryid' => $this->categoryid,
                ];
                $viewurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('deletebundle', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('i/delete', $linklbl, 'core');
                $cmds .= '&nbsp;<a href="'.$viewurl.'">'.$pixicon.'</a>';
            }
        }

        if ($this->thecatalog->isslave) {
            if ($bundle->masterrecord == 1) {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'makecopy',
                    'productid' => $bundle->id,
                    'categoryid' => $this->categoryid,
                ];
                $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('addoverride', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';
            } else {
                $params = [
                    'view' => 'viewAllProducts',
                    'shopid' => $shopid,
                    'what' => 'freecopy',
                    'productid' => $bundle->id,
                    'categoryid' => $this->categoryid,
                ];
                $deletecopyurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('deleteoverride', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('uncopy', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$deletecopyurl.'">'.$pixicon.'</a>';
            }
        }
        $template->controls = $cmds;

        if ($hassubs) {
            $template->subs = $this->bundle_admin_elements($bundle);
        } else {
            $template->subs = $OUTPUT->notification(get_string('noproductinbundle', 'local_shop'));
        }

        return $this->output->render_from_template('local_shop/products_bundle_admin_line', $template);
    }

    /**
     * Prints the set subelements
     * @param Set $set a complete set structure.
     */
    public function set_admin_elements(Set $set) {
        global $OUTPUT;

        $table = $this->prepare_elements_table();
        $shopid = $this->theshop->id;

        foreach ($set->elements as $setelm) {
            if (!$this->thecatalog->isslave || (@$setelm->masterrecord == 0)) {
                $table->rowclasses[] = '';
            } else {
                $table->rowclasses[] = 'slaved';
            }
            $row = [];
            $row[] = '<img class="thumb" src="'.$setelm->get_thumb_url().'" height="50">';
            $row[] = $setelm->code;
            $row[] = format_string($setelm->name);
            $row[] = sprintf("%.2f", round($setelm->price1, 2)).'<br/>('.$setelm->taxcode.')';
            $row[] = sprintf("%.2f", round($setelm->TTCprice, 2));
            $row[] = get_string($setelm->status, 'local_shop');

            $commands = '';
            if ((!$this->thecatalog->isslave) || ($setelm->masterrecord == 0)) {
                // We cannot edit master records ghosts from the slave catalog.
                $params = ['shopid' => $shopid, 'itemid' => $setelm->id, 'categoryid' => $this->categoryid];
                $editurl = new moodle_url('/local/shop/products/edit_product.php', $params);
                $linklbl = get_string('editproduct', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('t/edit', $linklbl, 'moodle');
                $commands .= '<a href="'.$editurl.'">'.$pixicon.'</a>';

                if (!$this->thecatalog->isslave) {

                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'clone',
                        'itemid' => $setelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('t/copy', get_string('copy'), 'moodle');
                    $commands .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';

                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'unlink',
                        'itemid' => $setelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $unlinkurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('unlinkproduct', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$unlinkurl.'">'.$pixicon.'</a>';

                    // Only real products can be unlinked or deleted or copied.
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'delete',
                        'items' => $setelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $deleteurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('delete');
                    $pixicon = $OUTPUT->pix_icon('t/delete', $linklbl, 'moodle');
                    $commands .= '&nbsp;<a href="'.$deleteurl.'">'.$pixicon.'</a>';
                }
            }

            if ($this->thecatalog->isslave) {
                if ($setelm->masterrecord == 1) {
                    // If we do not have a local override, allow creating one.
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'makecopy',
                        'itemid' => $setelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('addoverride', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$pixicon.'</a>';
                } else {
                    // If we do have an override, allow discarding it.
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'freecopy',
                        'itemid' => $setelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $uncopyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('deleteoverride', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('uncopy', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$uncopyurl.'">'.$pixicon.'</a>';
                }
            }
            $row[] = $commands;

            $table->data[] = $row;
        }

        $str = html_writer::table($table);

        return $str;
    }

    /**
     * Prints subelements of a bundle.
     * @param CatalogItem $bundle a complete bundle structure.
     */
    public function bundle_admin_elements(CatalogItem $bundle) {
        global $OUTPUT;

        $table = $this->prepare_elements_table();
        $shopid = $this->theshop->id;

        foreach ($bundle->elements as $bundleelm) {
            if (!$this->thecatalog->isslave || (@$bundleelm->masterrecord == 0)) {
                $table->rowclasses[] = '';
            } else {
                $table->rowclasses[] = 'slaved';
            }
            $row = [];
            $row[] = '<img class="thumb" src="'.$bundleelm->get_thumb_url().'" height="50">';
            $row[] = $bundleelm->code;
            $row[] = $bundleelm->name;
            $row[] = '<span class="shop-shadow">'.sprintf("%.2f", round($bundleelm->price1, 2)).'<br/>('.$bundleelm->taxcode.')</span>';
            $row[] = '<span class="shop-shadow">'.sprintf("%.2f", round($bundleelm->TTCprice, 2)).'</span>';
            $row[] = get_string($bundleelm->status, 'local_shop');

            $commands = '';
            if (!$this->thecatalog->isslave || ($bundleelm->masterrecord == 0)) {
                $params = ['shopid' => $shopid, 'itemid' => $bundleelm->id, 'categoryid' => $this->categoryid];
                $editurl = new moodle_url('/local/shop/products/edit_product.php', $params);
                $linklbl = get_string('editproduct', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('t/edit', $linklbl, 'moodle');
                $commands .= '<a href="'.$editurl.'">'.$pixicon.'</a>';

                if (!$this->thecatalog->isslave) {

                    $params = ['view' => 'viewAllProducts', 'shopid' => $shopid, 'what' => 'clone', 'itemid' => $bundleelm->id,
                            'categoryid' => $this->categoryid];
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('t/copy', get_string('copy'), 'moodle');
                    $commands .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a> ';

                    // Only real products can be unlinked or deleted.
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'unlink',
                        'itemid' => $bundleelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $unlinkurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('unlinkproduct', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$unlinkurl.'">'.$pixicon.'</a>';

                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'delete',
                        'itemid' => $bundleelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $deleteurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('delete');
                    $pixicon = $OUTPUT->pix_icon('t/delete', $linklbl, 'moodle');
                    $commands .= '&nbsp;<a href="'.$deleteurl.'">'.$pixicon.'</a>';
                }
            }

            if ($this->thecatalog->isslave) {
                if ($bundleelm->masterrecord == 1) {
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'makecopy',
                        'itemid' => $bundleelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('addoverride', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';
                } else {
                    $params = [
                        'view' => 'viewAllProducts',
                        'shopid' => $shopid,
                        'what' => 'freecopy',
                        'itemid' => $bundleelm->id,
                        'categoryid' => $this->categoryid,
                    ];
                    $deletecopyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('deleteoverride', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('uncopy', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$deletecopyurl.'">'.$pixicon.'</a>';
                }
            }
            $row[] = $commands;

            $table->data[] = $row;
        }

        $str = html_writer::table($table);

        return $str;
    }

    /**
     * Links to product categories
     * @param Catalog $thecatalog
     */
    public function catlinks(Catalog $thecatalog) {
        global $OUTPUT, $SESSION;

        $this->check_context();

        $firstcategory = $thecatalog->get_first_category();
        if ($firstcategory) {
            $firstcategoryid = $firstcategory->id;
        } else {
            $firstcategoryid = 0;
        }
        $categoryid = optional_param('categoryid', $firstcategoryid, PARAM_INT);

        $template = new StdClass;

        $params = ['view' => 'viewAllCategories', 'shopid' => $this->theshop->id, 'catalogid' => $this->thecatalog->id];
        $template->catlinkurl = new moodle_url('/local/shop/products/category/view.php', $params);

        if (Category::count(['catalogid' => $thecatalog->id])) {
            $template->hascategories = true;
            $params = ['id' => $this->theshop->id, 'categoryid' => $categoryid];

            if (local_shop_supports_feature() == 'pro' || CatalogItem::count(array()) < 10) {
                $template->producturl = new moodle_url('/local/shop/products/edit_product.php', $params);

                $template->seturl = new moodle_url('/local/shop/products/edit_set.php', $params);

                $template->bundleurl = new moodle_url('/local/shop/products/edit_bundle.php', $params);
            }

            $template->testurl = new moodle_url('/local/shop/unittests/index.php', ['id' => $this->theshop->id]);
        }

        $linkedshops = Shop::get_instances(['catalogid' => $this->thecatalog->id], 'name');
        if (count($linkedshops) == 1) {
            $template->hasonelinkedshop = true;
            $shop = array_shift($linkedshops);
            $template->fronturl = new moodle_url('/local/shop/front/view.php', ['view' => 'shop', 'id' => $shop->id]);
        } else {
            $template->hasseverallinkedshops = true;
            $shopopts = [];
            foreach ($linkedshops as $sh) {
                $shopopts[$sh->id] = format_string($sh->name);
            }
            $shopurl = new moodle_url('/local/shop/front/view.php', ['view' => 'shop']);
            $template->shopselect = $this->output->single_select($shopurl, 'id', $shopopts);
        }

        return $this->output->render_from_template('local_shop/products_catlinks', $template);
    }

    /**
     * Category chooser
     */
    public function category_chooser($url) {
        global $OUTPUT, $SESSION;

        // In case it was not done before, but it might.
        $SESSION->shop->categoryid = $current = optional_param('categoryid', 0 + @$SESSION->shop->categoryid, PARAM_INT);

        $categories = Category::get_instances(['catalogid' => $this->thecatalog->id, 'parentid' => 0], 'sortorder');

        $catoptions = [];
        $this->feed_chooser($catoptions, $categories);

        if (count($categories) <= 1) {
            return '';
        }

        $template = new StdClass;
        $name = 'categoryid';
        $params = [0 => get_string('allcategories', 'local_shop']);
        $categoryselect = $OUTPUT->single_select($url, $name, $catoptions, $current, $params);
        $template->categories = get_string('category', 'local_shop').' : '.$categoryselect;

        return $this->output->render_from_template('local_shop/products_category_chooser', $template);
    }

    /**
     * Feed chooser
     */
    protected function feed_chooser(&$catoptions, $categories, $prefix = '') {
        foreach ($categories as $cat) {
            $catoptions[$cat->id] = $prefix.format_string($cat->name);
            $subs = Category::get_instances(['catalogid' => $this->thecatalog->id, 'parentid' => $cat->id], 'sortorder');
            if ($subs) {
                $prefixtmp = $prefix;
                $prefix .= format_string($cat->name).'/';
                $this->feed_chooser($catoptions, $subs, $prefix);
                $prefix = $prefixtmp;
            }
        }
    }

    /**
     * Print categories
     */
    public function categories($categories) {
        $order = optional_param('order', 'sortorder', PARAM_ALPHA);
        $dir = optional_param('dir', 'ASC', PARAM_ALPHA);

        $namestr = get_string('catname', 'local_shop');
        $catdescstr = get_string('catdescription', 'local_shop');
        $prodcountstr = get_string('productcount', 'local_shop');
        $parentcatstr = get_string('parentcategory', 'local_shop');

        $table = new html_table();
        $table->class = 'generaltable';
        $table->head = ["<b>$namestr</b>", "<b>$parentcatstr</b>", "<b>$catdescstr</b>", "<b>$prodcountstr</b>", ''];
        $table->width = '100%';
        $table->align = ['left', 'left', 'left', 'center', 'right'];
        $table->size = ['20%', '20%', '30%', '10%', '20%'];

        foreach ($categories as $cat) {
            $this->category_add_row($table, $cat, $order, $dir);
        }

        echo html_writer::table($table);
    }

    /**
     * Print the category add button
     * @param objectref &$table
     * @param Category $category
     * @param string $order
     * @param string $dir
     */
    protected function category_add_row(&$table, $category, $order, $dir) {
        global $OUTPUT, $DB;
        static $indentarr = [];

        $subs = Category::get_instances(['catalogid' => $this->thecatalog->id,
                                              'parentid' => $category->id], "$order $dir");

        $params = ['catalogid' => $this->thecatalog->id, 'view' => 'viewAllCategories', 'order' => $order, 'dir' => $dir];
        $url = new moodle_url('/local/shop/products/category/view.php', $params);
        $params = ['catalogid' => $this->thecatalog->id, 'parentid' => $category->parentid];
        $maxorder = $DB->get_field('local_shop_catalogcategory', 'MAX(sortorder)', $params);

        $indent = implode('', $indentarr);

        $row = [];

        $class = (!$category->visible) ? 'shop-shadow' : '';
        $row[] = $indent.'<span class="'.$class.'">'.format_string($category->name).'</span>';

        $row[] = $category->get_parent_name();

        $contextid = context_system::instance()->id;
        $category->description = file_rewrite_pluginfile_urls($category->description, 'pluginfile.php',
                                                              $contextid, 'local_shop', 'categorydescription', $category->id);
        $row[] = format_text($category->description);

        $row[] = $DB->count_records('local_shop_catalogitem', ['categoryid' => $category->id]);

        if ($category->visible) {
            $pixurl = \local_shop\compat::pix_url('t/hide', 'core');
            $cmd = 'hide';
        } else {
            $pixurl = \local_shop\compat::pix_url('t/show', 'core');
            $cmd = 'show';
        }
        $commands = "<a href=\"{$url}&amp;what=$cmd&amp;categoryid={$category->id}\"><img src=\"$pixurl\" /></a>";
        $params = ['catalogid' => $this->thecatalog->id, 'categoryid' => $category->id, 'what' => 'updatecategory'];
        $editurl = new moodle_url('/local/shop/products/category/edit_category.php', $params);
        $commands .= '&nbsp;<a href="'.$editurl.'">'.$OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle').'</a>';

        if (empty($subs)) {
            $params = [
                'catalogid' => $this->thecatalog->id,
                'view' => 'viewAllCategories',
                'order' => $order,
                'dir' => $dir,
                'what' => 'delete',
                'categoryids[]' => $category->id,
            ];
            $deleteurl = new moodle_url('/local/shop/products/category/view.php', $params);
            $commands .= '&nbsp;<a href="'.$deleteurl.'">'.$OUTPUT->pix_icon('t/delete', get_string('delete')).'</a>';
        }

        if ($category->sortorder > 1) {
            $icon = $OUTPUT->pix_icon('/t/up', '', 'moodle');
            $commands .= "&nbsp;<a href=\"{$url}&amp;categoryid={$category->id}&amp;what=down\">".$icon.'</a>';
        }
        if ($category->sortorder < $maxorder) {
            $icon = $OUTPUT->pix_icon('t/down', '');
            $commands .= "&nbsp;<a href=\"{$url}&amp;categoryid={$category->id}&amp;what=up\">".$icon.'</a>';
        }
        $row[] = $commands;

        $table->data[] = $row;

        if ($subs) {
            foreach ($subs as $s) {
                array_push($indentarr, '&nbsp;&nbsp;&nbsp;');
                $this->category_add_row($table, $s, $order, $dir);
                array_pop($indentarr);
            }
        }
    }

    /**
     * Prepare a table for elements
     */
    protected function prepare_elements_table() {
        $codestr = get_string('code', 'local_shop');
        $namestr = get_string('name', 'local_shop');
        $pricestr = get_string('price', 'local_shop');
        $ttcstr = get_string('ttc', 'local_shop');
        $availabilitystr = get_string('availability', 'local_shop');

        $table = new html_table();
        $table->head = [
            '',
            "<b>$codestr</b>",
            "<b>$namestr</b>",
            "<b>$pricestr</b>",
            "<b>$ttcstr</b>",
            "<b>$availabilitystr</b>",
            '',
        ];
        $table->width = '100%';
        $table->size = ['10%', '10%', '45%', '10%', '10%', '10%', '5%'];
        $table->align = ['left', 'left', 'left', 'right', 'right', 'center', 'right'];
        $table->colclasses = ['', '', '', '', '', '', 'shop-setcontrols'];

        return $table;
    }

    /**
     * Prints detail of a product (general function).
     * @param CatalogItem $catalogitem
     */
    public function catalogitem_details(CatalogItem $catalogitem) {

        $frontrenderer = shop_get_renderer('front');
        $frontrenderer->load_context($this->theshop, $this->thecatalog, $this->theblock);

        $catalogitem->check_availability();
        $catalogitem->currency = $this->theshop->get_currency('symbol');
        $catalogitem->salesunit = $catalogitem->get_sales_unit_url();
        $catalogitem->preset = 0 + @$SESSION->shoppingcart->order[$catalogitem->shortname];
        switch ($catalogitem->isset) {
            case PRODUCT_SET:
                $template = $frontrenderer->product_set($catalogitem, true);
                $tplfile = 'local_shop/front_product_block_details';
                break;
            case PRODUCT_BUNDLE:
                $template = $frontrenderer->product_bundle($catalogitem, true);
                $tplfile = 'local_shop/front_product_bundle_details';
                break;
            default:
                $template = $frontrenderer->product_block($catalogitem, true);
                $tplfile = 'local_shop/front_product_block_details';
        }

        $params = [];
        $params['view'] = 'shop';
        $params['category'] = $catalogitem->categoryid;
        $params['productname'] = $catalogitem->shortname;
        $params['what'] = 'addunit';
        $params['shopid'] = $this->theshop->id;
        $params['redirect'] = 1;
        $template->addtocarturl = (new moodle_url('/local/shop/front/view.php', $params))->out();

        // Temporary approach. Then we will specialize the template.
        return $this->output->render_from_template($tplfile, $template);
    }
}
