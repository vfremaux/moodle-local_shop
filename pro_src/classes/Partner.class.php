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
 * A tax instance applies for a country.
 *
 * @package     local_shop
 * @category    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>, Florence Labord <info@expertweb.fr>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (ActiveProLearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop;

use StdClass;
use local_shop\Customer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/ShopObject.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Customer.class.php');

class Partner extends ShopObject {

    protected static $table = 'local_shop_partner';

    public function __construct($idorrecord, $lightweight = false) {
        global $DB;

        parent::__construct($idorrecord, self::$table);

        if ($idorrecord) {
            if ($lightweight) {
                // This builds a lightweight proxy of the Bill, without items.
                return;
            }

            if (!empty($this->record->customerid)) {
                $this->customer = new Customer($this->record->customerid);
            }

            if (!empty($this->record->moodleuser)) {
                $this->user = $DB->get_record('user', ['id' => $this->record->moodleuser]);
            }
        } else {
            // Initiate empty fields.
            $this->record->id = 0;
            $this->record->shopid = 0;
            $this->record->name = '';
            $this->record->partnerkey = '';
            $this->record->referer = '';
            $this->record->partnersecretkey = '';
            $this->record->customerid = '';
            $this->record->enabled = 0;
        }
    }

    public function disable() {
        $this->record->enabled = 0;
    }

    public function enable() {
        $this->record->enabled = 1;
    }

    /**
     * Partner validates is it is known as a registered partner.
     * A referrer check is performed to verify the import request origin.
     * This will allow SSO binding based on email recognition.
     */
    public static function validate(string $partnerkey) {

        $partner = self::get_by_key($partnerkey);

        if (is_null($partner)) {
            // Not registered partners cannot be validated.
            return false;
        }

        if (empty($_SERVER['HTTP_REFERER'])) {
            // Not refering clients cannot be validated.
            return false;
        }

        if (!preg_match('#'.preg_quote($partner->referer).'#', $_SERVER['HTTP_REFERER'])) {
            return false;
        }

        return true;
    }

    /**
     * Get a partner object by key.
     * @param string $partnerkey
     * @return a Partner object or null.
     */
    public static function get_by_key(string $partnerkey) {
        global $DB;

        if ($partnerid = $DB->get_field('local_shop_partner', 'id', array('partnerkey' => $partnerkey))) {
            return new Partner($partnerid);
        }

        return null;
    }

    /**
     * Get a partner object by key.
     * @param string $partnerkey
     * @return a Partner object or null.
     */
    public static function get_by_secretkey(string $partnersecretkey) {
        global $DB;

        if ($partnerid = $DB->get_field('local_shop_partner', 'id', array('partnersecretkey' => $partnersecretkey))) {
            return new Partner($partnerid);
        }

        return null;
    }

    /**
     * Partner validates is it is known as a registered partner. This will allow
     * SSO binding based on email recognition.
     */
    public static function checkauth(StdClass $partner) {
        global $DB, $SESSION;

        if (!$partner->validated) {
            // Non validated partner inputs cannot preauth.
            return false;
        }

        if (isloggedin() && !isguestuser()) {
            // Already authenticated. No need preauth or preauth already passed.
            // Do not check to let resolution pass action through.
            return false;
        }

        if (!empty($partner->customeremail)) {
            $params = array('email' => $partner->customeremail, 'deleted' => 0, 'suspended' => 0);
            $user = $DB->get_record('user', $params);
            if ($user) {
                complete_user_login($user);
                shop_load_customerinfo($user);

                // At this moment import process cannot use distinct invoice info.
                /* TODO : check if invoiceinfo is registered in a customer record, in which case we need 
                 * stop the autodrive and let the use choose.
                 */
                $SESSION->shoppingcart->usedistinctinvoiceinfo = false;
                shop_trace($SESSION->shoppingcart->transid." - Partner delegated auth for {$partner->customeremail} from {$partner->partnerkey}");
                return true;
            }
        }

        return false;
    }

    /**
     * Resolves the customer action : choose appropriate action depending on drive mode
     */
    public static function resolve_customer_action($checked, $action) {
        global $SESSION;

        if ($checked) {
            if (!empty($SESSION->shoppingcart->autodrive)) {
                $action = 'navigate';
            } else {
                $action = 'revalidate';
                unset($SESSION->shoppingcart->autodrive);
            }
        } else {
            if (!empty($SESSION->shoppingcart->autodrive)) {
                unset($SESSION->shoppingcart->autodrive);
            }
        }

        return array($action, $SESSION->shoppingcart);
    }

    /**
     * Tags bill with partner info. If the partner is NOT VALIDATED, this may
     * only mean the import could NOT be referer checked, but the partner yet
     * exists. In this case, the purchase will have to be explictely authentified
     * locally on the shop.
     * @param objectref &$bill the bill
     * @param object $data If provided, take partenr infor from data, elsewhere from session.
     */
    public static function register_in_bill(&$bill, $data = null) {
        global $SESSION;

        if (empty($data)) {
            if (!empty($SESSION->shoppingcart->partner)) {
                $partner = Partner::get_by_key($SESSION->shoppingcart->partner->partnerkey);
                if ($partner) {
                    $bill->partnerid = $partner->id;
                    $bill->partnertag = $SESSION->shoppingcart->partner->partnertag;
                } else {
                    shop_debug_trace("Partner::register_in_bill Error from shoppingcart : {$SESSION->shoppingcart->partner->partnerkey} not found", SHOP_TRACE_ERRORS);
                }
            }
        } else {
            $partner = Partner::get_by_key($data->partnerkey);
            if ($partner) {
                if (!empty($bill)) {
                    $bill->partnerid = $partner->id;
                    $bill->partnertag = @$data->partnertag;
                }
            } else {
                shop_debug_trace("Partner::register_in_bill Error : {$data->partnerkey} not found", SHOP_TRACE_ERRORS);
            }
        }
    }

    public static function get_instances($filter = array(), $order = '', $fields = '*', $limitfrom = 0, $limitnum = '') {
        return parent::_get_instances(self::$table, $filter, $order, $fields, $limitfrom, $limitnum);
    }

    public static function get_instances_menu($filter = array(), $order = '', $chooseopt = 'choosedots') {
        return parent::_get_instances_menu(self::$table, $filter, $order, 'title', $chooseopt);
    }
}