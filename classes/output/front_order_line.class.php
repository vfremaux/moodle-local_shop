<?php

namespace local_shop\output;

defined('MOODLE_INTERNAL') || die();

class front_order_line implements \Templatable {

    protected $catalogitem;

    protected $theshop;

    protected $options;

    protected $q;

    public function __construct($catalogitem, $q, $theshop, $options) {
        $this->catalogitem = $catalogitem;
        $this->theshop = $theshop;
        $this->options = $options;
        $this->q = $q;
    }

    public function export_for_template($output) {

        $template = new \StdClass;

        $theshop = $this->theshop;
        $catalogitem = $this->catalogitem;
        $options = $this->options;

        $template->currency = $theshop->get_currency('symbol');
        $template->itemname = $catalogitem->name;
        $template->abstract = '';
        if (!empty($options['description'])) {
            $template->abstract .= $catalogitem->description;
        }
        if (!empty($options['notes'])) {
            $template->abstract .= '<br/>'.$catalogitem->notes;
        }
        $template->itemcode = $catalogitem->code;
        $template->taxedprice = sprintf('%.2f', $catalogitem->get_taxed_price($this->q));
        $template->q = $this->q;
        $template->totaltaxedprice = sprintf('%.2f', $catalogitem->get_taxed_price($this->q) * $this->q);

        return $template;
    }
}