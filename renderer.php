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

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Catalog;
use local_shop\Shop;

/**
 * A general renderer for global parts of the moodle shop
 * It will provide generic functions that may be used in several services inside
 the shop front and backoffice implementation.
 */
class local_shop_renderer extends plugin_renderer_base {

    /**
     * prints an owner menu and changes currently viewed owner if required
     */
    function print_owner_menu($urlroot) {
        global $OUTPUT, $DB;

        $config = get_config('local_shop');

        if (empty($config->usedelegation)) return;

        $activeowner = optional_param('shopowner', null, PARAM_INT);

        $owners = $DB->get_records_select('local_shop_customer', " hasaccount > 0 ", array(), 'hasaccount,firstname,lastname');

        $ownersmenu = array();
        if ($owners) {
            foreach($owners as $accountid => $owner) {
                $ownersmenu[$accountid] = $owner->lastname.' '.$owner->firstname;
            }
        }

        $ownerlabel = get_string('currentowner', 'local_shop');

        if (count($owners) == 1) {
            $ownername = reset($owners);
            $output = $ownerlabel.': '.$ownername->lastname.' '.$ownername->firstname;
        } else {
            $select = new single_select(new moodle_url($urlroot), 'shopowner', $ownersmenu, $activeowner, null, 'selectowner');
            $select->label = $ownerlabel;
            $output = $OUTPUT->render($select);
        }

        $output = '<div class="shopownerselector">'.$output.'</div>';

        return $output;
    }

    /**
     * prints a customer menu and changes currently viewed owner if required
     */
    function print_customer_menu($urlroot, $shopownerid = 0) {
        global $OUTPUT, $DB;

        $activecustomer = optional_param('customer', null, PARAM_INT);

        $select = " hasaccount > 0 ";
        $join = '';
        $params = array();
        if ($shopownerid) {
            $select .= " AND co.userid = ? ";
            $params[] = $shopownerid;
            $join = "
                LEFT JOIN
                    {local_shop_customer_owner} co
                ON
                    co.customerid = c.id
            ";
        }

        $sql = "
            SELECT
                c.id,
                c.firstname,
                c.lastname,
                c.city,
                c.country,
                c.hasaccount
            FROM
                {local_shop_customer} c
            $join
            WHERE
                $select
            ORDER BY
                c.lastname,
                c.firstname
        ";

        $customers = $DB->get_records_sql($sql, $params);

        $customersmenu = array();
        if ($customers) {
            foreach($customers as $cid => $cu) {
                $customersmenu[$cid] = $cu->lastname.' '.$cu->firstname.' ('.$cu->city.') ['.$cu->country.']';
            }
        }

        $customerlabel = get_string('currentcustomer', 'local_shop');

        if (count($customers) == 1) {
            $customername = reset($customers);
            $output = $customerlabel.': '.$customername->lastname.' '.$customername->firstname. ' ('.$customername->city.') ['.$customername->country.']';
        } else {
            $select = new single_select(new moodle_url($urlroot), 'customer', $customersmenu, $activecustomer, null, 'selectcustomer');
            $select->label = $customerlabel;
            $output = $OUTPUT->render($select);
        }

        $output = '<div class="shopcustomerselector">'.$output.'</div>';

        return $output;
    }

    function paging_results($portlet) {
        if (empty($portlet->pagesize)) {
            $portlet->pagesize = 20;
        }
        if ($portlet->pagesize < $portlet->total) {
            $pages = ceil($portlet->total / $portlet->pagesize);
            if ($offset = optional_param('offset', 0, PARAM_INT) > 0) {
                $pageoffset = $offset - $portlet->pageSize;
                $str .= '<a href="'.$portlet->url.'&offset='.$pageoffset.'">&lt;</a> - ';
            }
            $str .= '<span class="paging">';
            for ($i = 1 ; $i <= $pages ; $i++) {
                if ($i == ($offset / $portlet->pagesize) + 1) {
                    echo " $i - ";
                } else {
                    $pageoffset = $portlet->pagesize * ($i - 1);
                    $str .= '<a class="paging" href="'.$portlet->url.'&offset='.$pageoffset.'">'.$i.'</a> - ';
                }
            }
            $str .= '</span>';
            if ($offset + $portlet->pagesize < $portlet->total) {
                $pageoffset = $offset + $portlet->pagesize;
                $nexturl = $portlet->url.'&offset='.$pageoffset;
                $str .= '<a href="'.$nexturl.'" ?>">&gt;</a>';
            }
        }
    }

