<?php

namespace local_shop\output;


class front_paymode implements \Templatable {

    protected $bill;

    public function __construct($bill) {
        $this->bill = $bill;
    }

    public function export_for_template(\renderer_base $output) {
        global $CFG;

        $afullbill = $this->bill;

        $template = new \StdClass;
        include_once($CFG->dirroot.'/local/shop/paymodes/'.$afullbill->paymode.'/'.$afullbill->paymode.'.class.php');

        $classname = 'shop_paymode_'.$afullbill->paymode;

        $pm = new $classname($afullbill->theshop);
        $template->paymode = $pm->print_name(true);

        return $template;
    }
}