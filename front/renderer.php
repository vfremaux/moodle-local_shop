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

    const STATE_DONE = 0;
    const STATE_TODO = 1;
    const STATE_FREEZE = 2;

    /**
     * prints a purchase procedure progression bar
     * @param string $progress the progress state
     */
    public function progress($progress) {
        global $SESSION;

        $str = '';

        // This converts configuration steps to internal effective steps.
        $radicals = array(
            'CHOOSE' => 'shop',
            'CONFIGURE' => 'purchaserequ',
            'USERS' => 'users',
            'CUSTOMER' => 'customer',
            'CONFIRM' => 'order',
            'PAYMENT' => 'payment',
            'PENDING' => 'payment', // Payment blockd for some reason.
            'PRODUCE' => 'produce',
            'BILL' => 'invoice',
            'INVOICE' => 'invoice',
        );

        // This converts API inputs to internal effective steps.
        $inputmapping = array(
            'shop' => 'shop',
            'purchaserequ' => 'purchaserequ',
            'users' => 'users',
            'customer' => 'customer',
            'order' => 'order',
            'payment' => 'payment',
            'pending' => 'payment', // Payment blockd for some reason.
            'produce' => 'produce',
            'invoice' => 'invoice',
        );

        $stepicons = array_flip($radicals);

        $str .= '<div id="progress">';
        $str .= '<center>';

        $steps = explode(',', $this->theshop->navsteps);

        $state = self::STATE_DONE;
        $iconstate = '_on';
        foreach ($steps as $step) {
            if (($state == self::STATE_DONE)) {
                $iconstate = '_on';
                if ($inputmapping[$step] == $radicals[$progress]) {
                    $state = self::STATE_TODO;
                }
            } else if ($state == self::STATE_TODO) {
                $iconstate = '_off';
                $state = self::STATE_FREEZE;
            }

            // Disable step on configuration or environment conditions.
            $icon = $stepicons[trim($step)];
            if (!empty($SESSION->shoppingcart->norequs) && ($icon == 'CONFIGURE') && ($iconstate == '_on')) {
                $iconstate = '_dis';
            }
            if (empty($SESSION->shoppingcart->seats) && ($icon == 'USERS') && ($iconstate == '_on')) {
                $iconstate = '_dis';
            }

            $stepicon = $this->output->image_url(current_language().'/'.$icon.$iconstate, 'local_shop');
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

        $template = new StdClass;
        $template->currency = $this->theshop->get_currency('symbol');

        $template->amount = sprintf('%0.2f', round($amount, 2));
        $template->totalobjects = $totalobjects;

        $template->discountrate = $this->theshop->calculate_discountrate_for_user($amount, $this->context, $reason);
        $template->reason = $reason;

        if ($template->discountrate) {
            $discounted = $amount - ($amount * $discountrate / 100);
        } else {
            $discounted = $amount;
        }

        $template->discounted = sprintf('%0.2f', round($discounted, 2));

        if (!empty($config->useshipping)) {
            $template->useshipping = true;
            $template->shipchecked = (!empty($SESSION->shoppingcart->shipping)) ? 'checked="checked"' : '';
        }

        return $this->output->render_from_template('local_shop/front_order_total_summary', $template);
    }

    public function shop_return_button($theshop) {

        $str = '';
        $options['id'] = $theshop->id;
        $options['class'] = 'singlebutton shop-inline';
        $label = get_string('backtoshop', 'local_shop');
        $str .= $this->output->single_button('/local/shop/front/view.php', $label, 'post',  $options);

        return $str;
    }

    /**
     * Prints the customer info summary
     * @param object $bill
     */
    public function customer_info(&$bill = null) {
        global $SESSION, $CFG;

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

        $template = new StdClass;

        $template->transid = $transid;
        $template->emissiondate = userdate($emissiondate);
        $template->organisation = @$ci['organisation'];

        if ($usedistinctinvoiceinfo) {
            $template->usedistinctinvoiceinfo = true;
            $template->department = @$ci['department'];
        }

        $template->customername = $ci['lastname'].' '.$ci['firstname'];

        if (local_shop_supports_feature('shop/partners')) {
            include_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');
            if (!empty($SESSION->shoppingcart->partner)) {
                $sessionpartner = $SESSION->shoppingcart->partner;
                $partner = \local_shop\Partner::get_by_key($SESSION->shoppingcart->partner->partnerkey);
                $template->partnername = $partner->name;
                $template->haspartner = true;
            }
        }

        $template->city = $ci['zip'].' '.$ci['city'];
        $template->country = core_text::strtoupper($ci['country']);

        $template->email = @$ci['email'];

        if ($usedistinctinvoiceinfo) {
            $template->vatcode = @$ci['vatcode'];
        }

        return $this->output->render_from_template('local_shop/front_customer_info', $template);
    }

    /**
     *
     *
     */
    public function local_confirmation_form($requireddata) {

        $template = new StdClass;
        $template->disabled = (!empty($requireddata)) ? 'disabled="disabled"' : '';
        $template->formurl = new moodle_url('/local/shop/front/view.php');

        return $this->output->render_from_template('local_shop/front_local_confirmation', $template);
    }

    /**
     * prints tabs for js activation of the category panel
     */
    public function category_tabs(&$categories, $selected, $parent, $isactive, $isvisiblebranch, $catlevel) {

        $str = '';

        $rows[0] = array();
        foreach ($categories as $cat) {
            if ($cat->visible) {
                $params = array('view' => 'shop',
                                'category' => $cat->id,
                                'shopid' => $this->theshop->id,
                                'blockid' => $this->theblock->id);
                $categoryurl = new moodle_url('/local/shop/front/view.php', $params);
                $rows[0][] = new tabobject('catli'.$cat->id, $categoryurl, format_string($cat->name));
            }
        }

        if ($isvisiblebranch) {
            $visibleclass = 'shop-category-visible';
        } else {
            $visibleclass = 'shop-category-hidden';
        }

        if ($catlevel > 0) {
            $catlevel .= ' subcats';
        }

        $str .= '<div class="'.$visibleclass.' level-'.$catlevel.'" id="shop-cat-children-of-'.$parent.'">';

        if ($isactive) {
            $str .= print_tabs($rows, $selected, '', '', true);
        } else {
            $str .= print_tabs($rows, '', '', array($selected), true);
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

        $template = new StdClass;

        if (empty($categories)) {
            $template->notification = $this->output->notification(get_string('nocats', 'local_shop'));
            return $this->output->render_from_remplate('local_shop/catalog', $template);
        }

        $template->hascategories = true;

        // Make a comma list of all category ids.
        $catidsarr = array();
        foreach ($categories as $cat) {
            $catidsarr[] = $cat->id;
        }
        $catids = implode(',', $catidsarr);

        $template->withtabs = (@$this->theshop->printtabbedcategories == 1);
        $template->categorytabs = array();

        if ($template->withtabs) {
            $categoryid = optional_param('category', null, PARAM_INT);

            // Get the tree branch up to the category starting from the top.
            if ($categoryid) {
                $category = new Category($categoryid);
                $branch = array_reverse($category->get_branch());
            } else {
                $branch = array_reverse(Category::get_first_branch($this->thecatalog->id, 0));
            }

            // Render all upper branch choices, with preselected items in the active branch.
            $catlevel = 0;
            while ($catid = array_shift($branch)) {
                $cat = new Category($catid);
                if ($cat->visible) {
                    $params = array('catalogid' => $this->thecatalog->id, 'parentid' => $cat->parentid, 'visible' => 1);
                    $levelcategories = Category::get_instances($params, 'sortorder');
                    $iscurrent = $cat->id == $categoryid;
                    $categorytabtpl = new StdClass;
                    $categorytabtpl->category = $this->category_tabs($levelcategories, 'catli'.$cat->id, $cat->parentid, $iscurrent, true, $catlevel);
                    $template->categorytabs[] = $categorytabtpl;

                    // Print childs.
                    $catlevel++;
                    $attrs = array('catalogid' => $this->thecatalog->id, 'parentid' => $cat->id);
                    if ($subs = Category::get_instances($attrs, 'sortorder')) {
                        $categorytabtpl = new StdClass;
                        $categorytabtpl->category = $this->category_tabs($subs, null, $cat->id, false, $cat->id == $categoryid, $catlevel);
                        $template->categorytabs[] = $categorytabtpl;
                    }
                }
                $catlevel++;
            }
        }

        // Print catalog product line on the active category if tabbed.
        $catids = array_keys($categories);
        $currentcategory = optional_param('category', $catids[0], PARAM_INT);

        $c = 0;
        foreach ($levelcategories as $c) {
            $cat = $categories[$c->id];
            if ($template->withtabs && ($currentcategory != $cat->id)) {
                continue;
            }
            if (!isset($firstcatid)) {
                $firstcatid = $cat->id;
            }

            $categorytpl = new StdClass;
            $categorytpl->id = $cat->id;

            if (empty($withtabs)) {
                $cat->level = 1;
                $categorytpl->heading = $this->output->heading($cat->name, $cat->level);
            }

            if (!empty($cat->description)) {
                $categorytpl->description = format_text($cat->description, FORMAT_MOODLE, array('para' => false));
            }

            if (!empty($cat->products)) {
                $categorytpl->hasproducts = true;
                foreach ($cat->products as $product) {

                    $producttpl = new StdClass;
                    $product->check_availability();
                    $product->currency = $this->theshop->get_currency('symbol');
                    $product->salesunit = $product->get_sales_unit_url();
                    $product->preset = 0 + @$SESSION->shoppingcart->order[$product->shortname];
                    switch ($product->isset) {
                        case PRODUCT_SET:
                            $producttpl->product = $this->product_set($product, true);
                            break;
                        case PRODUCT_BUNDLE:
                            $producttpl->product = $this->product_bundle($product, true);
                            break;
                        default:
                            $producttpl->product = $this->product_block($product);
                    }
                    $categorytpl->products[] = $producttpl;
                }
            } else {
                $categorytpl->hasproducts = false;
                $categorytpl->noproductincategorynotification = get_string('noproductincategory', 'local_shop');
            }
            $c++;

            $template->categories[] = $categorytpl;
        }

        return $this->output->render_from_template('local_shop/front_catalog', $template);
    }

    /**
     * Prints a product block on front shop
     * @param objectref &$product
     * @param bool $astemplate if true, just returns the template object rather than the html output.
     */
    public function product_block(&$product, $astemplate = false) {
        global $CFG, $OUTPUT;

        $config = get_config('local_shop');

        $this->check_context();

        $template = new StdClass;

        $subelementclass = (!empty($product->ispart)) ? 'element' : 'product';
        $subelementclass .= ($product->available) ? '' : ' shadowed';

        $template->subelementclass = $subelementclass;
        $template->ispart = $product->ispart;
        $template->issetpart = $product->issetpart;
        $template->isbundlepart = $product->isbundlepart;
        $template->id = $product->id;
        $template->shortname = $product->shortname;
        $template->code = $product->code;
        $template->subelementclass = $subelementclass;

        $image = $product->get_image_url();
        if ($image) {
            $template->hasimage = true;
            $template->imageurl = $image;
        } else {
            $template->hasimage = false;
        }
        $template->thumburl = $product->get_thumb_url();

        $template->name = format_string($product->name);
        $template->shortname = $product->shortname;
        $template->puttcstr = get_string('puttc', 'local_shop');

        if (!empty($product->isbundlepart)) {
            $template->isbundlepart = true;
        }

        $template->showdescription = true;
        $template->showname = true;
        if (!empty($product->issetpart)) {
            $template->issetpart = true;
            $template->showdescription = $product->showsdescriptioninset;
            $template->showname = $product->showsnameinset;
        }

        $template->isshortdescription = false;
        if ($product->description) {
            $product->description = file_rewrite_pluginfile_urls($product->description, 'pluginfile.php', $this->context->id, 'local_shop',
                                               'catalogitemdescription', $product->id);
            $template->description = format_text($product->description, FORMAT_MOODLE, array('para' => false));

            $cutoff = $config->shortdescriptionthreshold;
            $template->shortdescription = $this->trim_chars($product->description, $cutoff);

            if (($template->description != $template->shortdescription) || $product->has_leaflet()) {
                // $template->shorthandlepixurl = $OUTPUT->image_url('ellipsisopen', 'local_shop');
                $template->readmorestr = get_string('readmore', 'local_shop');
                $template->isshortdescription = true;
            }
        } else {
            $template->description = '';
        }
        if (!$product->available) {
            $template->notavailablestr = get_string('notavailable', 'local_shop');
        }

        if ($product->has_leaflet()) {
            $template->hasleaflet = true;
            $template->leafleturl = $product->get_leaflet_url();
            $template->leafletlinkstr = get_string('leafletlink', 'local_shop');
        }

        if (empty($product->noorder)) {
            $template->canorder = true;
            $template->refstr = get_string('ref', 'local_shop');

            $template->currencystr = $product->currency;
            $prices = $product->get_printable_prices(true);
            if (count($prices) <= 1) {
                $template->pricelist = false;
                $price = array_shift($prices);
                $template->price = $price;
            } else {
                $template->pricelist = true;
                foreach ($prices as $range => $price) {
                    $pricetpl = new StdClass;
                    $pricetpl->range = $range;
                    $pricetpl->price = $price;
                    $template->prices[] = $pricetpl;
                }
            }

            if ($product->available) {
                $template->available = true;
                $template->buystr = get_string('buy', 'local_shop');
                $isdisabled = !empty($product->record->maxdeliveryquant) && ($product->record->maxdeliveryquant >= $product->preset);
                $template->disabled = ($isdisabled) ? 'disabled="disabled"' : '';
                if ($product->password) {
                    $template->password = true;
                    $template->needspasscodetobuystr = get_string('needspasscodetobuy', 'local_shop');
                    $template->disabled = 'disabled="disabled"';
                }
                $template->maxdeliveryquant = $product->record->maxdeliveryquant;
                $template->units = $this->units($product);
            }
        }

        if ($astemplate) {
            return $template;
        }
        return $this->output->render_from_template('local_shop/front_product_block', $template);
    }

    /**
     * Prints a product set on front shop
     * @param objectref &$set
     */
    public function product_set(&$set) {
        global $OUTPUT;

        $config = get_config('local_shop');

        $template = new StdClass;

        $template->name = format_string($set->name);
        if ($set->description) {
            $set->description = file_rewrite_pluginfile_urls($set->description, 'pluginfile.php', $this->context->id, 'local_shop',
                                               'catalogitemdescription', $set->id);
            $template->sethasdescription = true;
            $template->description = format_text($set->description, FORMAT_MOODLE, array('para' => false));
            $cutoff = $config->shortdescriptionthreshold;
            if (core_text::strlen($set->description) > $cutoff) {
                $template->shorthandlepixurl = $OUTPUT->image_url('ellipsisopen', 'local_shop');
                $template->readmorestr = get_string('readmore', 'local_shop');
                $template->shortdescription = true;
            }
        } else {
            $template->sethasdescription = false;
        }

        $image = $set->get_image_url();
        if ($image) {
            $template->image = '<a class="fancybox" rel="group" href="'.$image.'"><img src="'.$set->get_thumb_url().'"></a>';
        } else {
            $template->image = '<img src="'.$set->get_thumb_url().'">';
        }

        foreach ($set->elements as $element) {
            $element->check_availability();
            $element->noorder = false; // Bundle can only be purchased as a group.
            $element->ispart = true; // Reduced title.
            $element->issetpart = true; // Reduced title.
            $template->elements[] = $this->product_block($element, true);
        }

        return $this->output->render_from_template('local_shop/front_product_set', $template);
    }

    /**
     * Prints a product bundle on front shop
     * @param objectref &$set
     */
    public function product_bundle(&$bundle) {
        global $CFG, $OUTPUT;

        $config = get_config('local_shop');

        $template = new StdClass;

        $template->code = $bundle->code;
        $template->shortname = $bundle->shortname;

        $image = $bundle->get_image_url();
        if ($image) {
            $template->image = '<a class="fancybox" rel="group" href="'.$image.'"><img src="'.$bundle->get_thumb_url().'"></a>';
        } else {
            $template->image = '<img src="'.$bundle->get_thumb_url().'">';
        }

        if (!empty($bundle->ispart)) {
            $template->name = format_string($bundle->name);
        } else {
            $template->name = format_string($bundle->name);
            if ($bundle->description) {
                $template->hasdescription = true;
                $template->description = format_text($bundle->description, FORMAT_MOODLE, array('para' => false));
            }
            $cutoff = $config->shortdescriptionthreshold;
            if (core_text::strlen($bundle->description) > $cutoff) {
                // $template->rarrowpix = $OUTPUT->image_url('rarrow', 'local_shop');
                $template->shorthandlepixurl = $OUTPUT->image_url('ellipsisopen', 'local_shop');
                $template->readmorestr = get_string('readmore', 'local_shop');
                $template->shortdescription = true;
            }
        }

        if ($bundle->has_leaflet()) {
            $template->leafleturl = $bundle->get_leaflet_url();
            $template->linklabel = get_string('leafletlink', 'local_shop');
        }

        $template->refstr = get_string('ref', 'local_shop');
        $template->puttcstr = get_string('puttc', 'local_shop');
        $template->currency = $bundle->currency;

        foreach ($bundle->elements as $element) {
            // $elementtpl = new StdClass;
            $element->check_availability();
            $element->noorder = true; // Bundle can only be purchased as a group.
            $element->isbundlepart = true; // Reduced title.
            $element->ispart = true;
            $template->elements[] = $this->product_block($element, true); // return as template.
        }

        // We will use price of the bundle element.
        $prices = $bundle->get_printable_prices(true);
        if (count($prices) <= 1) {
            $template->price = array_shift($prices);
        } else {
            $template->pricelist = true;
            foreach ($prices as $range => $price) {
                $pricetpl = new StdClass;
                $pricetpl->range = $range;
                $pricetpl->price = $price;
                $template->prices[] = $pricetpl;
            }
        }

        $template->buystr = get_string('buy', 'local_shop');
        $disabled = ($bundle->maxdeliveryquant && $bundle->maxdeliveryquant == $bundle->preset) ? 'disabled="disabled"' : '';
        if ($bundle->password) {
            $template->password = true;
            $template->jshandler = 'check_pass_code(\''.$bundle->shortname.'\', this, event)';
            $template->needspasscodetobuystr = get_string('needspasscodetobuy', 'local_shop');
            $template->disabled = 'disabled="disabled"';
        }
        $template->jshandleraddunit = 'ajax_add_unit('.$this->theshop->id;
        $template->jshandleraddunit .= ', \''.$bundle->shortname.'\', \''.$bundle->maxdeliveryquant.'\')';
        $template->units = $this->units($bundle);

        return $this->output->render_from_template('local_shop/front_product_bundle', $template);
    }

    public function units(&$product) {
        global $SESSION, $CFG;

        $this->check_context();

        $template = new StdClass;

        $template->unitimageurl = $product->get_sales_unit_url();
        $template->shortname = $product->shortname;
        $template->tenunitsimageurl = $product->get_sales_ten_units_url();

        $q = @$SESSION->shoppingcart->order[$product->shortname];
        $packs = floor($q / 10);
        $units = $q % 10;

        for ($i = 0; $i < 0 + $packs; $i++) {
            $template->packs[] = new StdClass;
        }

        for ($j = 0; $j < 0 + $units; $j++) {
            $template->units[] = new StdClass;
        }

        if (($i * 10 + $j) > 0) {
            $template->hashandler = true;
        }

        return $this->output->render_from_template('local_shop/front_units', $template);
    }

    /**
     * prints order detail lines.
     */
    public function order_detail(&$categories) {
        global $SESSION;

        if (empty($categories)) {
            return $this->output->notification("no categories");
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

    /**
     * Prints a single product line in order details summary.
     */
    public function product_total_line(&$product) {
        global $CFG;

        $this->check_context();

        $template = new StdClass;

        $view = optional_param('view', 'shop', PARAM_ALPHA);
        $template->isshopview = false;
        if ($view == 'shop') {
            $template->isshopview = true;
            $template->shopurl = new moodle_url('/local/shop/front/view.php');
        }

        $ttcprice = $product->get_taxed_price($product->preset, $product->taxcode);
        $template->preset = $product->preset;
        $template->total = sprintf('%0.2f', round($ttcprice * $product->preset, 2));
        $template->shortname = $product->shortname;
        $template->code = '<span class="shop-pcode">'.$product->code.'</span>';
        $template->name = $product->name;
        $template->currency = $product->currency;
        $template->disabled = ' disabled="disabled" ';
        $template->maxdeliveryquant = $product->maxdeliveryquant;
        if ($view == 'shop') {
            $template->isshopview = true;
            $template->disabled = '';
        }
        $template->ttcprice = 'x '.sprintf("%0.2f", round($ttcprice, 2));

        return $this->output->render_from_template('local_shop/front_product_total_line', $template);
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
                             class="local-shop-toggle-invoiceinfo"
                             value="1"
                             name="usedistinctinvoiceinfo"
                            '.$checked.' />';
        $heading .= '<span class="tiny-text"> '.get_string('usedistinctinvoiceinfo', 'local_shop').'</span>';
        $str .= $this->output->heading($heading);

        if (isloggedin() && !isguestuser()) {
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

        $template = new StdClass;
        $template->lastname = $lastname;
        $template->firstname = $firstname;
        $template->customerorganisationrequired = $this->theshop->customerorganisationrequired;
        $template->organisation = $organisation;
        $template->address = $address;
        $template->city = $city;
        $template->zip = $zip;
        $choices = get_string_manager()->get_list_of_countries();
        $this->thecatalog->process_country_restrictions($choices);
        $template->countryselect = html_writer::select($choices, 'customerinfo::country', $country, array('' => 'choosedots'));
        $template->email = $email;

        $str .= $this->output->render_from_template('local_shop/front_customer_form', $template);
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

        if (!empty($shoppingcart->errors->invoiceinfo)) {
            foreach (array_keys($shoppingcart->errors->invoiceinfo) as $f) {
                $f = str_replace('invoiceinfo::', '', $f);
                $var = "{$f}class";
                $$var = 'shop-error';
            }
        }

        $str .= $this->output->heading(get_string('invoiceinformation', 'local_shop'));

        $template = new StdClass;
        $template->institution = @$shoppingcart->invoiceinfo['organisation'];
        $template->department = @$shoppingcart->invoiceinfo['department'];
        $template->lastname = @$shoppingcart->invoiceinfo['lastname'];
        $template->firstname = @$shoppingcart->invoiceinfo['firstname'];
        $template->email = @$shoppingcart->invoiceinfo['email'];
        $template->address = @$shoppingcart->invoiceinfo['address'];
        $template->zip = @$shoppingcart->invoiceinfo['zip'];
        $template->city = @$shoppingcart->invoiceinfo['city'];
        $template->country = @$shoppingcart->invoiceinfo['country'];
        $template->vatcode = @$shoppingcart->invoiceinfo['vatcode'];

        $choices = get_string_manager()->get_list_of_countries();
        $this->thecatalog->process_country_restrictions($choices);
        $attrs = array();
        $template->countryselect = html_writer::select($choices, 'invoiceinfo::country', $template->country, array('' => 'choosedots'), $attrs);

        $str .= $this->output->render_from_template('local_shop/front_invoice_form', $template);

        return $str;
    }

    public function participant_row($participant = null) {
        global $CFG;

        $template = new StdClass;

        if ($participant) {
            $template->participant = true;

            $template->lastname = @$participant->lastname;
            $template->firstname = @$participant->firstname;
            $template->email = @$participant->email;
            $template->city = strtoupper(@$participant->city);
            $template->endusermobilephonerequired = !empty($this->theshop->endusermobilephonerequired);
            $template->phone2 = @$participant->phone2;
            $template->enduserorganisationrequired = !empty($this->theshop->enduserorganisationrequired);
            $template->institution = strtoupper(@$participant->institution);

            if (@$participant->moodleid) {
                $template->hasaccount = true;
            } else {
                $template->hasaccount = false;
            }
        } else {
            $template->endusermobilephonerequired = !empty($this->theshop->endusermobilephonerequired);
            $template->enduserorganisationrequired = !empty($this->theshop->enduserorganisationrequired);
        }

        return $this->output->render_from_template('local_shop/front_participant_row', $template);
    }

    public function participant_blankrow() {

        $this->check_context();

        static $i = 0;

        $template = new StdClass;
        $template->i = $i;

        $template->endusermobilephonerequired = $this->theshop->endusermobilephonerequired;
        $template->enduserorganisationrequired = $this->theshop->enduserorganisationrequired;

        $i++;

        return $this->output->render_from_template('local_shop/front_participant_blanckrow', $template);
    }

    public function new_participant_row() {

        $this->check_context();

        $template = new StdClass;
        $template->endusermobilephonerequired = $this->theshop->endusermobilephonerequired;
        $template->enduserorganisationrequired = $this->theshop->enduserorganisationrequired;

        return $this->output->render_from_template('local_shop/front_new_participant_row', $template);
    }

    public function assignation_row($participant, $role, $shortname) {
        global $CFG;

        $template = new StdClass;
        $template->lastname = @$participant->lastname;
        $template->firstname = @$participant->firstname;
        $template->shortname = $shortname;
        $template->role = $role;
        $template->email = $participant->email;

        return $this->output->render_from_template('local_shop/front_assignation_row', $template);
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
        $params = ['' => get_string('chooseparticipant', 'local_shop')];
        $attrs = ['data-product' => $shortname,
                  'data-role' => $role,
                  'data-requiredroles' => json_encode($this->thecatalog->check_required_roles()),
                  'class' => 'local-shop-add-assign'];
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

        $template = new StdClass;

        if (!empty($SESSION->shoppingcart->order)) {
            foreach (array_keys($SESSION->shoppingcart->order) as $itemname) {
                $product = $this->thecatalog->get_product_by_shortname($itemname);
                $catalogitemtpl = new StdClass;
                $catalogitemtpl->name = format_string($product->name);
                $catalogitemtpl->code = $product->code;
                $catalogitemtpl->q = $SESSION->shoppingcart->order[$itemname];
                $catalogitemtpl->desc = shorten_text(strip_tags(format_text($product->description)), 120);
                $template->catalogitem[] = $catalogitemtpl;
            }
        }

        return $this->output->render_from_template('local_shop/front_cart_summary', $template);
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

        if (is_null($shortname)) {
            return $this->output->render_from_template('local_shop/front_order_header', new StdClass);
        } else {
            $q = (!empty($SESSION->shoppingcart->order)) ? $SESSION->shoppingcart->order[$shortname] : $q;
            $catalogitem = $this->thecatalog->get_product_by_shortname($shortname);

            try {
                $outputclass = 'front_order_line';
                shop_load_output_class($outputclass);
                $tpldata = new \local_shop\output\front_order_line($catalogitem, $q, $this->theshop, $options);
                $template = $tpldata->export_for_template($this);
                return $this->output->render_from_template('local_shop/front_order_line', $template);
            } catch (Exception $e) {
                print_error("Missing output class $outputclass");
            }
        }
    }

    /**
     * Prints an order line
     * @param objectref $billitem the billitem
     */
    public function bill_line($billitem, $options = null) {

        try {
            $outputclass = 'front_bill_line';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\front_bill_line($billitem, $options);
            $template = $tpldata->export_for_template($this);
            return $this->output->render_from_template('local_shop/front_bill_line', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }

    }

    /**
     * @param object $bill
     */
    public function full_order_totals($bill = null, $theshop = null) {
        global $SESSION;

        $this->check_context();

        try {
            $outputclass = 'front_order_totals';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\front_order_totals(array($bill, $theshop, $this->context));
            $template = $tpldata->export_for_template($this);
            return $this->output->render_from_template('local_shop/front_order_totals', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
    }

    /**
     * @param object $bill
     */
    public function full_order_taxes(&$bill = null, $theshop = null) {
        global $SESSION;

        $this->check_context();

        if (!empty($bill)) {
            $taxes = $bill->taxlines;
            $finaltaxestotal = $bill->finaltaxestotal;
        } else {
            $taxes = $SESSION->shoppingcart->taxes;
            $finaltaxestotal = $SESSION->shoppingcart->finaltaxestotal;
        }

        try {
            $outputclass = 'front_taxes';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\front_taxes($taxes, $finaltaxestotal, $theshop);
            $template = $tpldata->export_for_template($this);
            return $this->output->render_from_template('local_shop/front_taxes', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
    }

    /**
     * prints the payment block on GUI
     *
     */
    public function payment_block() {
        global $SESSION, $CFG, $OUTPUT, $USER;

        $config = get_config('local_shop');
        $this->check_context();

        include_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');

        $systemcontext = context_system::instance();
        $template = new StdClass;

        $str = $this->output->heading(get_string('paymentmethod', 'local_shop'));
        // Checking  paymodes availability and creating radios.
        if ($SESSION->shoppingcart->finalshippedtaxedtotal > 0) {
            $paymodes = get_list_of_plugins('/local/shop/paymodes');

            \local_shop\Shop::expand_paymodes($this->theshop);

            $payinputs = '';

            foreach ($paymodes as $var) {

                $paymodeplugin = shop_paymode::get_instance($this->theshop, $var);

                // User must be allowed to use non immediate payment methods.

                $instant = $paymodeplugin->is_instant_payment();

                if (!$instant) {
                    if (!has_capability('local/shop:paycheckoverride', $systemcontext) &&
                        !has_capability('local/shop:usenoninstantpayments', $systemcontext) && !$config->testoverride) {
                        continue;
                    }
                }

                // If test payment, check if we are logged in and admin, or logged in from an admin behalf.

                if (!empty($USER->realuser)) {
                    $isrealadmin = has_capability('moodle/site:config', $systemcontext, $USER->realuser);
                } else {
                    $isrealadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);
                }

                if ($var == 'test') {
                    if (!$isrealadmin && !$config->testoverride) {
                        continue;
                    }
                } else {
                    if ($config->test && $instant) {
                        if (empty($config->testoverride)) {
                            if (!isloggedin()) {
                                continue;
                            }
                        }
                    }
                }

                $isenabledvar = "enable$var";
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
                    $paymodetpl = new StdClass;
                    $paymodetpl->var = $var;
                    $paymodetpl->checked = $checked;
                    $paymodetpl->paymodename = get_string($isenabledvar.'2', 'shoppaymodes_'.$var, $config);
                    $template->paymode[] = $paymodetpl;
                }
            }
            if (empty($template->paymode)) {
                $template->nopaymodesavailable = $this->output->notification(get_string('nopaymodesavailable', 'local_shop'));
            }
        } else {
            $template->freeorderonly = true;
        }

        return $this->output->render_from_template('local_shop/front_payment', $template);
    }

    /**
     *
     */
    public function order_short() {
        global $SESSION, $DB;

        $shoppingcart = $SESSION->shoppingcart;

        $this->check_context();

        $template = new StdClass;
        $template->currency = $this->theshop->get_currency('symbol');

        $template->transid = $SESSION->shoppingcart->transid;
        $template->untaxedtotal = sprintf('%0.2f', round($shoppingcart->untaxedtotal, 2));
        if (!empty($shoppingcart->taxes)) {
            foreach ($shoppingcart->taxes as $taxcode => $taxsum) {
                if ($tax = $DB->get_record('local_shop_tax', array('id' => $taxcode))) {
                    $taxtpl = new StdClass;
                    $taxtpl->taxsum = sprintf('%.2f', $taxsum);
                    $taxtpl->title = $tax->title;
                    $template->tax[] = $taxtpl;
                }
            }
        }

        $reason = '';

        $template->discountrate = $this->theshop->calculate_discountrate_for_user($shoppingcart->taxedtotal,
                                                                        $this->context, $reason);
        $template->reason = $reason;
        if ($template->discountrate) {
            // Taxed value.
            $template->discountedtaxed = sprintf('%0.2f', round($shoppingcart->taxedtotal * ($template->discountrate / 100), 2));
        }

        if (!empty($shoppingcart->shipping)) {
            $template->hasshipping = $shoppingcart->shipping;
            $template->shippingvalue = sprintf('%0.2f', round($shoppingcart->shipping->value, 2));
        }

        $template->finalshippedtaxedtotal = sprintf('%0.2f', round(0 + $shoppingcart->finalshippedtaxedtotal, 2));

        return $this->output->render_from_template('local_shop/front_order_short', $template);
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

        $template = new StdClass;
        $template->actionurl = new moodle_url('/local/shop/front/view.php');
        $template->isinform = @$options['inform'];
        $template->view = $view;
        $template->shopid = $this->theshop->id;
        $template->blockid = (0 + @$this->theblock->id);
        $template->wantstransid = !empty($options['transid']);
        $template->transid = @$options['transid'];
        $template->sesskey = sesskey();
        $template->hideback = @$options['hideback'];
        $template->hidenext = @$options['hidenext'];
        $template->overtext = @$options['overtext'];
        $template->nextdisabled = @$options['nextdisabled'];
        $template->nextstyle = @$options['nextstyle'];
        $template->nextstr = get_string($options['nextstring'], 'local_shop');

        return $this->output->render_from_template('local_shop/front_action_form', $template);
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
        $context = context_system::instance();
        $eula = file_rewrite_pluginfile_urls($eula, 'pluginfile.php', $context->id, 'local_shop',
                                               'eula', $this->theshop->id);

        foreach (array_keys($SESSION->shoppingcart->order) as $shortname) {
            $ci = $this->thecatalog->get_product_by_shortname($shortname);
            if (!($ci->eula === '')) {
                $eula .= '<h3>'.$ci->name.'</h3>';
                $cieula = file_rewrite_pluginfile_urls($ci->eula, 'pluginfile.php', $context->id, 'local_shop',
                                                       'catalogitemeula', $ci->id);
                $eula .= '<p>'.$cieula.'</p>';
            }
        }

        if (!empty($eula)) {
            $template = new StdClass;
            $template->eula = $eula;
            $eulastr .= $this->output->render_from_template('local_shop/front_eulasform', $template);
        }

        return $eulastr;
    }

    public function login_form() {

        $template = new StdClass;
        $params = array('view' => 'customer',
                        'shopid' => $this->theshop->id,
                        'blockid' => (0 + @$this->theblock->instance->id));
        $thisurl = new moodle_url('/local/shop/front/view.php', $params);
        $template->loginurl = new moodle_url('/login/index.php', array('wantsurl' => $thisurl));

        return $this->output->render_from_template('local_shop/front_login_button', $template);
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

        try {
            $outputclass = 'front_invoice_header';
            shop_load_output_class($outputclass);
            $invoiceheader = new \local_shop\output\front_invoice_header($afullbill);
            $template = $invoiceheader->export_for_template($this);
            return $this->output->render_from_template('local_shop/front_invoice_heading', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
    }

    public function sales_contact() {
        global $OUTPUT;

        $config = get_config('local_shop');

        $template = new StdClass;

        $template->heading = $OUTPUT->heading(get_string('customersupport', 'local_shop'), 2, '', 'shop-sales-support');
        $template->sellermail = $config->sellermail;

        return $this->output->render_from_template('local_shop/front_sales_contact', $template);
    }

    public function paymode($afullbill) {
        global $OUTPUT;

        try {
            $outputclass = 'front_paymode';
            shop_load_output_class($outputclass);
            $invoiceheader = new \local_shop\output\front_paymode($afullbill);
            $template = $invoiceheader->export_for_template($this);
            return $OUTPUT->render_from_template('local_shop/front_paymode', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
    }

    /**
     *
     *
     */
    public function printable_bill_link($billid, $transid) {
        global $DB;

        $config = get_config('local_shop');
        $template = new StdClass;

        $template->transid = $transid;
        $template->billid = $billid;
        if (!empty($config->pdfenabled)) {
            $template->ispdf = true;
            $template->actionurl = new moodle_url('/local/shop/pro/pdf/pdfbill.php', array('transid' => $transid));
            $template->iconurl = $this->output->image_url('f/pdf-64');
        } else {
            $template->islogin = true;
            $template->actionurl = new moodle_url('/local/shop/front/order.popup.php');
            $billurl = new moodle_url('/local/shop/front/order.popup.php', array('billid' => $billid, 'transid' => $transid));
            $customerid = $DB->get_field('local_shop_bill', 'customerid', array('id' => $billid));
            if ($userid = $DB->get_field('local_shop_customer', 'hasaccount', array('id' => $customerid))) {
                $billuser = $DB->get_record('user', array('id' => $userid));
                $ticket = ticket_generate($billuser, 'immediate access', $billurl);
                $options = array('ticket' => $ticket);
                $template->loginbutton = $this->output->single_button('/login/index.php' , get_string('printbill', 'local_shop'), 'post',  $options);
            }
        }
        return $this->output->render_from_template('local_shop/bills_link_to_bill', $template);
    }

	/**
	 * Cut a text to some length.
	 *
	 * @param $str
	 * @param $n
	 * @param $end_char
	 * @return string
	 */
	public function trim_chars($str, $n = 500, $endchar = '...') {
		if (strlen($str) < $n) {
			return $str;
		}

		$str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));
		if (mb_strlen($str) <= $n) {
			return $str;
		}

		$out = "";
		$small = mb_substr($str, 0, $n);
		$out = $small.$endchar;
		return $out;
	}
}