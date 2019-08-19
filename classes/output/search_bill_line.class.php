<?php

namespace local_shop\output;

defined('MOODLE_INTERNAL') || die();

class search_bill_line implements \Templatable {

    protected $bill;

    public function __construct($bill) {
        $this->bill = $bill;
    }

    public function export_for_template($output) {

        $template = new \StdClass();

        $bill = $this->bill;

        $params = array('view' => 'viewBill', 'id' => $bill->theshop->id, 'billid' => $bill->id);
        $template->billurl = new \moodle_url('/local/shop/bills/view.php', $params);
        $template->uniqueid = 'B-'.strftime('%Y%m%d', $bill->emissiondate).'-'.$bill->id;

        $params = array('view' => 'viewCustomer', 'customer' => $bill->customer->id);
        $template->customerurl = new  \moodle_url('/local/shop/customers/view.php', $params);
        $template->customername = $bill->customer->lastname.' '.$bill->customer->firstname;

        $template->emissiondate = strftime('%c', $bill->emissiondate);
        $template->transid = $bill->transactionid;

        $template->status = $bill->status;

        return $template;
    }
}
