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

}