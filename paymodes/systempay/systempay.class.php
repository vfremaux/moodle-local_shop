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
 * @package    shoppaymodes_systempay
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/paymodes/paymode.class.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use \local_shop\Bill;
use \local_shop\Shop;

// Response codes (vads_status).
define('SP_PAYMENT_ACCEPTED', '00'); // Paiement réalisé avec succès.
define('SP_PAYMENT_JOIN_BANK', '02'); // Le commerçant doit contacter la banque du porteur.
define('SP_PAYMENT_REJECTED', '05'); // Echéance du paiement refusée.
define('SP_PURCHASE_CANCELLED', '17'); // Abandon de l’internaute.
define('SP_REQUEST_ERROR', '30'); // Erreur de requete.
define('SP_INTERNAL_ERROR', '96'); // Erreur interne de traitement.

$vadsstatus = array(
    SP_PAYMENT_ACCEPTED => 'SP_PAYMENT_ACCEPTED',
    SP_PAYMENT_JOIN_BANK => 'SP_PAYMENT_JOIN_BANK', // Le commerçant doit contacter la banque du porteur.
    SP_PAYMENT_REJECTED => 'SP_PAYMENT_REJECTED', // Echéance du paiement refusée.
    SP_PURCHASE_CANCELLED => 'SP_PURCHASE_CANCELLED', // Abandon de l’internaute.
    SP_REQUEST_ERROR => 'SP_REQUEST_ERROR', // Erreur de requete.
    SP_INTERNAL_ERROR => 'SP_INTERNAL_ERROR', // Erreur interne de traitement.
);

// Extra error explicitaton code (vads_extra_result).
/*
 * Pas de contrôle effectué.
 */
define('SP_STATUS_NOCHECK', '');
/*
 * Tous les contrôles se sont déroulés avec succès.
 */
define('SP_STATUS_GOOD', '00');
/*
 * La carte a dépassé l’encours autorisé.
 */
define('SP_STATUS_OVER', '02');
/*
 * La carte appartient à la liste grise du commerçant.
 */
define('SP_STATUS_SELLER_EXCLUDES', '03');
/*
 * Le pays d’émission de la carte appartient à la liste grise du commerçant ou le pays d’émission
 * de la carte n’appartient pas à la liste blanche du commerçant.
 */
define('SP_STATUS_COUNTRY_EXCLUDES', '04');
/*
 * L’adresse IP appartient à la liste grise du commerçant.
 */
define('SP_STATUS_IP_EXCLUDES', '05');
/*
 * Le code bin appartient à la liste grise du commerçant.
 */
define('SP_STATUS_BINCODE_EXCLUDES', '06');
/*
 * Détection d’une E-Carte Bleue.
 */
define('SP_STATUS_E_CARD', '07');
/*
 * Détection d’une carte commerciale nationale.
 */
define('SP_STATUS_LOCAL_CARD', '08');
/*
 * Détection d’une carte commerciale étrangère.
 */
define('SP_STATUS_FOREIGN_CARD', '09');
/*
 * Détection d’une carte à autorisation systématique
 */
define('SP_STATUS_AUTH_CARD', '14');
/*
 * Contrôle de cohérence : aucun pays ne correspond (pays IP, payscarte, pays client)
 */
define('SP_STATUS_BAD_COUNTRY', '20');
/*
 * Le pays de l’adresse IP appartient à la liste grise.
 */
define('SP_STATUS_IP_COUNTRY_EXCLUDES', '30');
/*
 * Problème technique rencontré par le serveur lors du traitement d’un des contrôles locaux
 */
define('SP_STATUS_TECH_ERROR', '99');

$vadsextrastatus = array(
    SP_STATUS_NOCHECK => 'SP_STATUS_NOCHECK',
    SP_STATUS_GOOD => 'SP_STATUS_GOOD',
    SP_STATUS_OVER => 'SP_STATUS_OVER',
    SP_STATUS_SELLER_EXCLUDES => 'SP_STATUS_SELLER_EXCLUDES',
    SP_STATUS_COUNTRY_EXCLUDES => 'SP_STATUS_COUNTRY_EXCLUDES',
    SP_STATUS_IP_EXCLUDES => 'SP_STATUS_IP_EXCLUDES',
    SP_STATUS_BINCODE_EXCLUDES => 'SP_STATUS_BINCODE_EXCLUDES',
    SP_STATUS_E_CARD => 'SP_STATUS_E_CARD',
    SP_STATUS_LOCAL_CARD => 'SP_STATUS_LOCAL_CARD',
    SP_STATUS_FOREIGN_CARD => 'SP_STATUS_FOREIGN_CARD',
    SP_STATUS_AUTH_CARD => 'SP_STATUS_AUTH_CARD',
    SP_STATUS_BAD_COUNTRY => 'SP_STATUS_BAD_COUNTRY',
    SP_STATUS_IP_COUNTRY_EXCLUDES => 'SP_STATUS_IP_COUNTRY_EXCLUDES',
    SP_STATUS_TECH_ERROR => 'SP_STATUS_TECH_ERROR',
);

