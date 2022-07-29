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
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/renderer.php');

use local_shop\Shop;

class shop_customers_renderer extends local_shop_base_renderer {

    public function customers($customers, $url) {
        global $OUTPUT;

        $lastnamestr = get_string('lastname');
        $firstnamestr = get_string('firstname');
        $placedstr = get_string('placed', 'local_shop');
        $pendingsstr = get_string('pendings', 'local_shop');
        $purchasesstr = get_string('purchases', 'local_shop');
        $emailstr = get_string('email');
        $totalamountstr = get_string('totalamount', 'local_shop');

        $table = new html_table();
        $table->width = '100%';
        $table->head = array('CID',
                             "<b>$lastnamestr $firstnamestr</b>".$this->sortby($url, 'name'),
                             "<b>$emailstr</b>",
                             "<b>$placedstr</b>",
                             "<b>$pendingsstr</b>",
                             "<b>$purchasesstr</b>".$this->sortby($url, 'billcount'),
                             "<b>$totalamountstr</b>".$this->sortby($url, 'totalaccount'),
                             '');
        $table->align = array('center', 'left', 'left', 'left', 'center', 'center', 'right');

        $emptyaccounts = 0;
        foreach ($customers as $c) {
            if ($c->billcount == 0) {
                $emptyaccounts++;
            }
            $row = array();
            $params = array('view' => 'viewCustomer', 'customer' => $c->id);
            $customerurl = new moodle_url('/local/shop/customers/view.php', $params);
            $row[] = '<a href="'.$customerurl.'">'.$c->id.'</a>';
            $row[] = $c->lastname.' '.$c->firstname;
            $email = $c->email;
            if ($c->hasaccount) {
                $userurl = new moodle_url('/user/profile.php', ['id' => $c->hasaccount]);
                $accountlink = '<a href="'.$userurl.'">';
                $accountlink .= $OUTPUT->pix_icon('i/moodle_host', get_string('hasamoodleaccount', 'local_shop'));
                $accountlink .= '</a>';
                $email .= '&nbsp;'.$accountlink;
            }
            $row[] = $email;
            $row[] = $c->placedcount;
            $row[] = $c->pendingscount;
            $row[] = $c->billcount;
            $row[] = sprintf("%.2f", round($c->totalaccount, 2)).' '.$this->theshop->defaultcurrency;
            $editurl = new moodle_url('/local/shop/customers/edit_customer.php', array('customerid' => $c->id));
            $cmd = '<a href="'.$editurl.'">'.$this->output->pix_icon('t/edit', get_string('edit')).'</a>';
            if ($c->billcount == 0) {
                $params = array('view' => 'viewAllCustomers', 'customerid[]' => $c->id, 'what' => 'deletecustomer');
                $deleteurl = new moodle_url('/local/shop/customers/view.php', $params);
                $cmd .= '&nbsp;<a href="'.$deleteurl.'">'.$this->output->pix_icon('t/delete', get_string('delete')).'</a>';
            }
            $row[] = $cmd;
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    protected function sortby($url, $field) {
        global $OUTPUT;

        $order = optional_param('sortorder', 'name', PARAM_TEXT);
        $dir = optional_param('dir', 'ASC', PARAM_TEXT);

        if ($field == $order) {
            // Is this field ordering active ?
            switch ($dir) {
                case 'ASC' : {
                    $icon = 't/sort_asc';
                    $nextdir = 'DESC';
                    break;
                }
                case 'DESC' : {
                    $icon = 't/sort_desc';
                    $nextdir = 'ASC';
                    break;
                }
                default : {
                    $icon = 't/sort';
                    $nextdir = 'ASC';
                }
            }
        } else {
            $icon = 't/sort';
            $nextdir = 'ASC';
        }
        $params = ['href' => $url->out(false, ['sortorder' => $field, 'dir' => $nextdir])];
        $link = '&nbsp;'.html_writer::tag('a', $OUTPUT->pix_icon($icon, ''), $params);

        return $link;
    }

    /**
     * Detail information for a customer
     * @param \local_shop\Customer $customer
     */
    public function customer_detail($customer) {

        $template = new StdClass;

        $template->email = $customer->email;

        $template->hasaccount = $customer->hasaccount;
        if ($template->hasaccount) {
            $template->userurl = new moodle_url('/user/view.php', array('id' => $customer->hasaccount));
        }

        $template->lastname = $customer->lastname;
        $template->firstname = $customer->firstname;

        $template->city = $customer->city;

        $template->country = $customer->country;

        return $this->output->render_from_template('local_shop/customer_detail', $template);
    }

    /**
     * Bills for a customer
     * @param array $billset
     * @param string $status filtering on bill state
     */
    public function customer_bills($billset, $status) {
        global $OUTPUT;

        $config = get_config('local_shop');

        $numstr = get_string('num', 'local_shop');
        $idnumberstr = get_string('lettering', 'local_shop');
        $emissiondatestr = get_string('emissiondate', 'local_shop');
        $lastmovestr = get_string('lastmove', 'local_shop');
        $titlestr = get_string('title', 'local_shop');
        $amountstr = get_string('amount', 'local_shop');

        $table = new html_table();
        $table->heading = print_string('bill_' . $status.'s', 'local_shop');
        $table->head = array("<b>$numstr</b>",
                             "<b>$idnumberstr</b>",
                             "<b>$emissiondatestr</b>",
                             "<b>$lastmovestr</b>",
                             "<b>$titlestr</b>",
                             "<b>$amountstr</b>",
                             '');
        $table->size = array('5%', '5%', '%10', '10%', '50%', '10%', '10%');
        $table->width = '100%';
        $table->data = array();

        $markstr = get_string('mark', 'local_shop');
        $unmarkstr = get_string('unmark', 'local_shop');

        foreach ($billset as $portlet) {
            $row = array();
            $url = new moodle_url('/local/shop/bills/view.php', array('view' => 'viewBill', 'billid' => $portlet->id));
            $row[] = '<a href="'.$url.'">B-'.date('Y-m', $portlet->emissiondate).'-'.$portlet->id.'</a>';
            $row[] = '<a href="'.$url.'">'.$portlet->idnumber.'</a>';
            $row[] = userdate($portlet->emissiondate);
            $row[] = userdate($portlet->lastactiondate);
            $row[] = $portlet->title;
            $row[] = sprintf("%.2f", round($portlet->amount, 2)).' '.$config->defaultcurrency;
            if ($portlet->status == SHOP_BILL_PENDING) {
                $params = array('view' => 'viewCustomer',
                                'what' => 'sellout',
                                'billid' => $portlet->id,
                                'customer' => $portlet->userid);
                $url = new moodle_url('/local/shop/customers/view.php', $params);
                $row[] = '<a href="'.$url.'" alt="'.$markstr.'">'.$OUTPUT->pix_icon('mark', get_string('mark', 'local_shop'), 'local_shop').'</a>';
            } else if ($portlet->status == SHOP_BILL_SOLDOUT) {
                $params = array('view' => 'viewCustomer',
                                'what' => 'unmark',
                                'billid' => $portlet->id,
                                'customer' => $portlet->customerid);
                $url = new moodle_url('/local/shop/customers/view.php', $params);
                $row[] = '<a href="'.$url.'" alt="'.$unmarkstr.'">'.$OUTPUT->pix_icon('unmark', get_string('unmark', 'local_shop'), 'local_shop').'</a>';
            }
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }

    public function customers_options($mainrenderer) {
        global $SESSION;

        $shopid = optional_param('shopid', 0, PARAM_INT);
        $dir = optional_param('dir', 'asc', PARAM_TEXT);
        $sortorder = optional_param('order', 'lastname', PARAM_TEXT);

        $template = new StdClass;

        $params = array(
            'view' => 'viewAllCustomers',
            'dir' => $dir,
            'order' => $sortorder,
            'shopid' => $shopid,
        );

        $url = new moodle_url('/local/shop/customers/view.php', $params);
        $url->remove_params('shopid');
        $template->shopselect = $mainrenderer->shop_choice($url, true, $shopid);

        /*
        $params = array('view' => 'search');
        $template->searchurl = new moodle_url('/local/shop/customers/view.php', $params);
        $template->searchinbillsstr = get_string('searchincustomers', 'local_shop');
        */

        return $this->output->render_from_template('local_shop/customers_options', $template);
    }

    public function no_paging_switch($url, $urlfilter) {
        $nopaging = optional_param('nopaging', 0, PARAM_BOOL);
        if ($nopaging) {
            $str = '<span class="nolink">'.get_string('nopaging', 'local_shop').'</span>';
        } else {
            $urlfilter = str_replace('nopaging=0', 'nopaging=1', $urlfilter);
            $urlfilter = preg_replace('/customerpage=\d+/', '', $urlfilter);
            $urlfilter .= '&customerpage=-1';
            $str = ' <a href="'.$url.'&'.$urlfilter.'">'.get_string('nopaging', 'local_shop').'</a>';
        }

        return $str;
    }

    public function customer_view_links() {

        $template = new StdClass;

        $template->newaccounturl = new moodle_url('/local/shop/customers/edit_customer.php');

        return $this->output->render_from_template('local_shop/customer_view_link', $template);
    }
}
