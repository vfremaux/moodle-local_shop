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
 * Form for editing HTML block instances.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/BillItem.class.php');

use Exception;
use local_shop\Bill;
use local_shop\BillItem;

class bill_controller {

    protected $data;

    protected $received;

    protected $theshop;

    protected $thecatalog;

    protected $theblock;

    protected $mform;

    public function __construct(&$theshop, &$thecatalog, $theblock) {
        $this->theshop = $theshop;
        $this->thecatalog = $thecatalog;
        $this->theblock = $theblock;
    }

    public function receive($cmd, $data = array()) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'assignpartner':
                $this->data->billid = required_param('billid', PARAM_INT);
                $this->data->partnerid = required_param('partnerid', PARAM_INT);
                break;

            case 'unassignpartner':
                $this->data->billid = required_param('billid', PARAM_INT);
                break;

            case 'deletebill':
                $this->data->billids = required_param_array('billid[]', PARAM_INT);
                break;

            case 'deletemillitems':
                $this->data->items = required_param_array('billitem[]', PARAM_INT);
                break;

            case 'changestate':
                $this->data->billid = required_param('billid', PARAM_INT);
                $this->data->status = required_param('status', PARAM_TEXT);
                break;

            case 'ignoretax':
            case 'restoretax':
            case 'recalculate':
            case 'generatecode':
                $this->data->billid = required_param('billid', PARAM_INT);
                break;

            case 'relocate':
                $this->data->relocated = required_param('relocated', PARAM_INT);
                $this->data->z = required_param('z', PARAM_INT);
                $this->data->where = required_param('at', PARAM_INT);
                break;

            case 'flowchange':
                $this->data->billid = required_param('billid', PARAM_INT);
                $this->data->status = required_param('status', PARAM_TEXT);
                break;

            case 'unattachall':
                $this->data->billid = required_param('billid', PARAM_INT);
                break;

            case 'unattach':
                $this->data->billid = required_param('billid', PARAM_INT);
                $this->data->filepath = required_param('filepath', PARAM_INT);
                $this->data->filename = required_param('filename', PARAM_INT);
                break;

            case 'reclettering':
                $this->data->billid = required_param('billid', PARAM_INT);
                $this->data->lettering = required_param('idnumber', PARAM_TEXT);
                break;

            case 'edit':
                // Let data come from $data attributes.
                break;

            case 'switchfullon':
                break;

