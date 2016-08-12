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
 * @package    shoppaymodes_systempay
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

Use \local_shop\Bill;
Use \local_shop\Shop;

# Response codes
define('SP_PAYMENT_ACCEPTED', '1'); // Autorisation acceptée
define('SP_PAYMENT_REJECTED', '2'); // Autorisation refusée
define('SP_DELAYED_PAYMENT_ACCEPTED', '4'); // Echéance du paiement acceptée et en attente de remise
define('SP_DELAYED_PAYMENT_REJECTED', '5'); // Echéance du paiement refusée
define('SP_CHECK_PAYMENT_REJECTED', '6'); // Paiement par chèque accepté
define('SP_CHECK_PAYMENT_PAIEDOUT', '8'); // Chèque encaissé
define('SP_DELAYED_PAYMENT_COMPLETE', '10'); // Paiement terminé
define('SP_DELAYED_PAYMENT_CANCELLED', '11'); // Echéance du paiement annulée par le commerçant
define('SP_PURCHASE_CANCELLED', '12'); // Abandon de l’internaute
define('SP_PAYBACK_REGISTERED', '15'); // Remboursement enregistré
define('SP_PAYBACK_CANCELLED', '16'); // Remboursement annulé
define('SP_PAYBACK_PROCESSED', '17'); // Remboursement accepté
define('SP_DELAYED_PAYMENT_FAILURE', '20'); // Echéance du paiement avec un impayé
define('SP_DELAYED_PAYMENT_FAILED_PENDING', '21'); // Echéance du paiement avec un impayé et en attente de validation des services SP PLUS
define('SP_DELAYED_PAYMENT_PENDING', '30'); // Echéance du paiement remisée
define('SP_TEST_PAYMENT', '99'); // Paiement de test en production 
define('SP_BROKEN', '-1'); // Rupture de chaine d'état

define('SP_SECURE_NO', '0');
define('SP_SECURE_13DS', '1 3DS');
define('SP_SECURE_13DR', '1 3DR');
define('SP_SECURE_1ECB', '1 ECB');

class shop_paymode_systempay extends shop_paymode {

    function __construct(&$shop) {
        parent::__construct('systempay', $shop, true, true);
    }

    function is_instant_payment() {
        return true;
    }

