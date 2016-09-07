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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Category.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Shop;
use local_shop\Tax;
use local_shop\Category;

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class shop_products_renderer {

    protected $theshop;

    protected $thecatalog;

    protected $theblock;

    function load_context(&$theShop, &$theCatalog, &$theBlock = null) {
        $this->theshop = $theShop;
        $this->thecatalog = $theCatalog;
        $this->theblock = $theBlock;
    }

    private function _check_context() {
        if (empty($this->thecatalog)) {
            throw new coding_exception('context not ready in products_renderer. Missing Catlaog instance');
        }
    }

    function catalog_header() {

        $this->_check_context();

        $str = '';

        $str .= '<div class="shop-table container-fluid">';
        $str .= '<div class="shop-row">';
        $str .= '<div class="shop-cell header span4">'.get_string('name', 'local_shop').'</div>';
        $str .= '<div class="shop-cell header span4">'.format_string($this->thecatalog->name).'</div>';
        $str .= '<div class="shop-cell header span4">';

        if ($this->thecatalog->ismaster) {
            $str .= get_string('master', 'local_shop');
        } elseif ($this->thecatalog->isslave) {
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

    function product_admin_line($product, $return = false) {
        global $OUTPUT, $CFG;

        $this->_check_context();

        $str = '';

        if (is_null($product)) {

            $str .= '<tr class="shop-products-caption" valign="top">';
            $str .= '<!--<th class="header c0">';
            $str .= get_string('sel', 'local_shop');
            $str .= '</th>-->';
            $str .= '<th class="header c0" rowspan="2">';
            $str .= get_string('image', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c1">';
            $str .= get_string('code', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c2" colspan="6">';
            $str .= get_string('designation', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c3">';
            $str .= '</th>';
            $str .= '</tr>';

            $str .= '<tr class="shop-products-caption" valign="top">';
            $str .= '<th class="header c1" colspan="2">';
            $str .= get_string('price', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c2" colspan="2">';
            $str .= get_string('TTC', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c3" colspan="1" align="center">';
            $str .= get_string('status', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c4" align="center">';
            $str .= get_string('sales', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c5" align="center">';
            $str .= get_string('stock', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c6" align="center">';
            $str .= get_string('renewable', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c7" align="center">';
            $str .= get_string('seats', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header lastcol" class="shop-controls" width="30">';
            $str .= '</th>';
            $str .= '</tr>';
        } else {
            $pricelines = array();
            $prices = $product->get_printable_prices();
            foreach ($prices as $key => $price) {
                $pricelines[] = '<span class="shop-admin-pricerange">'.$key.' : </span><span class="shop-admin-amount">'.$price.'</span>';
            }

            $taxedpricelines = array();
            $prices = $product->get_printable_prices(true);
            foreach ($prices as $key => $price) {
                $taxedpricelines[] = '<span class="shop-admin-pricerange">'.$key.' : </span><span class="shop-admin-amount">'.$price.'</span>';
            }

            $statusclass = strtolower($product->status);
            $str .= '<tr class="shop-'.$statusclass.'line shop-product-row" valign="top">';
            $slaveclass  = (!$this->thecatalog->isslave || (@$product->masterrecord == 0)) ? '' : 'engraved slaved' ;
            $str .= '<td class="cell '.$slaveclass.'"align="center" rowspan="2">';
            $str .= '<img src="'.$product->thumb.'" vspace="10" height="50">';
            $str .= '</td>';
            $str .= '<td class="name cell '.$slaveclass.'" align="left">';
            $str .= $product->code;
            $str .= '</td>';
            $str .= '<td class="name cell '.$slaveclass.'" align="left" colspan="8">';
            $str .= $product->name;
            $str .= '</td>';
            $str .= '<td class="name cell '.$slaveclass.' shop-controls" align="left">';
            if ($product->enablehandler) {
                $str .= '<i class="fa fa-cog" title="'.$product->enablehandler.'"></i>';
            }
            $str .= '</td>';
            $str .= '</tr>';

            $str .= '<tr valign="top">';
            $str .= '<td class="amount cell '.$slaveclass.'" align="left" colspan="2">';
            $str .= implode('<br/>', $pricelines);
            $str .= '<br/>';
            $tax = new Tax($product->taxcode);
            $str .= '<div title="'.$tax->title.'">('.$product->taxcode. ')</div>';
            $str .= '</td>';
            $str .= '<td class="amount cell '.$slaveclass.'"align="left" colspan="2">';
            $str .= implode('<br/>', $taxedpricelines);
            $str .= '<br/>';
            $str .= '</td>'; 
            $str .= '<td class="status cell '.$slaveclass.'" align="right">';
            $str .= get_string($product->status, 'local_shop');
            $str .= '</td>';
            $str .= '<td class="amount cell '.$slaveclass.'" align="center">';
            $str .= $product->sold;
            $str .= '</td>'; 
            $str.= '<td class="amount cell '.$slaveclass.'" align="center">';
            $str .= $product->stock;
            $str .= '</td>';
            $str .= '<td class="amount cell '.$slaveclass.'" align="center">';
            $str .= ($product->renewable) ? get_string('yes') : '' ;
            $str .= '</td>';
            $str .= '<td class="amount cell '.$slaveclass.'" align="center">';
            switch ($product->quantaddressesusers) {
    
                case SHOP_QUANT_NO_SEATS:
                    $str .= get_string('no');
                    break;
    
                case SHOP_QUANT_ONE_SEAT:
                    $str .= get_string('oneseat', 'local_shop');
                    break;

                  case SHOP_QUANT_AS_SEATS:
                    $str .= get_string('yes');
                    break;
            }

            $str .= '</td>';
            $str .= '<td align="right" class="lastcol shop-controls">';
            if (!$this->thecatalog->isslave || (@$product->masterrecord == 0)) {
                // We cannot edit master records ghosts from the slave catalog.
                $editurl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $product->id));
                $str .= '<a href="'.$editurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" /></a> ';

                $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'clone', 'itemid' => $product->id));
                $str .= '<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('t/copy').'" title="'.get_string('copy').'"/></a> ';

                $deletestr = get_string('deleteproduct', 'local_shop');
                $deleteurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'delete', 'items[]' => $product->id));
                $str .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" title="'.$deletestr.'"></a>';
            }

            $createlocalstr = get_string('addoverride', 'local_shop');
            $deletelocalversionstr = get_string('deleteoverride', 'local_shop');

            if ($this->thecatalog->isslave) {
                if ($product->masterrecord == 1) {
                    $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $product->id));
                    $str .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('copy', 'local_shop').'" title="'.$createlocalstr.'"></a>';
                } else {
                    $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $product->id));
                    $str .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('uncopy', 'local_shop').'" title="'.$deletelocalversionstr.'"></a>';
                }
            }
            $str .= '</td>';
            $str .= '</tr>';
        }

        if ($return) return $str;
        echo $str;
    }

    /**
     * Prints an administration line for a product set
     */
    function set_admin_line($set) {
        global $OUTPUT;

        $this->_check_context();

        $slaveclass = (!$this->thecatalog->isslave || (@$set->masterrecord == 1)) ? 'master' : 'slave';

        $statusclass = strtolower($set->status);

        $str = '<tr class="shop-'.$statusclass.'line shop-set-row">';
        $str .= '<!-- td width="30" class="'.$slaveclass.'">';
        $str .= '<input type="checkbox" name="items[]" value="'.$set->id.'" />';
        $str .= '</td -->';
        $str .= '<td class="'.$slaveclass.'" align="center">';
        $str .= '<img src="'.$set->thumb.'" vspace="10" border="0" height="50">';
        $str .= '</td>';
        $str .= '<td class="name '.$slaveclass.'">';
        $str .= '<b>'.$set->code.'</b><br/>';
        $str .= '('.$set->shortname.')';
        $str .= '</td>';
        $str .= '<td class="name '.$slaveclass.'" colspan="8">';
        $str .= $set->name;
        $str .= '</td>';
        $str .= '<td width="10" class="shop-controls">';

        if (!$this->thecatalog->isslave || (@$set->masterrecord == 0)) {
            // We cannot edit master records ghosts from the slave catalog.
            $editseturl = new moodle_url('/local/shop/products/edit_set.php', array('setid' => $set->id));
            $str .= '<a href="'.$editseturl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" title="'.get_string('editset', 'local_shop').'"></a>';

            $deleteurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'delete', 'items[]' => $set->id));
            $linklbl = get_string('removeset', 'local_shop');
            $str .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" title="'.$linklbl.'"></a>';

            $unlinkurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'unlink', 'itemid' => $set->id));
            $linklbl = get_string('removealllinkedproducts', 'local_shop');
            $str .= '&nbsp;<a href="'.$unlinkurl.'"><img src="'.$OUTPUT->pix_url('unlink', 'local_shop').'" title="'.$linklbl.'"></a>';
        }

        if ($this->thecatalog->isslave) {
            if ($set->masterrecord == 1) {
                $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $set->id));
                $linklbl = get_string('addoverride', 'local_shop');
                $str .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('copy', 'local_shop').'" title="'.$linklbl.'" /></a>';
            } else {
                $uncopyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $set->id));
                $linklbl = get_string('deleteoverride', 'local_shop');
                $str .= '&nbsp;<a href="'.$uncopyurl.'"><img src="'.$OUTPUT->pix_url('uncopy', 'local_shop').'" title="'.$linklbl.'" /></a>';
            }
        }
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td colspan="2">';
        $str .= '<td class="list" colspan="9">';
        if (count($set->elements) == 0) {
            $str .= get_string('noproductinset', 'local_shop');
        } else {
            $str .= $this->set_admin_elements($set);
        }
        $str .= '</td>';

        return $str;
    }

    function bundle_admin_line($bundle) {
        global $OUTPUT;

        $this->_check_context();

        $slaveclass = (!$this->thecatalog->isslave || (@$bundle->masterrecord == 1)) ? 'master' : 'slaved';

        $statusclass = strtolower($bundle->status);

        $str = '<tr valign="top" class="shop-'.$statusclass.'line shop-bundle-row">';
        $str .= '<!-- td width="30" class="'.$slaveclass.'">';
        $str .= '<input type="checkbox" name="items[]" value="'.$bundle->id.'" />';
        $str .= '</td -->';
        $str .= '<td class="'.((@$bundle->masterrecord == 0) ? '' : 'engraved').' thumb" rowspan="2" align="center">';
        $str .= '<img src="'.$OUTPUT->pix_url('productbundle', 'local_shop').'" height="50" />';
        $str .= '</td>';
        $str .= '<td class="code '.$slaveclass.'">';
        $str .= '<b>'.$bundle->code.'</b><br/>';
        $str .= ' ('.$bundle->shortname.')';
        $str .= '</td>';
        $str .= '<td class="name '.$slaveclass.'" colspan="9">';
        $str .= format_string($bundle->name);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td class="amount '.$slaveclass.'" colspan="2">';
        $str .= sprintf("%.2f", round($bundle->price1, 2)).'<br>';
        $str .= ' ('.$bundle->taxcode.')';
        $str .= '</td>';
        $str .= '<td class="amount '.$slaveclass.'" colspan="2">';
        $str .= sprintf("%.2f", round($bundle->bundleTTCPrice, 2));
        $str .= '</td>';
        $str .= '<td class="status '.$slaveclass.'" align="center">';
        $str .= get_string($bundle->status, 'local_shop');
        $str .= '</td>';
        $str .= '<td class="sold '.$slaveclass.'" align="center">';
        $str .= $bundle->sold;
        $str .= '</td>';
        $str .= '<td class="stock '.$slaveclass.'" align="center">';
        $str .= $bundle->stock;
        $str .= '</td>';
        $str .= '<td class="renewable '.$slaveclass.'" align="center">';
        $str .= ($bundle->renewable) ? get_string('yes') : get_string('no');
        $str .= '</td>';
        $str .= '<td class="seats '.$slaveclass.'">';
        $str .= '</td>';
        $str .= '<td class="shop-setcontrols">';

        if (!$this->thecatalog->isslave || (@$bundle->masterrecord == 0)) {
            // We cannot edit master records ghosts from the slave catalog.
            $editurl = new moodle_url('/local/shop/products/edit_bundle.php', array('itemid' => $bundle->id));
            $linklbl = get_string('editbundle', 'local_shop');
            $str .= '<a href="'.$editurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" title="'.$linklbl.'" /></a>';

            $viewurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'unlinkset', 'itemid' => $bundle->id));
            $linklbl = get_string('deletebundle', 'local_shop');
            $str .= '&nbsp;<a href="'.$viewurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" title="'.$linklbl.'" /></a>';

            $deleteurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'delete', 'items[]' => $bundle->id));
            $linklbl = get_string('deletealllinkedproducts', 'local_shop');
            $str .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('unlink', 'local_shop').'" title="'.$linklbl.'" /></a>';
        }

        if ($this->thecatalog->isslave) {
            if ($bundle->masterrecord == 1) {
                $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'makecopy', 'productid' => $bundle->id));
                $linklbl = get_string('addoverride', 'local_shop');
                $str .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('copy', 'local_shop').'" title="'.$linklbl.'" /></a>';
            } else {
                $deletecopyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'freecopy', 'productid' => $bundle->id));
                $linklbl = get_string('deleteoverride', 'local_shop');
                $str .= '&nbsp;<a href="'.$deletecopyurl.'"><img src="'.$OUTPUT->pix_url('uncopy', 'local_shop').'" title="'.$linklbl.'" /></a>';
            }
        }
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td colspan="2">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td class="list" colspan="9">';

        if (count($bundle->elements) == 0) {
            $str .= $OUTPUT->notification(get_string('noproductinbundle', 'local_shop'));
        } else {
            $codestr = get_string('code', 'local_shop');
            $namestr = get_string('name', 'local_shop');
            $pricestr = get_string('price', 'local_shop');
            $ttcstr = get_string('TTC', 'local_shop');
            $availabilitystr = get_string('availability', 'local_shop');

            $str .= $this->bundle_admin_elements($bundle);

        }
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function set_admin_elements($set) {
        global $OUTPUT;

        $codestr = get_string('code', 'local_shop');
        $namestr = get_string('name', 'local_shop');
        $pricestr = get_string('price', 'local_shop');
        $ttcstr = get_string('ttc', 'local_shop');
        $availabilitystr = get_string('availability', 'local_shop');

        $table = new html_table();
        $table->head = array('', "<b>$codestr</b>", "<b>$namestr</b>", "<b>$pricestr</b>", "<b>$ttcstr</b>", "<b>$availabilitystr</b>", '');
        $table->width = '100%';
        $table->size = array('10%', '10%', '45%', '10%', '10%', '10%', '5%');
        $table->align = array('left', 'left', 'left', 'right', 'right', 'center', 'right');
        $table->colclasses = array('', '', '', '', '', '', 'shop-setcontrols');
        foreach ($set->elements as $elm) {
            if (!$this->thecatalog->isslave || (@$elm->masterrecord == 0)) {
                $table->rowclasses[] = '';
            } else {
                $table->rowclasses[] = 'slaved';
            }
            $row = array();
            $row[] = '<img class="thumb" src="'.$elm->get_thumb_url().'" height="50">';
            $row[] = $elm->code;
            $row[] = format_string($elm->name);
            $row[] = sprintf("%.2f", round($elm->price1, 2)).'<br/>('.$elm->taxcode.')';
            $row[] = sprintf("%.2f", round($elm->TTCprice, 2));
            $row[] = get_string($elm->status, 'local_shop');

            $commands = '';
            if ((!$this->thecatalog->isslave) || ($elm->masterrecord == 0)) {
                // We cannot edit master records ghosts from the slave catalog.
                $editurl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $elm->id));
                $linklbl = get_string('editproduct', 'local_shop');
                $commands .= '<a href="'.$editurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" title="'.$linklbl.'"></a>';

                if (!$this->thecatalog->isslave) {

                    $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'clone', 'itemid' => $elm->id));
                    $commands .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('t/copy').'" title="'.get_string('copy').'"/></a> ';

                    // Only real products can be unlinked or deleted or copied.
                    $deleteurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'deleteproduct', 'itemid' => $elm->id));
                    $linklbl = get_string('removeset', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" title="'.$linklbl.'"></a>';

                    $unlinkurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'unlink', 'itemid' => $elm->id));
                    $linklbl = get_string('unlinkproduct', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$unlinkurl.'"><img src="'.$OUTPUT->pix_url('unlink', 'local_shop').'" title="'.$linklbl.'"></a>';
                }
            }

            if ($this->thecatalog->isslave) {
                if ($elm->masterrecord == 1) {
                    // If we do not have a local override, allow creating one.
                    $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $elm->id));
                    $linklbl = get_string('addoverride', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('copy', 'local_shop').'" title="'.$linklbl.'" /></a>';
                } else {
                    // If we do have an override, allow discarding it.
                    $uncopyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $elm->id));
                    $linklbl = get_string('deleteoverride', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$uncopyurl.'"><img src="'.$OUTPUT->pix_url('uncopy', 'local_shop').'" title="'.$linklbl.'" /></a>';
                }
            }
            $row[] = $commands;

            $table->data[] = $row;
        }

        $str = html_writer::table($table);

        return $str;
    }


    function bundle_admin_elements($bundle) {
        global $OUTPUT;

        $codestr = get_string('code', 'local_shop');
        $namestr = get_string('name', 'local_shop');
        $pricestr = get_string('price', 'local_shop');
        $ttcstr = get_string('ttc', 'local_shop');
        $availabilitystr = get_string('availability', 'local_shop');

        $table = new html_table();
        $table->head = array('', "<b>$codestr</b>", "<b>$namestr</b>", "<b>$pricestr</b>", "<b>$ttcstr</b>", "<b>$availabilitystr</b>", '');
        $table->width = '100%';
        $table->size = array('10%', '10%', '45%', '10%', '10%', '10%', '5%');
        $table->align = array('left', 'left', 'left', 'right', 'right', 'center', 'right');
        $table->colclasses = array('', '', '', '', '', '', 'shop-setcontrols');
        foreach ($bundle->elements as $elm) {
            if (!$this->thecatalog->isslave || (@$elm->masterrecord == 0)) {
                $table->rowclasses[] = '';
            } else {
                $table->rowclasses[] = 'slaved';
            }
            $row = array();
            $row[] = '<img class="thumb" src="'.$elm->get_thumb_url().'" height="50">';
            $row[] = $elm->code;
            $row[] = $elm->name;
            $row[] = sprintf("%.2f", round($elm->price1, 2)).'<br/>('.$elm->taxcode.')';
            $row[] = sprintf("%.2f", round($elm->TTCprice, 2));
            $row[] = get_string($elm->status, 'local_shop');

            $commands = '';
            if (!$this->thecatalog->isslave || ($elm->masterrecord == 0)) {
                $editurl = new moodle_url('/local/shop/products/edit_product.php', array('itemid' => $elm->id));
                $linklbl = get_string('editproduct', 'local_shop');
                $commands .= '<a href="'.$editurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'" title="'.$linklbl.'"></a>';

                if (!$this->thecatalog->isslave) {

                    $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'clone', 'itemid' => $elm->id));
                    $commands .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('t/copy').'" title="'.get_string('copy').'"/></a> ';

                    // Only real products can be unlinked or deleted.
                    $unlinkurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'unlinkproduct', 'productid' => $elm->id));
                    $linklbl = get_string('removeproductfrombundle', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$unlinkurl.'"><img src="'.$OUTPUT->pix_url('unlink', 'local_shop').'" title="'.$linklbl.'" /></a>';
        
                    $deleteurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'deleteitems', 'itemid[]' => $elm->id));
                    $linklbl = get_string('delete');
                    $commands .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" title="'.$linklbl.'" /></a>';
                }
            }

            if ($this->thecatalog->isslave) {
                if ($elm->masterrecord == 1) {
                    $copyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'makecopy', 'itemid' => $elm->id));
                    $linklbl = get_string('addoverride', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$copyurl.'"><img src="'.$OUTPUT->pix_url('copy', 'local_shop').'" title="'.$linklbl.'" /></a>';
                } else {
                    $deletecopyurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'what' => 'freecopy', 'itemid' => $elm->id));
                    $linklbl = get_string('deleteoverride', 'local_shop');
                    $commands .= '&nbsp;<a href="'.$deletecopyurl.'"><img src="'.$OUTPUT->pix_url('uncopy', 'local_shop').'" title="'.$linklbl.'" /></a>';
                }
            }
            $row[] = $commands;

            $table->data[] = $row;
        }

        $str = html_writer::table($table);

        return $str;
    }

    function catlinks($theCatalog) {
        global $OUTPUT, $SESSION;

        $this->_check_context();

        $categoryid = 0 + @$SESSION->shop->categoryid;

        $str = '';

        $str .= '<div id="local-shop-catlinks">';

        $str .= '<div class="left-links">';
        $catlinkurl = new moodle_url('/local/shop/products/category/view.php', array('view' => 'viewAllCategory', 'catalogid' => $this->thecatalog->id));
        $str .= '<a href="'.$catlinkurl.'">'.get_string('edit_categories', 'local_shop').'</a> - ';
        if (Category::count(array('catalogid'=> $theCatalog->id))) {
            $producturl = new moodle_url('/local/shop/products/edit_product.php', array('id' => $this->theshop->id, 'categoryid' => $categoryid));
            $str .= '<a href="'.$producturl.'">'.get_string('newproduct', 'local_shop').'</a> - ';
            $seturl = new moodle_url('/local/shop/products/edit_set.php', array('id' => $this->theshop->id, 'categoryid' => $categoryid));
            $str .= '&nbsp;<a href="'.$seturl.'">'.get_string('newset', 'local_shop').'</a> - ';
            $bundleurl = new moodle_url('/local/shop/products/edit_bundle.php', array('id' => $this->theshop->id, 'categoryid' => $categoryid));
            $str .= '<a href="'.$bundleurl.'">'.get_string('newbundle', 'local_shop').'</a> - ';
            $testurl = new moodle_url('/local/shop/unittests/index.php', array('id' => $this->theshop->id));
            $str .= '&nbsp;<a href="'.$testurl.'">'.get_string('unittests', 'local_shop').'</a>';
        }
        $str .= '</div>';

        $str .= '<div class="right-links">';
        $linkedshops = Shop::get_instances(array('catalogid' => $this->thecatalog->id), 'name');
        if (count($linkedshops) == 1) {
            $shop = array_pop($linkedshops);
            $fronturl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $shop->id));
            $str .= '&nbsp;<a href="'.$fronturl.'">'.get_string('gotofrontoffice', 'local_shop').'</a>';
        } else {
            $shopopts = array();
            foreach ($linkedshops as $sh) {
                $shopopts[$sh->id] = format_string($sh->name);
            }
            $str .= get_string('gotofrontoffice', 'local_shop').': '.$OUTPUT->single_select(new moodle_url('/local/shop/front/view.php', array('view' => 'shop')), 'id', $shopopts);
        }
        $str .= '</div>';

        $str .= '</div>';

        return $str;
    }

    function category_chooser($url, $theCatalog) {
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
            $str .= get_string('category', 'local_shop').' : '.$OUTPUT->single_select($url, $name, $catoptions, $current, array(0 => get_string('allcategories', 'local_shop')));
            $str .= '</div>';
        }

        return $str;
    }

    protected function feed_chooser(&$catoptions, $categories, $prefix = '') {
        foreach ($categories as $cat) {
            $catoptions[$cat->id] = $prefix.$cat->name;
            $subs = Category::get_instances(array('catalogid' => $this->thecatalog->id, 'parentid' => $cat->id), 'sortorder');
            if ($subs) {
                $prefixtmp = $prefix;
                $prefix .= $cat->name.'/';
                $this->feed_chooser($catoptions, $subs, $prefix);
                $prefix = $prefixtmp;
            }
        }
    }

    function categories($categories) {
        $order = optional_param('order', 'name', PARAM_ALPHA);
        $dir = optional_param('dir', 'ASC', PARAM_ALPHA);

        $namestr = get_string('catname', 'local_shop');
        $catdescstr = get_string('catdescription', 'local_shop');
        $prodcountstr = get_string('productcount', 'local_shop');
        $parentcatstr = get_string('parentcategory', 'local_shop');

        $table = new html_table();
        $table->class = 'generaltable';
        $table->head = array("<b>$namestr</b>", "<b>$parentcatstr</b>", "<b>$catdescstr</b>", "<b>$prodcountstr</b>", '');
        $table->width = '100%';
        $table->align = array('left', 'left', 'center', 'right');
        $table->size = array('20%', '20%', '30%', '10%', '20%');

        foreach ($categories as $cat) {
            $this->category_add_row($table, $cat, $order, $dir);
        }

        echo html_writer::table($table);
    }
    
    protected function category_add_row(&$table, $category, $order, $dir) {
        global $OUTPUT, $DB;
        static $indentarr = array();

        $subs = Category::get_instances(array('catalogid' => $this->thecatalog->id, 'parentid' => $category->id), "$order $dir");

        $params = array('id' => $this->theshop->id, 'view' => 'viewAllCategories', 'order' => $order, 'dir' => $dir);
        $url = new moodle_url('/local/shop/products/category/view.php', $params);
        $maxorder = $DB->get_field('local_shop_catalogcategory', 'MAX(sortorder)', array('catalogid' => $this->thecatalog->id, 'parentid' => $category->parentid));

        $indent = implode('', $indentarr);

        $row = array();

        $class = ($category->visible) ? 'shop-shadow'  : '';
        $row[] = $indent.'<span class="'.$class.'">'.$category->name.'</span>';

        $category->description = file_rewrite_pluginfile_urls($category->description, 'pluginfile.php',context_system::instance()->id, 'local_shop', 'categorydescription', $category->id);
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
        $commands .= '&nbsp;<a href="'.$editurl.'"><img src="'.$OUTPUT->pix_url('t/edit').'"/></a>';

        if (empty($subs)) {
            $params = array('id' => $this->theshop->id, 'view' => 'viewAllCategories', 'order' => $order, 'dir' => $dir, 'what' => 'delete', 'categoryids[]' => $category->id);
            $deleteurl = new moodle_url('/local/shop/products/category/view.php', $params);
            $commands .= '&nbsp;<a href="'.$deleteurl.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
        }

        if ($category->sortorder > 1) {
              $commands .= "&nbsp;<a href=\"{$url}&amp;categoryid={$category->id}&amp;what=up\"><img src=\"".$OUTPUT->pix_url('/t/down').'" /></a>';
          }
        if ($category->sortorder < $maxorder) {
              $commands .= "&nbsp;<a href=\"{$url}&amp;categoryid={$category->id}&amp;what=down\"><img src=\"".$OUTPUT->pix_url('t/up').'" /></a>';
        }
        $row[] = $commands;

        $table->data[] = $row;

        if ($subs) {
            foreach($subs as $s) {
                array_push($indentarr, '&nbsp;&nbsp;&nbsp;');
                $this->category_add_row($table, $s, $order, $dir);
                array_pop($indentarr);
            }
        }
    }
}