            case 'switchfulloff':
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new Exception('Bill Controller triggered without data');
        }

        $null = null;

        if ($cmd == 'assignpartner') {
            if (local_shop_supports_feature('shop/partners')) {
                $bill = new Bill($this->data->billid);
                $bill->partnerid = $this->data->partnerid;
                $bill->save();
            }
        }

        if ($cmd == 'unassignpartner') {
            if (local_shop_supports_feature('shop/partners')) {
                $bill = new Bill($this->data->billid);
                $bill->partnerid = 0;
                $bill->save();
            }
        }

        // Delete a full bill ****************************** **.
        if ($cmd == 'deletebill') {

            if (!empty($this->data->billids)) {
                foreach ($this->data->billids as $billid) {
                    try {
                        $bill = new Bill($billid);
                        $bill->delete();
                    } catch (\Exception $e) {
                        print_error('objecterror', 'local_shop', $e->message);
                    }
                }
            }

            // Todo : delete filearea for the bill.
        }

        // Delete a set of billitems inside a bill ******************** **.
        if ($cmd == 'deletebillitems') {

            if (!empty($this->data->items)) {
                foreach ($this->data->items as $bid) {
                    $billitem = new BillItem($bid);
                    $billitem->delete();
                }
            }
        }

        // Change bill state without triggering transition ******************** **.

        if ($cmd == 'changestate') {

            $bill = new Bill($this->data->billid, true, $null, $null, $null); // Get a lightweight version.
            $bill->status = $this->data->status;
            $bill->save();
        }

        if ($cmd == 'ignoretax') {

            $bill = new Bill($this->data->billid); // Get a lightweight version.
            $bill->ignoretax = 1;
            $bill->save();
        }

        if ($cmd == 'restoretax') {
            $bill = new Bill($this->data->billid, true, $null, $null, $null); // Get a lightweight version.
            $bill->ignoretax = 0;
            $bill->save();
        }

        if ($cmd == 'recalculate') {
<<<<<<< HEAD
            $bill = new Bill($this->data->billid, false, $null, $null, $null); // Get a lightweight version.
            $bill->recalculate();
=======
            // Bill is recalculated in constructor. 
            $bill = new Bill($this->data->billid, false, $null, $null, $null); // Get a lightweight version.
            $bill->save();
>>>>>>> MOODLE_40_STABLE
        }

        // Generate bill code ************************* **.
        if ($cmd == 'generatecode') {
            $bill = new Bill($this->data->billid, true, $null, $null, $null); // Get a lightweight version.
            $bill->transactionid = md5(session_id() . time());
            $bill->save(true);
        }

        // Delete Items ************************* **.
        /* Probably obsolete, calls undefined functions */
        if ($cmd == 'deleteitems') {
            $items = required_param('items', PARAM_INT);
            $itemarr = explode(',', $items);
            list($insql, $inparams) = $DB->get_in_or_equal($itemarr, SQL_PARAMS_QM, 'param', false);

            // Fetches items to reorder (above the smaller deleted ordering).
            $select = "
                    billid = ? AND
                    ordering >= MIN($items) AND
                    ordering $insql
            ";
            if ($moveditems = $DB->get_records_select('local_shop_billitem', $select, array_merge(array($billid), $inparams), 'ordering', 'id,ordering')) {
                $minordering = $moveditems[0]->ordering; // Catch the min.
            }

            // Delete other records.
            $DB->delete_records_select('local_shop_billitem', " id $insql ", $inparams);

            // Reorder records.
            $i = $minordering;
            foreach ($moveditems as $moveditem) {
                $moveditem->ordering = $i;
                $DB->update_record('local_shop_billitem', $moveditem);
                $i++;
            }
            $bill->recalculate();
        }

        // Relocates.
        if ($cmd == 'relocate') {
            /*
             * Unlocks constraint
             * not safe,find better algorithm
             */
            $sql = "
                ALTER TABLE
                    {local_shop_billitem}
                DROP INDEX
                    unique_ordering
            ";
            $DB->execute($sql);

            // Relocates.
            $relocated = $this->data->relocated;
            $z = $thie->data->z;
            $where = $this->data->where;
            if ($z > $where) {
                $gap = $z - $where;
                for ($i = $z - 1; $i >= $where; $i--) {
                    moveRecord(1, $i, $this->data->billid);
                }
                $sql = "
                    UPDATE
                        {local_shop_billitem}
                    SET
                        ordering = ordering - $gap
                    WHERE
                        id = $relocated
                ";
                $DB->execute($sql);
            } else if ($z < $where) {
                for ($i = $z + 1; $i <= $where; $i++) {
                    moveRecord(-1, $i, $billid);
                }
                $gap = $where - $z;
                $sql = "
                    UPDATE
                        {local_shop_billitem}
                    SET
                        ordering = ordering + $gap
                    WHERE
                        id = '$relocated'
                ";
                $DB->execute($sql);
            }
            /*
             * Locks constraints back
             * remove this : cannot support concurrent operations
             */
            $sql = "
                ALTER TABLE
                    {local_shop_billitem}
                ADD INDEX
                    unique_ordering (billid, ordering)
            ";
            $DB->execute($sql);
        }

        // Unattach attachements.
        if ($cmd == 'unattachall') {
            $fs = get_file_storage();
            $context = context_system::instance();
            $fs->delete_area_files($context->id, 'local_shop', 'billattachments', $this->data->billid);
        }

        // Unattach attachements.
        if ($cmd == 'unattach') {
            $fs = get_file_storage();
            $context = context_system::instance();
            $fs->delete_area_files($context->id, 'local_shop', 'billattachments', $this->data->billid,
                                   $this->data->filepath, $this->data->filename);
        }

        // Registers accountance lettering **************************************.
        if ($cmd == 'reclettering') {
            if ($billrec = $DB->get_record('local_shop_bill', array('idnumber' => $this->data->lettering))) {
                if ($billrec->id != $this->data->billid) {
                    $params = array('view' => 'viewBill', 'billid' => $billrec->id);
                    $badbillurl = new \moodle_url('/local/shop/bills/view.php', $params);
                    $errorline = get_string('uniqueletteringfailure', 'local_shop', $badbillurl);
                    return '<div class="bill_error">'.$errorline.'</div>';
                }
                $bill = new Bill($billrec);
            } else {
                $bill = new Bill($this->data->billid);
            }

            $bill->idnumber = $this->data->lettering;
            $bill->save(true); // Light save.
        }

        // Change bill state and triggers transition ******************** **.

        if ($cmd == 'flowchange') {
            /*
             * this implements a statefull automaton on bills
             * trigers a state change handler if needed.
             * Typical resolution is a manual SOLDOUT order
             * for realizing production action when payed out.
             */
            $bill = new Bill($this->data->billid);
            $bill->work($this->data->status);
        }

        if ($cmd == 'edit') {

            $billrec = $this->data;

            if (!empty($billrec->billid)) {
                $bill = new Bill($billrec->billid);
                $bill->lastactiondate = $now;
            } else {
                $bill = new Bill(null, false, $this->theshop, $this->thecatalog, $this->theblock);
            }

            if (empty($billrec->currency)) {
                $billrec->currency = $theshop->defaultcurrency;
            }

            $shipping = new \StdClass;
            if (!empty($config->useshipping)) {
                $shipping->value = 0;
                // TODO : Call shipping calculation.
            } else {
                $shipping->value = 0;
            }

            // Creating a customer account for a user if missing.
            if ($billrec->useraccountid != 0) {
                $user = $DB->get_record('user', array('id' => $billrec->useraccountid));
                if (!$potcustomers = $DB->get_records('local_shop_customer', array('hasaccount' => $user->id))) {
                    $customer = new Customer(null);
                    $customer->firstname = $user->firstname;
                    $customer->lastname = $user->lastname;
                    $customer->email = $user->email;
                    $customer->address = $user->address;
                    $customer->city = $user->city;
                    $customer->zip = '';
                    $customer->country = $user->country;
                    $customer->hasaccount = $user->id;
                    $customer->save();
                    $billrec->customerid = $customer->id; // Will be transfered to bill a bit later.
                }
            } else {
                $bill->customerid = $billrec->userid;
            }
            unset($bill->userid);
            unset($bill->useraccountid);

            // Transfer all billrec attributes to the bill object.
            foreach ($billrec as $key => $value) {
                $bill->$key = $value;
            }

            $lastordering = Bill::last_ordering($this->theshop->id);
            $bill->lastordering = $lastordering + 1;

            $bill->save();

            return $bill;
        }

        if ($cmd == 'edititem') {

            /*
             * TODO : change form and add a way to select a product in catalog,
             * in which case the billitem type is BILLING.
             */

            $billitemrec = $this->data;
            $billitemrec->type = 'MANUAL';

            if (empty($billitemrec->billitemid)) {
                $bill = new Bill($billitemrec->billid, false, $this->theshop);
                $bill->add_item_data($billitemrec, -1);
            } else {
                $billitem = new BillItem($billitemrec->billitemid);
                unset($billitemrec->id);
                unset($billitemrec->billitemid);
                foreach ($billitemrec as $k => $v) {
                    $billitem->$k = $v;
                }
                $billitem->save();
            }
        }

        if ($cmd == 'switchfullon') {
            set_user_preference('local_shop_bills_fullview', 1);
        }

        if ($cmd == 'switchfulloff') {
            set_user_preference('local_shop_bills_fullview', null);
        }
    }
}