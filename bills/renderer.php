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

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');
require_once($CFG->dirroot.'/local/shop/renderer.php');

use local_shop\Tax;
use local_shop\Bill;

/**
 *
 */
class shop_bills_renderer extends local_shop_base_renderer {

    protected $theshop;
    protected $thecatalog;
    protected $theblock;

    /**
     *
     */
    public function bill_header($afullbill, $url) {

        $template = new StdClass;
        if ($afullbill->status == 'PENDING' || $afullbill->status == 'PLACED') {
            $template->heading = get_string('order', 'local_shop');
        } else {
            $template->heading = get_string('bill', 'local_shop');
        }

        $template->billunique = 'B-'.date('Ymd', $afullbill->emissiondate).'-'.$afullbill->id;
        $template->emissiondate = userdate($afullbill->emissiondate);

        $template->transid = $afullbill->transactionid;
        $params = array('id' => $afullbill->theshop->id, 'transid' => $afullbill->transactionid);
        $template->scanurl = new moodle_url('/local/shop/front/scantrace.php', $params);

        $template->onlinetransactionid = $afullbill->onlinetransactionid;
        $template->url = $url;

        $template->letteringhelpicon = $this->output->help_icon('lettering', 'local_shop');

        if ($afullbill->status == 'PENDING' || $afullbill->status == 'PLACED' || $afullbill->status == 'WORKING') {
            $template->paid = false;
        } else {
            $template->paid = true;
            $template->letteringform = $this->lettering_form($afullbill->theshop->id, $afullbill);
        }
        $template->paymode = get_string($afullbill->paymode, 'shoppaymodes_'.$afullbill->paymode);
        $template->printablebilllink = $this->printable_bill_link($afullbill->id, $afullbill->transactionid);

        $template->title = $afullbill->title;

        return $this->output->render_from_template('local_shop/bills_bill_header', $template);
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

    public function no_items() {
        $template = new StdClass;

        return $this->output->render_from_template('local_shop/bills_no_items', $template);
    }

    /**
     *
     *
     */
    public function customer_info($bill) {
        global $DB;

        $invoiceinfo = $bill->invoiceinfo; // Care of indirect magic __get with empty();
        if (!empty($invoiceinfo)) {
            $ci = (object) json_decode($bill->invoiceinfo);
            $useinvoiceinfo = true;
        } else {
            $ci = $DB->get_record('local_shop_customer', array('id' => $bill->customerid));
            $useinvoiceinfo = false;
        }

        $template = $ci;

        $template->emissiondate = userdate($bill->emissiondate);

        if ($useinvoiceinfo) {
            $template->useinvoiceinfo = true;
        }

        $template->country = strtoupper($ci->country);

        return $this->output->render_from_template('local_shop/bill_customer_info', $template);
    }

    /**
     *
     *
     */
    /*
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
    */

    /**
     * prints tabs for js activation of the category panel
     *
     */
    /*
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
    */

    /**
     * prints a full catalog on screen
     * @param array $catgories the full product line extractred from Catalog
     */
     /*
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

        try {
            $outputclass = 'bills_product_block';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\bills_product_block($product);
            $template = $tpldata->export_for_template($this->output);
            return $this->output->render_from_template('local_shop/bills_product_block', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }

        return $this->output->render_from_template();
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

        $template = new StdClass;

        $template->thumburl = $bundle->thumb;
        if ($bundle->image != '') {
            $template->hasimage = true;
            $template->imageurl = new moodle_url('/local/shop/photo.php', array('img' => $bundle->image));
        }
        $template->name = $bundle->name;
        $template->description = format_string($bundle->description);

        $outputclass = 'bills_product_block';
        shop_load_output_class($outputclass);

        $ttcprice = 0;
        foreach ($bundle->set as $product) {
            $ttcprice += $product->get_taxed_price(1);
            $product->noorder = true; // Bundle can only be purchased as a group.
            $tpldata = new \local_shop\output\bills_product_block($product);
            $template->parts[] = $tpldata->export_for_template($this->output);
        }
        $template->code = $bundle->code;
        $template->ttcprice = $ttcprice;
        $template->currency = $bundle->currency;
        $template->jshandler = 'addOneUnit(\''.$bundle->shortname.'\', \''.$bundle->code;
        $template->jshandler .= '\', '.$ttcprice.', \''.$bundle->maxdeliveryquant.'\')';
        $template->shortname = $bundle->shortname;

        return $this->output->render_from_template('local_shop/bills_bundle', $template);
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
            $str .= $OUTPUT->pix_icon('t/delete', get_string('deleteone', 'local_shop'));
            $str .= '</a>';
        }

        return $str;
    }
    */

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
        static $movestr;
        static $editstr;
        static $deletestr;

        $template = new StdClass;

        if (empty($billitem)) {
            $template->head = true;

            return $this->output->render_from_template('local_shop/bills_bill_item_line', $template);
        }

        $template->head = false;
        $params = array('shopid' => $this->theshop->id, 'view' => 'editBillItem', 'item' => $billitem->id);
        $template->billurl = new moodle_url('/local/coursehop/bills/view.php', $params);
        $template->billordering = $billitem->ordering;
        $template->itemcode = $billitem->itemcode;
        $template->abstract = format_string($billitem->abstract);
        $template->description = format_text($billitem->description);
        $template->delay = $billitem->delay;
        $template->unticost = sprintf("%.2f", round($billitem->unitcost, 2));
        $template->quantity = $billitem->quantity;
        $template->totalprice = sprintf("%.2f", round($billitem->totalprice, 2));
        $template->taxcode = $billitem->taxcode;

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
            $template->moveurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->movepix = $OUTPUT->pix_icon('t/move', 'move');

            $params = array('id' => $this->theshop->id,
                            'billid' => $billitem->bill->id,
                            'billitemid' => $billitem->id);
            $template->editurl = new moodle_url('/local/shop/bills/edit_billitem.php', $params);
            $template->editpix = $OUTPUT->pix_icon('i/edit', 'edit');

            $params = array('id' => $this->theshop->id,
                            'view' => 'viewBill',
                            'what' => 'deleteItem',
                            'billitemid' => $billitem->id,
                            'z' => $billitem->ordering,
                            'billid' => $billitem->bill->id);
            $template->deleteurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->deletepix = $OUTPUT->pix_icon('t/delete', 'delete');
        }

