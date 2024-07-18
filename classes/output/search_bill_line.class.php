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

use Templatable;
use Stdclass;
use renderer_base;

/**
 * Result of a bill search
 */
class search_bill_line implements Templatable {

    /** @var a bill that matches search */
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
     * @param renderer_base $output unused
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function export_for_template(renderer_base $output /* unused */) {

        $template = new StdClass();

        $bill = $this->bill;

        $params = ['view' => 'viewBill', 'id' => $bill->theshop->id, 'billid' => $bill->id];
        $template->billurl = new \moodle_url('/local/shop/bills/view.php', $params);
        $template->uniqueid = 'B-'.strftime('%Y%m%d', $bill->emissiondate).'-'.$bill->id;

        $params = ['view' => 'viewCustomer', 'customer' => $bill->customer->id];
        $template->customerurl = new  \moodle_url('/local/shop/customers/view.php', $params);
        $template->customername = $bill->customer->lastname.' '.$bill->customer->firstname;

        $template->emissiondate = strftime('%c', $bill->emissiondate);
        $template->transid = $bill->transactionid;

        $template->status = $bill->status;

        return $template;
    }
}