define('SP_SECURE_NO', '0');
define('SP_SECURE_13DS', '1 3DS');
define('SP_SECURE_13DR', '1 3DR');
define('SP_SECURE_1ECB', '1 ECB');

// Waranty codes.
define('SP_WARANTY_YES', 'YES'); // Le paiement est garanti.
define('SP_WARANTY_NO', 'NO'); // Le paiement n’est pas garanti.
define('SP_WARANTY_UNKNOWN', 'UNKNOWN'); // Suite à une erreur technique, le paiement ne peut pas être garanti.
define('SP_WARANTY_NA', ''); // Garantie de paiement non applicable.

class shop_paymode_systempay extends shop_paymode {

    public function __construct(&$shopblockinstance) {
        parent::__construct('systempay', $shopblockinstance, true, true);
    }

    // Prints a payment porlet in an order form.
    function print_payment_portlet(&$shoppingcart) {
        global $CFG;

        echo '<div id="shop-panel-caption">';

        echo shop_compile_mail_template('door_transfer_text', array(), 'shoppaymodes_systempay');

        echo '</div>';
        echo '<div id="shop-panel-systempay"><br />';

        $portlet = new StdClass;
        $portlet->sessionid = session_id();
        $portlet->amount = $shoppingcart->finalshippedtaxedtotal;
        $portlet->merchant_id = $this->_config->systempay_merchant_id;
        $portlet->transactionid = $shoppingcart->transid;
        $portlet->onlinetransactionid = $this->generate_online_id();
        $portlet->returnurl = new moodle_url('/local/shop/paymodes/systempay/process.php');
        $portlet->customer = (object)$shoppingcart->customerinfo;

        include($CFG->dirroot.'/local/shop/paymodes/systempay/systempayAPI.portlet.php');

        echo '<center><p><span class="procedureOrdering"></span>';
        echo '<p><span class="shop-procedure-cancel">X</span> ';
        $cancelstr = get_string('cancel');
        $params = array('view' => 'shop', 'shopid' => $this->theshop->id);
        $cancelurl = new moodle_url('/local/shop/shop/view.php', $params);
        echo '<a href="'.$cancelurl.'" class="smalltext">'.$cancelstr.'</a>';
        echo '</div>';
    }

    // Prints a payment porlet in an order form.
    function print_invoice_info(&$billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_systempay', $this->name);
    }

    function print_complete() {
        echo shop_compile_mail_template('bill_complete_text', array(), 'local_shop');
    }

    // Extract DATA, get context_return and bounce to shop entrance with proper context values.
    public function cancel() {
        global $SESSION;

        $paydata = $this->decode_return_data();

        list($cmd, $shopid, $transid) = explode('-', $paydata['return_context']);

        // Mark transaction (order record) as abandonned.
        $afullbill = Bill::get_by_transaction($transid);

        $this->theshop = $afullbill->theshop;

        $afullbill->onlinetransactionid = $paydata['vads_site_id'].'-'.$paydata['vads_trans_date'].'-'.$paydata['vads_trans_id'];
        $afullbill->paymode = 'systempay';
        $afullbill->status = SHOP_BILL_CANCELLED;
        $afullbill->save(true); //Light save.

        // Cancel shopping cart.
        unset($SESSION->shoppingcart);

        $params = array('view' => 'shop', 'shopid' => $this->theshop->id);
        $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
        redirect($redirecturl);
    }

