<?php

namespace local_shop\output;

defined('MOODLE_INTERNAL') || die();

class bills_search_form implements \Templatable {

    protected $billcount;

    protected $theshop;

    public function __construct($theshop, $billcount) {
        $this->billcount = $billcount;
        $this->theshop = $theshop;
    }

    public function export_for_template($output) {

        $template = new \StdClass();
        $template->shopid = $this->theshop->id;
        $template->sesskey = sesskey();
 
        if ($this->billcount) {
            $template->hasbills = true;
        } else {
            $template->nobillsnotification = $output->notification('nobills', 'local_shop');
        }

        return $template;
    }
}

