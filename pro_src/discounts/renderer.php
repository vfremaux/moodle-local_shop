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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/renderer.php');
require_once($CFG->dirroot.'/local/shop/classes/DBListUtils.class.php');

use local_shop\Shop;
use local_shop\backoffice\DBListUtils;

/**
 * An extended Pro renderer for discounts
 */
class shop_discounts_renderer_extended extends local_shop_base_renderer {

    /**
     * Render discounts
     */
    public function discounts($discounts) {
        global $OUTPUT;

        $namestr = get_string('discountname', 'local_shop');
        $typestr = get_string('discounttype', 'local_shop');
        $amountstr = get_string('discountamount', 'local_shop');
        $enabledstr = get_string('enabled', 'local_shop');
        $billsstr = get_string('bills', 'local_shop');
        $applyonstr = get_string('applyon', 'local_shop');
        $operatorstr = get_string('operator', 'local_shop');

        $table = new html_table();
        $table->width = '100%';
        $table->head = [
            "<b>$namestr</b>",
            "<b>$typestr</b>",
            "<b>$amountstr</b>",
            "<b>$applyonstr</b>",
            "<b>$enabledstr</b>",
            "<b>$operatorstr</b>",
            "<b>$billsstr</b>",
            '',
        ];
        $table->align = ['left', 'left', 'left', 'left', 'center', 'left', 'center', 'right'];

        $listutils = new DBListUtils('local_shop_discount', 'ordering', ['shopid' => $this->theshop->id]);

        $emptydiscounts = 0;
        foreach ($discounts as $d) {
            if ($d->billcount == 0) {
                $emptydiscounts++;
            }
            $row = [];

            $params = ['view' => 'viewDiscount', 'discount' => $d->id];
            $discounturl = new moodle_url('/local/shop/discounts/view.php', $params);
            $row[] = format_string($d->name);

            $row[] = $d->type;

            if ($d->has_multiple_ratios()) {
                $row[] = get_string('multipleratios', 'local_shop');
            } else {
                $row[] = $d->ratio. ' %';
            }

            $row[] = get_string('on'.$d->applyon, 'local_shop');

            $yesicon = $this->output->pix_icon('enabled', get_string('enabled', 'local_shop'), 'local_shop');
            $noicon = $this->output->pix_icon('disabled', get_string('disabled', 'local_shop'), 'local_shop');
            if ($d->enabled) {
                $params = ['shopid' => $this->theshop->id, 'view' => 'viewAllDiscounts', 'what' => 'disable', 'id' => $d->id];
                $discounturl = new moodle_url('/local/shop/pro/discounts/view.php', $params);
                $title = get_string('disable', 'local_shop');
                $cmd = '<a href="'.$discounturl.'" title="'.$title.'">'.$yesicon.'</a>';
            } else {
                $params = ['shopid' => $this->theshop->id, 'view' => 'viewAllDiscounts', 'what' => 'enable', 'id' => $d->id];
                $discounturl = new moodle_url('/local/shop/pro/discounts/view.php', $params);
                $title = get_string('enable', 'local_shop');
                $cmd = '<a href="'.$discounturl.'" title="'.$title.'">'.$noicon.'</a>';
            }
            $row[] = $cmd;

            $row[] = get_string($d->operator, 'local_shop');

            $row[] = 0 + $d->billcount;

            $editurl = new moodle_url('/local/shop/pro/discounts/edit_discount.php', ['discountid' => $d->id]);
            $cmd = '<a href="'.$editurl.'">'.$this->output->pix_icon('t/edit', get_string('edit')).'</a>';

            if ($d->billcount == 0) {
                $params = ['shopid' => $this->theshop->id, 'view' => 'viewAllDiscounts', 'discountid[]' => $d->id, 'what' => 'delete'];
                $deleteurl = new moodle_url('/local/shop/pro/discounts/view.php', $params);
                $cmd .= '&nbsp;<a href="'.$deleteurl.'">'.$this->output->pix_icon('t/delete', get_string('delete')).'</a>';
            }

            $params = ['view' => 'viewAllDiscounts', 'shopid' => $this->theshop->id];
            $listbaseurl = new moodle_url('/local/shop/pro/discounts/view.php', $params);
            $cmd .= '&nbsp;'.$listutils->get_down_cmd($d->id, $d->ordering, $listbaseurl);
            $cmd .= '&nbsp;'.$listutils->get_up_cmd($d->id, $d->ordering, $listbaseurl);
            $row[] = $cmd;
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

    /**
     * Detail information for a discount
     * @param Customer $customer
     */
    public function customer_detail(Customer $customer) {

        $template = new StdClass();

        $template->email = $customer->email;

        $template->hasemail = $customer->hasaccount;
        if ($template->hasemail) {
            $template->userurl = new moodle_url('/user/view.php', ['id' => $customer->hasaccount]);
        }

        $template->lastname = $customer->lastname;
        $template->firstname = $customer->firstname;

        $template->city = $customer->city;

        $template->country = $customer->country;

        return $this->output->render_from_template('local_shop/customer_detail', $template);
    }

    /**
     * Bills applying a discount
     * @param array $billset
     * @param string $status filtering on bill state
     */
    public function discount_bills($billset, $status) {
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
        $table->head = [
            "<b>$numstr</b>",
            "<b>$idnumberstr</b>",
            "<b>$emissiondatestr</b>",
            "<b>$lastmovestr</b>",
            "<b>$titlestr</b>",
            "<b>$amountstr</b>",
            '',
        ];
        $table->size = ['5%', '5%', '%10', '10%', '50%', '10%', '10%'];
        $table->width = '100%';
        $table->data = [];

        $markstr = get_string('mark', 'local_shop');
        $unmarkstr = get_string('unmark', 'local_shop');

        foreach ($billset as $portlet) {
            $row = [];
            $url = new moodle_url('/local/shop/bills/view.php', ['view' => 'viewBill', 'billid' => $portlet->id]);
            $row[] = '<a href="'.$url.'">B-'.date('Y-m', $portlet->emissiondate).'-'.$portlet->id.'</a>';
            $row[] = '<a href="'.$url.'">'.$portlet->idnumber.'</a>';
            $row[] = userdate($portlet->emissiondate);
            $row[] = userdate($portlet->lastactiondate);
            $row[] = $portlet->title;
            $row[] = sprintf("%.2f", round($portlet->amount, 2)).' '.$config->defaultcurrency;
            if ($portlet->status == SHOP_BILL_PENDING) {
                $params = [
                    'view' => 'viewCustomer',
                    'what' => 'sellout',
                    'billid' => $portlet->id,
                    'customer' => $portlet->userid,
                ];
                $url = new moodle_url('/local/shop/customers/view.php', $params);
                $icon = $OUTPUT->pix_icon('mark', get_string('mark', 'local_shop'));
                $row[] = '<a href="'.$url.'" alt="'.$markstr.'">'.$icon.'</a>';
            } else if ($portlet->status == SHOP_BILL_SOLDOUT) {
                $params = [
                    'view' => 'viewCustomer',
                    'what' => 'unmark',
                    'billid' => $portlet->id,
                    'customer' => $portlet->customerid,
                ];
                $url = new moodle_url('/local/shop/customers/view.php', $params);
                $icon = $OUTPUT->pix_icon('unmark', get_string('unmark', 'local_shop'));
                $row[] = '<a href="'.$url.'" alt="'.$unmarkstr.'">'.$icon.'</a>';
            }
            $table->data[] = $row;
        }
        echo html_writer::table($table);
    }

    /**
     * Links to discounts
     */
    public function discount_view_links() {
        $template = new StdClass;
        $template->newdiscounturl = new moodle_url('/local/shop/pro/discounts/edit_discount.php');
        return $this->output->render_from_template('local_shop/discount_view_link', $template);
    }
}