    /**
     * processes an explicit payment return
     */
    public function process() {
        $paydata = $this->decode_return_data();

        // OK, affichage des champs de la réponse.
        if (debugging() && $this->_config->test) {
            // OK, affichage du mode DEBUG si activé.
            echo "<center>\n";
            echo "<H3>R&eacute;ponse manuelle du serveur SP Plus</H3>\n";
            echo "</center>\n";
            echo '<hr/>';
            echo print_r($paydata, true);
            echo "<br/><br/><hr/>";
        }

        list($cmd, $shopid, $transid) = explode('-', $paydata['return_context']);

        $afullbill = Bill::get_by_transaction($transid);

        $this->theshop = $afullbill->theshop;

        /*
         * bill could already be SOLDOUT by IPN    so do nothing
         * process it only if needing to process.
         */
        if ($paydata['vads_result'] == SP_PAYMENT_ACCEPTED) {
            // Processing bill changes.
            if ($afullbill->status == SHOP_BILL_PLACED || $afullbill->status == SHOP_BILL_PENDING) {
                $tid = $paydata['vads_site_id'].'-'.$paydata['vads_trans_date'].'-'.$paydata['vads_trans_id'];
                $afullbill->onlinetransactionid = $tid;
                $afullbill->status = SHOP_BILL_SOLDOUT;
                $afullbill->paiedamount = $paydata['vads_effective_amount'];
                $afullbill->save(true);

                // Redirect to success for ordering production with significant data.
                shop_trace("[$transid] SystemPay : Transation Complete, transferring to success end point");
                $params = array('view' => 'produce', 'shopid' => $this->theshop->id, 'what' => 'confirm', 'transid' => $transid);
                $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
                redirect($redirecturl);
            }

            if ($afullbill->status == SHOP_BILL_SOLDOUT) {
                $params = array('view' => 'produce', 'shopid' => $this->theshop->id, 'what' => 'confirm', 'transid' => $transid);
                $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
                redirect($redirecturl);
            }

            /*
             * Other situations should be weird cases...
             * Silent redirect but shop_trace something
             */
            shop_trace("[$transid] SystemPay : Weird state sequence Trans accept in status ".$afullbill->status);
            $params = array('view' => 'produce', 'shopid' => $this->theshop->id, 'what' => 'confirm', 'transid' => $transid);
            $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
            redirect($redirecturl);
        } else {
            $afullbill->status = SHOP_BILL_FAILED;
            $afullbill->save(true);

            // Do not erase shopping cart : user might try again with other payment mean.

            $params = array('view' => 'shop', 'shopid' => $shopid, 'transid' => $transid);
            redirect(new moodle_url('/local/shop/front/view.php', $params));
        }

    }

    /**
     * processes a payment asynchronous confirmation
     */
    public function process_ipn() {
        global $CFG;

        $paydata = $this->decode_return_data();

        list($cmd, $shopid, $transid) = explode('-', $paydata['return_context']);
        shop_trace("[$transid] SystemPay IPN processing");

        if ($_POST['vads_operation_type'] != 'CREDIT') {
            shop_trace("[$transid] SystemPay IPN : Unsupported operation : Operation was ".$_POST['vads_operation_type']);
            die;
        }

        $afullbill = Bill::get_by_transaction($transid);
        $laststatus = (strrchr($afullbill->remotestatus, ',')) ? 0 : substr(strrchr($afullbill->remotestatus, ','), 1);

        // Initiate systempay processing.
        switch($paydata['vads_result']) {
            case SP_PAYMENT_ACCEPTED :

                // Processing bill changes.
                if ($afullbill->status != SHOP_BILL_PLACED && $afullbill->status != SHOP_BILL_PENDING) {
                    break;
                }
                $tid = $paydata['vads_site_id'].'-'.$paydata['vads_trans_date'].'-'.$paydata['vads_trans_id'];
                $afullbill->onlinetransactionid = $tid;
                $afullbill->status = SHOP_BILL_SOLDOUT;
                $afullbill->paiedamount = $paydata['vads_effective_amount'] / 100;
                $afullbill->remotestatus = $vadsstatus[$paydata['vads_status']];
                $afullbill->save(true);
                shop_trace("[$transid]  SystemPay IPN : success, transferring to success controller");

                /*
                 * now we need to execute non interactive production code
                 * this SHOULD NOT be done by redirection as Systempay server might not
                 * handle this. Thus only use the controller and die afterwoods.
                 */

                include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
                $controller = new \local_shop\front\production_controller($afullbill, true, false);
                $result = $controller->process('produce');
                die;
            default:
                $afullbill->status = SHOP_BILL_FAILED;
                $afullbill->remotestatus = $vadstatus[$paydata['vads_status']];
                $afullbill->save(true);
                $tracereport = "[$transid] SystemPay IPN failure : {$vadsstatus[$paydata['vads_status']]} ";
                if ($paydata['vads_status'] == SP_REQUEST_ERROR) {
                    $tracereport .= " / Error cause : {$vadsextrastatus[$paydata['vads_extra_status']]} ";
                }
                shop_trace($tracereport);
                die;
        }
    }

