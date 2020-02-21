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
 * @package    shoppaymodes_monetico
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

class shop_paymode_monetico extends shop_paymode {

    public function __construct(&$shopblockinstance) {
        parent::__construct('monetico', $shopblockinstance, true, true);
    }

    public function is_instant_payment() {
        return true;
    }

    // Prints a payment porlet in an order form.
    function print_payment_portlet(&$shoppingcart) {
        global $CFG;

        echo "To implement";
    }

    // Prints a payment porlet in an order form.
    function print_invoice_info(&$billdata = null) {
        echo get_string($this->name.'paymodeinvoiceinfo', 'shoppaymodes_monetico', $this->name);
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

        $afullbill->onlinetransactionid = 'Compute onlineTS';
        $afullbill->paymode = 'monetico';
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
            echo "<H3>R&eacute;ponse manuelle du serveur Monetico</H3>\n";
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
        if ('TODO : Implement acceptance TEST HERE') {
            // Processing bill changes.
            if ($afullbill->status == SHOP_BILL_PLACED || $afullbill->status == SHOP_BILL_PENDING) {
                $tid = 'TODO : Compute Transaction ID';
                $afullbill->onlinetransactionid = $tid;
                $afullbill->status = SHOP_BILL_SOLDOUT;
                $afullbill->paiedamount = 'TODO : Get ammount in data';
                $afullbill->save(true);

                // Redirect to success for actually produce  with significant data.
                shop_trace("[$transid] Monetico : Transation Complete, transferring to success end point");
                $params = array('view' => 'produce', 'shopid' => $this->theshop->id, 'what' => 'produce', 'transid' => $transid);
                $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
                redirect($redirecturl);
            }

            if (($afullbill->status == SHOP_BILL_SOLDOUT) || ($afullbill->status == SHOP_BILL_COMPLETE)) {
                shop_trace("[$transid] monetico : Transation Already Complete, transferring to success end point");
                $params = array('view' => 'produce', 'shopid' => $this->theshop->id, 'what' => 'produce', 'transid' => $transid);
                $redirecturl = new moodle_url('/local/shop/front/view.php', $params);
                redirect($redirecturl);
            }

            /*
             * Other situations should be weird cases...
             * Silent redirect but shop_trace something
             * and confirm the order to process it in backoffice.
             */
            shop_trace("[$transid] Monetico : Weird state sequence Trans accept in status ".$afullbill->status);
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
        shop_trace("[$transid] Monetico IPN processing");

        $afullbill = Bill::get_by_transaction($transid);
        $laststatus = (strrchr($afullbill->remotestatus, ',')) ? 0 : substr(strrchr($afullbill->remotestatus, ','), 1);

        if ('TODO : Check abandonned') {
            $afullbill->status = SHOP_BILL_CANCELLED;
            $afullbill->paiedamount = 0;
            $afullbill->remotestatus = 'TODO : Get remote status';
            $afullbill->save(true);
            shop_trace("[$transid]  Monetico IPN : transaction abandonned before payment");
            die;
        }

        // Initiate monetico processing.
        switch ('TOTO Get transaction result status') {
            case MONETICO_PAYMENT_ACCEPTED:

                // Processing bill changes.
                if ($afullbill->status != SHOP_BILL_PLACED && $afullbill->status != SHOP_BILL_PENDING) {
                    break;
                }
                $tid = 'TODO : Calculate online transaction id';
                $afullbill->onlinetransactionid = $tid;
                $afullbill->status = SHOP_BILL_SOLDOUT;
                $afullbill->paiedamount = 'TODO : Get amount' / 100;
                $afullbill->remotestatus = 'TODO : Get status';
                $afullbill->save(true);
                shop_trace("[$transid]  Monetico IPN : success, transferring to success controller");

                /*
                 * now we need to execute non interactive production code
                 * this SHOULD NOT be done by redirection as monetico server might not
                 * handle this. Thus only use the controller and die afterwoods.
                 */

                include_once($CFG->dirroot.'/local/shop/front/produce.controller.php');
                $nullblock = null;
                $controller = new \local_shop\front\production_controller($afullbill->theshop, $afullbill->thecatalogue, $nullblock, $afullbill, true, false);
                $result = $controller->process('produce');
                die;

            default: {
                if (($afullbill->status == SHOP_BILL_PLACED) || ($afullbill->status == SHOP_BILL_PENDING)) {
                    $afullbill->status = SHOP_BILL_FAILED;
                    $afullbill->remotestatus = 'TODO : Get transaction status';
                    $afullbill->save(true);
                    $tracereport = "[$transid] Monetico IPN failure : {$paydata['vads_trans_status']} ";
                    if ('TODO : Test extra error information') {
                        $tracereport .= " / Error cause : {$vadsextraresult[$paydata['vads_extra_result']]} ";
                    }
                    shop_trace($tracereport);
                } else {
                    $tracereport = "[$transid] Monetico IPN ignored failure : {GET remote stzte info}} ";
                    if ('TODO : Test extra error information') {
                        $tracereport .= " / Error cause : {GET remote stzte info} ";
                    }
                    shop_trace($tracereport);
                }
                die;
            }
        }
    }

    /**
     * Provides global settings to add to courseshop settings when installed.
     */
    public function settings(&$settings) {

        $label = get_string('moneticopaymodeparams', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_heading('local_shop/'.$this->name, $label, ''));

        $key = 'local_shop/monetico_service_url';
        $label = get_string('moneticoserviceurl', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticoserviceurl', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/monetico_merchant_id';
        $label = get_string('moneticomerchantid', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticomerchantid', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/monetico_test_certificate';
        $label = get_string('moneticotestcertificate', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticotestcertificate', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/monetico_prod_certificate';
        $label = get_string('moneticoprodcertificate', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticoprodcertificate', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configtext($key, $label, $desc, '', PARAM_TEXT));

        $key = 'local_shop/monetico_use_3dsecure';
        $label = get_string('moneticousesecure', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticousesecure', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1, PARAM_BOOL));

        $bankoptions = array('sg' => 'Société Générale');
        $key = 'local_shop/monetico_bank';
        $label = get_string('moneticobankbrand', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticobankbrand', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configselect($key, $label, $desc, 'ce', $bankoptions));

        // TODO : Generalize.
        $countryoptions['FR'] = get_string('france', 'shoppaymodes_monetico');
        $countryoptions['EN'] = get_string('england', 'shoppaymodes_monetico');
        $countryoptions['DE'] = get_string('germany', 'shoppaymodes_monetico');
        $countryoptions['ES'] = get_string('spain', 'shoppaymodes_monetico');

        $key = 'local_shop/monetico_country';
        $label = get_string('moneticocountry', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticocountry', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configselect($key, $label, $desc, '', $countryoptions));

        $currencycodesoptions = array('978' => get_string('cur978', 'shoppaymodes_monetico'),
                                    '840' => get_string('cur840', 'shoppaymodes_monetico'),
                                    '756' => get_string('cur756', 'shoppaymodes_monetico'),
                                    '826' => get_string('cur826', 'shoppaymodes_monetico'),
                                    '124' => get_string('cur124', 'shoppaymodes_monetico'));

        $key = 'local_shop/monetico_currency_code';
        $label = get_string('moneticocurrencycode', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticocurrencycode', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configselect($key, $label, $desc, '', $currencycodesoptions));

        $key = 'local_shop/monetico_use_localtime';
        $label = get_string('moneticouselocaltime', 'shoppaymodes_monetico');
        $desc = get_string('configmoneticouselocaltime', 'shoppaymodes_monetico');
        $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 1));
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
        if ($midnight > 0 + @$this->_config->monetico_lastmidnight) {
            set_config('monetico_idseq', 1, 'local_shop');
            set_config('monetico_lastmidnight', $midnight, 'local_shop');
        }

        $onlinetxid = sprintf('%06d', ++$this->_config->monetico_idseq);
        set_config('monetico_idseq', $this->_config->monetico_idseq, 'local_shop');

        return $onlinetxid;
    }

    /**
     * Get the monetico buffer and extract info from cryptic response.
     */
    public function decode_return_data() {
        // Get crypted data DATA.
        $paydata = $_REQUEST;

        // Decode private data as vads_order_info.
        $paydata['return_context'] = base64_decode($paydata['vads_order_info']);

        if (empty($paydata['return_context'])) {
              $moneticoreturnerrorstr = get_string('emptymessage', 'shoppaymodes_monetico');
              echo "<br/><center>$moneticoreturnerrorstr</center><br/>";
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
