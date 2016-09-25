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

namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

Use local_shop\Bill;
Use local_shop\BillItem;

class payment_controller extends front_controller_base {

    function process($cmd) {
        global $SESSION, $DB, $USER;

        if ($cmd == 'place') {

        // Convert all data in bill records.

            // Customer info.
            $customer = (object)$SESSION->shoppingcart->customerinfo;
            if ($customerrec = $DB->get_record('local_shop_customer', array('email' => $customer->email, 'lastname' => strtoupper($customer->lastname)))) {
                $DB->update_record('local_shop_customer', $customer);
                $customer->id = $customerrec->id;
                unset($customerrec); // free some memory
            } else {
                $customer->timecreated = time();
        
                /*
                 * if we could login when validating the customer info that attach to internal account. 
                 * Note this process needs to be very carfully designed against authentication or security hole may allow
                 * people to steel moodle accounts.
                 */
                if (isloggedin()) {
                    $customer->hasaccount = $USER->id;
                }
        
                $customer->id = $DB->insert_record('local_shop_customer', $customer);
            }
            
            // Invoice info.
            if ($oldbillrec = $DB->get_record('local_shop_bill', array('transactionid' => $SESSION->shoppingcart->transid))) {
                $bill = new Bill($oldbillrec, $this->theshop, $this->thecatalog, $this->theblock, true);
                // clear all items as they might have changed
                $bill->delete_items();
                $bill->reset_taxlines();
            } else {
                $bill = new Bill(null, $this->theshop, $this->thecatalog, $this->theblock, true);
            }
        
            $bill->transactionid = $SESSION->shoppingcart->transid;
            $bill->blockid = 0 + @$this->theblock->id;
            $bill->onlinetransactionid = '';
            $bill->customerid = $SESSION->shoppingcart->customerinfo['id'];
            $bill->idnumber = '';
            $formatted = format_string($this->theshop->name);
            $bill->title = (empty($formatted)) ? get_string('defaultbilltitle', 'local_shop') : $formatted;
            $bill->status = SHOP_BILL_PLACED;
            $bill->emissiondate = time();
            $bill->lastactiondate = time();
            $bill->worktype = 'PROD';
            $bill->assignedto = 0;
            $bill->timetodo = 0;
            $bill->untaxedamount = 0;
            $bill->taxes = 0;
            $bill->amount = 0;
            $bill->currency = $this->theshop->get_currency();
            $bill->convertedamount = 0;
            $bill->paymode = $SESSION->shoppingcart->paymode;
            $bill->paiedamount = 0;
            $bill->expectedpaiement = 0;
            $bill->ignoretax = 0;
            $bill->paymentfee = 0;

            $select = "
                billid = ? AND
                ordering = (SELECT MAX(ordering) FROM {local_shop_billitem} WHERE billid = ?)
            ";
            if ($maxordering = $DB->get_record_select('local_shop_billitem', $select, array($bill->id, $bill->id))) {
                $ordering = $maxordering->ordering + 1;
            } else {
                $ordering = 0;
            }

            $totalitems = 0;
            foreach ($SESSION->shoppingcart->order as $shortname => $quant) {
                $itemrec = new \StdClass();
                $itemrec->quantity = $quant;
                $itemrec->itemcode = $shortname;
                $itemrec->type = 'BILLING';
                $itemrec->productiondata = new \StdClass;
                $itemrec->productiondata->users = @$SESSION->shoppingcart->users[$shortname]; // be carefull that production data may aggregate more data from catalog
                $itemrec->productiondata->id = $this->theshop->id; // for further reference to some origin shop parameters and defaults
                $itemrec->productiondata->blockid = 0 + @$this->theblock->id; // for further reference to some origin block parameters and defaults
                $itemrec->customerdata = @$SESSION->shoppingcart->customerdata[$shortname];
                $bill->add_item_data($itemrec, $ordering++);
                $totalitems += $quant;
            }

            // This is the first generation of the DB bill. All further step should rely on this information and not shoppingcart anymore.
            $billid = $bill->save();

            shop_trace("[{$bill->transactionid}] ".'Order placed : '.$bill->amount.' for '.$totalitems.' objects');
        }

        // This is for interactive payment methods.
        if ($cmd == 'navigate') {
            if ($back = optional_param('back', false, PARAM_BOOL)) {
                $params = array('view' => $this->theshop->get_prev_step('payment'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'back' => 1);
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            } else {
                confirm_sesskey();
                // security. No one should be able to trigger this case from outside
                // if it has been possible to continue, trigger the payment module interactive processing function and go ahead

                $aFullBill = Bill::get_by_transaction($SESSION->shoppingcart->transid);
                $paymentplugin = \shop_paymode::get_instance($this->theshop, $aFullBill->paymode);
                if ($interactivepayment = $paymentplugin->process($aFullBill)) {
                    $params = array('view' => $this->theshop->get_next_step('payment'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'what' => 'produce', 'transid' => $aFullBill->transactionid);
                    redirect(new \moodle_url('/local/shop/front/view.php', $params));
                } else {
                    $params = array('view' => $this->theshop->get_next_step('payment'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'what' => 'confirm', 'transid' => $aFullBill->transactionid);
                    redirect(new \moodle_url('/local/shop/front/view.php', $params));
                }
            }
        }
    }
}