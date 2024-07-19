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

namespace local_shop\output;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use Templatable;
use renderer_base;
use local_shop\Bill;
use StdClass;
use moodle_url;
use context_system;

/**
 * the invoice header
 */
class front_invoice_header implements Templatable {

    /** @var the bill */
    protected $bill;

    /**
     * Constructor
     * @param Bill $bill
     */
    public function __construct(Bill $bill) {
        $this->bill = $bill;
    }

    /**
     * Exporter for template
     * @param renderer_base $output
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;

        $config = get_config('local_shop');

        $afullbill = $this->bill;

        $realized = [SHOP_BILL_SOLDOUT, SHOP_BILL_COMPLETE, SHOP_BILL_PARTIAL, SHOP_BILL_PREPROD];

        $template = new StdClass();

        $template->subheadingstr = '';
        if (!in_array($afullbill->status, $realized)) {
            $headerstring = get_string('ordersheet', 'local_shop');
            $template->subheadingstr = get_string('ordertempstatusadvice', 'local_shop');
        } else {
            if (empty($afullbill->idnumber)) {
                $headerstring = get_string('proformabill', 'local_shop');
            } else {
                $headerstring = get_string('bill', 'local_shop');
            }
        }

        if (!empty($afullbill->withlogo)) {
            $template->withlogo = true;

            if (!empty($config->sellerlogo)) {
                $syscontext = context_system::instance();
                $component = 'local_shop';
                $filearea = 'shoplogo';
                $itemid = 0;
                $filepath = $config->sellerlogo;
                $path = "/$syscontext->id/$component/$filearea/$itemid".$filepath;
                $template->logourl = moodle_url::make_file_url($CFG->wwwroot.'/pluginfile.php', $path);
            } else {
                $template->logourl = $output->image_url('logo', 'theme');
            }
        }

        $template->headingstr = $output->heading($headerstring, 1);
        $template->headingstringstr = $headerstring; // Unformated version.

        $template->sellername = $config->sellername;
        $template->selleraddress = $config->selleraddress;
        $template->sellerzip = $config->sellerzip;
        $template->sellercity = $config->sellercity;
        $template->sellercountry = $config->sellercountry;

        $emissiondatestamp = date('Ymd', $afullbill->emissiondate);
        $template->uniqueid = 'B-'.$emissiondatestamp.'-'.$afullbill->ordering;
        $template->emissiondatestr = date(get_string('billdatefmt', 'local_shop'), $afullbill->emissiondate);
        $template->billid = $afullbill->id;

        $template->transactionid = $afullbill->transactionid;
        $template->providetransactioncodestr = get_string('providetransactioncode', 'local_shop');

        // Be carefull of empty on a magic __get return;
        $invoiceinfo = $afullbill->invoiceinfo;
        if (empty($invoiceinfo)) {
            if (!empty($afullbill->customer->organisation)) {
                $template->organisation = $afullbill->customer->organisation;
            }
            $template->invoicename = $afullbill->customer->lastname.' '.$afullbill->customer->firstname;
            $template->invoicezip = $afullbill->customer->zip;
            $template->invoicecity = $afullbill->customer->city;
            $template->invoicecountry = strtoupper($afullbill->customer->country);
        } else {
            // Invoice identity comes from invoice info.
            $customer = json_decode($afullbill->invoiceinfo);
            if (!empty($customer->organisation)) {
                $template->organisation = $customer->organisation;
            }
            $template->invoicename = $customer->lastname.' '.$customer->firstname;
            $template->invoicezip = $customer->zip;
            $template->invoicecity = $customer->city;
            if (!empty($customer->country)) {
                $template->invoicecountry = strtoupper($customer->country);
            }
        }

        return $template;
    }
}