    // prints a payment porlet in an order form
    function print_payment_portlet(&$portlet) {
        global $CFG;

        $confing = get_config('local_shop');

        echo '<table id="systempay-panel"><tr><td>';
        echo shop_compile_mail_template('door_transfer_text', array(), 'shoppaymodes_systempay');
        echo '</td></tr>';
        echo '<tr><td align="center"><br />';

       $portlet->sessionid = session_id();
       $portlet->amount = $portlet->totaltaxedamount;
       $portlet->merchant_id = $config->sellerID;
       $portlet->onlinetransactionid = $this->generate_online_id();
       $portlet->returnurl = new moodle_url('/local/shop/paymodes/systempay/process.php');
       include($CFG->dirroot.'/local/shop/paymodes/systempay/systempayAPI.portlet.php');

        echo '<center><p><span class="procedureOrdering"></span>';
        /*
        $payonlinestr = get_string('payonline', 'local_shop');
        echo "<input type=\"button\" name=\"go_btn\" value=\"$payonlinestr\" onclick=\"document.confirmation.submit();\" />";
        */

        echo '<p><span class="shop-procedure-cancel">X</span> ';
        $cancelstr = get_string('cancel');
        $shopurl = new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $this->theshop->id));
        echo '<a href="'.$shopurl.'" class="smalltext">'.$cancelstr.'</a>';
        echo '</td></tr></table>';
    }

    // prints a payment porlet in an order form
    function print_invoice_info(&$billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_systempay', $this->name);
    }

    function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', array());
    }

    // extract DATA, get context_return and bounce to shop entrance with proper context values
    function cancel() {
        global $CFG, $SESSION;

        $paydata = $this->decode_return_data();

        list($cmd, $shopinstanceid, $transid) = explode('-', $paydata['return_context']);

        $aFullBill = Bill::get_by_transaction($transid);

        $this->theshop = $aFullBill->theshop;

        $aFullBill->onlinetransactionid = $paydata['shop_id'].'-'.$paydata['transmission_date'].'-'.$paydata['transaction_id'];
        $aFullBill->paymode = 'systempay';
        $aFullBill->status = 'CANCELLED';
        $aFullBill->save(true);

        // cancel shopping cart
        unset($SESSION->shoppingcart);
        redirect(new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $this->theshop->id)));
    }

    /**
    * processes an explicit payment return
    */
    function process() {
        global $CFG, $SESSION;

        $config = get_config('local_shop');

        $paydata = $this->decode_return_data();

        // Erreur, affiche le message d'erreur
        if ($paydata['code'] != 0 && !empty($paydata['error'])) {
            $systempayapierrorstr = get_string('systempayapierror', 'shoppaymodes_systempay');
            echo "<center><b>{$systempayapierrorstr}</b></center>";
            echo '<br/><br/>';
            $systempayerror = get_string('systempayerror', 'shoppaymodes_systempay', $paydata['error']);
            echo $systempayerror.'<br/>';
            return false;
        } else {
            // OK, affichage des champs de la réponse
            if (debugging() && $config->test) {
                # OK, affichage du mode DEBUG si activé
                echo "<center>\n";
                echo "<H3>R&eacute;ponse manuelle du serveur SP Plus</H3>\n";
                echo "</center>\n";
                echo '<hr/>';
                print_object($paydata);
                echo "<br/><br/><hr/>";
            }

            list($cmd, $shopinstanceid, $transid) = explode('-', $paydata['return_context']);

            $aFullBill = Bill::get_by_transaction($transid);

            $this->theshop = $aFullBill->theshop;

            // bill could already be SOLDOUT by IPN    so do nothing
            // process it only if needing to process.
            if ($aFullBill->status == 'PENDING') {

                // processing bill changes
                if ($paydata['response_code'] == SP_DELAYED_PAYMENT_COMPLETE) {

                    $aFullBill->onlinetransactionid = $paydata['merchant_id'].'-'.$paydata['transmission_date'].'-'.$paydata['transaction_id'];
                    $aFullBill->status = 'SOLDOUT';
                    $aFullBill->save(true);

                    // redirect to success for ordering production with significant data
                    shop_trace("[$transid] SystemPay : Transation Complete, transferring to success end point");
                    redirect(new moodle_url('/local/shop/front/view.php', array('view' => 'produce', 'id' => $this->theshop->id, 'what' => 'confirm', 'transid' => $transid)));
                } else {
                    $aFullBill->status  = 'FAILED';
                    $aFullBill->save(true);

                    // Do not erase shopping cart : user might try again with other payment mean
                    // unset($SESSION->shoppingcart);

                    redirect(new moodle_url('/local/shop/front/view.php', array('view' => 'shop', 'id' => $this->theshop->id, 'transid' => $transid)));
                }
            }

            if ($aFullBill->status == 'SOLDOUT') {
                redirect(new moodle_url('/local/shop/front/view.php', array('view' => 'produce', 'id' => $this->theshop->id, 'what' => 'finish', 'transid' => $transid)));
            }
        }
    }

    /**
    * processes a payment asynchronous confirmation
    */
    function process_ipn() {
        global $CFG, $SITE, $DB;

        $config = get_config('local_shop');

        $paydata = $this->decode_return_data();
        // Erreur, affiche le message d'erreur
        if ($this->decodebin($paydata['hmac'])) {
            $systempayapierrorstr = get_string('systempaysecreterror', 'local_shop');
            shop_trace("SystemPay IPN : {$systempaysecreterrorstr}");
            die;
        }

        list($cmd, $instanceid, $transid) = explode('-', $paydata['return_context']);

        shop_trace("[$transid] SystemPay IPN processing");

        $aFullBill = Bill::get_by_transaction($transid);

        $this->theshop = $aFullBill->theshop;

        $laststatus = (strrchr($aFullBill->remotestatus, ',')) ? 0 : substr(strrchr($aFullBill->remotestatus, ','), 1);

        // initiate state machine systempay
        switch ($paydata['etat']) {
            case SP_DELAYED_PAYMENT_COMPLETE :
                switch ($laststatus) {
                    case SP_DELAYED_PAYMENT_ACCEPTED : // correct sequence SP_PAYMENT_ACCEPTED > SP_DELAYED_PAYMENT_ACCEPTED > SP_DELAYED_PAYMENT_COMPLETE

                        // mark transaction (order record) as abandonned

                        // processing bill changes
                        if ($aFullBill->status == 'PENDING' || $aFullBill->status == 'PLACED') {

                            $aFullBill->onlinetransactionid = $paydata['reference'].'-'.$paydata['refsfp'];
                            $aFullBill->status = 'SOLDOUT';
                            $aFullBill->remotestatus = (!empty($bill->remotestatus)) ? $bill->remotestatus.','.$paydata['etat'] : $paydata['etat'] ;
                            $aFullBill->save(true);

                            shop_trace("[$transid]  Mercanet IPN : success, transferring to success controller");

                            // now we need to execute non interactive production code
                            // this SHOULD NOT be done by redirection as Systempay server might not 
                            // handle this. Thus only use the controller and die afterwoods.

                            // here we need to fake user login in if has account or we might create another diverging account
                            // payment acceptation is sufficiant to validate userid information out from bill data
                            if (!empty($aFullBill->customer->hasaccount)) {
                                global $USER;
                                $USER = $DB->get_record('user', array('id' => $aFullBill->customer->hasaccount));
                            }

                            include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
                            $controller = new \local_shop\front\production_controller($aFullBill, true, false);
                            $result = $controller->process('produce');
                            die;
                        }

                        break;
                    case SP_BROKEN :
                        shop_trace("[$transid] SystemPay IPN Error : Broken endchain : {$paydata['etat']} ");
                        break;
                    default:

                        shop_trace("[$transid] SystemPay IPN Warning : Wrong sequence ");
                        $aFullBill->remotestatus = (!empty($aFullBill->remotestatus)) ? $aFullBill->remotestatus.','.$paydata['etat'].','.SP_BROKEN : $paydata['etat'] ;
                        $aFullBill->save(true) ;

                        // Notify sales roles of error
                        if ($salesrole = $DB->get_record('role', array('shortname' => 'sales'))) {
                            $seller = new StdClass;
                            $seller->firstname = $config->sellername;
                            $seller->lastname = '';
                            $seller->email = $config->sellermail;
                            $seller->maildisplay = true;
                            $title = $SITE->shortname . ' : ' . get_string('systempayfailure', 'local_shop');
                            $sentnotification = "[$transid] SystemPay IPN Warning : Wrong sequence ";
                            $administratorViewUrl = new moodle_url('/local/shop/front/scantrace.php', array('transid' => $transid, 'id' => $instanceid));
                            ticket_notifyrole($salesrole->id, context_system::instance(), $seller, $title, $sentnotification, $sentnotification, $administratorViewUrl);
                        } else {
                            shop_trace("[{$aFullBill->transactionid}] ".'Success Controller : No sales role defined');
                        }
                          die;
                        break;
                }

            case SP_PAYMENT_REJECTED :
                    $aFullBill->status = 'FAILED';
                    $aFullBill->remotestatus = (!empty($aFullBill->remotestatus)) ? $aFullBill->remotestatus.','.$paydata['etat'] : $paydata['etat'] ;
                    $aFullBill->save(true);

                    shop_trace("[$transid] SystemPay IPN failure : SP_PAYMENT_REJECTED ");
                    die;
                break;

            case SP_DELAYED_PAYMENT_REJECTED : // similar to previous, but may come after SP_DELAYED_PAYMENT_ACCEPTED
                    $aFullBill->status = 'FAILED';
                    $aFullBill->remotestatus = (!empty($aFullBill->remotestatus)) ? $aFullBill->remotestatus.','.$paydata['etat'] : $paydata['etat'] ;
                    $aFullBill->save(true);

                    shop_trace("[$transid] SystemPay IPN failure : SP_DELAYED_PAYMENT_REJECTED ");
                    die;
                break;

            default:
                // just accumulate remote status chain
                $aFullBill->remotestatus = (!empty($aFullBill->remotestatus)) ? $aFullBill->remotestatus.','.$paydata['etat'] : $paydata['etat'] ;
                $aFullBill->save(true) ;
                break;
        }
    }

    /**
     * provides global settings to add to shop settings when installed
     */
    function settings(&$settings) {

        $settings->add(new admin_setting_heading('local_shop_'.$this->name, get_string($this->name.'paymodeparams', 'shoppaymodes_systempay'), 
            get_string('systempayinfo', 'shoppaymodes_systempay')));

        $settings->add(new admin_setting_configtext('local_shop_systempay_service_url', get_string('systempayserviceurl', 'shoppaymodes_systempay'),
                           get_string('configsystempayserviceurl', 'shoppaymodes_systempay'), '', PARAM_TEXT));

        // TODO : Generalize
        $countryoptions['FR'] = get_string('france', 'shoppaymodes_systempay');
        $countryoptions['EN'] = get_string('england', 'shoppaymodes_systempay');
        $countryoptions['DE'] = get_string('germany', 'shoppaymodes_systempay');
        $countryoptions['ES'] = get_string('spain', 'shoppaymodes_systempay');

        $settings->add(new admin_setting_configselect('local_shop_systempay_country', get_string('systempaycountry', 'shoppaymodes_systempay'),
                           get_string('configsystempaycountry', 'shoppaymodes_systempay'), '', $countryoptions));

        $currencycodesoptions = array('978' => get_string('cur978', 'shoppaymodes_systempay'), 
                                    '840' => get_string('cur840', 'shoppaymodes_systempay'),
                                    '756' => get_string('cur756', 'shoppaymodes_systempay'),
                                    '826' => get_string('cur036', 'shoppaymodes_systempay'),
                                    '124' => get_string('cur124', 'shoppaymodes_systempay'),
                                    // Yen 392 0 106 106
                                    // Peso Mexicain 484 2 106.55 10655
                                    // '949' => get_string('cur949', 'shoppaymodes_systempay'),
                                    // '036' => get_string('cur036', 'shoppaymodes_systempay'),
                                    // '554' => get_string('cur554', 'shoppaymodes_systempay'),
                                    // '578' => get_string('cur578', 'shoppaymodes_systempay'),
                                    // '986' => get_string('cur986', 'shoppaymodes_systempay'),
                                    // '032' => get_string('cur032', 'shoppaymodes_systempay'),
                                    // '116' => get_string('cur116', 'shoppaymodes_systempay'),
                                    // '901' => get_string('cur901', 'shoppaymodes_systempay'),
                                    // '752' => get_string('cur752', 'shoppaymodes_systempay'),
                                    // '208' => get_string('cur208', 'shoppaymodes_systempay'),
                                    // '702' => get_string('cur702', 'shoppaymodes_systempay')
        );

        $settings->add(new admin_setting_configselect('local_shop_systempay_currency_code', get_string('systempaycurrencycode', 'shoppaymodes_systempay'),
                           get_string('configsystempaycurrencycode', 'shoppaymodes_systempay'), '', $currencycodesoptions));

    }

    /**
     * returns encoded hmac token for sending transaction secured signature
     */
    function encode_bin($os) {
    }

    /**
     * returns decodes encrypted information
     */
    function decode_bin($os) {
    }

    /**
     * generates a suitable online id for the transaction.
     * real bill online id is : shopid (2d), payment_date (yyyymmdd as 8d), and the onlinetxid (4d) generated here.
     */
    function generate_online_id() {
        $config = get_config('local_shop');

        $now = time();
        $midnight = mktime (0, 0, 0, date("n", $now), date("j", $now), date("Y", $now));
        if ($midnight > 0 + @$config->systempay_lastmidnight) {
            set_config('systempay_idseq', 1, 'local_shop');
            set_config('systempay_lastmidnight', $midnight, 'local_shop');
        }
        $onlinetxid = sprintf('%04d', ++$config->systempay_idseq);
        set_config('systempay_idseq', $config->systempay_idseq, 'local_shop');
        return $onlinetxid;
    }

    /**
     * Get the systempay buffer and extract info from cryptic response.
     */
    function decode_return_data() {
        // Récupération de la variable cryptée DATA
        $paydata = $_REQUEST;
        // decode private data as arg3
        $paydata['return_context'] = base64_decode($paydata['arg3']);
        if (empty($paydata['return_context'])) {
              $systempayreturnerrorstr = get_string('emptymessage', 'local_shop');
            echo "<br/><center>$systempayreturnerrorstr</center><br/>";
            return false;
        }
        return $paydata;
    }
}