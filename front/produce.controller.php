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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/auth/ticket/lib.php');
require_once($CFG->dirroot.'/local/shop/datahandling/production.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

class production_controller extends front_controller_base {

    var $ipncall;
    var $interactive;
    var $abill;

    function __construct(&$aFullBill, $ipncall = false, $interactive = false) {
        $this->abill = $aFullBill;
        $this->ipncall = $ipncall;
        $this->interactive = $interactive;
    }

    function process($cmd, $holding = false) {
        global $SESSION, $DB, $CFG, $SITE;

        $config = get_config('local_shop');

        if ($cmd == 'navigate') {
            // No back possible after production.
            redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->abill->theshop->get_next_step('produce'), 'shopid' => $this->abill->theshop->id, 'blockid' => 0 + @$this->abill->theblock->id, 'transid' => $this->abill->transactionid)));
        }

        $systemcontext = \context_system::instance();

        // Simpler to handle in code
        $aFullBill = $this->abill;

        // trap any non defined command here (increase security).
        if ($cmd != 'produce' && $cmd != 'confirm') {
            return;
        }

        /**
         * Payment is overriden if :
         * - this is a free order (paymode has been detected as freeorder)
         * - the user is logged in and has special payment override capability
         */
        $paycheckoverride = (isloggedin() && has_capability('local/shop:paycheckoverride', $systemcontext)) || ($aFullBill->paymode == 'freeorder');
        $overriding = false;

        if ($cmd == 'confirm') {

            if ($paycheckoverride) {
                // bump to next case
                $cmd = 'produce';
                $overriding = true;
            } else {
        
                // a more direct resolution when paiement is not performed online
                // we can perform pre_pay operations
                shop_trace("[{$aFullBill->transactionid}] ".'Order confirm (offline payments, bill is expected to be PENDING)');
                shop_trace("[{$aFullBill->transactionid}] ".'Production starting ...');
                shop_trace("[{$aFullBill->transactionid}] ".'Production Controller : Pre Pay process');

                if ($this->interactive && $this->ipncall) {
                    mtrace("[{$aFullBill->transactionid}] ".'Order confirm (offline payments, bill is expected to be PENDING)');
                    mtrace("[{$aFullBill->transactionid}] ".'Production starting ...');
                    mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Pre Pay process');
                }

                if ($this->interactive && $this->ipncall) {
                    mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Pre Pay process');
                }
                $productionfeedback = produce_prepay($aFullBill);
                // log new production data into bill record
                // the first producing procedure stores production data.
                // if interactive shopback process comes later, we just have production
                // data to display to user.
                shop_aggregate_production($aFullBill, $productionfeedback, $this->interactive);
                // all has been finished
                unset($SESSION->shoppingcart);
            }
        }
        if ($cmd == 'produce') {

            // start production
            shop_trace("[{$aFullBill->transactionid}] ".'Production Controller : Full production starting from '.$aFullBill->status.' ...');
            if ($this->interactive && $this->ipncall) {
                mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Full production starting from '.$aFullBill->status.' ...');
            }

            if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'SOLDOUT' || $overriding) {
                // when using the controller to finish a started production, do not
                // preproduce again (paypal IPN finalization)
                if ($this->interactive && $this->ipncall) {
                    mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Pre Pay process');
                }
                $productionfeedback = produce_prepay($aFullBill);

                if ($aFullBill->status == 'SOLDOUT' || $overriding) {
                    shop_trace("[{$aFullBill->transactionid}] ".'Production Controller : Post Pay process');
                    if ($this->interactive && $this->ipncall) {
                        mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Post Pay process');
                    }

                    if ($productionfeedback2 = produce_postpay($aFullBill)) {
                        $productionfeedback->public .= '<br/>'.$productionfeedback2->public;
                        $productionfeedback->private .= '<br/>'.$productionfeedback2->private;
                        $productionfeedback->salesadmin .= '<br/>'.$productionfeedback2->salesadmin;
                        if ($overriding) {
                            $aFullBill->status = 'PREPROD'; // Let replay for test
                        } else {
                            $aFullBill->status = 'COMPLETE'; // Let replay for test
                        }
                        if (!$holding) {
                            // If holding for repeatable tests, do not complete the bill.
                            $aFullBill->save();
                        }
                    }
                }
                // log new production data into bill record
                // the first producing procedure stores production data.
                // if interactive shopback process comes later, we just have production
                // data to display to user.
                shop_aggregate_production($aFullBill, $productionfeedback, $this->interactive);
            } else {
                $productionfeedback = new \StdClass;
                $productionfeedback->public = "Completed";
                shop_aggregate_production($aFullBill, $productionfeedback, $this->interactive);
            }
        }

        // Send final notification by mail if something has been done the end user should know.
        shop_trace("[{$aFullBill->transactionid}] ".'Production Controller : Transaction Complete Operations');
        if ($this->interactive && $this->ipncall) {
            mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Transaction Complete Operations');
            mtrace($productionfeedback->public);
            mtrace($productionfeedback->private);
        }
        // notify end user
        // feedback customer with mail confirmation.
        $customer = $DB->get_record('local_shop_customer', array('id' => $aFullBill->customerid));

        $notification  = shop_compile_mail_template('sales_feedback', array('SERVER' => $SITE->shortname,
                                                                   'SERVER_URL' => $CFG->wwwroot,
                                                                   'SELLER' => $config->sellername,
                                                                   'FIRSTNAME' => $customer->firstname,
                                                                   'LASTNAME' =>  $customer->lastname,
                                                                   'MAIL' => $customer->email,
                                                                   'CITY' => $customer->city,
                                                                   'COUNTRY' => $customer->country,
                                                                   'ITEMS' => $aFullBill->itemcount,
                                                                   'PAYMODE' => get_string($aFullBill->paymode, 'shoppaymodes_'.$aFullBill->paymode),
                                                                   'AMOUNT' => sprintf("%.2f", round($aFullBill->amount, 2))), 
                                                 '');
        $customerBillViewUrl = $CFG->wwwroot."/local/shop/front/view.php?id={$aFullBill->shopid}&blockid={$aFullBill->blockid}&view=bill&billid={$aFullBill->id}&transid={$aFullBill->transactionid}";

        $seller = new \StdClass;
        $seller->id = $DB->get_field('user', 'id', array('username' => 'admin', 'mnethostid' => $CFG->mnet_localhost_id));
        $seller->firstname = $config->sellername;
        $seller->lastname = '';
        $seller->email = $config->sellermail;
        $seller->maildisplay = true;

        // Complete seller with expected fields.
        $fields = get_all_user_name_fields();
        foreach($fields as $f) {
            if (!isset($seller->$f)) {
                $seller->$f = '';
            }
        }

        $title = $SITE->shortname . ' : ' . get_string('yourorder', 'local_shop');
        if (!empty($productiondata->private)) {
            $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->private, $notification);
        } else {
            $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', '', $notification);
        }
        if (empty($aFullBill->customeruser)) {
            $aFullBill->customeruser = $DB->get_record('user', array('id' => $aFullBill->customer->hasaccount));
        }

        if ($aFullBill->customeruser) {
            ticket_notify($aFullBill->customeruser, $seller, $title, $sentnotification, $sentnotification, $customerBillViewUrl);
        }

        if ($this->interactive && $this->ipncall) {
            mtrace("[{$aFullBill->transactionid}] ".'Production Controller : Transaction notified to customer');
        }
        /* notify sales forces and administrator */
        // Send final notification by mail if something has been done the sales administrators users should know.
        $salesnotification = shop_compile_mail_template('transaction_confirm', array('TRANSACTION' => $aFullBill->transactionid,
                                                                   'SERVER' => $SITE->fullname,
                                                                   'SERVER_URL' => $CFG->wwwroot,
                                                                   'SELLER' => $config->sellername,
                                                                   'FIRSTNAME' => $customer->firstname,
                                                                   'LASTNAME' => $customer->lastname,
                                                                   'MAIL' => $customer->email,
                                                                   'CITY' => $customer->city,
                                                                   'COUNTRY' => $customer->country,
                                                                   'PAYMODE' => $aFullBill->paymode,
                                                                   'ITEMS' => $aFullBill->itemcount,
                                                                   'AMOUNT' => sprintf("%.2f", round($aFullBill->untaxedamount, 2)),
                                                                   'TAXES' => sprintf("%.2f", round($aFullBill->taxes, 2)),
                                                                   'TTC' => sprintf("%.2f", round($aFullBill->amount, 2))
                                                                    ), '');
        $administratorViewUrl = $CFG->wwwroot . "/local/shop/bills/view.php?id={$aFullBill->shopid}&view=viewBill&billid={$aFullBill->id}&transid={$aFullBill->transactionid}";
        if ($salesrole = $DB->get_record('role', array('shortname' => 'sales'))) {
            $seller = new \StdClass;
            $seller->firstname = $config->sellername;
            $seller->lastname = '';
            $seller->email = $config->sellermail;
            $seller->maildisplay = true;
            $title = $SITE->shortname . ' : ' . get_string('orderconfirm', 'local_shop');
            if (!empty($productiondata->private)) {
                $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productiondata->salesadmin, $salesnotification);
            } else {
                $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', '', $salesnotification);
            }
            $sent = ticket_notifyrole($salesrole->id, $systemcontext, $seller, $title, $sentnotification, $sentnotification, $administratorViewUrl);
            if ($sent) {
                shop_trace("[{$aFullBill->transactionid}] Production Controller : shop Transaction Confirm Notification to sales");
            } else {
                shop_trace("[{$aFullBill->transactionid}] Production Controller Warning : Seems no sales manager are assigned");
            }
        } else {
            shop_trace("[{$aFullBill->transactionid}] ".'Production Controller : No sales role defined');
        }

        // final destruction of the shopping session

        if (!empty($this->interactive)) {
            if (!$holding) {
                unset($SESSION->shoppingcart);
            }
        }
    }
}