    /**
     * Provides global settings to add to courseshop settings when installed.
     */
    public function settings(&$settings) {

        $label = get_string('systempaypaymodeparams', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_heading('local_shop/'.$this->name, $label, ''));

        $key = 'local_shop/systempay_service_url';
        $label = get_string('systempayserviceurl', 'shoppaymodes_systempay');
        $desc = get_string('configsystempayserviceurl', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/systempay_merchant_id';
        $label = get_string('systempaymerchantid', 'shoppaymodes_systempay');
        $desc = get_string('configsystempaymerchantid', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/systempay_test_certificate';
        $label = get_string('systempaytestcertificate', 'shoppaymodes_systempay');
        $desc = get_string('configsystempaytestcertificate', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/systempay_prod_certificate';
        $label = get_string('systempayprodcertificate', 'shoppaymodes_systempay');
        $desc = get_string('configsystempayprodcertificate', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/systempay_use_3dsecure';
        $label = get_string('systempayusesecure', 'shoppaymodes_systempay');
        $desc = get_string('configsystempayusesecure', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1, PARAM_BOOL));

        $bankoptions = array('ce' => 'Caisse d\'Epargne',
                             'bp' => 'Banque Populaire',
                             'sg' => 'Payzen / Société Générale');
        $key = 'local_shop/systempay_bank';
        $label = get_string('systempaybankbrand', 'shoppaymodes_systempay');
        $desc = get_string('configsystempaybankbrand', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configselect($key, $label, $desc, 'ce', $bankoptions));

        // TODO : Generalize.
        $countryoptions['FR'] = get_string('france', 'shoppaymodes_systempay');
        $countryoptions['EN'] = get_string('england', 'shoppaymodes_systempay');
        $countryoptions['DE'] = get_string('germany', 'shoppaymodes_systempay');
        $countryoptions['ES'] = get_string('spain', 'shoppaymodes_systempay');

        $key = 'local_shop/systempay_country';
        $label = get_string('systempaycountry', 'shoppaymodes_systempay');
        $desc = get_string('configsystempaycountry', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configselect($key, $label, $desc, '', $countryoptions));

        $currencycodesoptions = array('978' => get_string('cur978', 'shoppaymodes_systempay'),
                                    '840' => get_string('cur840', 'shoppaymodes_systempay'),
                                    '756' => get_string('cur756', 'shoppaymodes_systempay'),
                                    '826' => get_string('cur826', 'shoppaymodes_systempay'),
                                    '124' => get_string('cur124', 'shoppaymodes_systempay'));

        $key = 'local_shop/systempay_currency_code';
        $label = get_string('systempaycurrencycode', 'shoppaymodes_systempay');
        $desc = get_string('configsystempaycurrencycode', 'shoppaymodes_systempay');
        $settings->add(new admin_setting_configselect($key, $label, $desc, '', $currencycodesoptions));
    }

    /**
     * signs the parameter chain with seller's certificate
     * @param array $parms
     * @param string $certificate
     */
    public function generate_sign($parms, $certificate) {
        ksort($parms); // Parameters need being sorted.
        $signature = '';
        foreach ($parms as $key => $value) {
            if (substr($key, 0, 5) == 'vads_') {
                $signature .= $value.'+';
            }
        }
        $signature .= $certificate; // Customerid is added at the end.
        $encryptedsignature = sha1($signature);

        return($encryptedsignature);
    }

    /**
     * generates a suitable online id for the transaction.
     * real bill online id is : shopid (2d), payment_date (yyyymmdd as 8d), and the onlinetxid (6d) generated here.
     */
    public function generate_online_id() {
        $now = time();
        $midnight = mktime (0, 0, 0, date('n', $now), date('j', $now), date('Y', $now));
        if ($midnight > 0 + @$this->_config->systempay_lastmidnight) {
            set_config('systempay_idseq', 1, 'local_shop');
            set_config('systempay_lastmidnight', $midnight, 'local_shop');
        }

        $onlinetxid = sprintf('%06d', ++$this->_config->systempay_idseq);
        set_config('systempay_idseq', $this->_config->systempay_idseq, 'local_shop');

        return $onlinetxid;
    }

    /**
     * Get the systempay buffer and extract info from cryptic response.
     */
    public function decode_return_data() {
        // Get crypted data DATA.
        $paydata = $_REQUEST;

        // Decode private data as vads_order_info.
        $paydata['return_context'] = base64_decode($paydata['vads_order_info']);

        if (empty($paydata['return_context'])) {
              $systempayreturnerrorstr = get_string('emptymessage', 'shoppaymodes_systempay');
              echo "<br/><center>$systempayreturnerrorstr</center><br/>";
            return false;
        }

        return $paydata;
    }

    /**
     * Get identifying data from the returned information from payment service.
     * guess transid from it
     *
     * @returns an array with (cmd, block instance id, pinned, transid)
     */
    public function identify_transaction() {
        // Decode private data as vads_order_info.
        if (!$identity = base64_decode(@$_REQUEST['vads_order_info'])) {
            return null;
        }
        return explode('-', $identity);
    }
}
