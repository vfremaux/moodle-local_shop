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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

use StdClass;
use moodle_url;
use context_system;

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/auth/ticket/lib.php');
require_once($CFG->dirroot.'/local/shop/datahandling/production.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

class production_controller extends front_controller_base {

    /**
     * this boolean value is true if the call comes from an IPN asynchronous paiement return.
     */
    protected $ipncall;

    /**
     * this boolean value is true if the payment is interactive, using online payment methods.
     */
    public $interactive;

    /**
     * The complete bill to produce.
     */
    protected $abill;

    public function __construct(&$theshop, &$thecatalog, &$theblock, &$afullbill, $ipncall = false, $interactive = false) {
        $this->abill = $afullbill;
        $this->ipncall = $ipncall;
        $this->interactive = $interactive;
        parent::__construct($theshop, $thecatalog, $theblock);
    }

    /**
     * In this case, all the data resides already in session.
     * there is nothing to get from a query.
     */
    public function receive($cmd, $data = array()) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            return;
        } else {
            $this->data = new StdClass;
        }

        switch ($cmd) {
            case 'navigate':
                break;

            case 'confirm':
                break;

            case 'produce':
                break;
        }
    }

    public function process($cmd, $holding = false) {
        global $SESSION, $DB, $CFG, $SITE, $OUTPUT;

        $config = get_config('local_shop');

        if ($cmd == 'navigate') {
            // No back possible after production.
            $next = $this->abill->theshop->get_next_step('produce');
            $params = array('view' => $next,
                            'shopid' => $this->abill->theshop->id,
                            'blockid' => 0 + @$this->abill->theblock->id,
                            'transid' => $this->abill->transactionid);
            $url = new \moodle_url('/local/shop/front/view.php', $params);
            if (empty($SESSION->shoppingcart->debug)) {
                redirect($url);
            } else {
                echo $OUTPUT->continue_button($url);
                die;
            }
        }

        $systemcontext = context_system::instance();

        // Simpler to handle in code.
        $afullbill = $this->abill;

        // Trap any non defined command here (increase security).
        if (($cmd != 'produce') && ($cmd != 'confirm')) {
            shop_trace("[{$afullbill->transactionid}] Error : Illegal production command $cmd.");
            return;
        }

        /*
         * Payment is overriden if :
         * - this is a free order (paymode has been detected as freeorder)
         * - the user is logged in and has special payment override capability
         */
        $paycheckoverride = (isloggedin() &&
                has_capability('local/shop:paycheckoverride', $systemcontext)) ||
                        ($afullbill->paymode == 'freeorder');
        $overriding = false;

        if ($cmd == 'confirm') {

            if ($paycheckoverride) {
                // Bump to next case.
                $cmd = 'produce';
                $overriding = true;
            } else {

                /*
                 * A more direct resolution when paiement is not performed online
                 * we can perform pre_pay operations
                 */

                if ($this->interactive) {
                    if ($this->ipncall) {
                        mtrace("[{$afullbill->transactionid}] ".'Order confirm (online asynchronous payment return, bill is expected to be PENDING)');
                    }
                } else {
                    shop_trace("[{$afullbill->transactionid}] ".'Order confirm (offline payments, bill is expected to be PENDING)');
                }

                // mtrace("[{$afullbill->transactionid}] ".'Production starting ...');
                // mtrace("[{$afullbill->transactionid}] ".'Production Controller : Pre Pay process');
                $productionfeedback = produce_prepay($afullbill);
                /*
                 * log new production data into bill record
                 * the first producing procedure stores production data.
                 * if interactive shopback process comes later, we just have production
                 * data to display to user.
                 */
                shop_aggregate_production($afullbill, $productionfeedback, $this->interactive);
                // All has been finished.
                unset($SESSION->shoppingcart);
            }
        }
        if ($cmd == 'produce') {

            // Start production.
            $message = "[{$afullbill->transactionid}] Production Controller :";
            $message .= " Full production starting from {$afullbill->status} ...";
            shop_trace($message);
            if ($this->interactive && $this->ipncall) {
                $message = "[{$afullbill->transactionid}] Production Controller :";
                $message .= " Full production starting from {$afullbill->status} ...";
                mtrace($message);
            }

            $pendingstates = array(SHOP_BILL_PENDING, SHOP_BILL_SOLDOUT);
            if (in_array($afullbill->status, $pendingstates) || $overriding) {
                /*
                 * when using the controller to finish a started production, do not
                 * preproduce again (paypal IPN finalization)
                 */
                if ($this->interactive && $this->ipncall) {
                    mtrace("[{$afullbill->transactionid}] ".'Production Controller : Pre Pay process');
                }
                $productionfeedback = produce_prepay($afullbill);

                if (($afullbill->status == SHOP_BILL_SOLDOUT) || $overriding) {
                    shop_trace("[{$afullbill->transactionid}] ".'Production Controller : Post Pay process');
                    if ($this->interactive && $this->ipncall) {
                        mtrace("[{$afullbill->transactionid}] ".'Production Controller : Post Pay process');
                    }

                    if ($productionfeedback2 = produce_postpay($afullbill)) {
                        $productionfeedback->public .= '<br/>'.$productionfeedback2->public;
                        $productionfeedback->private .= '<br/>'.$productionfeedback2->private;
                        $productionfeedback->salesadmin .= '<br/>'.$productionfeedback2->salesadmin;
                        if ($overriding) {
                            /*
                             * this marks the purchase is not paied, but has been allowed to
                             * produce because the customer is trusted.
                             */
                            if ($afullbill->paymode == 'freeorder') {
                                $afullbill->status = SHOP_BILL_SOLDOUT;
                            } else {
                                $afullbill->status = SHOP_BILL_PREPROD;
                            }
                        } else {
                            // Purchase has been fully completed and paied.
                            $afullbill->status = SHOP_BILL_COMPLETE;
                        }
                        if (!$holding) {
                            // If holding for repeatable tests, do not complete the bill.
                            shop_trace("[{$afullbill->transactionid}] ".'Production Controller : Updating bill state to '.$afullbill->status);
                            if ($this->interactive && $this->ipncall) {
                                mtrace("[{$afullbill->transactionid}] ".'Production Controller : Updating bill state to '.$afullbill->status);
                            }
                            $afullbill->save();
                        }
                    }
                }
                /*
                 * log new production data into bill record
                 * the first producing procedure stores production data.
                 * if interactive shopback process comes later, we just have production
                 * data to display to user.
                 */
                shop_aggregate_production($afullbill, $productionfeedback, $this->interactive);
            } else {
                $productionfeedback = new StdClass;
                $productionfeedback->public = 'Completed';
                $productionfeedback->private = 'Completed';
                $productionfeedback->salesadmin = 'Completed';
                shop_aggregate_production($afullbill, $productionfeedback, $this->interactive);
            }
        }

        // Send final notification by mail if something has been done the end user should know.
        shop_trace("[{$afullbill->transactionid}] ".'Production Controller : Transaction Complete Operations');
        if ($this->interactive && $this->ipncall) {
            mtrace("[{$afullbill->transactionid}] ".'Production Controller : Transaction Complete Operations');
            mtrace('--- Public customer feedback ------------------');
            mtrace($productionfeedback->public);
            mtrace('--- Private customer feedback ------------------');
            mtrace($productionfeedback->private);
            mtrace('--- Sales admin feedback ------------------');
            mtrace($productionfeedback->salesadmin);
            mtrace('---------------------------------------');
        }

        // Notify end user.
        // Feedback customer with mail confirmation.
        $customer = $DB->get_record('local_shop_customer', array('id' => $afullbill->customerid));

        if (empty($afullbill->customeruser)) {
            $afullbill->customeruser = $DB->get_record('user', array('id' => $afullbill->customer->hasaccount));
        }

        if ($afullbill->customeruser) {
            $billurl = new moodle_url('/local/shop/front/order.popup.php', array('billid' => $afullbill->id, 'transid' => $afullbill->transactionid));
            $ticket = ticket_generate($afullbill->customeruser, 'immediate access', $billurl);
        } else {
            $ticket = 'NOUSER';
        }

        $paymodename = get_string($afullbill->paymode, 'shoppaymodes_'.$afullbill->paymode);
        $vars = array('SERVER' => $SITE->shortname,
                      'SERVER_URL' => $CFG->wwwroot,
                      'SELLER' => $config->sellername,
                      'FIRSTNAME' => $customer->firstname,
                      'LASTNAME' => $customer->lastname,
                      'MAIL' => $customer->email,
                      'CITY' => $customer->city,
                      'COUNTRY' => $customer->country,
                      'ITEMS' => $afullbill->itemcount,
                      'PAYMODE' => $paymodename,
                      'AMOUNT' => sprintf("%.2f", round($afullbill->amount, 2)),
                      'TICKET' => $ticket);
        $notification = shop_compile_mail_template('sales_feedback', $vars, '');
        $params = array('id' => $afullbill->shopid,
                        'blockid' => $afullbill->blockid,
                        'view' => 'bill',
                        'transid' => $afullbill->transactionid);
        $customerbillviewurl = new \moodle_url('/local/shop/front/view.php', $params);

        $seller = new StdClass;
        $seller->id = $DB->get_field('user', 'id', array('username' => 'admin', 'mnethostid' => $CFG->mnet_localhost_id));
        $seller->firstname = '';
        $seller->lastname = $config->sellername;
        $seller->email = $config->sellermail;
        $seller->maildisplay = true;
        $seller->id = $DB->get_field('user', 'id', array('email' => $config->sellermail));

        // Complete seller with expected fields.
<<<<<<< HEAD
        // M4.
        $fields = \core_user\fields::for_name()->excluding('id')->get_required_fields();
=======
        $fields = \local_shop\compat::get_name_fields_as_array();
>>>>>>> MOODLE_401_STABLE
        foreach ($fields as $f) {
            if (!isset($seller->$f)) {
                $seller->$f = '';
            }
        }

        $title = $SITE->shortname.' : '.get_string('yourorder', 'local_shop');

        if (!empty($productionfeedback->private)) {
            $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', $productionfeedback->private, $notification);
        } else {
            $sentnotification = str_replace('<%%PRODUCTION_DATA%%>', '', $notification);
        }

        if ($afullbill->customeruser) {
            $sent = ticket_notify($afullbill->customeruser, $seller, $title, $sentnotification, $sentnotification, $customerbillviewurl);
            if ($sent) {
                $message = "[{$afullbill->transactionid}] Production Controller :";
                $message .= " shop Transaction Confirm Notification to Customer";
                shop_trace($message);
                shop_trace($sentnotification, 'mail', $afullbill->customeruser);
            } else {
                $message = "[{$afullbill->transactionid}] Production Controller Warning :";
                $message .= " Failed to notify notification to Customer";
                shop_trace($message);
            }
        } else {
            $message = "[{$afullbill->transactionid}] Production Controller Warning :";
            $message .= " No customer to send notification to.";
            shop_trace($message);
        }

        if ($this->interactive && $this->ipncall) {
            mtrace("[{$afullbill->transactionid}] ".'Production Controller : Transaction notified to customer');
        }

        // Notify sales forces and administrator.
        // Send final notification by mail if something has been done the sales administrators users should know.
        $vars = array('TRANSACTION' => $afullbill->transactionid,
                      'SERVER' => $SITE->fullname,
                      'SERVER_URL' => $CFG->wwwroot,
                      'SELLER' => $config->sellername,
                      'FIRSTNAME' => $customer->firstname,
                      'LASTNAME' => $customer->lastname,
                      'MAIL' => $customer->email,
                      'CITY' => $customer->city,
                      'COUNTRY' => $customer->country,
                      'PAYMODE' => $afullbill->paymode,
                      'ITEMS' => $afullbill->itemcount,
                      'AMOUNT' => sprintf("%.2f", round($afullbill->untaxedamount, 2)),
                      'TAXES' => sprintf("%.2f", round($afullbill->taxes, 2)),
                      'TTC' => sprintf("%.2f", round($afullbill->amount, 2)));

        $salesnotification = shop_compile_mail_template('transaction_confirm', $vars, '');

        $params = array('id' => $afullbill->shopid,
                        'view' => 'viewBill',
                        'transid' => $afullbill->transactionid);

        $administratorviewurl = new \moodle_url('/local/shop/bills/view.php', $params);;

        if ($salesrole = $DB->get_record('role', array('shortname' => 'sales'))) {
            // If the sales role is defined.

            $title = $SITE->shortname.' Backoffice : '.get_string('orderconfirm', 'local_shop');

            if (!empty($productionfeedback->salesadmin)) {
                $sn = str_replace('<%%PRODUCTION_DATA%%>', $productionfeedback->salesadmin, $salesnotification);
            } else {
                $sn = str_replace('<%%PRODUCTION_DATA%%>', '', $salesnotification);
            }

            $sent = ticket_notifyrole($salesrole->id, $systemcontext, $seller, $title, $sn, $sn, $administratorviewurl);

            if ($sent) {
                $message = "[{$afullbill->transactionid}] Production Controller :";
                $message .= " shop Transaction Confirm Notification to sales";
                shop_trace($message);
            } else {
                $message = "[{$afullbill->transactionid}] Production Controller Warning :";
                $message .= " Seems no sales manager are assigned";
                shop_trace($message);
            }
        } else {
            shop_trace("[{$afullbill->transactionid}] ".'Production Controller : No sales role defined');
        }

        // Final destruction of the shopping session.

        if (!empty($this->interactive)) {
            if (!$holding) {
                unset($SESSION->shoppingcart);
            }
        }
    }
}