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
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

use local_shop\Bill;
use local_shop\BillItem;

class payment_controller extends front_controller_base {

    public function receive($cmd, $data = array()) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        $this->data->debug = optional_param('debug', @$SESSION->shoppingcart->debug, PARAM_BOOL);

        switch ($cmd) {
            case 'place':
                break;
            case 'navigate':
                /*
                 * security. No one should be able to trigger this case from outside
                 */
                confirm_sesskey();
                $this->data->back = optional_param('back', false, PARAM_BOOL);
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $SESSION, $DB, $USER, $OUTPUT, $CFG;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $SESSION->shoppingcart->debug = @$this->data->debug;

        if ($cmd == 'place') {
            shop_debug_trace('Payment controller: placing', SHOP_TRACE_DEBUG);
            // Convert all data in bill records.
            // Customer info.
            $customer = (object)$SESSION->shoppingcart->customerinfo;
            $params = array('email' => $customer->email, 'lastname' => strtoupper($customer->lastname));
            if ($customerrec = $DB->get_record('local_shop_customer', $params)) {
                // Customer should already be pre recorded so this is expected to be the mostly used case.
                $DB->update_record('local_shop_customer', $customer);
                $customer->id = $customerrec->id;
                unset($customerrec); // Free some memory.
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

                if ($oldbillrec->status == SHOP_BILL_SOLDOUT || $oldbillrec->status == SHOP_BILL_COMPLETE) {
                    $params = array('view' => 'invoice', 'transid' => $SESSION->shoppingcart->transid);
                    $frontbillurl = new \moodle_url('/local/shop/front/view.php', $params);
                    redirect($frontbillurl);
                }

                $bill = new Bill($oldbillrec, true, $this->theshop, $this->thecatalog, $this->theblock);
                // Clear all items as they might have changed.
                $bill->delete_items();
                $bill->reset_taxlines();
            } else {
                $bill = new Bill(null, true, $this->theshop, $this->thecatalog, $this->theblock);
            }

            $bill->transactionid = $SESSION->shoppingcart->transid;
            $bill->blockid = 0 + @$this->theblock->id;

            $bill->onlinetransactionid = '';
            if (!empty($SESSION->shoppingcart->onlinetransactionid)) {
                // Some plugins (f.e. Stripe) can provide an early onlinetransaction ID before bill creation.
                $bill->onlinetransactionid = $SESSION->shoppingcart->onlinetransactionid;
            }

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

            $bill->partnerid = 0;
            $bill->partnertag = '';
            if (local_shop_supports_feature('shop/partners')) {
                include_once($CFG->dirroot.'/local/shop/pro/classes/Partner.class.php');
                \local_shop\Partner::register_in_bill($bill);
            }

            if (!empty($SESSION->shoppingcart->usedistinctinvoiceinfo)) {
                $bill->invoiceinfo = json_encode($SESSION->shoppingcart->invoiceinfo);
            }

            // First save of the bill in order bill items can be added. We need a first id. We save "light".
            // The bill will be full save back later.
            $bill->id = $bill->save(true);

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
                // Be carefull that production data may aggregate more data from catalog.
                $itemrec->productiondata->users = @$SESSION->shoppingcart->users[$shortname];
                // For further reference to some origin shop parameters and defaults.
                $itemrec->productiondata->id = $this->theshop->id;
                // For further reference to some origin block parameters and defaults.
                $itemrec->productiondata->blockid = 0 + @$this->theblock->id;
                if (!empty($SESSION->shoppingcart->customerdata[$shortname])) {
                    $itemrec->customerdata = $SESSION->shoppingcart->customerdata[$shortname];
                } else {
                    $itemrec->customerdata = '';
                }

                $billitem = $bill->add_item_data($itemrec, $ordering++);
                if (local_shop_supports_feature('products/smarturls')) {
                    include_once($CFG->dirroot.'/local/shop/pro/lib.php');
                    $catalogitem = $billitem->get_catalog_item();
                    if (get_config('local_shop', 'usesmarturls') && ($catalogitem)) {
                        shop_debug_trace("Firing SEO order url for {$catalogitem->shortname} ", SHOP_TRACE_DEBUG);
                        local_shop_fire_smart_order($catalogitem);
                    }
                }
                $totalitems += $quant;
            }

            if (local_shop_supports_feature('shop/discounts')) {
                include_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');
                $discounts = \local_shop\Discount::get_applicable_discounts($this->theshop->id);
                if (!empty($discounts)) {
                    foreach ($discounts as $d) {
                        if ($d->check_applicability($bill)) {
                            $d->apply_to_bill($bill);
                        }
                    }
                }
            }

            /*
             * Now save everything in.
             */
            $billid = $bill->save();

            // Confirm transaction ID. This should not be necessary.
            $DB->set_field('local_shop_bill', 'transactionid', $bill->transactionid, array('id' => $billid));

            shop_trace("[{$bill->transactionid}] ".'Order placed : '.$totalitems.' objects');
        }

        // This is for interactive payment methods.
        if ($cmd == 'navigate') {
            if ($this->data->back) {
                $prev = $this->theshop->get_prev_step('payment');
                $params = array('view' => $prev,
                                'shopid' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id,
                                'back' => 1);
                $url = new \moodle_url('/local/shop/front/view.php', $params);
                if (empty($SESSION->shoppingcart->debug)) {
                    return $url;
                } else {
                    echo $OUTPUT->continue_button($url);
                }
            } else {
                /*
                 * if it has been possible to continue, trigger the payment module interactive
                 * processing function and go ahead
                 */

                $afullbill = Bill::get_by_transaction($SESSION->shoppingcart->transid);
                $paymentplugin = \shop_paymode::get_instance($this->theshop, $afullbill->paymode);
                if ($paymentplugin->process($afullbill)) {
                    $next = $this->theshop->get_next_step('payment');
                    $params = array('view' => $next,
                                    'shopid' => $this->theshop->id,
                                    'blockid' => 0 + @$this->theblock->id,
                                    'what' => 'produce',
                                    'transid' => $afullbill->transactionid);
                    $url = new \moodle_url('/local/shop/front/view.php', $params);
                    if (empty($SESSION->shoppingcart->debug)) {
                        return $url;
                    } else {
                        echo $OUTPUT->header();
                        echo $OUTPUT->continue_button($url);
                        echo $OUTPUT->footer();
                        die;
                    }
                } else {
                    $next = $this->theshop->get_next_step('payment');
                    $params = array('view' => $next,
                                    'shopid' => $this->theshop->id,
                                    'blockid' => 0 + @$this->theblock->id,
                                    'what' => 'confirm',
                                    'transid' => $afullbill->transactionid);
                    $url = new \moodle_url('/local/shop/front/view.php', $params);
                    if (empty($SESSION->shoppingcart->debug)) {
                        return $url;
                    } else {
                        echo $OUTPUT->header();
                        echo $OUTPUT->continue_button($url);
                        echo $OUTPUT->footer();
                        die;
                    }
                }
            }
        }
    }
}