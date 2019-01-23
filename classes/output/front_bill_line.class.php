<?php

namespace local_shop\output;

class front_bill_line implements \Templatable {

    protected $billitem;
    protected $options;

    public function __construct($billitem, $options) {
        $this->billitem = $billitem;
        $this->options = $options;
    }

    public function export_for_template(\renderer_base $output) {
        $template = new \StdClass;

        $billitem = $this->billitem;
        $options = $this->options;
        $template->q = $billitem->quantity;
        $template->abstract = $billitem->abstract;
        if (!empty($options['description'])) {
            $template->description = $billitem->description;
        }
        $template->itemcode = $billitem->itemcode;
        $template->unitcost = sprintf('%0.2f', $billitem->unitcost);
        $template->totalprice = sprintf('%0.2f', $billitem->totalprice);

        return $template;
    }
}