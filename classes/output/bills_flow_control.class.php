<?php

namespace local_shop\output;

defined('MOODLE_INTERNAL') || die();

class bills_flow_control implements \Templatable {

    protected $status;

    protected $url;

    public function __construct($status, $url) {
        $this->status = $status;
        $this->url = $url;
    }

    public function export_for_template(\renderer_base $output) {
        global $DB;

        $select = "
            element = 'bill' AND
            `tostate` = ?
            GROUP BY element,`fromstate`
        ";
        $froms = $DB->get_records_select('local_flowcontrol', $select, array($this->status));

        $select = "
            element = 'bill' AND
            `fromstate` = ?
            GROUP BY element,`tostate`
        ";
        $tos = $DB->get_records_select('local_flowcontrol', $select, array($this->status));

        $template = new \StdClass;

        $template->statusstr = get_string($this->status, 'local_shop');

        if ($froms) {
            foreach ($froms as $from) {
                $fromtpl = new \StdClass;
                $fromtpl->label = get_string($from->fromstate, 'local_shop');
                $fromtpl->fromstate = $from->fromstate;
                $template->froms[] = $fromtpl;
            }
        }

        if ($tos) {
            foreach ($tos as $to) {
                $totpl = new \StdClass;
                $totpl->label = get_string($to->tostate, 'local_shop');
                $totpl->tostate = $to->tostate;
                $template->tos[] = $totpl;
            }
        }

        return $template;
    }
}
