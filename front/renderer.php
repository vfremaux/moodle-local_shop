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

use local_shop\Catalog;
use local_shop\Tax;

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class shop_front_renderer {

    // Context references
    var $theblock;

    var $theshop;

    var $thecatalog;

    var $context;

    var $view;

    /**
     * Loads the renderer with contextual objects. Most of the renderer function need
     * at least a shop instance.
     */
    function load_context(&$theshop, &$theblock = null) {

        $this->theshop = $theshop;
        $this->thecatalog = new Catalog($this->theshop->catalogid);
        $this->theblock = $theblock;

        if (!empty($this->theblock->instance->id)) {
            $this->context = context_block::instance($this->theblock->instance->id);
            $this->theblock->id = $this->theblock->instance->id;
        } else {
            $this->context = context_system::instance();
            $this->theblock = new Stdclass();
            $this->theblock->id = 0;
        }
    }

    function check_context() {
        if (empty($this->theshop) || empty($this->thecatalog)) {
            throw new coding_exception('the renderer is not ready for use. Load a shop and a catalog before calling.');
        }
    }

    /**
     * prints a purchase procedure progression bar
     * @param string $progress the progress state
     */
    function progress($progress) {
        global $OUTPUT, $SESSION;

        $str = '';

        $radicals = array(
            'CHOOSE' => 'shop',
            'CONFIGURE' => 'purchaserequ',
            'USERS' => 'users',
            'CUSTOMER' => 'customer',
            'CONFIRM' => 'order',
            'PAYMENT' => 'payment',
            'PENDING' => 'pending',
            'PRODUCE' => 'produce',
            'BILL' => 'invoice',
            'INVOICE' => 'invoice',
        );
        $stepicons = array_flip($radicals);

        $str .= '<div id="progress">';
        $str .= '<center>';
        $lang = current_language();

        $steps = explode(',', $this->theshop->navsteps);

        $state = 0;
        $iconstate = '_on';
        foreach ($steps as $step) {
            if (($state == 0)) {
                $iconstate = '_on';
                if ($step == $radicals[$progress]) {
                    $state = 1;
                }
            } else if ($state == 1) {
                $iconstate = '_off';
                $state = 2;
            }

            $icon = $stepicons[$step];
            if (!empty($SESSION->shoppingcart->norequs) && ($icon == 'CONFIGURE') && ($iconstate == '_on')) {
                $iconstate = '_dis';
            }
            if (empty($SESSION->shoppingcart->seats) && ($icon == 'USERS') && ($iconstate == '_on')) {
                $iconstate = '_dis';
            }

            $stepicon = $OUTPUT->pix_url(current_language().'/'.$icon.$iconstate, 'local_shop');
            $str .= '<img src="'.$stepicon.'" />&nbsp;';
        }
        $str .= '</center>';
        $str .= '</div>';

        return $str;
    }

    /**
     * Prints a summary form for the purchase
     */
    function order_totals() {
        global $CFG, $SESSION;

        $this->check_context();

        $config = get_config('local_shop');

        $str = '';

        $totalobjects = 0;
        $amount = 0;
        $untaxed = 0;
        $taxes = 0;
        if (!empty($SESSION->shoppingcart->order)) {
            foreach ($SESSION->shoppingcart->order as $shortname => $q) {
                $totalobjects += $q;
                $ci = $this->thecatalog->get_product_by_shortname($shortname);
                $ttc = $ci->get_taxed_price($q) * $q;
                $ht = $ci->get_price($q) * $q;
                $amount += $ttc;
                $untaxed += $ht;
                $taxes += ($ttc - $ht) * $q;
            }
        }

        $str .= '<table width="100%">';

        $str .= '<tr valign="top">';
        $str .= '<td align="left" class="shop-ordercell">';
        $str .= '<b>'.get_string('ordertotal', 'local_shop').'</b> :';
        $str .= '</td>';
        $str .= '<td align="left" class="shop-ordercell">';
        $str .= '<span id="total_euros_span"> '.sprintf('%.2f', round($amount, 2)).' </span>';
        $str .= $this->theshop->get_currency('symbol');
        $str .= ' '.get_string('for', 'local_shop');
        $str .= '<span id="object_count_span"> '.$totalobjects.'&nbsp;</span>';
        $str .= get_string('objects', 'local_shop');
        $str .= '</td></tr>';

        $discountrate = shop_calculate_discountrate_for_user($amount, $this->context, $reason);

        if ($discountrate) {
            $str .= '<tr>';
            $str .= '<td colspan="2">';
            $str .= $reason;
            // $str .= get_string('yougetdiscountof', 'local_shop');
            $str .= ' : <b>'.$discountrate.' %</b>.<br/>';
            $str .= '</td>';
            $str .= '</tr>';
            $discounted = $amount - ($amount * $discountrate / 100);
        } else {
            $discounted = $amount;
        }
        $str .= '<tr valign="bottom">';
        $str .= '<td class="shop-finalcount">';
        $str .= '<b>'.get_string('orderingtotal', 'local_shop').'</b>';
        $str .= '</td>';
        $str .= '<td align="left" class="shop-finalcount">';
        $str .= '<span id="shop-discounted-span"> '.sprintf('%.2f', $discounted).' </span>';
        $str .= $this->theshop->get_currency('symbol');
        $str .= '</td>';
        $str .= '</tr>';
        if (!empty($config->useshipping)) {
            $shipchecked = (!empty($SESSION->shoppingcart->shipping)) ? 'checked="checked"' : '';
            $str .= '<tr>';
            $str .= '<td align="left" colspan="2">';
            $str .= '<span class="smalltext">(*)'. get_string('shippingadded', 'local_shop') .'<br/></span>';
            $str .= '<input type="checkbox" name="shipping" value="1" '.sprintf('%.2f', $shipchecked).' /> '.get_string('askforshipping', 'local_shop');
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '</table>';

        return $str;
    }

    /**
     *
     *
     */
    function printable_bill_link(&$bill) {
        global $CFG, $DB, $OUTPUT;

        $str = '';

        $popup  = ($bill->status == SHOP_BILL_SOLDOUT || $bill->status == SHOP_BILL_COMPLETE) ? 'bill' : 'order' ;

        $popupurl = new moodle_url('/local/shop/front/'.$popup.'.popup.php');
        $str .= '<form name="bill" action="'.$popupurl.'" target="_blank" />';
        $str .= '<input type="hidden" name="transid" value="'.$bill->transactionid.'" />';
        $str .= '<input type="hidden" name="billid" value="'.$bill->id.'">';
        $str .= '<input type="hidden" name="shopid" value="'.$this->theshop->id.'\">';
        $str .= '<input type="hidden" name="blockid" value="'.(0 + @$this->theblock->id).'\">';
        $str .= '<table><tr valign="top"><td align="center">';
        $str .= '<br /><br /><br /><br />';
        $billurl = new moodle_url('/local/shop/front/'.$popup.'.popup.php', array('shopid' => $this->theshop->id, 'blockid' => (0 + @$this->theblock->id), 'billid' => $bill->id, 'transid' => $bill->transactionid));
        $customerid = $DB->get_field('local_shop_bill', 'customerid', array('id' => $bill->id));
        if ($userid = $DB->get_field('local_shop_customer', 'hasaccount', array('id' => $customerid))) {
            $billuser = $DB->get_record('user', array('id' => $userid));
            $ticket = ticket_generate($billuser, 'immediate access', $billurl);
            $options = array('ticket' => $ticket);
            $str .= $OUTPUT->single_button('/login/index.php', get_string('printbill', 'local_shop'), 'post',  $options);
        }
        $str .= '</td></tr></table>';
        $str .= '</form>';

        return $str;
    }

    /**
     * Prints the customer info summary
     * @param object $bill
     */
    function customer_info(&$bill = null) {
        global $SESSION;

        if (empty($bill)) {
            if (!empty($SESSION->shoppingcart->usedistinctinvoiceinfo)) {
                $ci = $SESSION->shoppingcart->invoiceinfo;
            } else {
                $ci = $SESSION->shoppingcart->customerinfo;
            }

            $emissiondate = $SESSION->shoppingcart->emissiondate = time();
            $transid = $SESSION->shoppingcart->transid;
        } else {
            $ci = (array)$bill->customer;
            $transid = $bill->transactionid;
            $emissiondate = $bill->emissiondate;
        }

        $str = '';

        $str .= '<div id="shop-customerinfo">';
        $str .= '<table cellspacing="4" width="100%">';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('orderID', 'local_shop').'</b>'. $transid;
        $str .= '</td><td width="40%" valign="top" align="right">';
        $str .= '<b>'.get_string('on', 'local_shop').':</b> '.userdate($emissiondate);
        $str .= '</td></tr>';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('organisation', 'local_shop').':</b> '.@$ci['organisation'];
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('customer', 'local_shop').':</b> '.$ci['lastname'].' '.$ci['firstname'];
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('city').': </b> '.$ci['zip'].' '.$ci['city'];
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('country').':</b> '.strtoupper($ci['country']);
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';
        $str .= '<tr><td width="60%" valign="top">';
        $str .= '<b>'.get_string('email').':</b> '.@$ci['email'];
        $str .= '</td><td width="40%" valign="top">';
        $str .= '</td></tr>';
        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    /**
     *
     *
     */
    function local_confirmation_form($requireddata) {

        $confirmstr = get_string('confirm', 'local_shop');
        $disabled = (!empty($requireddata)) ? 'disabled="disabled"' : '';
        $str = '<center>';
        $formurl = new moodle_url('/local/shop/front/view.php');
        $str .= '<form name="confirmation" method="POST" action="'.$formurl.'" style="display : inline">';
        $str .= '<table style="display : block ; visibility : visible" width="100%"><tr><td align="center">';
        if (!empty($disabled)) {
            $str .= '<br><span id="disabled-advice-span" class="error">'.get_string('requiredataadvice', 'local_shop').'</span><br/>';
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
    function category_tabs(&$categories) {

        $category = optional_param('category', null, PARAM_INT);
        if ($category) {
            $selected = 'catli'.$category;
        }

        $str = '';

        $catidsarr = array();
        foreach ($categories as $cat) {
            if (empty($category)) {
                $category = $cat->id;
            }
            $catidsarr[] = $cat->id;
        }
        $catids = implode(',', $catidsarr);

        $rows[0] = array();
        foreach ($categories as $cat) {
            if (empty($selected)) {
                $selected = 'catli'.$cat->id;
            }
            $categoryurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'category' => $cat->id, 'id' => $this->theshop->id, 'blockid' => $this->theblock->id));
            $rows[0][] = new tabobject('catli'.$cat->id, $categoryurl, format_string($cat->name));
            // $rows[0][] = new tabobject('catli'.$cat->id, "javascript:showcategory('.$cat->id.', \''.$catids.'\')", format_string($cat->name));
        }

        $str .= print_tabs($rows, $selected, '', '', true);

        return $str;
    }

    /**
     * prints a full catalog on screen
     * @param objectref $theBlock the shop block instance
     * @param array $catgories the full product line extractred from Catalog
     */
    function catalog(&$categories) {
        global $CFG, $OUTPUT;

        $config = get_config('local_shop');
        $this->check_context();

        if (empty($categories)) {
            return $OUTPUT->notification(get_string('nocats', 'local_shop'));
        }

        $str = '';

        $catidsarr = array();
        foreach ($categories as $cat) {
            $catidsarr[] = $cat->id;
        }
        $catids = implode(',', $catidsarr);

        $withtabs = (@$this->theshop->printtabbedcategories == 1);

        if ($withtabs) {
            $str .= $this->category_tabs($categories, $this->theblock->id);
        }

        // print catalog product line on the active category if tabbed
        $catids = array_keys($categories);
        $category = optional_param('category', array_keys($categories)[0], PARAM_INT);

        $c = 0;
        foreach ($categories as $cat) {
            if ($withtabs && ($category != $cat->id)) {
                continue;
            }
            if (!isset($firstcatid)) $firstcatid = $cat->id;
    
            if ($withtabs) {
                $str .= '<div class="shopcategory" id="category'.$cat->id.'" />';
            } else {
                $cat->level = 1;
                $str .= $OUTPUT->heading($cat->name, $cat->level);
            }

            if (!empty($cat->description)) {
                $str .= '<div class="shop-category-description">';
                $str .= format_text($cat->description);
                $str .= '</div>';
            }

            if (!empty($cat->products)) {
                foreach ($cat->products as $product) {

                    $product->check_availability();
                    $product->currency = $this->theshop->get_currency('symbol');
                    $product->salesunit = $product->get_sales_unit_url();
                    $product->preset = 0 + @$SESSION->shoppingcart->order[$product->shortname];
                    switch ($product->isset) {
                        case PRODUCT_SET:
                            $str .= $this->product_set($product, true);
                            break;
                        case PRODUCT_BUNDLE:
                            $str .= $this->product_bundle($product, true);
                            break;
                        default:
                            $str .= $this->product_block($product, true);
                    }
                }
            } else {
                $str .= get_string('noproductincategory', 'local_shop');
            }

            $c++;

            if ($withtabs) {
                $str .= '</div>';
            }
        }
        // $str .= "<script type=\"text/javascript\">showcategory(".@$firstcatid.", '{$catids}');</script>";

        return $str;
    }

    function product_block(&$product) {
        global $CFG;

        $this->check_context();

        $str = '';

        $subelementclass = (!empty($product->ispart)) ? 'element' : 'product';
        $subelementclass .= ($product->available) ? '' : ' shadowed';

        $str .= '<a name="'.$product->code.'"></a>';
        $str .= '<div class="shop-article '.$subelementclass.'">';

        $image = $product->get_image_url();
        if ($image) {
            $imagestr = '<a class="fancybox" rel="group" href="'.$image.'"><img src="'.$product->get_thumb_url().'"></a>';
        } else {
            $imagestr = '<img src="'.$product->get_thumb_url().'">';
        }
        $str .= '<div class="shop-front-productpix">'.$imagestr.'</div>';

        $str .= '<div class="shop-front-productdef">';
        if (!empty($product->ispart)) {
            $str .= '<h3 class="shop-front-partof">'.$product->name.'</h3>';
        } else {
            $str .= '<h2>'.$product->name.'</h2>';
            if ($product->description) {
                $str .= '<div class="shop-front-description">'.$product->description.'</div>';
            }
            if (!$product->available) {
                $str .= '<div class="shop-not-available">'.get_string('notavailable', 'local_shop').'</div>';
            }
        }

        if ($product->has_leaflet()) {
            $leafleturl = $product->get_leaflet_url();
            $str .= '<div class="shop-front-leaflet"><a href="'.$leafleturl.'" target="_blank">'.get_string('leafletlink', 'local_shop').'</a></div>';
        }

        if (empty($product->noorder)) {
            $str .= '<div class="shop-front-refblock">';
            $str .= '<div class="shop-front-ref">'.get_string('ref', 'local_shop').' : '.$product->code.'</div>';
    
            $prices = $product->get_printable_prices(true);
            if (count($prices) <= 1) {
                $str .= '<div class="shop-front-label">'.get_string('puttc', 'local_shop').'</div>';
                $price = array_shift($prices);
                $str .= '<div class="shop-front-price">'.$price.' '.$product->currency.'</div>';
            } else {
                $str .= '<div class="shop-front-pricelist">';
                $str .= '<div class="shop-front-label">'.get_string('puttc', 'local_shop').'</div>';
                foreach ($prices as $range => $price) {
                    $str .= '<div class="shop-front-pricerange">'.$range.' : </div><div class="shop-front-price">'.$price.' '.$product->currency.'</div>';
                }
                $str .= '</div>'; // shop-front-pricelist
            }

            if ($product->available) {
                $str .= '<div class="shop-front-order">';
                $buystr = get_string('buy', 'local_shop');
                $disabled = ($product->maxdeliveryquant && $product->maxdeliveryquant == $product->preset) ? 'disabled="disabled"' : '' ;
                $str .= '<input type="button" id="ci-'.$product->shortname.'" value="'.$buystr.'" onclick="ajax_add_unit(\''.$CFG->wwwroot.'\', '.$this->theshop->id.', \''.$product->shortname.'\', \''.$product->maxdeliveryquant.'\')" '.$disabled.' />';
                $str .= '<div class="shop-order-item" id="bag_'.$product->shortname.'">';
                $str .= $this->units($product);
                $str .= '</div>';
                $str .= '</div>'; // shop-front-order
            }
            $str .= '</div>'; // shop-front-refblock
        }

        $str .= '</div>'; // shop-front-productdef
        $str .= '</div>'; // front-article

        return $str;
    }

    function product_set(&$set) {
        global $CFG;

        $str = '';

        $str .= '<div class="shop-article set">';
        $str .= '<div class="shop-front-setcaption">';
        $str .= '<h2>'.$set->name.'</h2>';
        $str .= '<div class="shop-front-description">'.$set->description.'</div>';
        $str .= '</div>'; // shop-front-setcaption

        $str .= '<div class="shop-front-elements">';
        foreach ($set->elements as $element) {
            $element->check_availability();
            $element->noorder = false; // Bundle can only be purchased as a group
            $element->ispart = true; // reduced title
            $str .= $this->product_block($element);
        }
        $str .= '</div>'; // shop-front-elements
        $str .= '</div>'; // shop-article

        return $str;
    }

    function product_bundle(&$bundle) {
        global $CFG;

        $str = '';

        $str .= '<div class="shop-article bundle">';

        $image = $bundle->get_image_url();
        if ($image) {
            $imagestr = '<a class="fancybox" rel="group" href="'.$image.'"><img src="'.$bundle->get_thumb_url().'"></a>';
        } else {
            $imagestr = '<img src="'.$bundle->get_thumb_url().'">';
        }
        $str .= '<div class="shop-front-productpix">'.$imagestr.'</div>';

        $str .= '<div class="shop-front-productdef">';
        if (!empty($bundle->ispart)) {
            $str .= '<div class="shop-front-partof">'.$bundle->name.'</div>';
        } else {
            $str .= '<h2>'.$bundle->name.'</h2>';
            if ($bundle->description) {
                $str .= '<div class="shop-front-description">'.$bundle->description.'</div>';
            }
        }

        if ($bundle->has_leaflet()) {
            $leafleturl = $bundle->get_leaflet_url();
            $str .= '<div class="shop-front-leaflet"><a href="'.$leafleturl.'" target="_blank">'.get_string('leafletlink', 'local_shop').'</a></div>';
        }

        $str .= '<div class="shop-front-refblock">';
        $str .= '<div class="shop-front-ref">'.get_string('ref', 'local_shop').' : '.$bundle->code.'</div>';

        $TTCprice = 0;
        foreach ($bundle->elements as $element) {
            $element->check_availability();
            $element->noorder = true; // Bundle can only be purchased as a group
            $element->ispart = true; // reduced title
            $str .= $this->product_block($element);
        }

        // We will use price of the bundle element.
        $prices = $bundle->get_printable_prices(true);
        if (count($prices) <= 1) {
            $str .= get_string('puttc', 'local_shop');
            $price = array_shift($prices);
            $str .= '<div class="shop-front-price">'.$price.' '.$bundle->currency.'</div>';
        } else {
            $str .= '<div class="shop-front-pricelist">';
            $str .= '<div class="shop-front-label">'.get_string('puttc', 'local_shop').'</div>';
            foreach ($prices as $range => $price) {
                $str .= '<div class="shop-front-pricerange">'.$range.' : </div><div class="shop-front-price">'.$price.' '.$bundle->currency.'</div>';
            }
            $str .= '</div>'; // shop-front-pricelist
        }

        $str .= '<div class="shop-front-order">';
        $buystr = get_string('buy', 'local_shop');
        $disabled = ($bundle->maxdeliveryquant && $bundle->maxdeliveryquant == $bundle->preset) ? 'disabled="disabled"' : '' ;
        $str .= '<input type="button" id="ci-'.$bundle->shortname.'" value="'.$buystr.'" onclick="ajax_add_unit(\''.$CFG->wwwroot.'\', '.$this->theshop->id.', \''.$bundle->shortname.'\', \''.$bundle->maxdeliveryquant.'\')" '.$disabled.' />';
        $str .= '<div class="shop-order-item" id="bag_'.$bundle->shortname.'">';
        $str .= $this->units($bundle);
        $str .= '</div>';
        $str .= '</div>'; // shop-front-order
        $str .= '</div>'; // shop-front-refblock

        $str .= '</div>'; // shop-front-productdef
        $str .= '</div>'; // shop-article

        return $str;
    }

    function units(&$product) {
        global $SESSION, $CFG, $OUTPUT;

        $this->check_context();

        $unitimage = $product->get_sales_unit_url();

        $str = '';
        for ($i = 0 ; $i < 0 + @$SESSION->shoppingcart->order[$product->shortname] ; $i++) {
            $str .= '&nbsp;<img src="'.$unitimage.'" align="middle" />';
        }

        if ($i > 0) {
            $str .= '&nbsp;<a title="'.get_string('deleteone', 'local_shop').'" href="Javascript:ajax_delete_unit(\''.$CFG->wwwroot.'\', '.$this->theshop->id.', \''.$product->shortname.'\')"><img src="'.$OUTPUT->pix_url('t/delete').'" valign="center" /></a>';
        }

        return $str;
    }

    function order_detail(&$categories) {
        global $SESSION, $CFG, $OUTPUT;

        if (empty($categories)) {
            return;
        }

        $str = '';

        $str .= '<table width="100%" id="orderblock">';

        foreach ($categories as $aCategory) {
            if (empty($aCategory->products)) continue;
            foreach ($aCategory->products as $aProduct) {
                if ($aProduct->isset == PRODUCT_SET) {
                    foreach ($aProduct->elements as $portlet) {
                        $portlet->currency = $this->theshop->get_currency('symbol');
                        $portlet->preset = !empty($SESSION->shoppingcart->order[$portlet->shortname]) ? $SESSION->shoppingcart->order[$portlet->shortname] : 0;
                        if ($portlet->preset) {
                            $str .= $this->product_total_line($portlet, true);
                        }
                    }
                } else {
                    $portlet = &$aProduct;
                    $portlet->currency = $this->theshop->get_currency('symbol');
                    $portlet->preset = !empty($SESSION->shoppingcart->order[$portlet->shortname]) ? $SESSION->shoppingcart->order[$portlet->shortname] : 0;
                    if ($portlet->preset) {
                        $str .= $this->product_total_line($portlet, true);
                    }
                }
            }
        }
        $str .= '</table>';

        return $str;
    }

    function product_total_line(&$product) {
        global $CFG, $OUTPUT;

        $this->check_context();

        $str = '';

        $TTCprice = $product->get_taxed_price($product->preset, $product->taxcode);
        $product->total = $TTCprice * $product->preset;

        $str .= '<tr id="producttotalcaption_'.$product->shortname.'">';
        $str .= '<td class="shop-ordercaptioncell" colspan="3">';
        $str .= $product->name;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr id="producttotal_'.$product->shortname.'">';
        $str .= '<td class="shop-ordercell">';
        $disabled = ' disabled="disabled" ';
        if ($this->view == 'shop') {
            $str .= '<a title="'.get_string('clearall', 'local_shop').'" href="Javascript:ajax_clear_product(\''.$CFG->wwwroot.'\', '.$this->theshop->id.', '.$this->theblock->id.', \''.$product->shortname.'\')"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
            $disabled = '';
        }
        $str .= '<input type="text" class="order-detail" id="id_'.$product->shortname.'" name="'.$product->shortname.'" value="'.$product->preset.'" size="3" style="width:40px" onChange="ajax_update_product(\''.$CFG->wwwroot.'\', '.$this->theshop->id.', \''.$product->shortname.'\', this, \''.$product->maxdeliveryquant.'\')" '.$disabled.' >';
        $str .= '</td>';
        $str .= '<td class="shop-ordercell">';
        $str .= '<p>x '.sprintf("%.2f", round($TTCprice, 2)).' '.$product->currency.' : ';
        $str .= '</td>';
        $str .= '<td class="shop-ordercell">';
        $str .= '<input type="text" class="order-detail" id="id_total_'.$product->shortname.'" name="'.$product->shortname.'_total" value="'.sprintf("%.2f", round($product->total, 2)).'" size="6" disabled class="totals" >';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * Prints the customer information form
     * @param object $theBlock
     * @param object $theCatalog
     */
    function customer_info_form() {
        global $USER, $OUTPUT, $DB, $SESSION;

        $this->check_context();

        $str = '';

        $checked = (!empty($SESSION->shoppingcart->usedistinctinvoiceinfo)) ? 'checked="checked"' : '';

        $str .= $OUTPUT->heading(get_string('customerinformation', 'local_shop').' <input type="checkbox" value="1" name="usedistinctinvoiceinfo" onchange="local_toggle_invoiceinfo(this)" '.$checked.' /><span class="tiny-text"> '.get_string('usedistinctinvoiceinfo', 'local_shop').'</span>' );
        if (isloggedin()) {
            $lastname = $USER->lastname;
            $firstname = $USER->firstname;
            $organisation = (!empty($SESSION->shoppingcart->customerinfo['organisation'])) ? $SESSION->shoppingcart->customerinfo['organisation'] : $USER->institution;
            $city = (!empty($SESSION->shoppingcart->customerinfo['city'])) ? $SESSION->shoppingcart->customerinfo['city'] : $USER->city;
            $address = (!empty($SESSION->shoppingcart->customerinfo['address'])) ? $SESSION->shoppingcart->customerinfo['address'] : $USER->address;
            $zip = (!empty($SESSION->shoppingcart->customerinfo['zip'])) ? $SESSION->shoppingcart->customerinfo['zip'] : '';
            $country = (!empty($SESSION->shoppingcart->customerinfo['country'])) ? $SESSION->shoppingcart->customerinfo['country'] : $USER->country;
            $email = $USER->email;
            // get potential ZIP code information from an eventual customer record
            if ($customer = $DB->get_record('local_shop_customer', array('hasaccount' => $USER->id))) {
                $zip = (!empty($SESSION->shoppingcart->customerinfo['zip'])) ? $SESSION->shoppingcart->customerinfo['zip'] : $customer->zip;
                $organisation = (!empty($SESSION->shoppingcart->customerinfo['organisation'])) ? $SESSION->shoppingcart->customerinfo['organisation'] : $customer->organisation;
                $address = (!empty($SESSION->shoppingcart->customerinfo['address'])) ? $SESSION->shoppingcart->customerinfo['address'] : $customer->address;
            }
        } else {
            $lastname = @$SESSION->shoppingcart->customerinfo['lastname'];
            $firstname = @$SESSION->shoppingcart->customerinfo['firstname'];
            $organisation = @$SESSION->shoppingcart->customerinfo['organisation'];
            $country = @$SESSION->shoppingcart->customerinfo['country'];
            $address = @$SESSION->shoppingcart->customerinfo['address'];
            $city = @$SESSION->shoppingcart->customerinfo['city'];
            $zip = @$SESSION->shoppingcart->customerinfo['zip'];
            $email = @$SESSION->shoppingcart->customerinfo['email'];
        }

        $lastnameclass = '';
        $firstnameclass = '';
        $organisationclass = '';
        $countryclass = '';
        $addressclass = '';
        $cityclass = '';
        $zipclass = '';
        $mailclass = '';

        if (!empty($SESSION->shoppingcart->errors->customerinfo)) {
            foreach (array_keys($SESSION->shoppingcart->errors->customerinfo) as $f) {
                $f = str_replace('customerinfo::', '', $f);
                $var = "{$f}class";
                $$var = 'shop-error';
            }
        }
        $str .= '<div id="shop-customerdata">';
        $str .= '<table cellspacing="3" width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('lastname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$lastnameclass.'" name="customerinfo::lastname" size="20" onchange="setupper(this)" value="'. $lastname.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('firstname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$firstnameclass.'" name="customerinfo::firstname" size="20" onchange="capitalizewords(this)" value="'.$firstname.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        if (!empty($this->theshop->customerorganisationrequired)) {
            $str .= '<tr valign="top">';
            $str .= '<td align="right">';
            $str .= get_string('organisation', 'local_shop');
            $str .= ':</td>';
            $str .= '<td align="left">';
            $str .= '<input type="text" name="customerinfo::organisation" size="26" maxlength="64" value="'.$organisation.'" />';
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('address');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"  class="'.$addressclass.'"name="customerinfo::address" size="26" onchange="setupper(this)" value="'. $address .'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('city');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"  class="'.$cityclass.'"name="customerinfo::city" size="26" onchange="setupper(this)" value="'. $city .'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('zip','local_shop');
        $str .= '<sup style="color : red">*</sup>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"  class="'.$zipclass.'" name="customerinfo::zip" size="6" value="'. $zip .'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('country');
        $str .= '<sup style="color : red">*</sup>: <br>';
        $str .= '</td>';
        $str .= '<td align="left">';
        // $country = 'FR';
        $choices = get_string_manager()->get_list_of_countries();
        $this->thecatalog->process_country_restrictions($choices);
        $str .= html_writer::select($choices, 'customerinfo::country', $country, array('' => 'choosedots'));
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('email', 'local_shop');
        $str.= '<sup style="color : red">*</sup>';
        $str.= '</td>';
        $str.= '<td align="left">';
        $str.= '<input type="text"  class="'.$mailclass.'" name="customerinfo::email" size="30" onchange="testmail(this)" value="'.$email.'" />';
        $str.= '</td>';
        $str.= '</tr>';
        $str.= '</table>';
        $str.= '</div>';

        return $str;
    }

    /**
     * Prints the form for invoicing customer identity
     * @param object $theBlock
     * @param object $theCatalog
     */
    function invoicing_info_form() {
        global $OUTPUT, $SESSION;

        $this->check_context();

        $str = '';

        $institution = @$SESSION->shoppingcart->invoiceinfo['organisation'];
        $department = @$SESSION->shoppingcart->invoiceinfo['department'];
        $lastname = @$SESSION->shoppingcart->invoiceinfo['lastname'];
        $firstname = @$SESSION->shoppingcart->invoiceinfo['firstname'];
        $address = @$SESSION->shoppingcart->invoiceinfo['address'];
        $zip = @$SESSION->shoppingcart->invoiceinfo['zip'];
        $city = @$SESSION->shoppingcart->invoiceinfo['city'];
        $country = @$SESSION->shoppingcart->invoiceinfo['country'];
        $vatcode = @$SESSION->shoppingcart->invoiceinfo['vatcode'];
        // $plantcode = @$SESSION->shoppingcart->invoiceinfo['plantcode'];

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

        if (!empty($SESSION->shoppingcart->errors->invoiceinfo)) {
            foreach (array_keys($SESSION->shoppingcart->errors->invoiceinfo) as $f) {
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
        $str .= '<input type="text" class="'.$organisationclass.'" name="invoiceinfo::organisation" size="26" maxlength="64" value="'.$institution.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('department');
        $str .= ':</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$departmentclass.'" name="invoiceinfo::department" size="26" maxlength="64" value="'.$department.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('lastname');
        $str .= '<sup style="color : red">*</sup>:';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$lastnameclass.'" name="invoiceinfo::lastname" size="20" onchange="setupper(this)" value="'. $lastname.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('firstname');
        $str.= '<sup style="color : red">*</sup>:';
        $str.= '</td>';
        $str.= '<td align="left">';
        $str.= '<input type="text" class="'.$firstnameclass.'" name="invoiceinfo::firstname" size="20" onchange="capitalizewords(this)" value="'.$firstname.'" />';
        $str.= '</td>';
        $str.= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('address');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$addressclass.'" name="invoiceinfo::address" size="26" onchange="setupper(this)" value="'. $address .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('city');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$cityclass.'" name="invoiceinfo::city" size="26" onchange="setupper(this)" value="'. $city .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('zip','local_shop');
        $str .= '<sup style="color : red">*</sup>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$zipclass.'" name="invoiceinfo::zip" size="6" value="'. $zip .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('country');
        $str .= '<sup style="color : red">*</sup>: <br>';
        $str .= '</td>';
        $str .= '<td align="left">';
        // $country = 'FR';
        $choices = get_string_manager()->get_list_of_countries();
        $this->thecatalog->process_country_restrictions($choices);
        $str .= html_writer::select($choices, 'invoiceinfo::country', $country, array('' => 'choosedots'), array('class' => $countryclass));
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('vatcode','local_shop');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="'.$vatcodeclass.'" name="invoiceinfo::vatcode" size="15" value="'. $vatcode .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        // print_string('plantcode','local_shop');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="hidden" class="'.$plantcodeclass.'" name="invoiceinfo::plantcode" value="" />';
        // echo '<input type="text" class="'.$plantcodeclass.'" name="invoiceinfo::plantcode" size="20" value="'. $plantcode .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    function participant_row($participant = null) {
        global $CFG, $OUTPUT, $SITE;

        $this->check_context();

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
                if (file_exists($CFG->dirroot.'/theme/'.$PAGE->theme->name.'/favicon.jpg')) {
                    $str .= '<img src="'.$OUTPUT->pix_url('favicon').'" title="'.get_string('isuser', 'local_shop', $SITE->shortname).'" />';
                } else {
                    $str .= '<img src="'.$OUTPUT->pix_url('i/moodle_host').'" title="'.get_string('isuser', 'local_shop', $SITE->shortname).'" />';
                }
            } else {
                $str .= '<img src="'.$OUTPUT->pix_url('new', 'local_shop').'" title="'.get_string('isnotuser', 'local_shop', $SITE->shortname).'" />';
            }
            $str .= '</td>';
            $str .= '<td align="right">';
            $str .= '<a title="'.get_string('deleteparticipant', 'local_shop').'" href="Javascript:ajax_delete_user(\''.$CFG->wwwroot.'\', \''.$participant->email.'\')"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
            $str .= '</td>';
            $str .= '</tr>';
        } else {
            // print a caption row
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
            if (!empty($this->shop->endusermobilephonerequired)) {
                $str .= '<th align="left">';
                $str .= get_string('phone2');
                $str .= '</th>';
            }
            if (!empty($this->shop->enduserorganisationrequired)) {
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

    function participant_blankrow() {
        global $CFG, $OUTPUT;

        $this->check_context();

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
        $str .= '<input type="text" name="city_foo_'.$i.'" size="14" disabled="disabled" class="shop-disabled" />';
        $str .= '</td>';
        if (!empty($this->theshop->endusermobilephonerequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="phone2_foo_'.$i.'" size="13" disabled="disabled" class="shop-disabled" />';
            $str .= '</td>';
        }
        if (!empty($this->theshop->enduserorganisationrequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="institution_foo_'.$i.'" size="13" disabled="disabled" class="shop-disabled" />';
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

    function new_participant_row() {
        global $CFG, $SESSION;

        $this->check_context();

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
        $str .= '<input type="text" name="lastname" size="15" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="firstname" size="15" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="email" size="20" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" name="city" size="14" />';
        $str .= '</td>';
        if (!empty($this->theshop->endusermobilephonerequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="phone2" size="13" maxlength="10" />';
            $str .= '</td>';
        }
        if (!empty($this->theshop->enduserorganisationrequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text" name="institution" size="15" size="15" maxlength="40" />';
            $str .= '</td>';
        }
        $str .= '<td align="right">';
        $str .= '<input type="button" value="'.get_string('addparticipant', 'local_shop').'" name="add_button" onclick="ajax_add_user(\''.$CFG->wwwroot.'\', document.forms[\'participant\'])" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</form>';

        return $str;
    }

    function assignation_row($participant, $role, $shortname) {
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
        $str .= '<a href="Javascript:ajax_delete_assign(\''.$CFG->wwwroot.'\', \''.$role.'\', \''.$shortname.'\', \''.$participant->email.'\')"><img src="'.$OUTPUT->pix_url('t/delete').'" /></a>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
    * prints a user selector for a product/role list from declared
    * participants removing already assigned people.
    */
    function assignation_select($role, $shortname) {
        global $SESSION, $CFG;

        $str = '';

        if (empty($SESSION->shoppingcart)) return;

        if (!empty($SESSION->shoppingcart->users[$shortname][$role])) {
            $rkeys = array_keys($SESSION->shoppingcart->users[$shortname][$role]);
        } else {
            $rkeys = array();
        }

        $options = array();
        if (!empty($SESSION->shoppingcart->participants)) {
            foreach ($SESSION->shoppingcart->participants as $email => $pt) {
                if (!in_array($email, $rkeys)) {
                    $options[$email] = $pt->lastname.' '.$pt->firstname;
                }
            }
        }
        $str .= html_writer::select($options, 'addassign'.$role.'_'.$shortname, '', array('' => get_string('chooseparticipant', 'local_shop')), array('onchange' => 'ajax_add_assign(\''.$CFG->wwwroot.'\', \''.$role.'\', \''.$shortname.'\', this)'));

        return $str;
    }

    function role_list($role, $shortname) {
        global $OUTPUT, $SESSION;

        $this->check_context();

        $str = '';

        $roleassigns = @$SESSION->shoppingcart->users;

        $str .= $OUTPUT->heading(get_string(str_replace('_', '', $role), 'local_shop'));  // remove pseudo roles markers
        if (!empty($roleassigns[$shortname][$role])) {
            $str .= '<div class="shop-role-list-container">';
            $str .= '<table width="100%" class="shop-role-list">';
                foreach ($roleassigns[$shortname][$role] as $participant) {
                    $str .= $this->assignation_row($participant, $role, $shortname, true);
                }
            $str .= '</table>';
            $str .= '</div>';
        } else {
            $str .= '<div class="shop-role-list-container">';
            $str .= '<div class="shop-role-list">';
            $str .= get_string('noassignation', 'local_shop');
            $str .= '</div>';
            $str.= '</div>';
        }
        if (@$SESSION->shoppingcart->assigns[$shortname] < $SESSION->shoppingcart->order[$shortname]) {
            $str .= $this->assignation_select($role, $shortname, true);
        } else {
            $str .= get_string('seatscomplete', 'local_shop');
        }

        return $str;
    }

    function cart_summary() {
        global $SESSION, $CFG;

        $str = '';

        if (!empty($SESSION->shoppingcart->order)) {
            $str .= '<table width="100%">';
            foreach ($SESSION->shoppingcart->order as $itemname => $itemcount) {
                $product = $this->thecatalog->get_product_by_shortname($itemname);
                $str .= '<tr>';
                $str .= '<td class="short-order-name"><span title="'.$product->name.'" alt="'.$product->name.'">'.$product->code.'</span></td>';
                $str .= '<td class="short-order-quantity">'.$SESSION->shoppingcart->order[$itemname].'</td>';
                $str .= '</tr>';
                $str .= '<tr>';
                $str .= '<td colspan="2"  class="short-order-summary">'.shorten_text(strip_tags($product->description), 120).'</td>';
                $str .= '</tr>';
            }
            $str .= '</table>';
        }

        return $str;
    }

    function admin_options() {
        global $OUTPUT, $SESSION, $DB;

        $this->check_context();

        $str = '';

        if (isloggedin() && has_capability('moodle/site:config', context_system::instance())) {
            $str .= $OUTPUT->box_start('', 'shop-adminlinks');
            $str .= get_string('adminoptions', 'local_shop');
            $disableall = get_string('disableallmode', 'local_shop');
            $enableall = get_string('enableallmode', 'local_shop');
            $intancesettingsstr = get_string('instancesettings', 'local_shop');
            $toproductbackofficestr = get_string('gotobackoffice', 'local_shop');

            if (!empty($SESSION->shopseeall)) {
                $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'seeall' => 0, 'id' => $this->theshop->id, 'blockid' => $this->theblock->id));
                $str .= '<a href="'.$shopurl.'">'.$disableall.'</a>';
            } else {
                $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'seeall' => 1, 'id' => $this->theshop->id, 'blockid' => $this->theblock->id));
                $str .= '<a href="'.$shopurl.'">'.$enableall.'</a>';
            }
            $backofficeurl = new moodle_url('/local/shop/products/view.php', array('view' => 'viewAllProducts', 'catalogid' => $this->thecatalog->id, 'blockid' => $this->theblock->id));
            $str .= '&nbsp;-&nbsp;<a href="'.$backofficeurl.'">'.$toproductbackofficestr.'</a>';

            $str .= $OUTPUT->box_end();
        }

        return $str;
    }

    /**
     * Prints an order line
     * @param objectref $theCatalog the whole catalog reference
     * @param string $shortname the product short code
     * @param int $q quantity
     * @param array $options
     * @param boolean $return if true returns a string
     */
    function order_line($shortname = null, $q = null, $options = null) {
        global $SESSION;

        $this->check_context();

        $str = '';

        if (is_null($shortname)) {
            $str .= '<tr valign="top">';
            $str .= '<th width="60%" align="left" class="header c0">';
            $str .= get_string('designation', 'local_shop');
            $str .= '</th>';
            $str .= '<th width="20%" align="left" class="header c1">';
            $str .= get_string('reference', 'local_shop');
            $str .= '</th>';
            $str .= '<th width="8%" align="left" class="header c2">';
            $str .= get_string('unitprice', 'local_shop');
            $str .= '</th>';
            $str .= '<th width="4%" class="header c3">';
            $str .= get_string('quantity', 'local_shop');
            $str .= '</th>';
            $str .= '<th width="8%" align="right" style="text-align:right" class="header lastcol">';
            $str .= get_string('totalpriceTTC', 'local_shop');
            $str .= '</th>';
            $str .= '</tr>';
        } else {
            $q = (!empty($SESSION->shoppingcart->order)) ? $SESSION->shoppingcart->order[$shortname] : $q;
            $catalogitem = $this->thecatalog->get_product_by_shortname($shortname);
            $str .= '<tr valign="top">';
            $str .= '<td width="60%" align="left" class="c0">';
            $str .= $catalogitem->name;
            $str .= '<div class="shop-bill-abstract">';
            if (!empty($options['description'])) {
                $str .= $catalogitem->description;
            }
            if (!empty($options['notes'])) {
                $str .= '<br/>'.$catalogitem->notes;
            }
            $str .= '</div>';
            $str .= '</td>';
            $str .= '<td width="20%" align="left" class="c1">';
            $str .= $catalogitem->code;
            $str .= '</td>';
            $str .= '<td width="8%" align="left" class="c2">';
            $str .= sprintf('%.2f', $catalogitem->get_taxed_price($q));
            $str .= '</td>';
            $str .= '<td width="4%" class="c3">';
            $str .= $q;
            $str .= '</td>';
            $str .= '<td width="8%" align="right" style="text-align:right" class="lastcol">';
            $str .= sprintf('%.2f', $catalogitem->get_taxed_price($q) *  $q);
            $str .= '</td>';
            $str .= '</tr>';
        }

        return $str;
    }

    /**
     * Prints an order line
     * @param objectref $theCatalog the whole catalog reference
     * @param string $shortname the product short code
     * @param int $q quantity
     * @param array $options
     * @param boolean $return if true returns a string
     */
    function bill_line($billitem) {
        global $SESSION;

        $str = '';

        $q = $billitem->quantity;
        $str .= '<tr valign="top">';
        $str .= '<td width="60%" align="left" class="c0">';
        $str .= $billitem->abstract;
        $str .= '<div class="shop-bill-abstract">';
        if (!empty($options['description'])) {
            $str .= $billitem->description;
        }
        $str .= '</div>';
        $str .= '</td>';
        $str .= '<td width="20%" align="left" class="c1">';
        $str .= $billitem->itemcode;
        $str .= '</td>';
        $str .= '<td width="8%" align="left" class="c2">';
        $str .= sprintf('%.2f', $billitem->unitcost);
        $str .= '</td>';
        $str .= '<td width="4%" class="c3">';
        $str .= $q;
        $str .= '</td>';
        $str .= '<td width="8%" align="right" style="text-align:right" class="lastcol">';
        $str .= sprintf('%.2f', $billitem->totalprice);
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function full_order_totals($bill = null) {
        global $SESSION, $CFG;

        $this->check_context();

        $config = get_config('local_shop');

        if (!is_null($bill)) {
            $taxedtotal = $bill->amount;
            $finaltaxedtotal = $bill->amount;
            $finaluntaxedtotal = $bill->untaxedamount;
            $finaltaxestotal = $bill->taxes;
            $discount = 0;
            $shippingtaxedvalue = 0;
            $discountrate = shop_calculate_discountrate_for_user($taxedtotal, $this->context, $reason);

            foreach ($bill->items as $bi) {
                if ($bi->itemcode == '_SHIPPING_') {
                    $shippingtaxedvalue = 0 + $bi->totalprice;
                }
                if ($bi->itemcode == '_DISCOUNT_') {
                    $discount = 0 + $bi->totalprice;
                }
                $finalshippedtaxedtotal = $finaltaxedtotal + $shippingtaxedvalue;
            }
        } else {
            $taxedtotal = $SESSION->shoppingcart->taxedtotal;
            $discountrate = shop_calculate_discountrate_for_user($taxedtotal, $this->context, $reason);
            $finaltaxedtotal = $SESSION->shoppingcart->finaltaxedtotal;
            $finaluntaxedtotal = $SESSION->shoppingcart->finaluntaxedtotal;
            $finaltaxestotal = @$SESSION->shoppingcart->finaltaxestotal;
            $discount = $SESSION->shoppingcart->discount;
            $shippingtaxedvalue = 0 + @$SESSION->shoppingcart->shipping->taxedvalue;
            $finalshippedtaxedtotal = $SESSION->shoppingcart->finalshippedtaxedtotal;
        }

        $str = '';
        $str .= '<table cellspacing="5" class="generaltable" width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<th colspan="3" class="cell c0">';
        $str .= get_string('totals', 'local_shop');
        $str .= '</th>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0">';
        $str .= get_string('subtotal', 'local_shop');
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 lastcol">';
        $str .= sprintf("%0.2f", round($taxedtotal, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';
    
        if ($discountrate) {
            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="40%" class="shop-totaltitle ratio cell c1">';
            $str .= get_string('discount', 'local_shop').' :';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="shop-totals ratio cell c2">';
            $str .= '<b>-'.$discountrate.'%</b>';
            $str .= '</td>';
            $str .= '</tr>';

            /*
            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="40%" class="shop-totaltitle cell c1">';
            $str .= get_string('totaldiscounted', 'local_shop').' :';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" style="text-align:right" class="shop-totals cell c2">';
            $str .= sprintf("%0.2f", round($finaltaxedtotal, 2)).'&nbsp;'.shop_currency($theBlock, 'symbol').'&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';
            */
        }
    
        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0">';
        $str .= get_string('untaxedsubtotal', 'local_shop');
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 lastcol">';
        $str .= sprintf("%0.2f", round($finaluntaxedtotal, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0 shop-taxes">';
        $str .= get_string('taxes', 'local_shop');
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 lastcol shop-taxes">';
        $str .= sprintf("%0.2f", round($finaltaxestotal, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0 shop-totaltitle topay">';
        $str .= '<b>'.get_string('finaltotalprice', 'local_shop').'</b>:';
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 shop-total topay">';
        if (empty($config->hasshipping)) {
            $str .= '<b>'.sprintf("%0.2f", round($finaltaxedtotal, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'</b>&nbsp;';
        } else {
            $str .= '<b>'.sprintf("%0.2f", round($finaltaxedtotal + $shippingtaxedvalue), 2).'&nbsp;'.$this->theshop->get_currency('symbol').'</b>&nbsp;';
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
            $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 shop-totals">';
            $str .= sprintf("%0.2f", round($shippingtaxedvalue, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';

            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0 shop-totaltitle topay">';
            $str .= '<b>'.get_string('finaltotalprice', 'local_shop').'</b>:';
            $str .= '</td>';
            $str .= '<td width="40%" class="cell c1">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 shop-total topay">';
            $str .= '<b>'.sprintf("%0.2f", round($finalshippedtaxedtotal, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'</b>&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '</table>';

        return $str;
    }

    function full_order_taxes(&$bill = null) {
        global $SESSION, $CFG, $DB, $OUTPUT;

        $this->check_context();

        if (!empty($bill)) {
            $taxes = $bill->taxelines;
        } else {
            $taxes = $SESSION->shoppingcart->taxes;
        }

        $str = '';

        if ($taxlines = $taxes) {

            $str .= $OUTPUT->heading(get_string('taxes', 'local_shop'), 2, '', true);

            $str .= '<table cellspacing="5" class="generaltable" width="100%">';

            $str .= '<tr class="shop-tax" valign="top">';
            $str .= '<th align="left" class="cell c0">';
            $str .= get_string('taxname', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="left" class="cell c1">';
            $str .= get_string('taxratio', 'local_shop');
            $str .= '</th>';
            $str .= '<th align="right" style="text-align:right" class="cell c2 lastcoll" style="text-align:right">';
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
                $str .= '<td align="right" style="text-align:right" class="cell c2 lastcoll">';
                $str .= sprintf(round($tamount, 2)).'&nbsp;'.$this->theshop->get_currency('symbol');
                $str .= '</td>';
                $str .= '</tr>';
            }
            $str .= '</table>';
        }

        return $str;
    }

    /**
     * prints the payment block on GUI
     *
     */
    function payment_block() {
        global $SESSION, $OUTPUT, $USER, $CFG;

        $config = get_config('local_shop');
        $this->check_context();

        include_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

        $systemcontext = context_system::instance();

        $str = $OUTPUT->heading(get_string('paymentmethod', 'local_shop'));
        $str .= '<table width="100%" id="shop-paymodes">';
        $str .= '<tr>';
        $str .= '<td valign="top" colspan="1">';
        $str .= get_string('paymentmode', 'local_shop');
        $str .= '<sup>*</sup>:';
        $str .= '</td></tr>';
        $str .= '<tr><td valign="top" align="left">';

        // Checking  paymodes availability and creating radios
        if ($SESSION->shoppingcart->finalshippedtaxedtotal == 0) {
            $str .= '<input type="hidden" name="paymode" value="freeorder" />';
            $str .= '<em>'.get_string('freeordersignal', 'local_shop').'</em>';
        } else {
            $paymodes = get_list_of_plugins('/local/shop/paymodes');

            \local_shop\Shop::expand_paymodes($this->theshop);

            foreach ($paymodes as $var) {
                $isenabledvar = "enable$var";

                $paymodeplugin = shop_paymode::get_instance($theBlock, $var);

                // user must be allowed to use non immediate payment methods.
                if (!$paymodeplugin->is_instant_payment()) {
                    if (!has_capability('local/shop:paycheckoverride', $this->context) && !has_capability('local/shop:usenoninstantpayments', $this->context)) {
                        continue;
                    }
                }

                // if test payment, check if we are logged in and admin, or logged in from an admin behalf

                if (($var == 'test') && (!$config->test)) {
                    if (!isloggedin()) continue;

                    if (!empty($USER->realuser)) {
                        $isrealadmin = has_capability('moodle/site:config', $systemcontext, $USER->realuser);
                    } else {
                        $isrealadmin = false;
                    }
                    if (!is_siteadmin() && !$isrealadmin) continue;
                }

                $check = $this->theshop->{$isenabledvar};

                if ($check) {
                    // set default paymode as first available.
                    if (empty($SESSION->shoppingcart->paymode)) {
                        $default = (empty($this->theshop->defaultpaymode)) ? $var : $this->theshop->defaultpaymode;
                        $SESSION->shoppingcart->paymode = $default ;
                        $paymode = $default;
                    } else {
                        $paymode = $SESSION->shoppingcart->paymode;
                    }
                    $checked = ($paymode == $var) ? 'checked="checked" ' : '';
                    $str .= '<input type="radio" name="paymode" value="'.$var.'" '.$checked.' /> <em>';
                    $str .= get_string($isenabledvar.'2', 'shoppaymodes_'.$var);
                    $str .= '</em><br/>';
                }
            }
        }
        $str .= '</td></tr></table>';

        return $str;
    }

    /**
     *
     */
    function order_short() {
        global $CFG, $SESSION, $DB;

        $this->check_context();

        $str = '';

        $str .= '<div class="shop-order-short">';
        $str .= '<table width="100%">';

        $str .= '<tr>';
        $str .= '<td>';
        $str .= get_string('transactionid', 'local_shop');
        $str .= '</td><td>';
        $str .= $SESSION->shoppingcart->transid;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td>';
        $str .= get_string('untaxedtotal', 'local_shop');
        $str .= '</td><td>';
        $str .= sprintf('%.2f', $SESSION->shoppingcart->untaxedtotal).' '.$this->theshop->get_currency('symbol');
        $str .= '</td>';
        $str .= '</tr>';
        if (!empty($SESSION->shoppingcart->taxes)) {
            foreach ($SESSION->shoppingcart->taxes as $taxcode => $taxsum) {
                $tax = $DB->get_record('local_shop_tax', array('id' => $taxcode));
                $str .= '<tr>';
                $str .= '<td class="shop-tax-line">';
                $str .= get_string('taxes', 'local_shop').': '.$tax->title;
                $str .= '</td><td class="shop-tax-line">';
                $str .= sprintf('%.2f', $taxsum).' '.$this->theshop->get_currency('symbol');
                $str .= '</td>';
                $str .= '</tr>';
            }
        }

        $discountrate = shop_calculate_discountrate_for_user($SESSION->shoppingcart->taxedtotal, $this->context, $reason);
        if ($discountrate) {
            $str .= '<tr>';
            $str .= '<td>';
            $str .= get_string('discount', 'local_shop');
            $str .= '</td><td>';
            $str .= sprintf('%.2f', $SESSION->shoppingcart->taxedtotal * ($discountrate / 100)).' '.$this->theshop->get_currency('symbol'); // taxed value
            $str .= '</td>';
            $str .= '</tr>';
        }
        if (!empty($SESSION->shoppingcart->shipping)) {
            $str .= '<tr>';
            $str .= '<td>';
            $str .= get_string('shipping', 'local_shop');
            $str .= '</td><td>';
            $str .= sprintf('%.2f', $SESSION->shoppingcart->shipping->value).' '.$this->theshop->get_currency('symbol');
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '<tr>';
        $str .= '<td>';
        $str .= get_string('finaltotal', 'local_shop');
        $str .= '</td><td>';
        $str .= sprintf('%.2f', 0 + $SESSION->shoppingcart->finalshippedtaxedtotal).' '.$this->theshop->get_currency('symbol');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    function field_start($legend, $class) {
        global $OUTPUT;

        $str = '';
        $str .= '<fieldset class="'.$class."\">\n";
        $str .= '<legend>'.$legend."</legend>\n";

        return $str;
    }

    function field_end() {
        return '</field></fieldset>';
    }

    /**
     *
     * @see users.php
     */
    function seat_roles_assignation_form(&$catalogentry, &$requiredroles, $shortname, $q) {

        $str = '';

        $str .= '<fieldset>';
        $str .= '<legend><h2>'.get_string('seatassignation', 'local_shop', $q).' : '.$catalogentry->name.'</h2></legend>';

        $colwidth = floor(100 / (2 + count($requiredroles)));
        $str .= '<table width="100%" class="shop-role-assignations">';
        $str .= '<tr valign="top">';
        foreach ($requiredroles as $role) {
            $str .= '<td width="'.$colwidth.'%">';
            $str .= '<div id="'.$role.'list'.$shortname.'">';
            $str .= $this->role_list($role, $shortname, $q);
            $str .= '</div>';
            $str .= '</td>';
        }
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    /**
     * the common case action form at bottom of the step form.
     * some options may add additional output depending on the purchase step.
     * @param string $view the current view
     * @param array $options additional information as an array
     */
    function action_form($view, $options) {

        $this->check_context();

        if (!array_key_exists('nextstring', $options)) {
            $options['nextstring'] = 'next';
        }

        $actionurl = new moodle_url('/local/shop/front/view.php');

        $str = '';

        $str .= '<p align="center">';
        if (empty($options['inform'])) {
            $str .= '<form name="driverform" action="'.$actionurl.'">';
        }
        $str .= '<input type="hidden" name="view" value="'.$view.'" />';
        $str .= '<input type="hidden" name="shopid" value="'.$this->theshop->id.'" />';
        $str .= '<input type="hidden" name="blockid" value="'.(0 + @$this->theblock->id).'" />';
        if (!empty($options['transid'])) {
            // In some cases we need explicit transmission of the transaction id.
            $str .= '<input type="hidden" name="transid" value="'.$options['transid'].'" />';
        }
        $str .= '<input type="hidden" name="what" value="navigate" />';
        $str .= '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        if (empty($options['hideback'])) {
            $str .= '<input type="submit" name="back" value="'.get_string('previous', 'local_shop').'" />';
        }
        if (empty($options['hidenext'])) {
            $str .= '&nbsp;<input title="'.@$options['overtext'].'" type="submit" id="next-button" name="go" value="'.get_string($options['nextstring'], 'local_shop').'" '.@$options['nextdisabled'].' style="'.@$options['nextstyle'].'" />';
        }
        if (empty($options['inform'])) {
            $str .= '</form>';
        }
        $str .= '</p>';

        return $str;
    }

    /**
     * Prints a form that collects all customer specific data required by the purchased
     * products.
     * @return an HTML string with the form
     */
    function customer_requirements(&$errors) {
        global $SESSION, $CFG;

        $this->check_context();

        $str = '';

        
        // Samples for json programming
        /*
        $test = array(
            array('field' => 'coursename',
                  'label' => 'cours',
                  'type' => 'textfield',
                  'desc' => 'Nom du cours',
                  'attrs' => array('size' => 80)),
             array('field' => 'description',
                   'label' => 'Description',
                   'type' => 'textarea',
                   'desc' => 'Descriotion courte'),
             array('name' => 'template',
                   'label' => 'Modele',
                   'type' => 'select',
                   'desc' => 'Modele de cours',
                   'options' => array('MOD1' => 'Modele 1', 'MOD2' => 'Modele 2')));

            echo json_encode($test);
        */

        if (!empty($SESSION->shoppingcart->order)) {
            $str .= '<form name="shop-requirements">';
            $str .= '<input type="hidden" name="view" value="purchaserequ">';
            $str .= '<input type="hidden" name="id" value="'.$this->theshop->id.'">';
            $str .= '<input type="hidden" name="blockid" value="'.$this->theblock->id.'">';
            $str .= '<input type="hidden" name="what" value="collect">';
            $str .= '<div id="shop-requirement-list">';
            $str .= '<div id="shop-requirement-caption">';
            $str .= get_string('requireddatacaption', 'local_shop');
            $str .= '</div>';
            foreach ($SESSION->shoppingcart->order as $itemname => $itemcount) {
                $product = $this->thecatalog->get_product_by_shortname($itemname);
                $requireddata = $product->requireddata; // Take care, result of magic _get() is not directly testable.
                $requirements = array_values((array)json_decode($requireddata));
                if (!empty($requirements)) {
                    $str .= '<div class="shop-product-requ">';
                    $str .= '<div class="shop-product-name">'.$product->name.'</div>';
                    for ($i = 0; $i < $itemcount ; $i++) {
                        $str .= '<div class="shop-product-requirements">';
                        $str .= '<div class="shop-product-requirement">'.get_string('instance', 'local_shop', $i + 1).'</div>';
                        foreach ($requirements as $requ) {
                            $reqobj = (object)$requ;

                            $attributes = '';
                            if (!empty($reqobj->attrs)) {
                                foreach($reqobj->attrs as $key => $value) {
                                    $attributes .= " {$key}=\"{$value}\" ";
                                }
                            }

                            $str .= '<div class="shop-requirement">';
                            $inputclass = '';
                            if (!empty($errors[$itemname][$reqobj->field][$i])) {
                                $str .= '<div class="shop-requ-error">';
                                $str .= $errors[$itemname][$reqobj->field][$i];
                                $str .= '</div>';
                                $inputclass = 'class="shop-requ-input-error" ';
                            }
                            $str .= '<div class="requ-label">';
                            $str .= $reqobj->label;
                            $str .= '</div>'; // Closing requ-label.
                            switch ($reqobj->type) {
                                case 'text':
                                case 'textfield':
                                    $str .= '<div class="requ-param">';
                                    $str .= '<input type"text" name="'.$itemname.'/'.$reqobj->field.$i.'" id="id-'.$reqobj->field.$i.'" value="'.@$SESSION->shoppingcart->customerdata[$itemname][$reqobj->field][$i].'" '.$attributes.' '.$inputclass.'/>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= @$reqobj->desc;
                                    $str .= '</div>'; // Closing requ-desc.
                                    $str .= '</div>'; // Closing requ-param.
                                    break;

                                case 'textarea':
                                    $str .= '<div class="requ-param">';
                                    $str .= '<textarea name="'.$itemname.'/'.$reqobj->field.$i.'" id="id-'.$reqobj->field.$i.'" '.$attributes.' '.$inputclass.'>'.@$SESSION->shoppingcart->customerdata[$itemname][$reqobj->field][$i].'</textarea>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= $reqobj->desc;
                                    $str .= '</div>';
                                    $str .= '</div>';
                                    break;

                                case 'checkbox':
                                    $checked = (@$SESSION->shoppingcart->customerdata[$itemname][$reqobj->field][$i]) ? 'checked="checked"' : '';
                                    $str .= '<div class="requ-param">';
                                    $str .= '<input type="checkbox" name="'.$itemname.'/'.$reqobj->field.$i.'" id="id-'.$reqobj->field.$i.'" '.$checked.' '.$attributes.' '.$inputclass.'>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= $reqobj->desc;
                                    $str .= '</div>';
                                    $str .= '</div>';
                                    break;

                                case 'select':
                                    $str .= '<div class="requ-param">';
                                    $str .= '<select name="'.$itemname.'/'.$reqobj->field.$i.'" id="id-'.$reqobj->field.$i.'" '.$attributes.' '.$inputclass.'>';
                                    $options = $reqobj->options;
                                    if ($options) {
                                        foreach ($options as $optkey => $optvalue) {
                                        $selected = ($optkey == @$SESSION->shoppingcart->customerdata[$itemname][$reqobj->field][$i]) ? 'selected' : '';
                                            $str.= '<option name="'.$optkey.'">'.$optvalue.'</option>';
                                        }
                                    }
                                    $str .= '</select>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= $reqobj->desc;
                                    $str .= '</div>';
                                    $str .= '</div>';
                                    break;

                                default:
                                    $str .= '<div class="shop-error">'.get_string('errorrequirementfieldtype', 'local_shop', "{$itemname}|{$reqobj->field}|{$reqobj->type}").'</div>';
                            }
                            $str .= '</div>'; // Closing shop-requirement.
                        }
                        $str .= '</div>';
                    }
                }
            }
            $extraclass = (empty($SESSION->shoppingcart->customerdata)) ? 'unsaved' : '';
            $str .= '<div id="requ-submit" class="'.$extraclass.'">';
            $str .= '<input type="submit" name="go_btn" value="'.get_string('saverequs', 'local_shop').'" style="width:200px" />';
            $str .= '</div>'; // Closes requ-submit.
            $str .= '</div>';
            $str .= '</form>';
        }

        return $str;
    }

    /**
     * This function checks for any product having an EULA url defined. 
     * If there are some, an EULA cover div will ask customer to agree with EULA
     * conditions, before accedding to the order confirm form.
     *
     * @param array $catalog catalog structure for product line reference 
     * @param array $bill
     */
    function check_and_print_eula_conditions() {
        global $CFG, $SESSION, $SITE;

        $eulastr = '';

        $eula = ''.$this->theshop->eula;

        foreach (array_keys($SESSION->shoppingcart->order) as $shortname) {
            $ci = $this->thecatalog->get_product_by_shortname($shortname);
            if (!($ci->eula === '')) {
                $eula .= '<h3>'.$ci->name.'</h3>';
                $eula .= '<p>'.$ci->eula.'</p>';
            }
        }

        if (!empty($eula)) {
            $confirmstr = get_string('confirm', 'local_shop');
            $eulastr .= '<div id="euladiv">';
            $eulastr .= '<h2>'.get_string('eulaheading', 'local_shop').'</h3>';
            $eulastr .= '<p><b>'.get_string('eula_help', 'local_shop', $SITE->fullname).'</b></p>';
            $eulastr .= '<div id="eulas-panel">';
            $eulastr .= $eula;
            $eulastr .= '<center>';
            $eulastr .= '<form name="eulaform" id="eula-form-validate">';
            $eulastr .= '<input type="checkbox" name="agreeeula" id="agreeeula" value="1">  '.get_string('eulaagree', 'local_shop');
            $eulastr .= '<br/><br/><input type="button" name="accept_btn" value="'.$confirmstr.'" onclick="accept_eulas(this)">';
            $eulastr .= '</form>';
            $eulastr .= '</center>';
            $eulastr .= '</div>';
            $eulastr .= '</div>';
        }
        return $eulastr;
    }

    function login_form() {

        /**
        $str = '<div id="shop-loginbox">';
        $str .= '<table cellspacing="3" width="100%">';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('login');
        $str .= ':</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="" name="login" size="20" maxlength="20" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('password');
        $str .= ':</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text" class="" name="login" size="20" maxlength="20" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</div>';
        */

        $str = '<div id="shop-loginbox">';
        $thisurl = new moodle_url('/local/shop/front/view.php', array('view' => 'customer', 'id' => $this->theshop->id, 'blockid' => (0 + @$this->theblock->instance->id)));
        $loginurl = new moodle_url('/login/index.php', array('wantsurl' => $thisurl));
        $str .= '<a href="'.$loginurl.'"><input type="button" class="shop-login-button" name="gologin" value="'.get_string('signin', 'local_shop').'" ></a>';
        $str .= '</div>';
        return $str;
    }

    function my_total_link() {
        $totalurl = new moodle_url('/local/shop/front/view.php', array('shopid' => $this->theshop->id));
        return '<center><div id="shop-total-link"><a href="'.$totalurl.'#total"><input type="button" value="'.get_string('mytotal', 'local_shop').'" /></a></div><center>';
    }

    function invoice_header(&$aFullBill) {
        global $OUTPUT;

        $config = get_config('local_shop');

        $realized = array(SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PARTIAL);
        
        if (!in_array($aFullBill->status, $realized)) {
        $headerstring = get_string('ordersheet', 'local_shop');
        print_string('ordertempstatusadvice', 'local_shop');
        } else {
            if (empty($aFullBill->idnumber)) {
                $headerstring = get_string('proformabill', 'local_shop');
            } else {
                $headerstring = get_string('bill', 'local_shop');
            }
        }

        $str = '';

        $str .= '<table>';
        if (!empty($aFullBill->withlogo)) {
            $str .= '<tr>';
            $str .= '<td><img src="'.$OUTPUT->pix_url('logo', 'theme').'"></td>';
            $str .= '<td align="right"></td>';
            $str .= '</tr>';
        }

        $str .= '<tr valign="top">';
        $str .= '<td colspan="2" align="center">';
        $str .= $OUTPUT->heading($headerstring, 1);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('transactioncode', 'local_shop').':</b><br />';
        $str .= '<code style="background-color : #E0E0E0">'.$aFullBill->transactionid.'</code><br />';
        $str .= '<span class="smaltext">'.get_string('providetransactioncode', 'local_shop').'</span>';
        $str .= '</td>';
        $str .= '<td width="40%" align="right" rowspan="5" class="order-preview-seller-address">';
        $str .= '<b>'.get_string('on', 'local_shop').':</b> '.userdate($aFullBill->emissiondate).'<br />';
        $str .= '<br />';
        $str .= '<b>'.$config->sellername.'</b><br />';
        $str .= '<b>'.$config->selleraddress.'</b><br />';
        $str .= '<b>'.$config->sellerzip.' '.$config->sellercity.'</b><br />';
        $str .= $config->sellercountry;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('customer', 'local_shop').': </b> '.$aFullBill->customer->lastname.' '.$aFullBill->customer->firstname;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('city').': </b>';
        $str .= $aFullBill->customer->zip.' '.$aFullBill->customer->city;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('country').': </b> '.strtoupper($aFullBill->customer->country);
        $str .= '</td>';
        $str .= '<td>';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('email').': </b>'.$aFullBill->customer->email;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td colspan="2">';
        $str .= '&nbsp;<br />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td colspan="2" class="sectionHeader">';
        $str .= $OUTPUT->heading(get_string('order', 'local_shop'), 2);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';

        return $str;
    }
}