    function catalog_choice($url) {
        global $SESSION, $OUTPUT;

        $str = '';
        $catalogs = Catalog::get_instances();
        $catalogmenu = array();
        foreach($catalogs as $c) {
            $catalogmenu[$c->id] = format_string($c->name);
        }
        $str .= $OUTPUT->single_select($url, 'catalogid', $catalogmenu, $SESSION->shop->catalogid);

        return $str;
    }

    function shop_choice($url, $chooseall = false) {
        global $SESSION, $OUTPUT;

        $str = '';
        $shops = Shop::get_instances();
        $shopmenu = array();

        if ($chooseall) {
            $shopmenu[0] = get_string('chooseall', 'local_shop');
        }

        foreach($shops as $s) {
            $shopmenu[$s->id] = format_string($s->name);
        }
        $str .= $OUTPUT->single_select($url, 'shopid', $shopmenu, $SESSION->shop->shopid);

        return $str;
    }

    function currency_choice($current, $url) {
        global $OUTPUT;

        $currencies = shop_get_supported_currencies();

        $str = '';

        $str .= $OUTPUT->single_select($url, 'cur', $currencies, $current);

        return $str;
    }

    function customer_choice($current, $url) {
        global $OUTPUT;

        $customers = Customer::get_instances_menu(array(), 'CONCAT(lastname, \' \', firstname)', 'lastname, firstname');

        $str = '';

        $str .= $OUTPUT->single_select($url, 'customerid', $customers, $current);

        return $str;
    }

    function main_menu($theShop) {
        $str = '<table class="shop-main-menu">';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/shop/view.php', array('view' => 'viewAllShops'));
        $str .= '<a href="'.$linkurl.'">'.get_string('allshops', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('allshopsdesc', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/bills/view.php', array('view' => 'viewAllBills'));
        $str .= '<a href="'.$linkurl.'">'.get_string('allbills', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('searchinbills', 'local_shop');
        $str .= '</td>';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/purchasemanager/view.php', array('view' => 'viewAllProductInstances'));
        $str .= '<a href="'.$linkurl.'">'.get_string('allproductinstances', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('searchinproductinstances', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/customers/view.php', array('view' => 'viewAllCustomers'));
        $str .= '<a href="'.$linkurl.'">'.get_string('allcustomers', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('searchincustomers', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/taxes/view.php', array('view' => 'viewAllTaxes'));
        $str .= '<a href="'.$linkurl.'">'.get_string('managetaxes', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('managetaxesdesc', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/shipzones/index.php');
        $str .= '<a href="'.$linkurl.'">'.get_string('manageshipping', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('manageshippingdesc', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $linkurl = new moodle_url('/local/shop/front/scantrace.php', array('id' => $theShop->id));
        $str .= '<a href="'.$linkurl.'">'.get_string('scantrace', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td width="75%">';
        $str .= get_string('tracescandesc', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        if (has_capability('moodle/site:config', context_system::instance())) {
            $str .= '<tr valign="top">';
            $str .= '<td width="25%">';
            $settingsurl = new moodle_url('/admin/settings.php', array('section' => 'local_shop'));
            $str .= '<a href="'.$settingsurl.'">'.get_string('settings', 'local_shop').'</a>';
            $str .= '</td>';
            $str .= '<td width="75%">';
            $str .= get_string('generalsettings', 'local_shop');
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '<tr valign="top">';
        $str .= '<td width="25%">';
        $reseturl = new moodle_url('/local/shop/reset.php', array('id' => $theShop->id));
        $str .= '<a href="'.$reseturl.'">'.get_string('reset', 'local_shop').'</a>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= get_string('resetdesc', 'local_shop');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }
}