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
 * A general renderer for all bill related data.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
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

    /**
     * The current shop instance
     */
    protected $theshop;

    /**
     * The current product catalog
     */
    protected $thecatalog;

    /**
     * The current access block having been used. May be unknown.
     */
    protected $theblock;

    /**
     * Prints a bill header.
     * @param object $afullbill
     * @param mixed $url
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
        $params = ['id' => $afullbill->theshop->id, 'transid' => $afullbill->transactionid];
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
     * Prints a link to the bill.
     * @param int $billid
     * @param string $transid
     */
    public function printable_bill_link($billid, $transid) {
        global $DB;

        $config = get_config('local_shop');
        $template = new StdClass;

        $template->transid = $transid;
        $template->billid = $billid;
        if (!empty($config->pdfenabled)) {
            $template->ispdf = true;
            $template->actionurl = new moodle_url('/local/shop/pro/pdf/pdfbill.php', ['transid' => $transid]);
            $template->iconurl = $this->output->image_url('f/pdf-64');
        } else {
            $template->islogin = true;
            $template->actionurl = new moodle_url('/local/shop/front/order.popup.php');
            $billurl = new moodle_url('/local/shop/front/order.popup.php', ['billid' => $billid, 'transid' => $transid]);
            $customerid = $DB->get_field('local_shop_bill', 'customerid', ['id' => $billid]);
            if ($userid = $DB->get_field('local_shop_customer', 'hasaccount', ['id' => $customerid])) {
                $billuser = $DB->get_record('user', ['id' => $userid]);
                $ticket = ticket_generate($billuser, 'immediate access', $billurl);
                $options = ['ticket' => $ticket];
                $loginurl = new moodle_url('/login/index.php');
                $label = get_string('printbill', 'local_shop');
                $template->loginbutton = $this->output->single_button($loginurl, $label, 'post',  $options);
            }
        }
        return $this->output->render_from_template('local_shop/bills_link_to_bill', $template);
    }

    /**
     * Prints when no items in bill.
     */
    public function no_items() {
        $template = new StdClass;

        return $this->output->render_from_template('local_shop/bills_no_items', $template);
    }

    /**
     * Prints customer inf part.
     * @param object $bill a full bill object.
     */
    public function customer_info($bill) {
        global $DB;

        $invoiceinfo = $bill->invoiceinfo; // Care of indirect magic __get with empty();
        if (!empty($invoiceinfo)) {
            $ci = (object) json_decode($bill->invoiceinfo);
            $useinvoiceinfo = true;
        } else {
            $ci = $DB->get_record('local_shop_customer', ['id' => $bill->customerid]);
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
     * Prints order detail, from the SESSION shopping cart
     * @param arrayref &$categories the product categores of the catalog.
     * @TODO : may be this turned into mustache
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

    /**
     * Prints a bill item line
     * @param object $billitem
     */
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
        $params = ['shopid' => $this->theshop->id, 'view' => 'editBillItem', 'item' => $billitem->id];
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
            $params = [
                'id' => $this->theshop->id,
                'view' => 'viewBill',
                'what' => 'relocating',
                'relocated' => $billitem->id,
                'z' => $billitem->ordering,
            ];
            $template->moveurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->movepix = $OUTPUT->pix_icon('t/move', 'move');

            $params = [
                'id' => $this->theshop->id,
                'billid' => $billitem->bill->id,
                'billitemid' => $billitem->id,
            ];
            $template->editurl = new moodle_url('/local/shop/bills/edit_billitem.php', $params);
            $template->editpix = $OUTPUT->pix_icon('i/edit', 'edit');

            $params = [
                'id' => $this->theshop->id,
                'view' => 'viewBill',
                'what' => 'deleteItem',
                'billitemid' => $billitem->id,
                'z' => $billitem->ordering,
                'billid' => $billitem->bill->id,
            ];
            $template->deleteurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->deletepix = $OUTPUT->pix_icon('t/delete', 'delete');
        }

        return $this->output->render_from_template('local_shop/bills_bill_item_line', $template);
    }

    /**
     * total line
     * @param object $ci a CatalogItem instance;
     * @TODO : amd-ize javascript.
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
     * Totalizer bloc
     * @param object $bill a full bill
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
     * Taxes block
     * @param object $bill
     */
    public function full_bill_taxes($bill) {
        global $PAGE;

        $renderer = $PAGE->get_renderer('local_shop');

        try {
            $outputclass = 'front_taxes';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\front_taxes($bill->taxlines, $bill->theshop);
            $template = $tpldata->export_for_template($renderer);
            return $this->output->render_from_template('local_shop/bills_full_taxes', $template);
        } catch (Exception $e) {
            throw new moodle_exception("Missing output class $outputclass");
        }
    }

    /**
     * Prints a fieldset start
     * @param string $legend
     * @param string $class
     */
    public function field_start($legend, $class) {
        $template = new StdClass;
        $template->varclass = $class;
        $template->legend = $legend;

        return $this->output->render_from_template('local_shop/bills_field_start', $template);
    }

    /**
     * Prints a fieldset end
     */
    public function field_end() {
        $template = new StdClass;
        return $this->output->render_from_template('local_shop/bills_field_end', $template);
    }

    /**
     * Prints a custom footer for online bills from the catalog info.
     * @param object $bill a full bill
     */
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

    /**
     * Prints the seller info
     * @param object $bill a full bill
     */
    public function bill_merchant_line($bill) {
        global $CFG;

        if (local_shop_supports_feature('shop/discounts')) {
            include_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');
            $hasdiscounts = \local_shop\Discount::count(['shopid' => $this->theshop->id]);
        }

        if (local_shop_supports_feature('shop/partners')) {
            $p = optional_param('p', 0 + @$SESSION->shop->partnerid, PARAM_INT);
        }

        if (is_null($bill)) {
            $template = new StdClass;
            $template->head = true;
            $template->haspartners = !($p > 1);
            $template->hasdiscounts = $hasdiscounts;
            return $this->output->render_from_template('local_shop/bills_merchant_line', $template);
        }

        $template = new StdClass;
        $template->head = false;
        $template->haspartners = !($p > 1);
        $template->hasdiscounts = $hasdiscounts;

        if ($hasdiscounts) {
            local_shop\Discount::export_to_bill_template($template, $bill);
        }

        // Magic getter trap !!
        $partnerid = $bill->partnerid;
        if ($template->haspartners && !empty($partnerid)) {
            include_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
            $partner = new \local_shop\Partner($bill->partnerid);
            $template->partnerkey = $partner->partnerkey;
            $template->partnername = $partner->name;
        }

        $params = ['view' => 'viewBill', 'id' => $this->theshop->id, 'billid' => $bill->id];
        $template->billurl = new moodle_url('/local/shop/bills/view.php', $params);
        $template->emissiondate = date('Ymd', $bill->emissiondate);
        $template->id = $bill->id;

        if (!empty($bill->customer)) {
            $template->firstname = $bill->customer->firstname;
            $template->lastname = $bill->customer->lastname;
        }

        $params = ['transid' => $bill->transactionid, 'shopid' => $this->theshop->id];
        $template->scanurl = new moodle_url('/local/shop/front/scantrace.php', $params);
        $template->transactionid = $bill->transactionid;
        $template->onlinetransactionid = $bill->onlinetransactionid;

        $template->emissiondatestr = strftime('%c', $bill->emissiondate);
        $template->idnumber = $bill->idnumber;

        $untaxedamount = sprintf("%.2f", round($bill->untaxedamount, 2));
        $template->amount1 = $untaxedamount;
        $template->amountsource1 = 'untaxedamount';
        if ($untaxedamount != $bill->amount) {
            $amount = sprintf("%.2f", round($bill->amount, 2));
            $template->amount2 = $amount;
            $template->amountsource2 = 'amount';
        }
        $template->currency = get_string($bill->currency.'symb', 'local_shop');

        $params = ['id' => $this->theshop->id, 'view' => 'viewCustomer', 'customer' => $bill->customer->id];
        $template->customerurl = new moodle_url('/local/shop/customers/view.php', $params);
        $template->email = $bill->email;

        return $this->output->render_from_template('local_shop/bills_merchant_line', $template);
    }

    /**
     * Prints the bill state flow controller
     * @param int $status the current state
     * @param moodle_url $url
     */
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
            throw new moodle_exception("Missing output class $outputclass");
        }
    }

    /**
     * Prints a currency selector for bill overview.
     * @TODO : check the compatibility with the locallib.php currency selector.
     * @TODO : shift javascript to amd
     * @param string $cur the current currency
     * @param mixed $url the base url
     * @param array $cgicontext a set of named attributes to add to outgoing urls
     */
    public function print_currency_choice($cur, $url, $cgicontext = []) {
        global $DB;

        $str = '';
        $usedcurrencies = $DB->get_records('local_shop_bill', null, '', ' DISTINCT(currency), currency ');
        if (count($usedcurrencies) > 1) {
            $curmenu = [];
            foreach ($usedcurrencies as $curid => $void) {
                if ($curid) {
                    $curmenu[$curid] = get_string($curid, 'local_shop');
                }
            }
            $str .= '<form name="currencyselect" action="'.$url.'" method="POST">';
            foreach ($cgicontext as $key => $value) {
                $str .= "<input type=\"hidden\" name=\"$key\" value=\"$value\" />\n";
            }
            $attrs = ['onchange' => 'document.forms[\'currencyselect\'].submit();'];
            $str .= html_writer::select($curmenu, 'cur', $cur, ['' => 'CHOOSEDOTS'], $attrs);
            $str .= '</form>';
        }
        return $str;
    }

    /**
     * print a relocation box for moves.
     * @param int $billid
     * @param int $ordering
     * @param int $z
     * @param int $relocated
     */
    public function relocate_box($billid, $ordering, $z, $relocated) {
        global $OUTPUT;

        $params = [
            'view' => 'viewBill',
            'billid' => $billid,
            'what' => 'relocate',
            'relocated' => $relocated,
            'z' => $z,
            'at' => $ordering
        ];
        $relocateurl = new moodle_url('/local/shop/bills/view.php', $params);

        $pixurl = $OUTPUT->image_url('relocatebox', 'local_shop');

        $template = new StdClass;
        $template->relocateurl = $relocateurl;
        $template->pixurl = $pixurl;

        return $this->output->render_from_template('local_shop/bills_relocate_box', $template);
    }

    /**
     * Prints a link for attachements
     * @param object $bill a full bill
     */
    public function attachments($bill) {
        global $OUTPUT;

        $template = new StdClass;

        if (!local_shop_supports_feature('bill/attachements')) {
            $template->billnoattachementnotification = $OUTPUT->notification(get_string('billattachementsispro', 'local_shop'));
        } else {
            // We should already be in an a pro extended renderer.
            $template = $this->export_attachements_template($bill);
        }

        return $this->output->render_from_template('local_shop/bills_bill_attachments', $template);
    }

    /**
     * Print a bill control block
     */
    public function bill_controls($bill) {
        $template = new StdClass;

        if (empty($bill->idnumber)) {
            $template->bill = false;
            $params = ['id' => $this->theshop->id, 'billid' => $bill->id];
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

    /**
     * Prints a little form for lettering bills (associating accountance code)
     * @param int $shopid
     * @param object $bill a full bill
     */
    public function lettering_form($shopid, $bill) {
        $template = new StdClass;

        $template->shopid = $shopid;
        $template->billid = $bill->id;
        $template->idnumber = $bill->idnumber;

        return $this->output->render_from_template('local_shop/bills_lettering_form', $template);
    }

    /**
     * Search widget for bills
     * àparam object $bill
     */
    public function search_bill_line($bill) {
        static $odd = 0;

        try {
            $outputclass = 'search_bill_line';
        } catch (Exception $e) {
            throw new moodle_exception("Missing output class $outputclass");
        }
        shop_load_output_class($outputclass);
        $tpldata = new \local_shop\output\search_bill_line($bill);
        $template = $tpldata->export_for_template($this->output);

        $template->lineclass = ($odd) ? 'r0' : 'r1';
        $odd = ($odd + 1) % 2;

        return $this->output->render_from_template('local_shop/bills_search_line', $template);
    }

    /**
     * full search form
     * @TODO : recheck the consistance of using block instance here.
     * @param object $blockinstance the shop_access instance
     * @param int $billcount
     */
    public function search_form($blockinstance, $billcount) {
        global $OUTPUT;

        try {
            $outputclass = 'bills_search_form';
            shop_load_output_class($outputclass);
            $tpldata = new \local_shop\output\bills_search_form($blockinstance, $billcount);
            $template = $tpldata->export_for_template($OUTPUT);
            return $this->output->render_from_template('local_shop/bills_search_form', $template);
        } catch (Exception $e) {
            throw new moodle_exception("Missing output class $outputclass");
        }
    }

    /**
     * Prints a search result set
     * @param array $results array of matching bills
     * @param object $theshop
     */
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

    /**
     * Bills status line
     * @param string $status bill state code
     */
    public function bill_status_line($status) {
        $template = new StdClass;
        $template->statusstr = get_string('bill_'.$status.'s', 'local_shop');
        return $this->output->render_from_template('local_shop/bills_bill_status_line', $template);
    }

    /**
     * Prints subtotal row of the bill list
     * @param array $data a data stub as an associative array giving data1, source1 data2 and source 2 values for resp. primary and secondary subtotal.
     * @param string $billcurrency currency symbol.
     * @param bool $samecurrency should be set false if all bills in the list do not share the same currecy unit.
     */
    public function bill_group_subtotal($data, $billcurrency, $samecurrency) {
        global $CFG;

        if (local_shop_supports_feature('shop/partners')) {
            $p = optional_param('p', 0 + @$SESSION->shop->partnerid, PARAM_INT);
        }

        $template = new StdClass;

        // Adjust span of total row.
        $template->span = 5;
        if ($p) {
            $template->span -= 1;
        }

        if (local_shop_supports_feature('shop/discounts')) {
            include_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');
            if (local_shop\Discount::count(['shopid' => $this->theshop->id])) {
                $template->span += 1;
            }
        }

        $template->subtotal1 = sprintf('%.2f', round($data['data1'], 2));
        $template->source1 = $data['source1'];

        if ($data['data1'] != $data['data2']) {
            $template->hassubtotal2 = true;
            $template->subtotal2 = sprintf('%.2f', round($data['data2'], 2));
            $template->source2 = $data['source2'];
        }

        $template->currency = get_string($billcurrency.'symb', 'local_shop');
        $template->issamecurrency = $samecurrency;

        return $this->output->render_from_template('local_shop/bills_group_subtotal', $template);
    }

    /**
     * Prints a link to a viewable (not editable) bill.
     * @param object $theshop
     */
    public function bill_view_links($theshop) {

        $shopid = optional_param('shopid', false, PARAM_INT);
        $y = optional_param('y', false, PARAM_INT);
        $m = optional_param('m', false, PARAM_INT);
        $status = optional_param('status', false, PARAM_TEXT);

        $params = [
            'what' => 'allbills',
            'format' => 'excel',
            'y' => $y,
            'm' => $m,
            'shopid' => $shopid,
            'status' => $status,
        ];

        if (local_shop_supports_feature('shop/partners')) {
            $p = optional_param('p', 0, PARAM_INT);
            $params['p'] = $p;
        }

        $excelurl = new moodle_url('/local/shop/export/export.php', $params);
        $billurl = new moodle_url('/local/shop/bills/edit_bill.php', ['shopid' => $theshop->id]);

        $template = new StdClass;

        $template->excelurl = $excelurl;
        $template->billurl = $billurl;

        return $this->output->render_from_template('local_shop/bills_bill_view_links', $template);
    }

    /**
     * Switch to toggle paging
     * @param moodle_url $url
     * @param string $urlfilter
     */
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

    /**
     * Print all search options in bills.
     * @param object $mainrenderer the shop main renderer for global functions
     * @param bool $fullview
     */
    public function bill_options($mainrenderer, $fullview) {
        global $SESSION;

        $y = optional_param('y', 0 + @$SESSION->shop->billyear, PARAM_INT);
        $m = optional_param('m', 0 + @$SESSION->shop->billmonth, PARAM_INT);
        if (local_shop_supports_feature('shop/partners')) {
            $p = optional_param('p', 0 + @$SESSION->shop->partnerid, PARAM_INT);
        }

        $status = optional_param('status', 'COMPLETE', PARAM_TEXT);
        $cur = optional_param('cur', 'EUR', PARAM_TEXT);
        $dir = optional_param('dir', 'asc', PARAM_TEXT);
        $sortorder = optional_param('order', 'emissiondate DESC', PARAM_TEXT);
        $customerid = optional_param('customerid', 0, PARAM_INT);
        $shopid = optional_param('shopid', 0, PARAM_INT);

        $template = new StdClass;

        $params = [
            'view' => 'viewAllBills',
            'dir' => $dir,
            'order' => $sortorder,
            'status' => $status,
            'customerid' => $customerid,
            'shopid' => $shopid,
            'cur' => $cur,
            'y' => $y,
            'm' => $m,
        ];
        if (local_shop_supports_feature('shop/partners')) {
            $params['p'] = $p;
        }

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('cur');
        $template->currencyselect = $mainrenderer->currency_choice($cur, $url);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('shopid');
        $template->shopselect = $mainrenderer->shop_choice($url, true, $shopid);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('y');
        $template->yearselect = $mainrenderer->year_choice($y, $url, true);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('m');
        $template->monthselect = $mainrenderer->month_choice($m, $url, true);

        $url = new moodle_url('/local/shop/bills/view.php', $params);
        $url->remove_params('customerid');
        $template->customerselect = $mainrenderer->customer_choice($customerid, $url, true);

        if (local_shop_supports_feature('shop/partners')) {
            $url = new moodle_url('/local/shop/bills/view.php', $params);
            $url->remove_params('p');
            $template->partnerselect = $mainrenderer->partner_choice($p, $url, true);
        }

        $params = ['view' => 'search'];
        $template->searchurl = new moodle_url('/local/shop/bills/view.php', $params);
        $template->searchinbillsstr = get_string('searchinbills', 'local_shop');

        if ($fullview) {
            $params = [
                'view' => 'viewAllBills',
                'dir' => $dir,
                'order' => $sortorder,
                'status' => $status,
                'customerid' => $customerid,
                'what' => 'switchfulloff',
            ];
            $template->switchfullviewurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->switchviewstr = get_string('fullviewoff', 'local_shop');
        } else {
            $params = [
                'view' => 'viewAllBills',
                'dir' => $dir,
                'order' => $sortorder,
                'status' => $status,
                'customerid' => $customerid,
                'what' => 'switchfullon',
            ];
            $template->switchfullviewurl = new moodle_url('/local/shop/bills/view.php', $params);
            $template->switchviewstr = get_string('fullviewon', 'local_shop');
        }

        return $this->output->render_from_template('local_shop/bills_options', $template);
    }

    /**
     * Prints ownership info. Ownership is related to partner subbilling.
     * @param object $bill
     */
    public function ownership($bill) {

        $template = new StdClass;

        if (!local_shop_supports_feature('shop/partners')) {
            $template->billnoownershipnotification = $this->output->notification(get_string('billownershipispro', 'local_shop'));
        } else {
            // We should already be in an a pro extended renderer.
            $template = $this->export_ownership_template($bill);
        }

        return $this->output->render_from_template('local_shop/bills_bill_ownership', $template);
    }
}
