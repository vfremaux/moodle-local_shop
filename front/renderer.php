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

require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/renderer.php');

use local_shop\Category;
use local_shop\Catalog;
use local_shop\Tax;

class shop_front_renderer extends local_shop_base_renderer {

    protected $context;

    protected $view;

    /**
     * prints a purchase procedure progression bar
     * @param string $progress the progress state
     */
    public function progress($progress) {
        global $SESSION;

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

            $icon = $stepicons[trim($step)];
            if (!empty($SESSION->shoppingcart->norequs) && ($icon == 'CONFIGURE') && ($iconstate == '_on')) {
                $iconstate = '_dis';
            }
            if (empty($SESSION->shoppingcart->seats) && ($icon == 'USERS') && ($iconstate == '_on')) {
                $iconstate = '_dis';
            }

            $stepicon = $this->output->pix_url(current_language().'/'.$icon.$iconstate, 'local_shop');
            $str .= '<img src="'.$stepicon.'" />&nbsp;';
        }
        $str .= '</center>';
        $str .= '</div>';

        return $str;
    }

    /**
     * Prints a summary form for the purchase
     */
    public function order_totals() {
        global $SESSION;

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

        $discountrate = $this->theshop->calculate_discountrate_for_user($amount, $this->context, $reason);

        if ($discountrate) {
            $str .= '<tr>';
            $str .= '<td colspan="2">';
            $str .= $reason;
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
            $str .= '<input type="checkbox"
                            name="shipping"
                            value="1"
                            '.sprintf('%.2f', $shipchecked).' /> '.get_string('askforshipping', 'local_shop');
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
    public function printable_bill_link(&$bill) {
        global $DB;

        $str = '';

        $states = array(SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PREPROD);
        $popup = (in_array($bill->status, $states)) ? 'bill' : 'order';

        $popupurl = new moodle_url('/local/shop/front/'.$popup.'.popup.php');
        $str .= '<form name="bill" action="'.$popupurl.'" target="_blank" />';
        $str .= '<input type="hidden" name="transid" value="'.$bill->transactionid.'" />';
        $str .= '<input type="hidden" name="billid" value="'.$bill->id.'">';
        $str .= '<input type="hidden" name="shopid" value="'.$this->theshop->id.'\">';
        $str .= '<input type="hidden" name="blockid" value="'.(0 + @$this->theblock->id).'\">';
        $str .= '<div id="shop-printable-link">'; // Table.
        $str .= '<div class="shop-row">'; // Row.
        $str .= '<div class="shop-cell">'; // Cell.
        $str .= '<br /><br /><br /><br />';

        $params = array('shopid' => $this->theshop->id,
                        'blockid' => (0 + @$this->theblock->id),
                        'billid' => $bill->id,
                        'transid' => $bill->transactionid);

        $billurl = new moodle_url('/local/shop/front/'.$popup.'.popup.php', $params);
        $customerid = $DB->get_field('local_shop_bill', 'customerid', array('id' => $bill->id));

        if ($userid = $DB->get_field('local_shop_customer', 'hasaccount', array('id' => $customerid))) {
            $billuser = $DB->get_record('user', array('id' => $userid));
            $ticket = ticket_generate($billuser, 'immediate access', $billurl);
            $options = array('ticket' => $ticket);
            $str .= $this->output->single_button('/login/index.php', get_string('printbill', 'local_shop'), 'post',  $options);
        }

        $str .= '</div>'; // Cell.
        $str .= '</div>'; // Row.
        $str .= '</div>'; // Table.
        $str .= '</form>';

        return $str;
    }

    public function shop_return_button($theshop) {

        $str = '';
        $options['id'] = $theshop->id;
        $str .= $this->output->single_button('/local/shop/front/view.php', get_string('backtoshop', 'local_shop'), 'post',  $options);

        return $str;
    }

    /**
     * Prints the customer info summary
     * @param object $bill
     */
    public function customer_info(&$bill = null) {
        global $SESSION;

        $usedistinctinvoiceinfo = false;
        if (empty($bill)) {
            if (!empty($SESSION->shoppingcart->usedistinctinvoiceinfo)) {
                $ci = $SESSION->shoppingcart->invoiceinfo;
                $usedistinctinvoiceinfo = true;
            } else {
                $ci = $SESSION->shoppingcart->customerinfo;
            }

            $emissiondate = $SESSION->shoppingcart->emissiondate = time();
            $transid = $SESSION->shoppingcart->transid;
        } else {
            if (empty($bill->invoiceinfo)) {
                $ci = (array)$bill->customer;
            } else {
                $ci = (array)unserialize($bill->invoiceinfo);
                $usedistinctinvoiceinfo = true;
            }
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

        if ($usedistinctinvoiceinfo) {
            $str .= '<tr><td width="60%" valign="top">';
            $str .= '<b>'.get_string('department').':</b> '.@$ci['department'];
            $str .= '</td><td width="40%" valign="top">';
            $str .= '</td></tr>';
        }

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

        if ($usedistinctinvoiceinfo) {
            $str .= '<tr><td width="60%" valign="top">';
            $str .= '<b>'.get_string('vatcode', 'local_shop').':</b> '.@$ci['vatcode'];
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
        $formurl = new moodle_url('/local/shop/front/view.php');
        $str .= '<form name="confirmation" method="POST" action="'.$formurl.'" style="display : inline">';
        $str .= '<table style="display : block ; visibility : visible" width="100%"><tr><td align="center">';
        if (!empty($disabled)) {
            $errstr = get_string('requiredataadvice', 'local_shop');
            $str .= '<br><span id="disabled-advice-span" class="error">'.$errstr.'</span><br/>';
        }
        $str .= '<input type="button" name="go_confirm" value="'.$confirmstr.'" onclick="send_confirm();" '.$disabled.' />';
        $str .= '</td></tr></table>';
        $str .= '</form>';
        $str .= '</center>';

        return $str;
    }

    /**
     * prints tabs for js activation of the category panel
     */
    public function category_tabs(&$categories, $selected, $parent, $isactive, $isvisiblebranch) {

        $str = '';

        $rows[0] = array();
        foreach ($categories as $cat) {
            $params = array('view' => 'shop',
                            'category' => $cat->id,
                            'shopid' => $this->theshop->id,
                            'blockid' => $this->theblock->id);
            $categoryurl = new moodle_url('/local/shop/front/view.php', $params);
            $rows[0][] = new tabobject('catli'.$cat->id, $categoryurl, format_string($cat->name));
        }

        if ($isvisiblebranch) {
            $visibleclass = 'shop-category-visible';
        } else {
            $visibleclass = 'shop-category-hidden';
        }

        $str .= '<div class="'.$visibleclass.'" id="shop-cat-children-of-'.$parent.'">';

        if ($isactive) {
            $str .= print_tabs($rows, $selected, '', '', true);
        } else {
            $str .= print_tabs($rows, null, null, null, true);
        }
        $str .= '</div>';

        return $str;
    }

    /**
     * prints a full catalog on screen
     * @param objectref $theblock the shop block instance
     * @param array $catgories the full product line extracted from Catalog. 
     * Only visible categories are provided.
     */
    public function catalog(&$categories) {
        global $SESSION;

        $this->check_context();

        if (empty($categories)) {
            return $this->output->notification(get_string('nocats', 'local_shop'));
        }

        $str = '';

        // Make a comma list of all category ids.
        $catidsarr = array();
        foreach ($categories as $cat) {
            $catidsarr[] = $cat->id;
        }
        $catids = implode(',', $catidsarr);

        $withtabs = (@$this->theshop->printtabbedcategories == 1);

        if ($withtabs) {
            $categoryid = optional_param('category', null, PARAM_INT);

            // Get the tree branch up to the category starting from the top.
            if ($categoryid) {
                $category = new Category($categoryid);
                $branch = array_reverse($category->get_branch());
            } else {
                $branch = array_reverse(Category::get_first_branch($this->thecatalog->id, 0));
            }

            // Render all upper branch choices, with preselected items in the active branch.
            while ($catid = array_shift($branch)) {
                $cat = new Category($catid);
                if ($cat->visible) {
                    $params = array('catalogid' => $this->thecatalog->id, 'parentid' => $cat->parentid, 'visible' => 1);
                    $levelcategories = Category::get_instances($params, 'sortorder');
                    $iscurrent = $cat->id == $categoryid;
                    $str .= $this->category_tabs($levelcategories, 'catli'.$cat->id, $cat->parentid, $iscurrent, true);
    
                    // Print childs.
                    $attrs = array('catalogid' => $this->thecatalog->id, 'parentid' => $cat->id);
                    if ($subs = Category::get_instances($attrs, 'sortorder')) {
                        $str .= $this->category_tabs($subs, null, $cat->id, false, $cat->id == $categoryid);
                    }
                }
            }
        }

        // Print catalog product line on the active category if tabbed.
        $catids = array_keys($categories);
        $category = optional_param('category', $catids[0], PARAM_INT);

        $c = 0;
        foreach ($levelcategories as $c) {
            $cat = $categories[$c->id];
            if ($withtabs && ($category != $cat->id)) {
                continue;
            }
            if (!isset($firstcatid)) {
                $firstcatid = $cat->id;
            }

            if ($withtabs) {
                $str .= '<div class="shopcategory" id="category'.$cat->id.'" />';
            } else {
                $cat->level = 1;
                $str .= $this->output->heading($cat->name, $cat->level);
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

        return $str;
    }

    public function product_block(&$product) {
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
            $str .= '<h3 class="shop-front-partof">'.format_string($product->name).'</h3>';
        } else {
            $str .= '<h2>'.format_string($product->name).'</h2>';
            if ($product->description) {
                $str .= '<div class="shop-front-description">'.format_text($product->description).'</div>';
            }
            if (!$product->available) {
                $str .= '<div class="shop-not-available">'.get_string('notavailable', 'local_shop').'</div>';
            }
        }

        if ($product->has_leaflet()) {
            $leafleturl = $product->get_leaflet_url();
            $str .= '<div class="shop-front-leaflet">';
            $str .= '<a href="'.$leafleturl.'" target="_blank">'.get_string('leafletlink', 'local_shop').'</a>';
            $str .= '</div>';
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
                    $str .= '<div class="shop-front-pricerange">'.$range.' : </div>';
                    $str .= '<div class="shop-front-price">'.$price.' '.$product->currency.'</div>';
                }
                $str .= '</div>'; // Shop-front-pricelist.
            }

            if ($product->available) {
                $str .= '<div class="shop-front-order">';
                $buystr = get_string('buy', 'local_shop');
                $isdisabled = $product->maxdeliveryquant && ($product->maxdeliveryquant == $product->preset);
                $disabled = ($isdisabled) ? 'disabled="disabled"' : '';
                if ($product->password) {
                    $str .= '<input type="text"
                                   id="ci-pass-'.$product->shortname.'"
                                   value=""
                                   maxlength="8"
                                   size="8"
                                   onkeypress="check_pass_code(\''.$product->shortname.'\', this, event)"
                                   title="'.get_string('needspasscodetobuy', 'local_shop').'" />';
                    $str .= '<div id="ci-pass-status-'.$product->shortname.'" class="shop-pass-state"></div>';
                    $disabled = 'disabled="disabled"';
                }
                $jshandler = 'ajax_add_unit('.$this->theshop->id;
                $jshandler .= ', \''.$product->shortname.'\', \''.$product->maxdeliveryquant.'\')';
                $str .= '<input type="button"
                                id="ci-'.$product->shortname.'"
                                value="'.$buystr.'"
                                onclick="'.$jshandler.'" '.$disabled.' />';
                $str .= '<div class="shop-order-item" id="bag_'.$product->shortname.'">';
                $str .= $this->units($product);
                $str .= '</div>';
                $str .= '</div>'; // Shop-front-order.
            }
            $str .= '</div>'; // Shop-front-refblock.
        }

        $str .= '</div>'; // Shop-front-productdef.
        $str .= '</div>'; // Front-article.

        return $str;
    }

    public function product_set(&$set) {

        $str = '';

        $str .= '<div class="shop-article set">';
        $str .= '<div class="shop-front-setcaption">';
        $str .= '<h2>'.$set->name.'</h2>';
        $str .= '<div class="shop-front-description">'.$set->description.'</div>';
        $str .= '</div>'; // Shop-front-setcaption.

        $str .= '<div class="shop-front-elements">';
        foreach ($set->elements as $element) {
            $element->check_availability();
            $element->noorder = false; // Bundle can only be purchased as a group.
            $element->ispart = true; // Reduced title.
            $str .= $this->product_block($element);
        }
        $str .= '</div>'; // Shop-front-elements.
        $str .= '</div>'; // Shop-article.

        return $str;
    }

    public function product_bundle(&$bundle) {
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
            $linklabel = get_string('leafletlink', 'local_shop');
            $str .= '<div class="shop-front-leaflet"><a href="'.$leafleturl.'" target="_blank">'.$linklabel.'</a></div>';
        }

        $str .= '<div class="shop-front-refblock">';
        $str .= '<div class="shop-front-ref">'.get_string('ref', 'local_shop').' : '.$bundle->code.'</div>';

        foreach ($bundle->elements as $element) {
            $element->check_availability();
            $element->noorder = true; // Bundle can only be purchased as a group.
            $element->ispart = true; // Reduced title.
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
                $str .= '<div class="shop-front-pricerange">'.$range.' : </div>';
                $str .= '<div class="shop-front-price">'.$price.' '.$bundle->currency.'</div>';
            }
            $str .= '</div>'; // Shop-front-pricelist.
        }

        $str .= '<div class="shop-front-order">';
        $buystr = get_string('buy', 'local_shop');
        $disabled = ($bundle->maxdeliveryquant && $bundle->maxdeliveryquant == $bundle->preset) ? 'disabled="disabled"' : '';
        if ($bundle->password) {
            $jshandler = 'check_pass_code(\''.$bundle->shortname.'\', this, event)';
            $str .= '<input type="text"
                           id="ci-pass-'.$bundle->shortname.'"
                           value=""
                           maxlength="8"
                           size="8"
                           onkeypress="'.$jshandler.'"
                           title="'.get_string('needspasscodetobuy', 'local_shop').'" />';
            $str .= '<div id="ci-pass-status-'.$bundle->shortname.'" class="shop-pass-state"></div>';
            $disabled = 'disabled="disabled"';
        }
        $jshandler = 'ajax_add_unit('.$this->theshop->id;
        $jshandler .= ', \''.$bundle->shortname.'\', \''.$bundle->maxdeliveryquant.'\')';
        $str .= '<input type="button"
                        id="ci-'.$bundle->shortname.'"
                        value="'.$buystr.'"
                        onclick="'.$jshandler.'" '.$disabled.' />';
        $str .= '<div class="shop-order-item" id="bag_'.$bundle->shortname.'">';
        $str .= $this->units($bundle);
        $str .= '</div>';
        $str .= '</div>'; // Shop-front-order.
        $str .= '</div>'; // Shop-front-refblock.

        $str .= '</div>'; // Shop-front-productdef.
        $str .= '</div>'; // Shop-article.

        return $str;
    }

    public function units(&$product) {
        global $SESSION, $CFG;

        $this->check_context();

        $unitimage = $product->get_sales_unit_url();
        $tenunitsimage = $product->get_sales_ten_units_url();

        $q = @$SESSION->shoppingcart->order[$product->shortname];
        $packs = floor($q / 10);
        $units = $q % 10;

        $str = '';
        for ($i = 0; $i < 0 + $packs; $i++) {
            $str .= '&nbsp;<img src="'.$tenunitsimage.'" align="middle" />';
        }

        for ($j = 0; $j < 0 + $units; $j++) {
            $str .= '&nbsp;<img src="'.$unitimage.'" align="middle" />';
        }

        if (($i * 10 + $j) > 0) {
            $jshandler = 'Javascript:ajax_delete_unit('.$this->theshop->id.', \''.$product->shortname.'\')';
            $str .= '&nbsp;<a title="'.get_string('deleteone', 'local_shop').'" href="'.$jshandler.'">';
            $str .= '<img src="'.$this->output->pix_url('t/delete').'" valign="center" />';
            $str .= '</a>';
        }

        return $str;
    }

    public function order_detail(&$categories) {
        global $SESSION;

        if (empty($categories)) {
            return;
        }

        $shoppingcart = @$SESSION->shoppingcart;

        $str = '';

        $str .= '<table width="100%" id="orderblock">';

        foreach ($categories as $acategory) {
            if (empty($acategory->products)) {
                continue;
            }
            foreach ($acategory->products as $aproduct) {
                if ($aproduct->isset == PRODUCT_SET) {
                    foreach ($aproduct->elements as $portlet) {
                        $portlet->currency = $this->theshop->get_currency('symbol');
                        $hasshort = !empty($shoppingcart->order[$portlet->shortname]);
                        $portlet->preset = $hasshort ? $shoppingcart->order[$portlet->shortname] : 0;
                        if ($portlet->preset) {
                            $str .= $this->product_total_line($portlet, true);
                        }
                    }
                } else {
                    $portlet = &$aproduct;
                    $portlet->currency = $this->theshop->get_currency('symbol');
                    $hasshort = !empty($shoppingcart->order[$portlet->shortname]);
                    $portlet->preset = $hasshort ? $shoppingcart->order[$portlet->shortname] : 0;
                    if ($portlet->preset) {
                        $str .= $this->product_total_line($portlet, true);
                    }
                }
            }
        }
        $str .= '</table>';

        return $str;
    }

    public function product_total_line(&$product) {
        global $CFG;

        $this->check_context();

        $str = '';

        $ttcprice = $product->get_taxed_price($product->preset, $product->taxcode);
        $product->total = $ttcprice * $product->preset;

        $str .= '<tr id="producttotalcaption_'.$product->shortname.'">';
        $str .= '<td class="shop-ordercaptioncell" colspan="3">';
        $str .= $product->name;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr id="producttotal_'.$product->shortname.'">';
        $str .= '<td class="shop-ordercell">';
        $disabled = ' disabled="disabled" ';
        if ($this->view == 'shop') {
            $jshandler = 'Javascript:ajax_clear_product('.$this->theshop->id;
            $jshandler .= ', '.$this->theblock->id.', \''.$product->shortname.'\')';
            $str .= '<a title="'.get_string('clearall', 'local_shop').'" href="'.$jshandler.'">';
            $str .= '<img src="'.$this->output->pix_url('t/delete').'" />';
            $str .= '</a>';
            $disabled = '';
        }
        $jshandler = 'ajax_update_product('.$this->theshop->id;
        $jshandler .= ', \''.$product->shortname.'\', this, \''.$product->maxdeliveryquant.'\')';
        $str .= '<input type="text"
                        class="order-detail"
                        id="id_'.$product->shortname.'"
                        name="'.$product->shortname.'"
                        value="'.$product->preset.'"
                        size="3"
                        style="width:40px"
                        onChange="'.$jshandler.'" '.$disabled.' >';
        $str .= '</td>';
        $str .= '<td class="shop-ordercell">';
        $str .= '<p>x '.sprintf("%.2f", round($ttcprice, 2)).' '.$product->currency.' : ';
        $str .= '</td>';
        $str .= '<td class="shop-ordercell">';
        $str .= '<input type="text"
                        class="order-detail"
                        id="id_total_'.$product->shortname.'"
                        name="'.$product->shortname.'_total"
                        value="'.sprintf("%.2f", round($product->total, 2)).'"
                        size="6"
                        disabled
                        class="totals" >';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    /**
     * Prints the customer information form
     * @param object $theblock
     * @param object $thecatalog
     */
    public function customer_info_form() {
        global $USER, $DB, $SESSION;

        $shoppingcart = $SESSION->shoppingcart;

        $this->check_context();

        $str = '';

        $checked = (!empty($shoppingcart->usedistinctinvoiceinfo)) ? 'checked="checked"' : '';

        $heading = get_string('customerinformation', 'local_shop');
        $heading .= ' <input type="checkbox"
                         value="1"
                         name="usedistinctinvoiceinfo"
                         onchange="local_toggle_invoiceinfo(this)"
                         '.$checked.' />';
        $heading .= '<span class="tiny-text"> '.get_string('usedistinctinvoiceinfo', 'local_shop').'</span>';
        $str .= $this->output->heading($heading);

        if (isloggedin()) {
            $lastname = $USER->lastname;
            $firstname = $USER->firstname;

            $hasorg = !empty($shoppingcart->customerinfo['organisation']);
            $organisation = $hasorg ? $shoppingcart->customerinfo['organisation'] : $USER->institution;

            $hascity = !empty($shoppingcart->customerinfo['city']);
            $city = $hascity ? $shoppingcart->customerinfo['city'] : $USER->city;

            $hasaddress = !empty($shoppingcart->customerinfo['address']);
            $address = $hasaddress ? $shoppingcart->customerinfo['address'] : $USER->address;

            $haszip = !empty($shoppingcart->customerinfo['zip']);
            $zip = $haszip ? $shoppingcart->customerinfo['zip'] : '';

            $hascountry = !empty($shoppingcart->customerinfo['country']);
            $country = $hascountry ? $shoppingcart->customerinfo['country'] : $USER->country;
            $email = $USER->email;

            // Get potential ZIP code information from an eventual customer record.
            if ($customer = $DB->get_record('local_shop_customer', array('hasaccount' => $USER->id, 'email' => $email))) {
                $zip = $haszip ? $shoppingcart->customerinfo['zip'] : $customer->zip;
                $organisation = $hasorg ? $shoppingcart->customerinfo['organisation'] : $customer->organisation;
                $address = $hasaddress ? $shoppingcart->customerinfo['address'] : $customer->address;
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

        if (!empty($shoppingcart->errors->customerinfo)) {
            foreach (array_keys($shoppingcart->errors->customerinfo) as $f) {
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
        $str .= '<input type="text"
                        name="customerinfo::lastname"
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
                        name="customerinfo::firstname"
                        size="20"
                        onchange="capitalizewords(this)" value="'.$firstname.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        if (!empty($this->theshop->customerorganisationrequired)) {
            $str .= '<tr valign="top">';
            $str .= '<td align="right">';
            $str .= get_string('organisation', 'local_shop');
            $str .= ':</td>';
            $str .= '<td align="left">';
            $str .= '<input type="text"
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
                        name="customerinfo::address"
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
                        name="customerinfo::city"
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
        $this->thecatalog->process_country_restrictions($choices);
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
     * Prints the form for invoicing customer identity
     * @param object $theblock
     * @param object $thecatalog
     */
    public function invoicing_info_form() {
        global $SESSION;

        $this->check_context();

        $str = '';

        $shoppingcart = $SESSION->shoppingcart;

        $institution = @$shoppingcart->invoiceinfo['organisation'];
        $department = @$shoppingcart->invoiceinfo['department'];
        $lastname = @$shoppingcart->invoiceinfo['lastname'];
        $firstname = @$shoppingcart->invoiceinfo['firstname'];
        $email = @$shoppingcart->invoiceinfo['email'];
        $address = @$shoppingcart->invoiceinfo['address'];
        $zip = @$shoppingcart->invoiceinfo['zip'];
        $city = @$shoppingcart->invoiceinfo['city'];
        $country = @$shoppingcart->invoiceinfo['country'];
        $vatcode = @$shoppingcart->invoiceinfo['vatcode'];

        if (!empty($shoppingcart->errors->invoiceinfo)) {
            foreach (array_keys($shoppingcart->errors->invoiceinfo) as $f) {
                $f = str_replace('invoiceinfo::', '', $f);
                $var = "{$f}class";
                $$var = 'shop-error';
            }
        }

        $str .= $this->output->heading(get_string('invoiceinformation', 'local_shop'));

        $str .= '<div id="shop-organisationdata">';
        $str .= '<table cellspacing="3" width="100%">';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('organisation', 'local_shop');
        $str .= ':</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
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
                        name="invoiceinfo::lastname"
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
                       name="invoiceinfo::firstname"
                       size="20"
                       onchange="capitalizewords(this)"
                       value="'.$firstname.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('email');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="invoiceinfo::email"
                        size="50"
                        onchange=""
                        value="'. $email .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('address');
        $str .= '<sup style="color : red">*</sup>: ';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="invoiceinfo::address"
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
                        name="invoiceinfo::city"
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
        $attrs = array('class' => $countryclass);
        $str .= html_writer::select($choices, 'invoiceinfo::country', $country, array('' => 'choosedots'), $attrs);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= get_string('vatcode', 'local_shop');
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="invoiceinfo::vatcode"
                        size="15"
                        value="'. $vatcode .'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="hidden"
                        name="invoiceinfo::plantcode"
                        value="" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    public function participant_row($participant = null) {
        global $CFG;

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
                $pixurl = $this->output->pix_url('i/moodle_host');
                $str .= '<img src="'.$pixurl.'" title="'.get_string('isuser', 'local_shop').'" />';
            } else {
                $pixurl = $this->output->pix_url('new', 'local_shop');
                $str .= '<img src="'.$pixurl.'" title="'.get_string('isnotuser', 'local_shop').'" />';
            }
            $str .= '</td>';
            $str .= '<td align="right">';
            $str .= '<a title="'.get_string('deleteparticipant', 'local_shop').'"
                        href="Javascript:ajax_delete_user(\''.$participant->email.'\')">';
            $str .= '<img src="'.$this->output->pix_url('t/delete').'" /></a>';
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

        $this->check_context();

        static $i = 0;

        $str = '';

        $str .= '<tr>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="lastname_foo_'.$i.'"
                        size="15"
                        disabled="disabled"
                        class="shop-disabled" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="firstname_foo_'.$i.'"
                        size="15"
                        disabled="disabled"
                        class="shop-disabled" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="mail_foo_'.$i.'"
                        size="20"
                        disabled="disabled"
                        class="shop-disabled" />';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="text"
                        name="city_foo_'.$i.'"
                        size="14"
                        disabled="disabled"
                        class="shop-disabled" />';
        $str .= '</td>';
        if (!empty($this->theshop->endusermobilephonerequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text"
                            name="phone2_foo_'.$i.'"
                            size="13"
                            disabled="disabled"
                            class="shop-disabled" />';
            $str .= '</td>';
        }
        if (!empty($this->theshop->enduserorganisationrequired)) {
            $str .= '<td align="left">';
            $str .= '<input type="text"
                            name="institution_foo_'.$i.'"
                            size="13"
                            disabled="disabled"
                            class="shop-disabled" />';
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
        $jshandler = 'ajax_add_user(document.forms[\'participant\'])';
        $label = get_string('addparticipant', 'local_shop');
        $str .= '<input type="button" value="'.$label.'" name="add_button" onclick="'.$jshandler.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</form>';

        return $str;
    }

    public function assignation_row($participant, $role, $shortname) {
        global $CFG;

        $str = '';

        $str .= '<tr>';
        $str .= '<td align="left">';
        $str .= @$participant->lastname;
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= @$participant->firstname;
        $str .= '</td>';
        $str .= '<td align="right">';
        $jshandler = 'Javascript:ajax_delete_assign(\''.$role.'\', \''.$shortname;
        $jshandler .= '\', \''.$participant->email.'\')';
        $str .= '<a href="'.$jshandler.'"><img src="'.$this->output->pix_url('t/delete').'" /></a>';
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
                    $options[$email] = $pt->lastname.' '.$pt->firstname;
                }
            }
        }
        $params = array('' => get_string('chooseparticipant', 'local_shop'));
        $attrs = array('onchange' => 'ajax_add_assign(\''.$role.'\', \''.$shortname.'\', this)');
        $str .= html_writer::select($options, 'addassign'.$role.'_'.$shortname, '', $params, $attrs);

        return $str;
    }

    public function role_list($role, $shortname) {
        global $SESSION;

        $this->check_context();

        $str = '';

        $roleassigns = @$SESSION->shoppingcart->users;

        $str .= $this->output->heading(get_string(str_replace('_', '', $role), 'local_shop'));  // Remove pseudo roles markers.
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
                $product = $this->thecatalog->get_product_by_shortname($itemname);
                $str .= '<tr>';
                $str .= '<td class="short-order-name">';
                $str .= '<span title="'.$product->name.'" alt="'.$product->name.'">'.$product->code.'</span>';
                $str .= '</td>';
                $str .= '<td class="short-order-quantity">'.$SESSION->shoppingcart->order[$itemname].'</td>';
                $str .= '</tr>';
                $str .= '<tr>';
                $desc = shorten_text(strip_tags($product->description), 120);
                $str .= '<td colspan="2"  class="short-order-summary">'.$desc.'</td>';
                $str .= '</tr>';
            }
            $str .= '</table>';
        }

        return $str;
    }

    public function admin_options() {
        global $SESSION;

        $this->check_context();

        $str = '';

        if (isloggedin() && has_capability('moodle/site:config', context_system::instance())) {
            $str .= $this->output->box_start('', 'shop-adminlinks');
            $str .= get_string('adminoptions', 'local_shop');
            $disableall = get_string('disableallmode', 'local_shop');
            $enableall = get_string('enableallmode', 'local_shop');
            $toproductbackofficestr = get_string('gotobackoffice', 'local_shop');

            if (!empty($SESSION->shopseeall)) {
                $params = array('view' => 'shop',
                                'seeall' => 0,
                                'id' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id);
                $shopurl = new moodle_url('/local/shop/front/view.php', $params);
                $str .= '<a href="'.$shopurl.'">'.$disableall.'</a>';
            } else {
                $params = array('view' => 'shop',
                                'seeall' => 1,
                                'id' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id);
                $shopurl = new moodle_url('/local/shop/front/view.php', $params);
                $str .= '<a href="'.$shopurl.'">'.$enableall.'</a>';
            }
            $params = array('view' => 'viewAllProducts',
                            'catalogid' => $this->thecatalog->id,
                            'blockid' => $this->theblock->id);
            $backofficeurl = new moodle_url('/local/shop/products/view.php', $params);
            $str .= '&nbsp;-&nbsp;<a href="'.$backofficeurl.'">'.$toproductbackofficestr.'</a>';

            $str .= $this->output->box_end();
        }

        return $str;
    }

    /**
     * Prints an order line
     * @param objectref $thecatalog the whole catalog reference
     * @param string $shortname the product short code
     * @param int $q quantity
     * @param array $options
     */
    public function order_line($shortname = null, $q = null, $options = null) {
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
            $str .= sprintf('%.2f', $catalogitem->get_taxed_price($q) * $q);
            $str .= '</td>';
            $str .= '</tr>';
        }

        return $str;
    }

    /**
     * Prints an order line
     * @param objectref $billitem the billitem
     */
    public function bill_line($billitem, $options = null) {

        $str = '';

        $q = $billitem->quantity;
        $str .= '<tr valign="top">';
        $str .= '<td width="60%" align="left" class="c0">';
        $str .= $billitem->abstract;
        if (!empty($options['description'])) {
            $str .= '<div class="shop-bill-abstract">';
            $str .= $billitem->description;
            $str .= '</div>';
        }
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

    /**
     * @param object $bill
     */
    public function full_order_totals($bill = null) {
        global $SESSION;

        $this->check_context();

        $config = get_config('local_shop');

        $shoppingcart = @$SESSION->shoppingcart;

        $reason = '';

        if (!is_null($bill)) {
            $taxedtotal = $bill->ordertaxed;
            $finaltaxedtotal = $bill->finaltaxedtotal;
            $finaluntaxedtotal = $bill->finaluntaxedtotal;
            $finaltaxestotal = $bill->taxes;
            $discount = $bill->discount;
            $shippingtaxedvalue = 0;
            $discountrate = $this->theshop->calculate_discountrate_for_user($taxedtotal, $this->context, $reason);
        } else {
            $taxedtotal = $shoppingcart->taxedtotal;
            $discountrate = $this->theshop->calculate_discountrate_for_user($taxedtotal, $this->context, $reason);
            $discount = $shoppingcart->discount;

            if ($discountrate) {
                $finaltaxedtotal = $taxedtotal * (1 - ($discountrate / 100));
                $finaluntaxedtotal = $shoppingcart->untaxedtotal * (1 - ($discountrate / 100));
            } else {
                $finaltaxedtotal = $shoppingcart->finaltaxedtotal;
                $finaluntaxedtotal = $shoppingcart->finaluntaxedtotal;
            }

            $finaltaxestotal = @$shoppingcart->finaltaxestotal;
            $shippingtaxedvalue = 0 + @$shoppingcart->shipping->taxedvalue;
            $finalshippedtaxedtotal = $shoppingcart->finalshippedtaxedtotal;
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

            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="40%" class="shop-totaltitle ratio cell c1">';
            $str .= get_string('discountamount', 'local_shop').' :';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" class="shop-totals ratio cell c2">';
            $str .= '<b>'.sprintf("%0.2f", round($discount, 2)).'&nbsp;'.$this->theshop->get_currency('symbol').'&nbsp;</b>';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0 shop-totaltitle topay">';
        $str .= '<b>'.get_string('finaltotalprice', 'local_shop').'</b>:';
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 shop-total topay">';
        if (empty($config->hasshipping)) {
            $str .= '<b>'.sprintf("%0.2f", round($finaltaxedtotal, 2)).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol').'</b>&nbsp;';
        } else {
            $str .= '<b>'.sprintf("%0.2f", round($finaltaxedtotal + $shippingtaxedvalue), 2).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol').'</b>&nbsp;';
        }
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="40%" class="cell c0">';
        $str .= get_string('untaxedsubtotal', 'local_shop');
        $str .= '</td>';
        $str .= '<td width="40%" class="cell c1">';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 lastcol">';
        $str .= sprintf("%0.2f", round($finaluntaxedtotal, 2)).'&nbsp;';
        $str .= $this->theshop->get_currency('symbol').'&nbsp;';
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
            $str .= sprintf("%0.2f", round($shippingtaxedvalue, 2)).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol').'&nbsp;';
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
            $str .= '<b>'.sprintf("%0.2f", round($finalshippedtaxedtotal, 2)).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol').'</b>&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '</table>';

        return $str;
    }

    /**
     * @param object $bill
     */
    public function full_order_taxes(&$bill = null) {
        global $SESSION;

        $this->check_context();

        if (!empty($bill)) {
            $taxes = $bill->taxlines;
            $finaltaxestotal = $bill->finaltaxestotal;
        } else {
            $taxes = $SESSION->shoppingcart->taxes;
            $finaltaxestotal = $SESSION->shoppingcart->finaltaxestotal;
        }

        $str = '';

        if ($taxlines = $taxes) {

            $str .= $this->output->heading(get_string('taxes', 'local_shop'), 2, '', true);

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
                $str .= sprintf("%0.2f", round($tamount, 2)).'&nbsp;'.$this->theshop->get_currency('symbol');
                $str .= '</td>';
                $str .= '</tr>';
            }

            $str .= '<tr valign="top">';
            $str .= '<td width="40%" class="cell c0 shop-taxes"><b>';
            $str .= get_string('totaltaxes', 'local_shop');
            $str .= '</b></td>';
            $str .= '<td width="40%" class="cell c1">';
            $str .= '&nbsp;';
            $str .= '</td>';
            $str .= '<td width="20%" align="right" style="text-align:right" class="cell c2 lastcol shop-taxes">';
            $str .= sprintf("%0.2f", round($finaltaxestotal, 2)).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol').'&nbsp;';
            $str .= '</td>';
            $str .= '</tr>';

            $str .= '</table>';
        }

        return $str;
    }

    /**
     * prints the payment block on GUI
     *
     */
    public function payment_block() {
        global $SESSION, $CFG;

        $config = get_config('local_shop');
        $this->check_context();

        include_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

        $systemcontext = context_system::instance();

        $str = $this->output->heading(get_string('paymentmethod', 'local_shop'));
        $str .= '<table width="100%" id="shop-paymodes">';
        $str .= '<tr>';
        $str .= '<td valign="top" colspan="1">';
        $str .= get_string('paymentmode', 'local_shop');
        $str .= '<sup>*</sup>:';
        $str .= '</td></tr>';
        $str .= '<tr><td valign="top" align="left">';

        // Checking  paymodes availability and creating radios.
        if ($SESSION->shoppingcart->finalshippedtaxedtotal == 0) {
            $str .= '<input type="hidden" name="paymode" value="freeorder" />';
            $str .= '<em>'.get_string('freeordersignal', 'local_shop').'</em>';
        } else {
            $paymodes = get_list_of_plugins('/local/shop/paymodes');

            \local_shop\Shop::expand_paymodes($this->theshop);

            foreach ($paymodes as $var) {
                $isenabledvar = "enable$var";

                $paymodeplugin = shop_paymode::get_instance($this->theshop, $var);

                // User must be allowed to use non immediate payment methods.
                if (!$paymodeplugin->is_instant_payment()) {
                    if (!has_capability('local/shop:paycheckoverride', $this->context) &&
                        !has_capability('local/shop:usenoninstantpayments', $this->context)) {
                        continue;
                    }
                }

                // If test payment, check if we are logged in and admin, or logged in from an admin behalf.

                if (($var == 'test') && (!$config->test)) {
                    if (!isloggedin()) {
                        continue;
                    }

                    if (!empty($USER->realuser)) {
                        $isrealadmin = has_capability('moodle/site:config', $systemcontext, $USER->realuser);
                    } else {
                        $isrealadmin = false;
                    }
                    if (!is_siteadmin() && !$isrealadmin) {
                        continue;
                    }
                }

                $check = $this->theshop->{$isenabledvar};

                if ($check) {
                    // Set default paymode as first available.
                    if (empty($SESSION->shoppingcart->paymode)) {
                        $default = (empty($this->theshop->defaultpaymode)) ? $var : $this->theshop->defaultpaymode;
                        $SESSION->shoppingcart->paymode = $default;
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
    public function order_short() {
        global $SESSION, $DB;

        $shoppingcart = $SESSION->shoppingcart;

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
        $str .= sprintf('%.2f', $shoppingcart->untaxedtotal).' '.$this->theshop->get_currency('symbol');
        $str .= '</td>';
        $str .= '</tr>';
        if (!empty($shoppingcart->taxes)) {
            foreach ($shoppingcart->taxes as $taxcode => $taxsum) {
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

        $reason = '';

        $discountrate = $this->theshop->calculate_discountrate_for_user($shoppingcart->taxedtotal,
                                                                        $this->context, $reason);
        if ($discountrate) {
            $str .= '<tr>';
            $str .= '<td>';
            $str .= get_string('discount', 'local_shop');
            $str .= '</td><td>';
            // Taxed value.
            $str .= sprintf('%.2f', $shoppingcart->taxedtotal * ($discountrate / 100)).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol');
            $str .= '</td>';
            $str .= '</tr>';
        }
        if (!empty($shoppingcart->shipping)) {
            $str .= '<tr>';
            $str .= '<td>';
            $str .= get_string('shipping', 'local_shop');
            $str .= '</td><td>';
            $str .= sprintf('%.2f', $shoppingcart->shipping->value).'&nbsp;';
            $str .= $this->theshop->get_currency('symbol');
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '<tr>';
        $str .= '<td>';
        $str .= get_string('finaltotal', 'local_shop');
        $str .= '</td><td>';
        $str .= sprintf('%.2f', 0 + $shoppingcart->finalshippedtaxedtotal).'&nbsp;';
        $str .= $this->theshop->get_currency('symbol');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    public function field_start($legend, $class) {

        $str = '';
        $str .= '<fieldset class="'.$class."\">\n";
        $str .= '<legend>'.$legend."</legend>\n";

        return $str;
    }

    public function field_end() {
        return '</field></fieldset>';
    }

    /**
     *
     * @see users.php
     */
    public function seat_roles_assignation_form(&$catalogentry, &$requiredroles, $shortname, $q) {

        $str = '';

        $str .= '<fieldset>';
        $title = get_string('seatassignation', 'local_shop', $q).' : '.$catalogentry->name;
        $str .= '<legend><h2>'.$title.'</h2></legend>';

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
    public function action_form($view, $options) {

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
            $str .= '&nbsp;<input title="'.@$options['overtext'].'"
                                  type="submit"
                                  id="next-button"
                                  name="go"
                                  value="'.get_string($options['nextstring'], 'local_shop').'"
                                  '.@$options['nextdisabled'].'
                                  style="'.@$options['nextstyle'].'" />';
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
    public function customer_requirements(&$errors) {
        global $SESSION;

        $this->check_context();

        $str = '';

        // Samples for json programming.
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

        $shoppingcart = $SESSION->shoppingcart;

        if (!empty($shoppingcart->order)) {
            $str .= '<form name="shop-requirements">';
            $str .= '<input type="hidden" name="view" value="purchaserequ">';
            $str .= '<input type="hidden" name="id" value="'.$this->theshop->id.'">';
            $str .= '<input type="hidden" name="blockid" value="'.$this->theblock->id.'">';
            $str .= '<input type="hidden" name="what" value="collect">';
            $str .= '<div id="shop-requirement-list">';
            $str .= '<div id="shop-requirement-caption">';
            $str .= get_string('requireddatacaption', 'local_shop');
            $str .= '</div>';
            foreach ($shoppingcart->order as $itemname => $itemcount) {
                $product = $this->thecatalog->get_product_by_shortname($itemname);
                $requireddata = $product->requireddata;
                // Take care, result of magic _get() is not directly testable.
                $requirements = array_values((array)json_decode($requireddata));
                if (!empty($requirements)) {
                    $str .= '<div class="shop-product-requ">';
                    $str .= '<div class="shop-product-name">'.$product->name.'</div>';
                    for ($i = 0; $i < $itemcount; $i++) {
                        $str .= '<div class="shop-product-requirements">';
                        $label = get_string('instance', 'local_shop', $i + 1);
                        $str .= '<div class="shop-product-requirement">'.$label.'</div>';
                        foreach ($requirements as $requ) {
                            $reqobj = (object)$requ;

                            $attributes = '';
                            if (!empty($reqobj->attrs)) {
                                foreach ($reqobj->attrs as $key => $value) {
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
                                    $value = @$shoppingcart->customerdata[$itemname][$reqobj->field][$i];
                                    $str .= '<input type"text"
                                                    name="'.$itemname.'/'.$reqobj->field.$i.'"
                                                    id="id-'.$reqobj->field.$i.'"
                                                    value="'.$value.'"
                                                    '.$attributes.'
                                                    '.$inputclass.'/>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= @$reqobj->desc;
                                    $str .= '</div>'; // Closing requ-desc.
                                    $str .= '</div>'; // Closing requ-param.
                                    break;

                                case 'textarea':
                                    $str .= '<div class="requ-param">';
                                    $str .= '<textarea name="'.$itemname.'/'.$reqobj->field.$i.'"
                                                       id="id-'.$reqobj->field.$i.'"
                                                       '.$attributes.'
                                                       '.$inputclass.'>';
                                    $str .= @$shoppingcart->customerdata[$itemname][$reqobj->field][$i];
                                    $str .= '</textarea>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= $reqobj->desc;
                                    $str .= '</div>';
                                    $str .= '</div>';
                                    break;

                                case 'checkbox':
                                    $ischecked = @$shoppingcart->customerdata[$itemname][$reqobj->field][$i];
                                    $checked = ($ischecked) ? 'checked="checked"' : '';
                                    $str .= '<div class="requ-param">';
                                    $str .= '<input type="checkbox"
                                                    name="'.$itemname.'/'.$reqobj->field.$i.'"
                                                    id="id-'.$reqobj->field.$i.'"
                                                    '.$checked.'
                                                    '.$attributes.'
                                                    '.$inputclass.'>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= $reqobj->desc;
                                    $str .= '</div>';
                                    $str .= '</div>';
                                    break;

                                case 'select':
                                    $str .= '<div class="requ-param">';
                                    $str .= '<select name="'.$itemname.'/'.$reqobj->field.$i.'"
                                                     id="id-'.$reqobj->field.$i.'"
                                                     '.$attributes.'
                                                     '.$inputclass.'>';
                                    $options = $reqobj->options;
                                    if ($options) {
                                        foreach ($options as $optkey => $optvalue) {
                                            $isselected = $optkey == @$shoppingcart->customerdata[$itemname][$reqobj->field][$i];
                                            $selected = ($isselected) ? 'selected' : '';
                                            $str .= '<option name="'.$optkey.'" '.$selected.'>'.$optvalue.'</option>';
                                        }
                                    }
                                    $str .= '</select>';
                                    $str .= '<div class="requ-desc">';
                                    $str .= $reqobj->desc;
                                    $str .= '</div>';
                                    $str .= '</div>';
                                    break;

                                default:
                                    $str .= '<div class="shop-error">';
                                    $a = "{$itemname}|{$reqobj->field}|{$reqobj->type}";
                                    $str .= get_string('errorrequirementfieldtype', 'local_shop', $a);
                                    $str .= '</div>';
                            }
                            $str .= '</div>'; // Closing shop-requirement.
                        }
                        $str .= '</div>';
                    }
                }
            }
            $extraclass = (empty($shoppingcart->customerdata)) ? 'unsaved' : '';
            $str .= '<div id="requ-submit" class="'.$extraclass.'">';
            $label = get_string('saverequs', 'local_shop');
            $str .= '<input type="submit" name="go_btn" value="'.$label.'" style="width:200px" />';
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
    public function check_and_print_eula_conditions() {
        global $SESSION, $SITE;

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
            $eulastr .= '<input type="checkbox" name="agreeeula" id="agreeeula" value="1"> ';
            $eulastr .= get_string('eulaagree', 'local_shop');
            $eulastr .= '<br/><br/>';
            $eulastr .= '<input type="button"
                                name="accept_btn"
                                value="'.$confirmstr.'"
                                onclick="accept_eulas(this)">';
            $eulastr .= '</form>';
            $eulastr .= '</center>';
            $eulastr .= '</div>';
            $eulastr .= '</div>';
        }
        return $eulastr;
    }

    public function login_form() {

        $str = '<div id="shop-loginbox">';
        $params = array('view' => 'customer',
                        'shopid' => $this->theshop->id,
                        'blockid' => (0 + @$this->theblock->instance->id));
        $thisurl = new moodle_url('/local/shop/front/view.php', $params);
        $loginurl = new moodle_url('/login/index.php', array('wantsurl' => $thisurl));
        $str .= '<a href="'.$loginurl.'">';
        $str .= '<input type="button"
                        class="shop-login-button"
                        name="gologin"
                        value="'.get_string('signin', 'local_shop').'" >';
        $str .= '</a>';
        $str .= '</div>';
        return $str;
    }

    public function my_total_link() {
        $totalurl = new moodle_url('/local/shop/front/view.php', array('shopid' => $this->theshop->id));
        $button = '<input type="button" value="'.get_string('mytotal', 'local_shop').'" />';
        $str = '<center><div id="shop-total-link"><a href="'.$totalurl.'#total">'.$button.'</a></div><center>';
        return $str;
    }

    /**
     * Prints the header on printable invoices and ordering documents.
     */
    public function invoice_header(&$afullbill) {
        global $CFG;

        $config = get_config('local_shop');

        $realized = array(SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PARTIAL);

        if (!in_array($afullbill->status, $realized)) {
            $headerstring = get_string('ordersheet', 'local_shop');
            $headerstring .= get_string('ordertempstatusadvice', 'local_shop');
        } else {
            if (empty($afullbill->idnumber)) {
                $headerstring = get_string('proformabill', 'local_shop');
            } else {
                $headerstring = get_string('bill', 'local_shop');
            }
        }

        $str = '';

        $str .= '<table>';
        if (!empty($afullbill->withlogo)) {

            if (!empty($config->sellerlogo)) {
                $syscontext = context_system::instance();
                $component = 'local_shop';
                $filearea = 'shoplogo';
                $itemid = 0;
                $filepath = $config->sellerlogo;
                $path = "/$syscontext->id/$component/$filearea/$itemid".$filepath;
                $pixurl = moodle_url::make_file_url($CFG->wwwroot.'/pluginfile.php', $path);
            } else {
                $pixurl = $this->output->pix_url('logo', 'theme');
            }

            $str .= '<tr>';
            $str .= '<td><img src="'.$pixurl.'"></td>';
            $str .= '<td align="right"></td>';
            $str .= '</tr>';
        }

        $str .= '<tr valign="top">';
        $str .= '<td colspan="2" align="center">';
        $str .= $this->output->heading($headerstring, 1);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('transactioncode', 'local_shop').':</b><br />';
        $str .= '<code style="background-color : #E0E0E0">'.$afullbill->transactionid.'</code><br />';
        $str .= '<span class="smaltext">'.get_string('providetransactioncode', 'local_shop').'</span>';
        $str .= '</td>';
        $str .= '<td width="40%" align="right" rowspan="5" class="order-preview-seller-address">';
        $str .= '<b>'.get_string('on', 'local_shop').':</b> '.userdate($afullbill->emissiondate).'<br />';
        $str .= '<br />';
        $str .= '<b>'.$config->sellername.'</b><br />';
        $str .= '<b>'.$config->selleraddress.'</b><br />';
        $str .= '<b>'.$config->sellerzip.' '.$config->sellercity.'</b><br />';
        $str .= $config->sellercountry;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $custname = $afullbill->customer->lastname.' '.$afullbill->customer->firstname;
        $str .= '<b>'.get_string('customer', 'local_shop').': </b> '.$custname;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('city').': </b>';
        $str .= $afullbill->customer->zip.' '.$afullbill->customer->city;
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('country').': </b> '.strtoupper($afullbill->customer->country);
        $str .= '</td>';
        $str .= '<td>';
        $str .= '&nbsp;';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td width="60%">';
        $str .= '<b>'.get_string('email').': </b>'.$afullbill->customer->email;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td colspan="2">';
        $str .= '&nbsp;<br />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td colspan="2" class="sectionHeader">';
        $str .= $this->output->heading(get_string('order', 'local_shop'), 2);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';

        return $str;
    }
}