        return $this->output->render_from_template('local_shop/bills_bill_item_line', $template);
    }

    /**
     * @param object $ci a CatalogItem instance;
     */
    public function item_total_line($ci) {
        global $CFG, $OUTPUT;

        $template = new StdClass;
        $ttcprice = $ci->get_taxed_price($ci->preset, $ci->taxcode);
        $template->total = $ttcprice * $ci->preset;
        $template->preset = $ci->preset;
        $template->shortname = $ci->shortname;
        $template->name = $ci->name;
        $template->jsclearhandler = 'Javascript:ajax_clear_product(\''.$CFG->wwwroot.'\', \''.$this->theshop->id.'\', \''.$ci->shortname.'\')';
        $template->deleteicon = $OUTPUT->pix_icon('t/delete', 'delete');
        $template->jsupdatehandler = 'ajax_update_product(\''.$ci->shortname.'\', this)';
        $template->ttcprice = sprintf("%.2f", round($ttcprice, 2));
        $template->currency = $ci->currency;

        return $this->output->render_from_template('local_shop/bills_item_total_line', $template);
    }

    /**
     * @param object $bill
     */
    public function full_bill_totals($bill) {

        $config = get_config('local_shop');

        $template = new StdClass;
        $template->currency = $this->theshop->get_currency('symbol');

        if (!empty($bill->discount) || !empty($config->hasshipping)) {
            $template->finaltaxedtotal = $bill->finaltaxedtotal;
        }

        if ($bill->discount != 0) {
            $template->discountrate = $config->discountrate;
            $template->finaltaxedtotal = $bill->finaltaxedtotal;
        }

        $template->finaluntaxedtotal = sprintf('%0.2f', round($bill->finaluntaxedtotal, 2));
        $template->finaltaxestotal = sprintf('%0.2f', round((0 + @$bill->finaltaxestotal), 2));

        if ($bill->status == SHOP_BILL_COMPLETE || $bill->status == SHOP_BILL_SOLDOUT) {
            $template->finaltotalpricestr = get_string('paiedfinaltotalprice', 'local_shop');
        } else {
            $template->finaltotalpricestr = get_string('finaltotalprice', 'local_shop');
        }

        if (empty($config->hasshipping)) {
            $template->finaltaxedtotal = sprintf('%0.2f', round($bill->finaltaxedtotal, 2));

        } else {
            $template->finaltaxedtotal = sprintf('%0.2f', round($bill->finaltaxedtotal + $bill->shipping->taxedvalue, 2));

            $template->hasshipping = true;
            $template->shippingtaxedvalue = sprintf('%0.2f', round($bill->shipping->taxedvalue, 2));

            if ($bill->status == SHOP_BILL_COMPLETE || $bill->status == SHOP_BILL_SOLDOUT) {
                $template->finaltotalpricestr = get_string('paiedfinaltotalprice', 'local_shop');
            } else {
                $template->finaltotalpricestr = get_string('finaltotalprice', 'local_shop');
            }

            $template->finalshippedtaxedtotal = sprintf('%0.2f', round($bill->finalshippedtaxedtotal, 2));
        }

        return $this->output->render_from_template('local_shop/bills_bill_totals', $template);
    }

    /**
     * @param object $bill
     */
    public function full_bill_taxes($bill) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_shop');

        try {
            $outputclass = 'front_taxes';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\front_taxes($bill->taxlines, $bill->finaltaxestotal, $bill->theshop);
            $template = $tpldata->export_for_template($renderer);
            return $this->output->render_from_template('local_shop/bills_full_taxes', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
    }

    public function field_start($legend, $class) {
        $template = new StdClass;
        $template->varclass = $class;
        $template->legend = $legend;

        return $this->output->render_from_template('local_shop/bills_field_start', $template);
    }

    public function field_end() {
        $template = new StdClass;
        return $this->output->render_from_template('local_shop/bills_field_end', $template);
    }

    public function bill_footer($bill) {
        $billfooter = $bill->thecatalogue->billfooter;
        $template = new StdClass;
        $systemcontext = context_system::instance();
        if (!empty($billfooter)) {
            $billfooter = file_rewrite_pluginfile_urls($billfooter, 'pluginfile.php', $systemcontext->id,
                'local_shop', 'catalogbillfooter', $bill->thecatalogue->id, null);
            $template->billfooter = $billfooter;
        }
        return $this->output->render_from_template('local_shop/bills_bill_footer', $template);
    }

    public function bill_merchant_line($portlet) {

        if (is_null($portlet)) {
            $template = new StdClass;
            $template->head = true;
            return $this->output->render_from_template('local_shop/bills_merchant_line', $template);
        }

        $template = new StdClass;
        $template->head = false;
        $params = array('view' => 'viewBill', 'id' => $this->theshop->id, 'billid' => $portlet->id);
        $template->billurl = new moodle_url('/local/shop/bills/view.php', $params);
        $template->emissiondate = date('Ymd', $portlet->emissiondate);
        $template->id = $portlet->id;

        if (!empty($portlet->customer)) {
            $template->firstname = $portlet->customer->firstname;
            $template->lastname = $portlet->customer->lastname;
        }

        $params = array('transid' => $portlet->transactionid, 'shopid' => $this->theshop->id);
        $template->scanurl = new moodle_url('/local/shop/front/scantrace.php', $params);
        $template->transactionid = $portlet->transactionid;
        $template->onlinetransactionid = $portlet->onlinetransactionid;

        $template->emissiondatestr = strftime('%c', $portlet->emissiondate);
        $template->idnumber = $portlet->idnumber;
        $template->amount = sprintf("%.2f", round($portlet->amount, 2));
        $template->currency = get_string($portlet->currency.'symb', 'local_shop');

        $params = array('id' => $this->theshop->id, 'view' => 'viewCustomer', 'customer' => $portlet->customer->id);
        $template->customerurl = new moodle_url('/local/shop/customers/view.php', $params);
        $template->email = $portlet->email;

        return $this->output->render_from_template('local_shop/bills_merchant_line', $template);
    }

    public function flow_controller($status, $url) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_shop');

        try {
            $outputclass = 'bills_flow_control';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\bills_flow_control($status, $url);
            $template = $tpldata->export_for_template($renderer);
            return $this->output->render_from_template('local_shop/bills_flow_controller', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
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

        $pixurl = $OUTPUT->image_url('relocatebox', 'local_shop');

        $template = new StdClass;
        $template->relocateurl = $relocateurl;
        $template->pixurl = $pixurl;

        return $this->output->render_from_template('local_shop/bills_relocate_box', $template);
    }

    public function attachments($bill) {
        global $OUTPUT;

        $template = new StdClass;
        $template->heading = $OUTPUT->heading(get_string('attachements', 'local_shop'));

        $fs = get_file_storage();

        $contextid = context_system::instance()->id;
        $attachments = $fs->get_area_files($contextid, 'local_shop', 'billattachments', $bill->id, true);
        if (empty($attachments)) {
            $template->attachment = false;
            $template->nobillattachements = $OUTPUT->notification(get_string('nobillattachements', 'local_shop'));
        } else {
            $template->attachment = true;
            foreach ($attachments as $afile) {
                $attachedfiletpl = $this->attachement($afile, $bill);
                $template->attachedfiles[] = $attachedfiletpl;
            }
        }

        $params = array('type' => 'bill', 'billid' => $bill->id, 'id' => $this->theshop->id);
        $attachurl = new moodle_url('/local/shop/bills/attachto.php', $params);
        $template->attachurl = $attachurl;

        return $this->output->render_from_template('local_shop/bills_bill_attachments', $template);
    }

    public function attachment($file, $bill) {
        global $OUTPUT;

        $template = new StdClass;
        $context = context_system::instance();

        $pathinfo = pathinfo($file->get_filename());
        $type = strtoupper($pathinfo['extension']);
        $filename = $pathinfo['basename'];
        $fileicon = $OUTPUT->image_url("/f/$type");
        if (!file_exists($fileicon)) {
            $template->fileicon = $OUTPUT->image_url('/f/unkonwn');
        }

        $template->filename = $file->get_filename();
        $template->fileurl = moodle_url::make_pluginfile_url($context->id, 'local_shop', 'billattachments',
                                                   $file->get_itemid(), '/', $filename);

        $template->filesize = $file->get_filesize();

        $params = array('id' => $bill->id, 'what' => 'unattach', 'type' => $portlet->attachementtype,
                        'file' => $filename);
        $template->linkurl = new moodle_url('/local/shop/bills/view.php', $params);

        return $template;
    }

    public function bill_controls($bill) {
        $template = new StdClass;

        if (empty($bill->idnumber)) {
            $template->bill = false;
            $params = array('id' => $this->theshop->id, 'billid' => $bill->id);
            $billitemurl = new moodle_url('/local/shop/bills/edit_billitem.php', $params);
            $template->billitemurl = $billitemurl;
        } else {
            $template->bill = true;
        }
        $params = ['id' => $this->theshop->id, 'view' => 'viewBill', 'billid' => $bill->id, 'what' => 'recalculate'];
        $recalcurl = new moodle_url('/local/shop/bills/view.php', $params);
        $template->recalcurl = $recalcurl;
        return $this->output->render_from_template('local_shop/bills_bill_controls', $template);
    }

    public function lettering_form($shopid, &$afullbill) {
        $template = new StdClass;

        $template->shopid = $shopid;
        $template->billid = $afullbill->id;
        $template->idnumber = $afullbill->idnumber;

        return $this->output->render_from_template('local_shop/bills_lettering_form', $template);
    }

    public function search_bill_line($bill) {
        static $odd = 0;

        try {
            $outputclass = 'search_bill_line';
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
        shop_load_output_class($outputclass);
        $tpldata = new \local_shop\output\search_bill_line($bill);
        $template = $tpldata->export_for_template($this->output);

        $template->lineclass = ($odd) ? 'r0' : 'r1';
        $odd = ($odd + 1) % 2;

        return $this->output->render_from_template('local_shop/bills_search_line', $template);
    }

    public function search_form($blockinstance, $billcount) {
        global $OUTPUT;

        try {
            $outputclass = 'bills_search_form';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\bills_search_form($blockinstance, $billcount);
            $template = $tpldata->export_for_template($OUTPUT);
            return $this->output->render_from_template('local_shop/bills_search_form', $template);
        } catch (Exception $e) {
            print_error("Missing output class $outputclass");
        }
    }

    public function search_results($results, $theshop) {
        $template = new StdClass;
        $odd = 0;
        foreach ($result as $bill) {
            $afullbill = Bill::get_by_transaction($bill->transactionid);
            $bill->lineclass = ($odd) ? 'r0' : 'r1';
            $odd = ($odd + 1) % 2;
            $template->afullbill[] = $bill;
        }

        return $this->output->render_from_template('local_shop/bills_search_result', $template);
    }

    public function bill_status_line($status) {
        $template = new StdClass;
        $template->statusstr = get_string('bill_'.$status.'s', 'local_shop');
        return $this->output->render_from_template('local_shop/bills_bill_status_line', $template);
    }

    public function bill_group_subtotal($subtotal, $billcurrency, $samecurrency) {
        $template = new StdClass;

        $template->subtotal = sprintf('%.2f', round($subtotal, 2));
        $template->currency = get_string($billcurrency.'symb', 'local_shop');
        $template->issamecurrency = $samecurrency;

        return $this->output->render_from_template('local_shop/bills_group_subtotal', $template);
    }

    public function bill_view_links(&$theshop) {

        $shopid = optional_param('shopid', false, PARAM_INT);
        $y = optional_param('y', false, PARAM_INT);
        $m = optional_param('m', false, PARAM_INT);
        $status = optional_param('status', false, PARAM_TEXT);

        $params = array('what' => 'allbills',
                        'format' => 'excel',
                        'y' => $y,
                        'm' => $m,
                        'shopid' => $shopid,
                        'status' => $status);
        $excelurl = new moodle_url('/local/shop/export/export.php', $params);
        $billurl = new moodle_url('/local/shop/bills/edit_bill.php', array('shopid' => $theshop->id));

        $template = new StdClass;

        $template->excelurl = $excelurl;
        $template->billurl = $billurl;

        return $this->output->render_from_template('local_shop/bills_bill_view_links', $template);
    }

    public function no_paging_switch($url, $urlfilter) {
        $nopaging = optional_param('nopaging', 0, PARAM_BOOL);
        if ($nopaging) {
            $str = '<span class="nolink">'.get_string('nopaging', 'local_shop').'</span>';
        } else {
            $urlfilter = str_replace('nopaging=0', 'nopaging=1', $urlfilter);
            $urlfilter = preg_replace('/billpage=\d+/', '', $urlfilter);
            $urlfilter .= '&billpage=-1';
            $str = ' <a href="'.$url.'&'.$urlfilter.'">'.get_string('nopaging', 'local_shop').'</a>';
        }

        return $str;
    }

    public function bill_options($mainrenderer, $fullview) {
        global $SESSION;

        $y = optional_param('y', 0 + @$SESSION->shop->billyear, PARAM_INT);
        $m = optional_param('m', 0 + @$SESSION->shop->billmonth, PARAM_INT);
        $shopid = optional_param('shopid', 0, PARAM_INT);
        $status = optional_param('status', 'COMPLETE', PARAM_TEXT);
        $cur = optional_param('cur', 'EUR', PARAM_TEXT);
        $dir = optional_param('dir', 'asc', PARAM_TEXT);
        $sortorder = optional_param('order', 'emissiondate', PARAM_TEXT);
        $customerid = optional_param('customerid', 0, PARAM_INT);
        $shopid = optional_param('shopid', 1, PARAM_INT);

        $template = new StdClass;

        $params = array('view' => 'viewAllBills',
                        'dir' => $dir,
                        'order' => $sortorder,
                        'status' => $status,
                        'customerid' => $customerid,
                        'shopid' => $shopid,
                        'cur' => $cur,
                        'y' => $y,
                        'm' => $m);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('cur');
        $template->currencyselect = $mainrenderer->currency_choice($cur, $url);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('shopid');
        $template->shopselect = $mainrenderer->shop_choice($url, true);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('y');
        $template->yearselect = $mainrenderer->year_choice($y, $url, true);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('m');
        $template->monthselect = $mainrenderer->month_choice($m, $url, true);

        $params = array('view' => 'search');
        $template->searchurl = new moodle_url('/local/shop/bills/view.php', $params);
        $template->searchinbillsstr = get_string('searchinbills', 'local_shop');

        if ($fullview) {
            $params = array('view' => 'viewAllBills',
                            'dir' => $dir,
                            'order' => $sortorder,
                            'status' => $status,
                            'customerid' => $customerid,
                            'what' => 'switchfulloff');
            $template->switchfullviewurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->switchviewstr = get_string('fullviewoff', 'local_shop');
        } else {
            $params = array('view' => 'viewAllBills',
                            'dir' => $dir,
                            'order' => $sortorder,
                            'status' => $status,
                            'customerid' => $customerid,
                            'what' => 'switchfullon');
            $template->switchfullviewurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->switchviewstr = get_string('fullviewon', 'local_shop');
        }

        return $this->output->render_from_template('local_shop/bills_options', $template);
    }
}