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
 * @category    local
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

class shop_products_renderer extends local_shop_base_renderer {

    public function catalog_header() {

        $this->check_context();

        $str = '';

        $str .= '<div class="shop-table container-fluid">';
        $str .= '<div class="shop-row">';
        $str .= '<div class="shop-cell header span4">'.get_string('name', 'local_shop').'</div>';
        $str .= '<div class="shop-cell header span4">'.format_string($this->thecatalog->name).'</div>';
        $str .= '<div class="shop-cell header span4">';

        if ($this->thecatalog->ismaster) {
            $str .= get_string('master', 'local_shop');
        } else if ($this->thecatalog->isslave) {
            $str .= get_string('slave', 'local_shop');
        } else {
            $str .= get_string('standalone', 'local_shop');
        }
        $str .= '</div>';
        $str .= '</div>';

        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param span4">'.get_string('description').'</div>';
        $str .= '<div class="shop-cell value span8">'.$this->thecatalog->description.'</div>';
        $str .= '</div>';

        $shops = Shop::count(array('catalogid' => $this->thecatalog->id));
        $str .= '<div class="shop-row row-fluid">';
        $str .= '<div class="shop-cell param span4">'.get_string('shops', 'local_shop').'</div>';
        $str .= '<div class="shop-cell value span8">'.(0 + $shops).'</div>';
        $str .= '</div>';

        $str .= '</div>';

        return $str;
    }

    public function product_admin_line($product) {
        global $OUTPUT;

        $this->check_context();

        $template = new StdClass;

        if (is_null($product)) {
            $template->header = true;
            $template->helpcodeicon = $this->output->help_icon('helpcode', 'local_shop');
            $template->helpshortnameicon = $this->output->help_icon('helpshortname', 'local_shop');
        } else {
            $template->header = false;
            $template->pricelines = array();
            $prices = $product->get_printable_prices();
            foreach ($prices as $key => $price) {
                $pricelinetpl = new StdClass;
                $pricelinetpl->pricekey = $key;
                $pricelinetpl->price = $price;
                $template->pricelines[] = $pricelinetpl;
            }

            $taxedpricelines = array();
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
            $template->thumburl = $product->get_thumb_url();
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
                $params = array('view' => 'viewAllProducts', 'what' => 'toset', 'itemid' => $product->id);
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('toset', get_string('toset', 'local_shop'), 'local_shop').'</a> ';

                $params = array('view' => 'viewAllProducts', 'what' => 'tobundle', 'itemid' => $product->id);
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('tobundle', get_string('tobundle', 'local_shop'), 'local_shop').'</a> ';

                // We cannot edit master records ghosts from the slave catalog.
                $editurl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $product->id));
                $cmds .= '<a href="'.$editurl.'">'.$OUTPUT->pix_icon('t/edit', get_string('edit')).'</a> ';

                $params = array('view' => 'viewAllProducts', 'what' => 'clone', 'itemid' => $product->id);
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/copy', get_string('copy')).'</a> ';

