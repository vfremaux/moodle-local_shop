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
 * Form for rendering Bill management outputs.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Tax;

/**
 *
 */
class shop_bills_renderer {

    protected $theshop;
    protected $thecatalog;
    protected $theblock;

    /**
     *
     */
    public function load_context(&$theshop, &$thecatalog, &$theblock = null) {
        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;
    }

    /**
     *
     *
     */
    public function printable_bill_link($billid, $transid) {
        global $DB, $OUTPUT;

        $str = '';

        $actionurl = new moodle_url('/local/shop/front/order.popup.php');
        $str .= '<form name="bill" action="'.$actionurl.'" target="_blank" />';
        $str .= '<input type="hidden" name="transid" value="'.$transid.'" />';
        $str .= '<input type="hidden" name="billid" value="'.$billid.'">';
        $str .= '<table><tr valign="top"><td align="center">';
        $str .= '<br /><br /><br /><br />';
        $billurl = new moodle_url('/local/shop/front/order.popup.php', array('billid' => $billid, 'transid' => $transid));
        $customerid = $DB->get_field('local_shop_bill', 'customerid', array('id' => $billid));
        if ($userid = $DB->get_field('local_shop_customer', 'hasaccount', array('id' => $customerid))) {
            $billuser = $DB->get_record('user', array('id' => $userid));
            $ticket = ticket_generate($billuser, 'immediate access', $billurl);
            $options = array('ticket' => $ticket);
            $str .= $OUTPUT->single_button('/login/index.php' , get_string('printbill', 'local_shop'), 'post',  $options);
        }
        $backtoshopstr = get_string('backtoshop', 'local_shop');
        $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'shopid' => $this->theshop->id));
        $str .= '<input type="button"
                        name="cancel_btn"
                        value="'.$backtoshopstr.'"
                        onclick="self.location.href=\''.$shopurl.'\'" />';
        $str .= '</td></tr></table>';
        $str .= '</form>';

        return $str;
    }

    public function no_items() {
        $str = '';

        $str .= '<tr class="billrow" height="100">';
        $str .= '<td valign="top" style="padding : 2px" class="billItemMessage" colspan="10">';
        $str .= get_string('emptybill', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     *
     *
     */
    public function customer_info($bill) {
        global $DB;

        if (!empty($bill->invoiceinfo)) {
            $ci = unserialize($bill->invoiceinfo);
            $useinvoiceinfo = true;
        } else {
            $ci = $DB->get_record('local_shop_customer', array('id' => $bill->customerid));
            $useinvoiceinfo = false;
        }

        $str = '';

        $str .= '<div id="shop-customerinfo">';
        $str .= '<table cellspacing="4" width="100%">';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('orderID', 'local_shop').'</b>'. $bill->transactionid;
        $str .= '</td><td width="40%" valign="top" align="right">';
        $str .= '<b>'.get_string('on', 'local_shop').':</b> '.userdate($bill->emissiondate);
        $str .= '</td></tr>';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('organisation', 'local_shop').':</b> '.$ci->organisation;
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';

        if ($useinvoiceinfo) {
            $str .= '<tr><td width="60%" valign="top">';
            $str .= '<b>'.get_string('department').':</b> '.$ci->department;
            $str .= '</td><td width="40%" valign="top">';
            $str .= '</td></tr>';
        }

        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('customer', 'local_shop').' :</b> '.$ci->lastname.' '.$ci->firstname;
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';

        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('address').' : </b> '.$ci->address;
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';

        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('city').' : </b> '.$ci->zip.' '.$ci->city;
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';

        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('country').' :</b> '.strtoupper($ci->country);
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';

        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('email').' :</b> '.$ci->email;
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';

        if ($useinvoiceinfo) {
            $str .= '<tr><td width="60%" valign="top">';
            $str .= '<b>'.get_string('vatcode', 'local_shop').' :</b> '.$ci->vatcode;
            $str .= '</td><td width="40%" valign="top">';
            $str .= '</td></tr>';
        }

        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    /**
     *
     *
     */
    public function local_confirmation_form($requireddata) {

        $confirmstr = get_string('confirm', 'local_shop');
        $disabled = (!empty($requireddata)) ? 'disabled="disabled"' : '';
        $str = '<center>';
        $actionurl = new moodle_url('/local/shop/front/view.php');
        $str .= '<form name="confirmation" method="POST" action="'.$actionurl.'" style="display : inline">';
        $str .= '<table style="display : block ; visibility : visible" width="100%">';
        $str .= '<tr>';
        $str .= '<td align="center">';

        if (!empty($disabled)) {
            $advice = get_string('requiredataadvice', 'local_shop');
            $str .= '<br/><span id="disabled-advice-span" class="error">'.$advice.'</span><br/>';
        }

        $str .= '<input type="button" name="go_confirm" value="'.$confirmstr.'" onclick="send_confirm();" '.$disabled.' />';
        $str .= '</td></tr></table>';
        $str .= '</form>';
        $str .= '</center>';

        return $str;
    }

    /**
     * prints tabs for js activation of the category panel
     *
     */
    public function category_tabs($categories) {

        $str = '';

        $str .= '<div class="tabtree">';
        $str .= '<ul class="tabrow0">';
        foreach ($categories as $cat) {
            $catidsarr[] = $cat->id;
        }
        $catids = implode(',', $catidsarr);

        $c = 0;
        foreach ($categories as $cat) {
            $catclass = ($c) ? 'onerow' : 'onerow here first';
            $emptyrow = (!$c) ? '<div class="tabrow1 empty"> </div>' : '';
            if ($c == (count($categories) - 1)) {
                $catclass .= ' last';
            }

            $str .= '<li id="catli'.$cat->id.'" class="'.$catclass.'">';
            $catname = '<span>'.format_string($cat->name).'</span>';
            $str .= '<a href="javascript:showcategory('.$cat->id.', \''.$catids.'\');">'.$catname.'</a>'.$emptyrow.'</li>';
            $c++;
        }
        $str .= '</ul>';
        $str .= '</div>';

        return $str;
    }

    /**
     * prints a full catalog on screen
     * @param array $catgories the full product line extractred from Catalog
     */
    public function catalog($categories, $context) {
        global $OUTPUT, $SESSION;

        $str = '';

        $catidsarr = array();
        foreach ($categories as $cat) {
            $catidsarr[] = $cat->id;
        }
        $catids = implode(',', $catidsarr);

        if ($this->theshop->printtabbedcategories) {
            $str .= $this->category_tabs($categories, true);
        }

        // Print catalog product line.

        $c = 0;
        foreach ($categories as $cat) {
            if (!isset($firstcatid)) {
                $firstcatid = $cat->id;
            }

            if ($this->theshop->printtabbedcategories) {
                $str .= '<div class="shopcategory" id="category'.$cat->id.'" />';
            } else {
                $cat->level = 1;
                $str .= $OUTPUT->heading($cat->name, $cat->level);
            }

            if (!empty($cat->products)) {
                foreach ($cat->products as $product) {
                    $product->currency = $this->theshop->get_currency('symbol');
                    $product->salesunit = $product->get_sales_unit_url();
                    $product->preset = 0 + @$SESSION->shoppingcart->order[$product->shortname];
                    switch ($product->isset) {
                        case PRODUCT_SET:
                            $str .= $this->product_set($product, $context, true);
                            break;
                        case PRODUCT_BUNDLE:
                            $str .= $this->product_bundle($product, $context, true);
                            break;
                        default:
                            $str .= $this->product_block($product, $context, true);
                    }
                }
            } else {
                $str .= get_string('noproductincategory', 'local_shop');
            }

            $c++;

            if ($this->theshop->printtabbedcategories) {
                $str .= '</div>';
            }
        }
        $str .= "<script type=\"text/javascript\">showcategory(".@$firstcatid.", '{$catids}');</script>";

        return $str;
    }

    public function product_block(&$product) {
        global $CFG;

        $str = '';

        $str .= '<table class="shop-article" width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<td width="180" rowspan="2" class="shop-productpix" valign="middle" align="center">';
        $str .= '<img src="'.$product->get_image_url().'" border="0"><br/>';
        $str .= '</td>';
        $str .= '<td width="*" class="shop-producttitle">';
        $str .= $product->name;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td class="shop-productcontent">';
        $str .= $product->description;
        $str .= '<p><strong>'.get_string('ref', 'local_shop').' : '.$product->code.' - </strong>';
        $prices = $product->get_printable_prices(true);
        if (count($prices) <= 1) {
             $str .= get_string('puttc', 'local_shop');
             $str .= ' = <b>';
            $price = array_shift($prices);
            $str .= $price;
             $str .= $product->currency;
             $str .= '</b><br />';
        } else {
            $str .= '<table class="shop-pricelist"><tr>';
            $str .= '<td class="shop-princelabel">'.get_string('puttc', 'local_shop').'</td>';
            foreach ($prices as $range => $price) {
                 $str .= '<td class="shop-pricerange"> '.$range.' : </td>';
                 $str .= '<td class="shop-priceamount">'.$price.' {'.$product->currency.'}</td>';
            }
            $str .= '</tr></table>';
        }

        $buystr = get_string('buy', 'local_shop');
        $ismax = $product->maxdeliveryquant && $product->maxdeliveryquant == $product->preset;
        $disabled = ($ismax) ? 'disabled="disabled"' : '';
        $blockid = 0 + @$this->theblock->instance->id;
        $jshandler = 'ajax_add_unit(\''.$CFG->wwwroot.'\', '.$blockid.', \''.$product->shortname.'\')';
        $str .= '<input type="button"
                        name="go_add"
                        value="'.$buystr.'"
                        onclick="'.$jshandler.'" '.$disabled.' />';
        $str .= '<span id="bag_'.$product->shortname.'">';
        $str .= $this->units($product, true);
        $str .= '</span>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    public function product_set(&$set) {

        $str = '';

        $str .= '<table class="shop-article" width="100%">';
        $str .= '<tr>';
        $str .= '<td class="shop-productpix" rowspan="'.count($set->set).'" width="180">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td align="left" class="shop-producttitle">';
        $str .= '<p><b>'.$set->name.'</b>';
        $str .= $set->description;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>';
        $str .= '<table width="100%">';
        foreach ($set->set as $element) {
            $element->TTCprice = $element->get_taxed_price();
            $str .= '<tr valign="top">';
            $str .= '<td>';
            $str .= '<img src="'.$element->thumb.'" vspace="10"><br/>';
            if ($element->image != '') {
                $jshandler = 'javascript:open_image(\''.$element->image.'\', \''.$CFG->wwwroot.'\')';
                $str .= '<a href="'.$jshandler.'">'.get_string('enlarge', 'local_shop').'</a>';
            }
            $str .= '</td>';
            $str .= '<td>';
            if ($element->showsnameinset) {
                $str .= '<div class="producttitle">'.$element->name.'</div>';
            }
            $str .= '<p><strong>'.get_string('ref', 'local_shop').' : '.$element->code.' - </strong>';
            $str .= get_string('puttc', 'local_shop').' = <b>'.$element->TTCprice.' '. $set->currency.' </b><br/>';
            if ($element->showsdescriptioninset) {
                $str .= $element->description;
            }
            $jshandler = 'addOneUnit(\''.$CFG->wwwroot;
            $jshandler .= '\', \''.$set->salesunit.'\', '.$set->TTCprice.', \''.$set->maxdeliveryquant.'\')';
            $str .= '<input type="button"
                            name="go_add"
                            value="'.get_string('buy', 'local_shop').'"
                            onclick="'.$jshandler.'">';
            $str .= '<span id="bag_'.$element->shortname.'"></span>';
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '</table>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    public function product_bundle(&$bundle) {
        global $CFG;

        $str = '';

        $str .= '<table class="shop-article" width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<td width="200" rowspan="4">';
        $str .= '<img src="'.$bundle->thumb.'" vspace="10" border="0"><br>';
        if ($bundle->image != '') {
            $imageurl = new moodle_url('/local/shop/photo.php', array('img' => $bundle->image));
            $str .= '<a href="javascript:openPopup(\''.$imageurl.'\')">'.get_string('viewlarger', 'local_shop').'</a>';
        }
        $str .= '</td>';
        $str .= '<td width="*" class="shop-producttitle">';
        $str .= $bundle->name;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td class="shop-productcontent">';
        $str .= format_string($bundle->description);
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>';

        $ttcprice = 0;
        foreach ($bundle->set as $product) {
            $ttcprice += $product->get_taxed_price(1);
            $product->noorder = true; // Bundle can only be purchased as a group.
            $str .= $this->product_block($product);
        }
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td>';
        $str .= '<strong>'.get_string('ref', 'local_shop').' : '.$bundle->code.' - </strong>';
        $str .= get_string('puttc', 'local_shop').' = <b>'.$ttcprice.' '. $bundle->currency.' </b><br/>';
        $jshandler = 'addOneUnit(\''.$CFG->wwwroot.'\', \''.$bundle->shortname.'\', \''.$bundle->code;
        $jshandler .= '\', '.$ttcprice.', \''.$bundle->maxdeliveryquant.'\')';
        $str .= '<input type="button"
                        name="go_add"
                        value="'.get_string('buy', 'local_shop').'"
                        onclick="'.$jshandler.'">';
        $str .= '<span id="bag_'.$bundle->shortname.'"></span>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    public function units(&$product) {
        global $SESSION, $CFG, $OUTPUT;

        $unitimage = $product->get_sales_unit_url();

        $str = '';

        for ($i = 0; $i < 0 + @$SESSION->shoppingcart->order[$product->shortname]; $i++) {
            $str .= '&nbsp;<img src="'.$unitimage.'" align="middle" />';
        }
        if ($i > 0) {
            $jshandler = 'Javascript:ajax_delete_unit(\''.$CFG->wwwroot;
            $jshandler .= '\', \''.$this->theshop->id.'\', \''.$product->shortname.'\')';
            $str .= '&nbsp;<a title="'.get_string('deleteone', 'local_shop').'" href="'.$jshandler.'">';
            $str .= '<img src="'.$OUTPUT->pix_url('t/delete').'" valign="center" />';
            $str .= '</a>';
        }

        return $str;
    }

    public function order_detail(&$categories) {
        global $SESSION, $CFG;

        $shoppingcart = $SESSION->shoppingcart;

        $str = '';

        $str .= '<table width="100%" id="orderblock">';
        foreach ($categories as $cat) {
            foreach ($cat->products as $aproduct) {
                if ($aproduct->isset === 1) {
                    foreach ($aproduct->set as $portlet) {
                        $portlet->currency = $this->theshop->get_currency('symbol');
                        $shortname = @$shoppingcart->order[$portlet->shortname];
                        $portlet->preset = !empty($shortname) ? $shortname : 0;
                        if ($portlet->preset) {
                            ob_start();
                            include($CFG->dirroot.'/local/shop/lib/shopProductTotalLine.portlet.php');
                            $str .= ob_get_clean();
                        }
                    }
                } else {
                    $portlet = &$aproduct;
                    $portlet->currency = shop_currency($this->theblock, 'symbol');
                    $shortname = $shoppingcart->order[$portlet->shortname];
                    $portlet->preset = !empty($shortname) ? $shortname : 0;
                    if ($portlet->preset) {
                           ob_start();
                        include($CFG->dirroot.'/local/shop/lib/shopProductTotalLine.portlet.php');
                        $str .= ob_get_clean();
                    }
                }
            }
        }
        $str .= "</table>";

        return $str;
    }

    public function billitem_line($billitem) {
        global $OUTPUT;

        $str = '';

        if (empty($billitem)) {
            $str .= '<tr valign="top">';
            $str .= '<!--<th class="header c0">';
            $str .= '&nbsp;';
            $str .= '</th>-->';
            $str .= '<th style="text-align:left" class="header c0">';
            $str .= get_string('order', 'local_shop');
            $str .= '</th>';
            $str .= '<th style="text-align:left" class="header c1">';
            $str .= get_string('code', 'local_shop');
            $str .= '</th>';
            $str .= '<th style="text-align:left" class="header c2">';
            $str .= get_string('product', 'local_shop');
            $str .= '</th>';
            $str .= '<th style="text-align:left" class="header c3">';
            $str .= get_string('deadline', 'local_shop');
            $str .= '</th>';
            $str .= '<th style="text-align:left" class="header c4">';
            $str .= get_string('unittex', 'local_shop');
            $str .= '</th>';
            $str .= '<th style="text-align:left" class="header c5">';
            $str .= get_string('quant', 'local_shop');
            $str .= '</th>';
            $str .= '<th style="text-align:left" class="header c6">';
            $str .= get_string('totaltex', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c7">';
            $str .= get_string('taxcode', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header lastcol">';
            $str .= get_string('orders', 'local_shop');
            $str .= '</th>';
            $str .= '</tr>';

            return $str;
        }

        $str .= '<tr valign="top">';
        $str .= '<td width="30" class="cell c0">';
        $params = array('shopid' => $this->theshop->id, 'view' => 'editBillItem', 'item' => $billitem->id);
        $billurl = new moodle_url('/local/coursehop/bills/view.php', $params);
        $str .= '<a class="activeLink" href="'.$billurl.'">'.$billitem->ordering.'. </a>';
        $str .= '</td>';
        $str .= '<td width="60" class="cell c1">';
        $str .= $billitem->itemcode;
        $str .= '</td>';
        $str .= '<td width="*" class="cell c2">';
        $str .= $billitem->description;
        $str .= '</td>';
        $str .= '<td width="40" class="cell c3">';
        $str .= $billitem->delay;
        $str .= '</td>';
        $str .= '<td width="80" class="cell c4">';
        $str .= sprintf("%.2f", round($billitem->unitcost, 2));
        $str .= '</td>';
        $str .= '<td width="30" class="cell c5">';
        $str .= $billitem->quantity;
        $str .= '</td>';
        $str .= '<td width="80" class="cell c6">';
        $str .= '<span id="price_'.$billitem->ordering.'">'.sprintf("%.2f", round($billitem->totalprice, 2)).'</span>';
        $str .= '</td>';
        $str .= '<td width="30" class="cell c7">';
        $str .= $billitem->taxcode;
        $str .= '</td>';
        $str .= '<td width="60" class="cell lastcol">';

        if (empty($billitem->bill->idnumber)) {
            /*
             * We can change sometihng in billitems if the bill has not been freezed with an idnumber.
             * that denotes it has been transfered to offical accountance.
             */
            $params = array('id' => $this->theshop->id,
                            'view' => 'viewBill',
                            'what' => 'relocating',
                            'relocated' => $billitem->id,
                            'z' => $billitem->ordering);
            $linkurl = new moodle_url('/local/shop/bills/view.php', $params);
            $pixurl = $OUTPUT->pix_url('t/move');
            $str .= '<a href="'.$linkurl.'"><img src="'.$pixurl.'" border="0" alt="'.get_string('move').'"></a>';
            $params = array('id' => $this->theshop->id, 'billid' => $billitem->bill->id, 'billitemid' => $billitem->id);
            $linkurl = new moodle_url('/local/shop/bills/edit_billitem.php', $params);
            $pixurl = $OUTPUT->pix_url('i/edit');
            $str .= '&nbsp;<a href="'.$linkurl.'"><img src="'.$pixurl.'" border="0" alt="'.get_string('edit').'"></a>';
            $params = array('id' => $this->theshop->id,
                            'view' => 'viewBill',
                            'what' => 'deleteItem',
                            'billitemid' => $billitem->id,
                            'z' => $billitem->ordering,
                            'billid' => $billitem->bill->id);
            $linkurl = new moodle_url('/local/shop/bills/view.php', $params);
            $pixurl = $OUTPUT->pix_url('t/delete');
            $str .= '&nbsp;<a href="'.$linkurl.'"><img src="'.$pixurl.'" alt="'.get_string('delete').'"></a>';
        }
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td>&nbsp;';
        $str .= '</td>';
        $str .= '<td valign="top" class="itemDescription" colspan="9">';
        $str .= format_text($billitem->description);
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * @param object $ci a CatalogItem instance;
     */
    public function item_total_line($ci) {
        global $CFG, $OUTPUT;

        $str = '';
        $ttcprice = $ci->get_taxed_price($ci->preset, $ci->taxcode);
        $ci->total = $ttcprice * $ci->preset;

        $str .= '<tr id="producttotalcaption_'.$ci->shortname.'">';
        $str .= '<td class="shop-ordercaptioncell" colspan="3">';
        $str .= $ci->name;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr id="producttotal_'.$ci->shortname.'">';
        $str .= '<td class="shop-ordercell">';
        $jshandler = 'Javascript:ajax_clear_product(\''.$CFG->wwwroot.'\', \''.$this->theshop->id.'\', \''.$ci->shortname.'\')';
        $str .= '<a title="'.get_string('clearall', 'local_shop').'" href="'.$jshandler.'">';
        $str .= '<img src="'.$OUTPUT->pix_url('t/delete').'" />';
        $str .= '</a>';
        $jshandler = 'ajax_update_product(\''.$ci->shortname.'\', this)';
        $str .= '<input type="text"
                        class="order-detail"
                        id="id_'.$ci->shortname.'"
                        name="'.$ci->shortname.'" value="'.$ci->preset.'" size="3" onChange="'.$jshandler.'">';
        $str .= '</td>';
        $str .= '<td class="shop-ordercell">';
        $str .= '<p>x '.sprintf("%.2f", round($ttcprice, 2)).' '.$ci->currency.' :';
        $str .= '</td>';
        $str .= '<td class="shop-ordercell">';
        $str .= '<input type="text"
                        class="order-detail"
                        id="id_total_'.$ci->shortname.'"
                        name="'.$ci->shortname.'_total"
                        value="'.$ci->total.'"
                        size="10"
                        disabled
                        class="totals">';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     *
     *
     */
    public function customer_info_form() {
        global $USER, $OUTPUT, $DB, $SESSION;

        $shoppingcart = $SESSION->shoppingcart;

        $str = '';

        $checked = (!empty($shoppingcart->usedistinctinvoiceinfo)) ? 'checked="checked"' : '';

        $heading = get_string('customerinformation', 'local_shop');
        $heading .= ' <input type="checkbox"
                         value="1"
                         name="usedistinctinvoiceinfo"
                         onchange="local_toggle_invoiceinfo(this)"
                         '.$checked.' />';
        $heading .= '<span class="tiny-text"> '.get_string('usedistinctinvoiceinfo', 'local_shop').'</span>';

        $str .= $OUTPUT->heading($heading);

        if (isloggedin()) {
            $lastname = $USER->lastname;
            $firstname = $USER->firstname;
            $hasorga = !empty($shoppingcart->customerinfo['organisation']);
            $organisation = ($hasorga) ? $shoppingcart->customerinfo['organisation'] : $USER->institution;
            $city = (!empty($shoppingcart->customerinfo['city'])) ? $shoppingcart->customerinfo['city'] : $USER->city;
            $address = (!empty($shoppingcart->customerinfo['address'])) ? $shoppingcart->customerinfo['address'] : $USER->address;
            $zip = (!empty($shoppingcart->customerinfo['zip'])) ? $shoppingcart->customerinfo['zip'] : '';
            $hascountry = !empty($shoppingcart->customerinfo['country']);
            $country = ($hascountry) ? $shoppingcart->customerinfo['country'] : strtolower($USER->country);
            $email = $USER->email;

            // Get potential ZIP code information from an eventual customer record.
            if ($customer = $DB->get_record('local_shop_customer', array('hasaccount' => $USER->id))) {
                $zip = (!empty($shoppingcart->customerinfo['zip'])) ? $shoppingcart->customerinfo['zip'] : $customer->zip;
                $organisation = ($hasorga) ? $shoppingcart->customerinfo['organisation'] : $customer->organisation;
                $hasaddress = !empty($shoppingcart->customerinfo['address']);
                $address = ($hasaddress) ? $shoppingcart->customerinfo['address'] : $customer->address;
            }
        } else {
            $lastname = @$shoppingcart->customerinfo['lastname'];
            $firstname = @$shoppingcart->customerinfo['firstname'];
            $organisation = @$shoppingcart->customerinfo['organisation'];
            $country = @$shoppingcart->customerinfo['country'];
            $address = @$shoppingcart->customerinfo['address'];
            $city = @$shoppingcart->customerinfo['city'];
            $zip = @$shoppingcart->customerinfo['zip'];
            $email = @$shoppingcart->customerinfo['email'];
        }

        $lastnameclass = '';
        $firstnameclass = '';
        $organisationclass = '';
        $addressclass = '';
        $cityclass = '';
        $zipclass = '';
        $mailclass = '';

        if (!empty($shoppingcart->errors->customerinfo)) {
            foreach (array_keys($shoppingcart->errors->customerinfo) as $f) {
                $f = str_replace('customerinfo::', '', $f);
                $var = "{$f}class";
                $$var = 'shop-error';
            }
        }
        $str .= '<div id="shop-customerdata">';
        $str .= '<table cellspacing="3" width="100%" >';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('lastname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$lastnameclass.'"
                        name="customerinfo::lastname"
                        size="20"
                        onchange="setupper(this)"
                        value="'. $lastname.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('firstname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$firstnameclass.'"
                        name="customerinfo::firstname"
                        size="20"
                        onchange="capitalizewords(this)"
                        value="'.$firstname.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        if (!empty($this->theshop->customerorganisationrequired)) {
            $str .= '<tr valign="top">';
            $str .= '<td align="right">';
            $str .= get_string('organisation', 'local_shop');
            $str .= ':</td>';
            $str .= '<td align="left">';
            $str .= '<input type="text"
                            class="'.$organisationclass.'"
                            name="customerinfo::organisation"
                            size="26"
                            maxlength="64"
                            value="'.$organisation.'" />';
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('address');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$addressclass.'"
                        name="customerinfo::address"
                        size="26"
                        onchange="setupper(this)"
                        value="'. $address .'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('city');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$cityclass.'"
                        name="customerinfo::city"
                        size="26"
                        onchange="setupper(this)"
                        value="'. $city .'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('zip', 'local_shop');
        $str .= '<sup style="color : red">*</sup>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$zipclass.'"
                        name="customerinfo::zip"
                        size="6"
                        value="'. $zip .'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('country');
        $str .= '<sup style="color : red">*</sup>: <br>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $choices = get_string_manager()->get_list_of_countries();
        $this->theshop->process_country_restrictions($choices);
        $str .= html_writer::select($choices, 'customerinfo::country', $country, array('' => 'choosedots'));
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('email', 'local_shop');
        $str .= '<sup style="color : red">*</sup>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$mailclass.'"
                        name="customerinfo::email"
                        size="30"
                        onchange="testmail(this)" value="'.$email.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    /**
     *
     *
     */
    public function invoicing_info_form() {
        global $OUTPUT, $SESSION;

        $shoppingcart = $SESSION->shoppingcart;

        $str = '';

        $institution = @$shoppingcart->invoiceinfo['organisation'];
        $department = @$shoppingcart->invoiceinfo['department'];
        $lastname = @$shoppingcart->invoiceinfo['lastname'];
        $firstname = @$shoppingcart->invoiceinfo['firstname'];
        $address = @$shoppingcart->invoiceinfo['address'];
        $zip = @$shoppingcart->invoiceinfo['zip'];
        $city = @$shoppingcart->invoiceinfo['city'];
        $country = @$shoppingcart->invoiceinfo['country'];
        $vatcode = @$shoppingcart->invoiceinfo['vatcode'];
        $plantcode = @$shoppingcart->invoiceinfo['plantcode'];

        $lastnameclass = '';
        $firstnameclass = '';
        $organisationclass = '';
        $departmentclass = '';
        $countryclass = '';
        $addressclass = '';
        $cityclass = '';
        $zipclass = '';
        $vatcodeclass = '';
        $plantcodeclass = '';

        if (!empty($shoppingcart->errors->invoiceinfo)) {
            foreach (array_keys($shoppingcart->errors->invoiceinfo) as $f) {
                $f = str_replace('invoiceinfo::', '', $f);
                $var = "{$f}class";
                $$var = 'shop-error';
            }
        }

        $str .= $OUTPUT->heading(get_string('invoiceinformation', 'local_shop'));

        $str .= '<div id="shop-organisationdata">';
        $str .= '<table cellspacing="3" width="100%">';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('organisation', 'local_shop');
        $str .= ':</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                         class="'.$organisationclass.'"
                         name="invoiceinfo::organisation"
                         size="26"
                         maxlength="64"
                         value="'.$institution.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('department');
        $str .= ':</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$departmentclass.'"
                        name="invoiceinfo::department"
                        size="26"
                        maxlength="64"
                        value="'.$department.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('lastname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$lastnameclass.'"
                        name="invoiceinfo::lastname"
                        size="20"
                        onchange="setupper(this)" value="'. $lastname.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('firstname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$firstnameclass.'"
                        name="invoiceinfo::firstname"
                        size="20"
                        onchange="capitalizewords(this)" value="'.$firstname.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('address');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$addressclass.'"
                        name="invoiceinfo::address"
                        size="26"
                        onchange="setupper(this)" value="'. $address .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('city');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$cityclass.'"
                        name="invoiceinfo::city"
                        size="26"
                        onchange="setupper(this)" value="'. $city .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('zip', 'local_shop');
        $str .= '<sup style="color : red">*</sup>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$zipclass.'"
                        name="invoiceinfo::zip"
                        size="6"
                        value="'. $zip .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('country');
        $str .= '<sup style="color : red">*</sup>: <br>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $choices = get_string_manager()->get_list_of_countries();
        $this->thecatalog->process_country_restrictions($choices);
        $params = array('' => 'choosedots');
        $attrs = array('class' => $countryclass);
        $str .= html_writer::select($choices, 'invoiceinfo::country', $country, $params, $attrs);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('vatcode', 'local_shop');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$vatcodeclass.'"
                        name="invoiceinfo::vatcode"
                        size="15"
                        value="'. $vatcode .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('plantcode', 'local_shop');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        class="'.$plantcodeclass.'"
                        name="invoiceinfo::plantcode"
                        size="20"
                        value="'. $plantcode .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    public function participant_row($participant = null) {
        global $CFG, $OUTPUT;

        $str = '';

        if ($participant) {

            $str .= '<tr>';
            $str .= '<td align="left">';
            $str .= @$participant->lastname;
            $str .= '</td>';
            $str .= '<td align="left">';
            $str .= @$participant->firstname;
            $str .= '</td>';
            $str .= '<td align="left">';
            $str .= @$participant->email;
            $str .= '</td>';
            $str .= '<td align="left">';
            $str .= strtoupper(@$participant->city);
            $str .= '</td>';
            if (!empty($this->theshop->endusermobilephonerequired)) {
                $str .= '<td align="left">';
                $str .= strtoupper(@$participant->phone2);
                $str .= '</td>';
            }
            if (!empty($this->theshop->enduserorganisationrequired)) {
                $str .= '<td align="left">';
                $str .= strtoupper(@$participant->institution);
                $str .= '</td>';
            }
            $str .= '<td align="left">';
            if (@$participant->moodleid) {
                $pixurl = $OUTPUT->pix_url('i/moodle_host');
                $str .= '<img src="'.$pixurl.'" title="'.get_string('isuser', 'local_shop').'" />';
            } else {
                $pixurl = $OUTPUT->pix_url('new', 'local_shop');
                $str .= '<img src="'.$pixurl.'" title="'.get_string('isnotuser', 'local_shop').'" />';
            }
            $str .= '</td>';
            $str .= '<td align="right">';
            $str .= '<a title="'.get_string('deleteparticipant', 'local_shop').'"
                        href="Javascript:ajax_delete_user(\''.$CFG->wwwroot.'\', \''.$participant->email.'\')">';
            $str .= '<img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
            $str .= '</td>';
            $str .= '</tr>';
        } else {
            // Print a caption row.
            $str .= '<tr>';
            $str .= '<th align="left">';
            $str .= get_string('lastname');
            $str .= '</th>';
            $str .= '<th align="left">';
            $str .= get_string('firstname');
            $str .= '</th>';
            $str .= '<th align="left">';
            $str .= get_string('email');
            $str .= '</th>';
            $str .= '<th align="left">';
            $str .= get_string('city');
            $str .= '</th>';
            if (!empty($this->theshop->endusermobilephonerequired)) {
                $str .= '<th align="left">';
                $str .= get_string('phone2');
                $str .= '</th>';
            }
            if (!empty($this->theshop->enduserorganisationrequired)) {
                $str .= '<th align="left">';
                $str .= get_string('institution');
                $str .= '</th>';
            }
            $str .= '<th align="left">';
            $str .= get_string('moodleaccount', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="right">';
            $str .= '</th>';
            $str .= '</tr>';
        }

        return $str;
    }

    public function participant_blankrow() {
        static $i = 0;

        $str = '';

        $str .= '<tr>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="lastname_foo_'.$i.'" size="15" disabled="disabled" class="shop-disabled" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="firstname_foo_'.$i.'" size="15" disabled="disabled" class="shop-disabled" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="mail_foo_'.$i.'" size="20" disabled="disabled" class="shop-disabled" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="city_foo_'.$i.'" size="15" disabled="disabled" class="shop-disabled" />';
        $str .= '</td>';
        if (!empty($this->theshop->endusermobilephonerequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="phone2_foo_'.$i.'" size="20" disabled="disabled" class="shop-disabled" />';
            $str .= '</td>';
        }
        if (!empty($this->theshop->enduserorganisationrequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="institution_foo_'.$i.'" size="20" disabled="disabled" class="shop-disabled" />';
            $str .= '</td>';
        }
        $str .= '<td align="left">';
        $str .= '</td>';
        $str .= '<td align="right">';
        $str .= '</td>';
        $str .= '</tr>';

        $i++;

        return $str;
    }

    public function new_participant_row() {
        global $CFG;

        $str = '';

        $str .= '<form name="participant">';
        $str .= '<table width="100%">';
        $str .= '<tr>';
        $str .= '<td align="left">';
        $str .= get_string('lastname');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= get_string('firstname');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= get_string('email');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= get_string('city');
        $str .= '</td>';
        if (!empty($this->theshop->endusermobilephonerequired)) {
            $str .= '<td align="left">';
            $str .= get_string('phone2');
            $str .= '</td>';
        }
        if (!empty($this->theshop->enduserorganisationrequired)) {
            $str .= '<td align="left">';
            $str .= get_string('institution');
            $str .= '</td>';
        }
        $str .= '<td align="right">';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="lastname" size="20" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="firstname" size="20" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="email" size="20" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="city" size="15" />';
        $str .= '</td>';
        if (!empty($this->theshop->endusermobilephonerequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="phone2" size="20" size="10" maxlength="10" />';
            $str .= '</td>';
        }
        if (!empty($this->theshop->enduserorganisationrequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="institution" size="20" size="15" maxlength="40" />';
            $str .= '</td>';
        }
        $str .= '<td align="right">';
        $str .= '<input type="button"
                        value="'.get_string('addparticipant', 'local_shop').'"
                        name="add_button"
                        onclick="ajax_add_user(\''.$CFG->wwwroot.'\', document.forms[\'participant\'])" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</form>';

        return $str;
    }

    public function assignation_row($participant, $role, $shortname) {
        global $CFG, $OUTPUT;

        $str = '';

        $str .= '<tr>';
        $str .= '<td align="left">';
        $str .= @$participant->lastname;
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= @$participant->firstname;
        $str .= '</td>';
        $str .= '<td align="right">';
        $jshandler = 'Javascript:ajax_delete_assign(\''.$CFG->wwwroot;
        $jshandler .= '\', \''.$role.'\', \''.$shortname.'\', \''.$participant->email.'\')';
        $str .= '<a href="'.$jshandler.'"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * prints a user selector for a product/role list from declared
     * participants removing already assigned people.
     */
    public function assignation_select($role, $shortname) {
        global $SESSION, $CFG;

        $str = '';

        if (empty($SESSION->shoppingcart)) {
            return;
        }

        $shoppingcart = $SESSION->shoppingcart;

        if (!empty($shoppingcart->users[$shortname][$role])) {
            $rkeys = array_keys($shoppingcart->users[$shortname][$role]);
        } else {
            $rkeys = array();
        }

        $options = array();
        if (!empty($shoppingcart->participants)) {
            foreach ($shoppingcart->participants as $email => $pt) {
                if (!in_array($email, $rkeys)) {
                    $options[$email] = fullname($pt);
                }
            }
        }
        $jshandler = 'ajax_add_assign(\''.$CFG->wwwroot.'\', \''.$role.'\', \''.$shortname.'\', this)';
        $options = array('' => get_string('chooseparticipant', 'local_shop'));
        $attrs = array('onchange' => $jshandler);
        $str .= html_writer::select($options, 'addassign'.$role.'_'.$shortname, '', $options, $attrs);

        return $str;
    }

    public function role_list($role, $shortname) {
        global $OUTPUT, $SESSION;

        $str = '';

        $roleassigns = @$SESSION->shoppingcart->users;

        $str .= $OUTPUT->heading(get_string(str_replace('_', '', $role), 'local_shop'));  // Remove pseudo roles markers.
        if (!empty($roleassigns[$shortname][$role])) {
            $str .= '<table width="100%" class="shop-role-list">';
            foreach ($roleassigns[$shortname][$role] as $participant) {
                $str .= $this->assignation_row($participant, $role, $shortname, true);
            }
            $str .= '</table>';
        } else {
            $str .= '<div class="shop-role-list">';
            $str .= get_string('noassignation', 'local_shop');
            $str .= '</div>';
        }
        if (@$SESSION->shoppingcart->assigns[$shortname] < $SESSION->shoppingcart->order[$shortname]) {
            $str .= $this->assignation_select($role, $shortname, true);
        } else {
            $str .= get_string('seatscomplete', 'local_shop');
        }

        return $str;
    }

    public function cart_summary() {
        global $SESSION;

        $str = '';

        if (!empty($SESSION->shoppingcart->order)) {
            $str .= '<table width="100%">';
            foreach (array_keys($SESSION->shoppingcart->order) as $itemname) {
                $portlet = $this->thecatalog->get_product_by_shortname($itemname);
                $str .= $this->cart_summary_line($portlet);
            }
            $str .= '</table>';
        }

        return $str;
    }

    public function cart_summary_line($portlet) {
        global $SESSION;

        $str .= '<tr>';
        $span = '<span title="'.$portlet->name.'" alt="'.$portlet->name.'">'.$portlet->code.'</span>';
        $str .= '<td class="short-order-name">'.$span.'</td>';
        $str .= '<td class="short-order-quantity">'.$SESSION->shoppingcart->order[$portlet->code].'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td colspan="2"  class="short-order-summary">'.shorten_text(strip_tags($portlet->description), 120).'</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * @param object $bill
     */
    public function full_bill_totals($bill) {

        $config = get_config('local_shop');

        $str = '';

        $str .= '<table cellspacing="5" class="generaltable" width="100%">';

        $str .= '<tr valign="top">';
        $str .= '<th colspan="3" class="cell c0">';
        $str .= get_string('totals', 'local_shop');
        $str .= '</th>';
        $str .= '</tr>';

        if (!empty($bill->discount) || !empty($config->hasshipping)) {
            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= get_string('subtotal', 'local_shop');
            $str .= '</td>';
            $str .= '<td width="40%" class="cell c1">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="cell c2 lastcol">';
            $str .= $bill->finaltaxedtotal.'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';
        }

        if ($bill->discount != 0) {
            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="40%" class="shop-totaltitle ratio cell c1">';
            $str .= get_string('discount', 'local_shop').' :';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="shop-totals ratio cell c2">';
            $str .= '<b>-' . ($config->discountrate).'%</b>';
            $str .= '</td>';
            $str .= '</tr>';

            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="40%" class="shop-totaltitle cell c1">';
            $str .= get_string('totaldiscounted', 'local_shop').' :';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="shop-totals cell c2">';
            $str .= $bill->finaltaxedtotal.'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0">';
        $str .= get_string('untaxedsubtotal', 'local_shop');
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" class="cell c2 lastcol">';
        $str .= $bill->finaluntaxedtotal.'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0 shop-taxes">';
        $str .= get_string('taxes', 'local_shop');
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" class="cell c2 lastcol shop-taxes">';
        $str .= (0 + @$bill->finaltaxestotal).'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0 shop-totaltitle topay">';
        $str .= '<b>'.get_string('finaltotalprice', 'local_shop').'</b>:';
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" class="cell c2 shop-total topay">';
        if (empty($config->hasshipping)) {
            $str .= '<b>'.$bill->finaltaxedtotal.'&nbsp;'.$this->theshop->get_currency('symbol').'</b>&nbsp;';
        } else {
            $str .= '<b>'.($bill->finaltaxedtotal + $bill->shipping->taxedvalue).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol').'</b>&nbsp;';
        }
        $str .= '</td>';
        $str .= '</tr>';

        if (!empty($config->hasshipping)) {
            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="40%" class="cell c1 shop-totaltitle">';
            $str .= get_string('shipping', 'local_shop').' :';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="cell c2 shop-totals">';
            $str .= $bill->shipping->taxedvalue.'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';

            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0 shop-totaltitle topay">';
            $str .= '<b>'.get_string('finaltotalprice', 'local_shop').'</b>:';
            $str .= '</td>';
            $str .= '<td width="40%" class="cell c1">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="cell c2 shop-total topay">';
            $str .= '<b>'.$bill->finalshippedtaxedtotal.'&nbsp;'.$this->theshop->get_currency('symbol').'</b>&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '</table>';

        return $str;
    }

    /**
     * @param object $bill
     */
    public function full_bill_taxes($bill) {
        global $DB, $OUTPUT;

        $str = '';

        if ($taxlines = $bill->taxlines) {

            $str .= $OUTPUT->heading(get_string('taxes', 'local_shop'), 2, '', true);

            $str .= '<table cellspacing="5" class="generaltable" width="100%">';

            $str .= '<tr class="shop-tax" valign="top">';
            $str .= '<th align="left" class="cell c0">';
            $str .= get_string('taxname', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="cell c1">';
            $str .= get_string('taxratio', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="cell c2 lastcoll">';
            $str .= get_string('taxamount', 'local_shop');
            $str .= '</th>';
            $str .= '</tr>';

            foreach ($taxlines as $tcode => $tamount) {
                $tax = new Tax($tcode);
                $str .= '<tr class="shop-tax" valign="top">';
                $str .= '<td align="left" class="cell c0">';
                $str .= $tax->title;
                $str .= '</td>';
                $str .= '<td align="left" class="cell c1">';
                $str .= $tax->ratio;
                $str .= '</td>';
                $str .= '<td align="left" class="cell c2 lastcoll">';
                $str .= $tamount;
                $str .= '</td>';
                $str .= '</tr>';
            }
            $str .= '</table>';
        }

        return $str;
    }

    public function field_start($legend, $class) {

        $str = '';
        $str .= '<fieldset class="'.$class."\">\n";
        $str .= '<legend>'.$legend."</legend>\n";

        return $str;
    }

    public function field_end() {
        return '</fieldset>';
    }

    public function bill_merchant_line($portlet) {

        $str = '';

        if (is_null($portlet)) {
            $str .= '<tr>';
            $str .= '<th class="header c0">';
            $str .= '</th>';
            $str .= '<th class="header c1">';
            $str .= get_string('num', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c2">';
            $str .= get_string('label', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header c3">';
            $str .= get_string('transaction', 'local_shop');
            $str .= '</th>';
            $str .= '<th class="header lastcol">';
            $str .= get_string('amount', 'local_shop');
            $str .= '</th>';
            $str .= '</tr>';

            return $str;
        }

        $str .= '<tr valign="top">';
        $str .= '<td width="30" class="cell c0">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="120" class="cell c1">';
        $params = array('view' => 'viewBill', 'id' => $this->theshop->id, 'billid' => $portlet->id);
        $billurl = new moodle_url('/local/shop/bills/view.php', $params);
        $str .= ' <a class="activeLink" href="'.$billurl.'">B-'.date('Y-m', $portlet->emissiondate).'-'.$portlet->id.'</a>';
        $str .= '</td>';
        $str .= '<td width="*" class="cell c2">';
        $str .= $portlet->title;
        $str .= '</td>';
        $str .= '<td width="120" class="cell c3">';
        $params = array('transid' => $portlet->transactionid, 'id' => $this->theshop->id);
        $scanurl = new moodle_url('/local/shop/front/scantrace.php', $params);
        $title = get_string('scantrace', 'local_shop');
        $str .= '<code><a href="'.$scanurl.'" title="'.$title.'">'.$portlet->transactionid.'</a></code>';
        $str .= '</td>';
        $str .= '<td width="100" align="right" class="cell lastcol">';
        $str .= sprintf("%.2f", round($portlet->amount, 2)).' '.get_string($portlet->currency.'symb', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td width="30" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="100" class="cell c2">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="*" class="cell c3" colspan="3">';
        $params = array('id' => $this->theshop->id, 'view' => 'viewCustomer', 'userid' => $portlet->userid);
        $customerurl = new moodle_url('/local/shop/customers/view.php', $params);
        $str .= '<a class="activeLink" href="'.$customerurl.'">'.$portlet->firstname.' '.$portlet->lastname.'</a>';
        $str .= ' (<a href="mailto:'.$portlet->email.'">'.$portlet->email.'</a>)';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    public function flow_controller($status, $url) {
        global $DB, $OUTPUT, $CFG;

        $str = '';

        $select = "
            element = 'bill' AND
            `tostate` = ?
            GROUP BY element,`fromstate`
        ";
        $froms = $DB->get_records_select('flowcontrol', $select, array($status));
        $select = "
            element = 'bill' AND
            `fromstate` = ?
            GROUP BY element,`tostate`
        ";
        $tos = $DB->get_records_select('flowcontrol', $select, array($status));

        $str .= '<table class="flowcontrolHead" cellspacing="0" width="100%">';
        $str .= '<tr class="billListTitle">';
        $str .= '<td valign="top" style="padding : 2px" align="left">';
        $str .= '<a href="Javascript:flowcontrol_toggle(\''.$CFG->wwwroot.'\')">';
        $str .= '<img name="flowcontrol_button" src="'.$OUTPUT->pix_url('t/switch_plus').'" /></a>';
        $str .= '</td>';
        $str .= '<td valign="top" style="padding : 2px" align="left">';
        $str .= get_string('billstates', 'local_shop');
        $str .= '</td>';
        $str .= '<td valign="top" style="padding : 2px" align="right">';
        $str .= get_string('actualstate', 'local_shop');
        $str .= ': <div class="billstate">'.get_string($status, 'local_shop').'</div>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<table id="flowcontrol" width="100%" class="generaltable" cellspacing="0" style="visibility:hidden">';
        $str .= '<tr valign="middle" >';
        $str .= '<th width="50%" class="header c0" align="left">';
        $str .= get_string('backto', 'local_shop');
        $str .= '</th>';
        $str .= '<th width="50%" class="header c1" align="right">';
        $str .= get_string('goto', 'local_shop');
        $str .= '</th>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td style="padding : 5px" align="left"><ul>';
        if ($froms) {
            foreach ($froms as $from) {
                $statestr = get_string($from->fromstate, 'local_shop');
                $str .= '<li><a href="'.$url.'&cmd=flowchange&status='.$from->fromstate.'">'.$statestr.'</a></li>';
            }
        } else {
            $str .= get_string('flowControlNetStart', 'local_shop');
        }
        $str .= '</ul></td>';
        $str .= '<td style="padding : 5px" align="right"><ul>';
        if ($tos) {
            foreach ($tos as $to) {
                $statestr = get_string($to->tostate, 'local_shop');
                $str .= '<li><a href="'.$url.'&cmd=flowchange&status='.$to->tostate.'">'.$statestr.'</a></li>';
            }
        } else {
             $str .= get_string('flowControlNetEnd', 'local_shop');
        }
        $str .= '</ul></td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    public function print_currency_choice($cur, $url, $cgicontext = array()) {
        global $DB;

        $str = '';
        $usedcurrencies = $DB->get_records('local_shop_bill', null, '', ' DISTINCT(currency), currency ');
        if (count($usedcurrencies) > 1) {
            $curmenu = array();
            foreach ($usedcurrencies as $curid => $void) {
                if ($curid) {
                    $curmenu[$curid] = get_string($curid, 'local_shop');
                }
            }
            $str .= '<form name="currencyselect" action="'.$url.'" method="POST">';
            foreach ($cgicontext as $key => $value) {
                $str .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
            }
            $attrs = array('onchange' => 'document.forms[\'currencyselect\'].submit();');
            $str .= html_writer::select($curmenu, 'cur', $cur, array('' => 'CHOOSEDOTS'), $attrs);
            $str .= '</form>';
        }
        return $str;
    }

    public function relocate_box($billid, $ordering, $z, $relocated) {
        global $OUTPUT;

        $params = array('view' => 'viewBill',
                        'billid' => $billid,
                        'what' => 'relocate',
                        'relocated' => $relocated,
                        'z' => $z,
                        'at' => $ordering);
        $relocateurl = new moodle_url('/local/shop/bills/view.php', $params);

        $str = '';

        $str .= '<tr class="billRow">';
        $str .= '<td>';
        $pixurl = $OUTPUT->pix_url('relocatebox', 'local_shop');
        $str .= '<a href="'.$relocateurl.'"><img src="'.$pixurl.'" class="shop-relocate-box" ></a>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    public function attachments($bill) {
        global $OUTPUT;

        $str = '';

        $str .= $OUTPUT->heading(get_string('attachements', 'local_shop'));

        $fs = get_file_storage();

        $contextid = context_system::instance()->id;
        $attachments = $fs->get_area_files($contextid, 'local_shop', 'billattachments', $bill->id, true);
        if (empty($attachments)) {
            $str .= $OUTPUT->notification(get_string('nobillattachements', 'local_shop'));
        } else {
            $str .= '<table class="globaltable">';
            foreach ($attachments as $afile) {
                $str .= $this->attachement($afile, $bill);
            }
            $str .= '</table>';
        }

        $str .= $this->attach_link($bill);

        return $str;
    }

    public function attach_link($bill) {
        global $OUTPUT;

        $str = '';

        $params = array('type' => 'bill', 'billid' => $bill->id, 'id' => $this->theshop->id);
        $attachurl = new moodle_url('/local/shop/bills/attachto.php', $params);
        $str .= '<div class="shop-attach-a-file">';
        $pixurl = $OUTPUT->pix_url('attach', 'local_shop');
        $label = get_string('attach', 'local_shop');
        $str .= '<a href="'.$attachurl.'"><img src="'.$pixurl.'" title="'.$label.'"/></a>';
        $str .= '</div>';

        return $str;
    }

    public function attachment($file, $bill) {
        global $OUTPUT;

        $context = context_system::instance();

        $pathinfo = pathinfo($file->get_filename());
        $type = strtoupper($pathinfo['extension']);
        $filename = $pathinfo['basename'];
        $fileicon = $OUTPUT->pix_url("/f/$type");
        if (!file_exists($fileicon)) {
            $fileicon = $OUTPUT->pix_url('/f/unkonwn');
        }

        $filename = $file->get_filename();

        $str = '<tr>';

        $str .= '<td width="10%">';
        $str .= '<img src="'.$fileicon.'">';
        $str .= '</td>';

        $str .= '<td width="60%">';
        $fileurl = moodle_url::make_pluginfile_url($context->id, 'local_shop', 'billattachments',
                                                   $file->get_itemid(), '/', $filename);
        $str .= '<a href="'.$fileurl.'">'.$filename.'</a>';
        $str .= '</td>';

        $str .= '<td width="20%">';
        $str .= $file->get_filesize().' b';
        $str .= '</td>';

        $str .= '<td width="10%">';
        $params = array('id' => $bill->id, 'what' => 'unattach', 'type' => $portlet->attachementtype,
                        'file' => $filename);
        $linkurl = new moodle_url('/local/shop/bills/view.php', $params);
        $pixurl = $OUTPUT->pix_url('t/delete');
        $str .= '<a href="'.$linkurl.'"><img src="'.$pixurl.'" border="0" alt="'.get_string('delete') .'"></a>';
        $str .= '</td>';

        $str .= '</tr>';

        return $str;
    }

    public function bill_controls($bill) {

        $str = '';
        $str .= '<table width="100%">';
        $str .= '<tr>';
        $str .= '<td align="right">';

        if (empty($bill->idnumber)) {
            $params = array('id' => $this->theshop->id, 'billid' => $bill->id);
            $billitemurl = new moodle_url('/local/shop/bills/edit_billitem.php', $params);
            $str .= '<a href="'.$billitemurl.'">'.get_string('newbillitem', 'local_shop').'</a> - ';
        }
        $params = array('id' => $this->theshop->id, 'view' => 'viewBill', 'billid' => $bill->id, 'what' => 'recalculate');
        $recalcurl = new moodle_url('/local/shop/bills/view.php', $params);
        $str .= '<a href="'.$recalcurl.'">'.get_string('recalculate', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '<br />';

        return $str;
    }

    public function lettering_form($shopid, &$afullbill) {
        $str = '';

        $str .= '<form name="billletteringform" action="" method="post" >';
        $str .= '<input type="hidden" name="view" value="viewBill" />';
        $str .= '<input type="hidden" name="shopid" value="'.$shopid.'" />';
        $str .= '<input type="hidden" name="billid" value="'.$afullbill->id.'" />';
        $str .= '<input type="hidden" name="what" value="reclettering" />';
        $str .= '<input type="text" name="idnumber" value="'.$afullbill->idnumber.'" />';
        $str .= '<input type="submit" name="go_lettering" value="'.get_string('updatelettering', 'local_shop').'" />';
        $str .= '</form>';

        return $str;
    }

    public function short_bill_line($portlet) {

        $str = '';

        $str .= '<tr>';
        $str .= '<td>';
        $billurl = new moodle_url('/local/shop/bills/view.php', array('billid' => $portlet->id));
        $str .= '<a href="'.$billurl.'">'.$portlet->id.'</a>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= $portlet->title;
        $str .= '</td>';
        $str .= '<td>';
        $str .= $portlet->userid;
        $str .= '</td>';
        $str .= '<td>';
        $str .= $portlet->emissiondate;
        $str .= '</td>';
        $str .= '<td>';
        $str .= $portlet->transactionid;
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }
}