                $deletestr = get_string('deleteproduct', 'local_shop');
                $params = array('view' => 'viewAllProducts', 'what' => 'delete', 'items[]' => $product->id);
                $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                $cmds .= '&nbsp;<a href="'.$cmdurl.'">'.$OUTPUT->pix_icon('t/delete', $deletestr).'</a>';
            }

            $createlocalstr = get_string('addoverride', 'local_shop');
            $deletelocalversionstr = get_string('deleteoverride', 'local_shop');

            if ($this->thecatalog->isslave) {
                if ($product->masterrecord == 1) {
                    $params = array('view' => 'viewAllProducts',
                                    'what' => 'makecopy',
                                    'itemid' => $product->id,
                                    'catalogid' => $this->thecatalog->id);
                    $cmdurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('copy', $createlocalstr, 'local_shop');
                    $cmds .= '&nbsp;<a href="'.$cmdurl.'">'.$pixicon.'</a>';
                } else {
                    $params = array('view' => 'viewAllProducts',
                                    'what' => 'freecopy',
                                    'itemid' => $product->id,
                                    'catalogid' => $this->thecatalog->id);
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
     */
    public function set_admin_line($set) {
        global $OUTPUT;

        $this->check_context();

        $hassubs = count($set->elements);

        $slaveclass = (!$this->thecatalog->isslave || (@$set->masterrecord == 1)) ? 'master' : 'slave';

        $statusclass = strtolower($set->status);

        $template = new Stdclass;
        $template->statusclass = $statusclass;
        $template->slaveclass = $slaveclass;
        $template->id = $set->id;

        $template->thumburl = $set->get_thumb_url(true);
        if (empty($template->thumburl)) {
            $template->thumburl = $OUTPUT->pix_url('productset', 'local_shop');
        }
        $template->code = $set->code;
        $template->shortname = $set->shortname;
        $template->name = format_string($set->name);

        $cmds = '';
        if (!$this->thecatalog->isslave || (@$set->masterrecord == 0)) {
            // We cannot edit master records ghosts from the slave catalog.
            $editseturl = new moodle_url('/local/shop/products/edit_set.php', array('itemid' => $set->id));
            $pixicon = $OUTPUT->pix_icon('t/edit', get_string('editset', 'local_shop'), 'moodle');
            $cmds .= '<a href="'.$editseturl.'">'.$pixicon.'</a>';

            if ($hassubs) {
                $params = array('view' => 'viewAllProducts', 'what' => 'unlink', 'itemid' => $set->id);
                $unlinkurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('unlinkcontent', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$unlinkurl.'">'.$pixicon.'</a>';
            } else {
                $params = array('view' => 'viewAllProducts', 'what' => 'delete', 'itemid' => $set->id);
                $deleteurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('delete');
                $pixicon = $OUTPUT->pix_icon('t/delete', $linklbl, 'moodle');
                $cmds .= '&nbsp;<a href="'.$deleteurl.'">'.$pixicon.'</a>';
            }
        }

        if ($this->thecatalog->isslave) {
            if ($set->masterrecord == 1) {
                $params = array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $set->id);
                $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('addoverride', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';
            } else {
                $params = array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $set->id);
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

    public function bundle_admin_line($bundle) {
        global $OUTPUT;

        $this->check_context();

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
            $template->thumburl = $OUTPUT->pix_url('productbundle', 'local_shop');
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
            $editurl = new moodle_url('/local/shop/products/edit_bundle.php', array('itemid' => $bundle->id));
            $linklbl = get_string('editbundle', 'local_shop');
            $pixicon = $OUTPUT->pix_icon('t/edit', $linklbl, 'moodle');
            $cmds .= '<a href="'.$editurl.'">'.$pixicon.'</a>';

            if ($hassubs) {
                $params = array('view' => 'viewAllProducts', 'what' => 'unlink', 'itemid' => $bundle->id);
                $viewurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('unlinkcontent', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$viewurl.'">'.$pixicon.'</a>';
            } else {
                $params = array('view' => 'viewAllProducts', 'what' => 'delete', 'itemid' => $bundle->id);
                $viewurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('deletebundle', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('i/delete', $linklbl, 'core');
                $cmds .= '&nbsp;<a href="'.$viewurl.'">'.$pixicon.'</a>';
            }
        }

        if ($this->thecatalog->isslave) {
            if ($bundle->masterrecord == 1) {
                $params = array('view' => 'viewAllProducts', 'what' => 'makecopy', 'productid' => $bundle->id);
                $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                $linklbl = get_string('addoverride', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                $cmds .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';
            } else {
                $params = array('view' => 'viewAllProducts', 'what' => 'freecopy', 'productid' => $bundle->id);
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
     * @param object $set a complete set structure.
     */
    public function set_admin_elements($set) {
        global $OUTPUT;

        $table = $this->prepare_elements_table();

        foreach ($set->elements as $setelm) {
            if (!$this->thecatalog->isslave || (@$setelm->masterrecord == 0)) {
                $table->rowclasses[] = '';
            } else {
                $table->rowclasses[] = 'slaved';
            }
            $row = array();
            $row[] = '<img class="thumb" src="'.$setelm->get_thumb_url().'" height="50">';
            $row[] = $setelm->code;
            $row[] = format_string($setelm->name);
            $row[] = sprintf("%.2f", round($setelm->price1, 2)).'<br/>('.$setelm->taxcode.')';
            $row[] = sprintf("%.2f", round($setelm->TTCprice, 2));
            $row[] = get_string($setelm->status, 'local_shop');

            $commands = '';
            if ((!$this->thecatalog->isslave) || ($setelm->masterrecord == 0)) {
                // We cannot edit master records ghosts from the slave catalog.
                $editurl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $setelm->id));
                $linklbl = get_string('editproduct', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('t/edit', $linklbl, 'moodle');
                $commands .= '<a href="'.$editurl.'">'.$pixicon.'</a>';

                if (!$this->thecatalog->isslave) {

                    $params = array('view' => 'viewAllProducts', 'what' => 'clone', 'itemid' => $setelm->id);
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('t/copy', get_string('copy'), 'moodle');
                    $commands .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';

                    $params = array('view' => 'viewAllProducts', 'what' => 'unlink', 'itemid' => $setelm->id);
                    $unlinkurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('unlinkproduct', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$unlinkurl.'">'.$pixicon.'</a>';

                    // Only real products can be unlinked or deleted or copied.
                    $params = array('view' => 'viewAllProducts', 'what' => 'delete', 'items' => $setelm->id);
                    $deleteurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('delete');
                    $pixicon = $OUTPUT->pix_icon('t/delete', $linklbl, 'moodle');
                    $commands .= '&nbsp;<a href="'.$deleteurl.'">'.$pixicon.'</a>';
                }
            }

            if ($this->thecatalog->isslave) {
                if ($setelm->masterrecord == 1) {
                    // If we do not have a local override, allow creating one.
                    $params = array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $setelm->id);
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('addoverride', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$pixicon.'</a>';
                } else {
                    // If we do have an override, allow discarding it.
                    $params = array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $setelm->id);
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
     * @param object $bundle a complete bundle structure.
     */
    public function bundle_admin_elements($bundle) {
        global $OUTPUT;

        $table = $this->prepare_elements_table();

        foreach ($bundle->elements as $bundleelm) {
            if (!$this->thecatalog->isslave || (@$bundleelm->masterrecord == 0)) {
                $table->rowclasses[] = '';
            } else {
                $table->rowclasses[] = 'slaved';
            }
            $row = array();
            $row[] = '<img class="thumb" src="'.$bundleelm->get_thumb_url().'" height="50">';
            $row[] = $bundleelm->code;
            $row[] = $bundleelm->name;
            $row[] = '<span class="shop-shadow">'.sprintf("%.2f", round($bundleelm->price1, 2)).'<br/>('.$bundleelm->taxcode.')</span>';
            $row[] = '<span class="shop-shadow">'.sprintf("%.2f", round($bundleelm->TTCprice, 2)).'</span>';
            $row[] = get_string($bundleelm->status, 'local_shop');

            $commands = '';
            if (!$this->thecatalog->isslave || ($bundleelm->masterrecord == 0)) {
                $editurl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $bundleelm->id));
                $linklbl = get_string('editproduct', 'local_shop');
                $pixicon = $OUTPUT->pix_icon('t/edit', $linklbl, 'moodle');
                $commands .= '<a href="'.$editurl.'">'.$pixicon.'</a>';

                if (!$this->thecatalog->isslave) {

                    $params = array('view' => 'viewAllProducts', 'what' => 'clone', 'itemid' => $bundleelm->id);
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $pixicon = $OUTPUT->pix_icon('t/copy', get_string('copy'), 'moodle');
                    $commands .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a> ';

                    // Only real products can be unlinked or deleted.
                    $params = array('view' => 'viewAllProducts', 'what' => 'unlink', 'itemid' => $bundleelm->id);
                    $unlinkurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('unlinkproduct', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('unlink', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$unlinkurl.'">'.$pixicon.'</a>';

                    $params = array('view' => 'viewAllProducts', 'what' => 'delete', 'itemid' => $bundleelm->id);
                    $deleteurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('delete');
                    $pixicon = $OUTPUT->pix_icon('t/delete', $linklbl, 'moodle');
                    $commands .= '&nbsp;<a href="'.$deleteurl.'">'.$pixicon.'</a>';
                }
            }

            if ($this->thecatalog->isslave) {
                if ($bundleelm->masterrecord == 1) {
                    $params = array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $bundleelm->id);
                    $copyurl = new moodle_url('/local/shop/products/view.php', $params);
                    $linklbl = get_string('addoverride', 'local_shop');
                    $pixicon = $OUTPUT->pix_icon('copy', $linklbl, 'local_shop');
                    $commands .= '&nbsp;<a href="'.$copyurl.'">'.$pixicon.'</a>';
                } else {
                    $params = array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $bundleelm->id);
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

    public function catlinks($thecatalog) {
        global $OUTPUT, $SESSION;

        $this->check_context();

        $categoryid = 0 + @$SESSION->shop->categoryid;

        $template = new StdClass;

        $params = array('view' => 'viewAllCategories', 'catalogid' => $this->thecatalog->id);
        $template->catlinkurl = new moodle_url('/local/shop/products/category/view.php', $params);

        if (Category::count(array('catalogid' => $thecatalog->id))) {
            $template->hascategories = true;
            $params = array('id' => $this->theshop->id, 'categoryid' => $categoryid);

            if (local_shop_supports_feature() == 'pro' || CatalogItem::count(array()) < 10) {
                $template->producturl = new moodle_url('/local/shop/products/edit_product.php', $params);

                $template->seturl = new moodle_url('/local/shop/products/edit_set.php', $params);

                $template->bundleurl = new moodle_url('/local/shop/products/edit_bundle.php', $params);
            }

            $template->testurl = new moodle_url('/local/shop/unittests/index.php', array('id' => $this->theshop->id));
        }

        $linkedshops = Shop::get_instances(array('catalogid' => $this->thecatalog->id), 'name');
        if (count($linkedshops) == 1) {
            $template->hasonelinkedshop = true;
            $shop = array_shift($linkedshops);
            $template->fronturl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $shop->id));
        } else {
            $template->hasseverallinkedshops = true;
            $shopopts = array();
            foreach ($linkedshops as $sh) {
                $shopopts[$sh->id] = format_string($sh->name);
            }
            $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop'));
            $template->shopselect = $this->output->single_select($shopurl, 'id', $shopopts);
        }

        return $this->output->render_from_template('local_shop/products_catlinks', $template);
    }

    public function category_chooser($url) {
        global $OUTPUT, $SESSION;

        // In case it was not done before, but it might.
        $SESSION->shop->categoryid = $current = optional_param('categoryid', 0 + @$SESSION->shop->categoryid, PARAM_INT);

        $categories = Category::get_instances(array('catalogid' => $this->thecatalog->id, 'parentid' => 0), 'sortorder');

        $catoptions = array();
        $this->feed_chooser($catoptions, $categories);

        $str = '';

        if (count($categories) > 1) {

            $name = 'categoryid';
            $str .= '<div class="shop-category-chooser">';
            $params = array(0 => get_string('allcategories', 'local_shop'));
            $str .= get_string('category', 'local_shop').' : '.$OUTPUT->single_select($url, $name, $catoptions, $current, $params);
            $str .= '</div>';
        }

        return $str;
    }

    protected function feed_chooser(&$catoptions, $categories, $prefix = '') {
        foreach ($categories as $cat) {
            $catoptions[$cat->id] = $prefix.format_string($cat->name);
            $subs = Category::get_instances(array('catalogid' => $this->thecatalog->id, 'parentid' => $cat->id), 'sortorder');
            if ($subs) {
                $prefixtmp = $prefix;
                $prefix .= format_string($cat->name).'/';
                $this->feed_chooser($catoptions, $subs, $prefix);
                $prefix = $prefixtmp;
            }
        }
    }

    public function categories($categories) {
        $order = optional_param('order', 'sortorder', PARAM_ALPHA);
        $dir = optional_param('dir', 'ASC', PARAM_ALPHA);

        $namestr = get_string('catname', 'local_shop');
        $catdescstr = get_string('catdescription', 'local_shop');
        $prodcountstr = get_string('productcount', 'local_shop');
        $parentcatstr = get_string('parentcategory', 'local_shop');

        $table = new html_table();
        $table->class = 'generaltable';
        $table->head = array("<b>$namestr</b>", "<b>$parentcatstr</b>", "<b>$catdescstr</b>", "<b>$prodcountstr</b>", '');
        $table->width = '100%';
        $table->align = array('left', 'left', 'left', 'center', 'right');
        $table->size = array('20%', '20%', '30%', '10%', '20%');

        foreach ($categories as $cat) {
            $this->category_add_row($table, $cat, $order, $dir);
        }

        echo html_writer::table($table);
    }

    protected function category_add_row(&$table, $category, $order, $dir) {
        global $OUTPUT, $DB;
        static $indentarr = array();

        $subs = Category::get_instances(array('catalogid' => $this->thecatalog->id,
                                              'parentid' => $category->id), "$order $dir");

        $params = array('id' => $this->theshop->id, 'view' => 'viewAllCategories', 'order' => $order, 'dir' => $dir);
        $url = new moodle_url('/local/shop/products/category/view.php', $params);
        $params = array('catalogid' => $this->thecatalog->id, 'parentid' => $category->parentid);
        $maxorder = $DB->get_field('local_shop_catalogcategory', 'MAX(sortorder)', $params);

        $indent = implode('', $indentarr);

        $row = array();

        $class = ($category->visible) ? 'shop-shadow' : '';
        $row[] = $indent.'<span class="'.$class.'">'.format_string($category->name).'</span>';

        $row[] = $category->get_parent_name();

        $contextid = context_system::instance()->id;
        $category->description = file_rewrite_pluginfile_urls($category->description, 'pluginfile.php',
                                                              $contextid, 'local_shop', 'categorydescription', $category->id);
        $row[] = format_text($category->description);

        $row[] = $DB->count_records('local_shop_catalogitem', array('categoryid' => $category->id));

        if ($category->visible) {
            $pixurl = $OUTPUT->pix_url('t/hide');
            $cmd = 'hide';
        } else {
            $pixurl = $OUTPUT->pix_url('t/show');
            $cmd = 'show';
        }
        $commands = "<a href=\"{$url}&amp;what=$cmd&amp;categoryid={$category->id}\"><img src=\"$pixurl\" /></a>";
        $params = array('id' => $this->theshop->id, 'categoryid' => $category->id, 'what' => 'updatecategory');
        $editurl = new moodle_url('/local/shop/products/category/edit_category.php', $params);
        $commands .= '&nbsp;<a href="'.$editurl.'">'.$OUTPUT->pix_icon('t/edit', get_string('edit'), 'moodle').'</a>';

        if (empty($subs)) {
            $params = array('shopid' => $this->theshop->id,
                            'view' => 'viewAllCategories',
                            'order' => $order,
                            'dir' => $dir,
                            'what' => 'delete',
                            'categoryids[]' => $category->id);
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

    protected function prepare_elements_table() {
        $codestr = get_string('code', 'local_shop');
        $namestr = get_string('name', 'local_shop');
        $pricestr = get_string('price', 'local_shop');
        $ttcstr = get_string('ttc', 'local_shop');
        $availabilitystr = get_string('availability', 'local_shop');

        $table = new html_table();
        $table->head = array('',
                             "<b>$codestr</b>",
                             "<b>$namestr</b>",
                             "<b>$pricestr</b>",
                             "<b>$ttcstr</b>",
                             "<b>$availabilitystr</b>",
                             '');
        $table->width = '100%';
        $table->size = array('10%', '10%', '45%', '10%', '10%', '10%', '5%');
        $table->align = array('left', 'left', 'left', 'right', 'right', 'center', 'right');
        $table->colclasses = array('', '', '', '', '', '', 'shop-setcontrols');

        return $table;
    }

    public function catalogitem_details($catalogitemid) {
        return 'to be written ';
    }
}