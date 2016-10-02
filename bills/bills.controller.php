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

namespace local_shop\bills;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');

use local_shop\Bill;

class bills_controller {

    public function process($cmd) {
        global $DB;

        $null = null;

        // Delete a full bill ****************************** **.
        if ($cmd == 'deletebill') {

            $billids = required_param_array('billid[]', PARAM_INT);
            foreach ($billids as $billid) {
                try {
                    $bill = new Bill($billid);
                    $bill->delete();
                } catch (\Exception $e) {
                    print_error('objecterror', 'local_shop', $e->message);
                }
            }

            // Todo : delete filearea for the bill.
        }

        // Delete a set of billitems inside a bill ******************** **.
        if ($cmd == 'deletebillitems') {

            $items = required_param_array('billitem[]', PARAM_INT);
            if (!empty($items)) {
                foreach ($items as $bid) {
                    $billitem = new BillItem($bid);
                    $billitem->delete();
                }
            }
        }

        if ($cmd == 'changestate') {

            $billid = required_param('billid', PARAM_INT);
            $bill = new Bill($billid, $null, $null, $null, true); // Get a lightweight version.
            $bill->status = required_param('status', PARAM_TEXT);
            $bill->save();
        }

        if ($cmd == 'ignoretax') {

            $billid = required_param('billid', PARAM_INT);
            $bill = new Bill($billid); // Get a lightweight version.
            $bill->ignoretax = 1;
            $bill->save();
        }

        if ($cmd == 'restoretax') {
            $billid = required_param('billid', PARAM_INT);
            $bill = new Bill($billid, $null, $null, $null, true); // Get a lightweight version.
            $bill->ignoretax = 0;
            $bill->save();
        }

        if ($cmd == 'recalculate') {
            $billid = required_param('billid', PARAM_INT);
            $bill = new Bill($billid, $null, $null, $null, true); // Get a lightweight version.
            $bill->recalculate();
        }

        // Generate bill code ************************* **.
        if ($cmd == 'generatecode') {
            $billid = required_param('billid', PARAM_INT);
            $bill = new Bill($billid, $null, $null, $null, true); // Get a lightweight version.
            $bill->transactionid = md5(session_id() . time());
            $bill->save(true);
        }

        // Delete Single ************************* **.
        if ($cmd == 'deleteItem') {
            $billitemid = required_param('billitemid', PARAM_INT);
            $z = required_param('z', PARAM_INT); // Ordering.

            $billitem = new BillItem($billitemid);
            $billitem->delete();

            $select = " billid = '$billid' GROUP BY billid ";
            if (!$maxorder = $DB->get_field_select('local_shop_billitem', 'MAX(ordering)', $select)) {
                $maxorder = 1;
            }

            // Reorder end of list.
            $i = $z;
            $select = "
                id = $billid AND
                ordering > ?
            ";
            if ($upperrecs = $DB->get_records_select('local_shop_billitem', $select, array($z), 'ordering', 'id, ordering')) {
                foreach ($upperrecs as $upperrec) {
                    $DB->update_record('local_shop_billitem', $upperrec);
                }
            }
        }

        // Delete Items ************************* **.
        if ($cmd == 'deleteItems') {
            $items = required_param('items', PARAM_INT);
            $itemlist = str_replace(',', "','", $items);

            // Fetches item to reorder (above the smaller deleted ordering).
            $sql = "
                SELECT
                    id, ordering as ordering
                FROM
                    {local_shop_billitem}
                WHERE
                    billid = '$billid' AND
                    ordering >= MIN($items) AND
                    ordering NOT IN ($items)
                ORDER BY
                    ordering ASC
            ";
            if ($moveditems = $DB->get_records_sql($sql)) {
                $minordering = $moveditems[0]->ordering; // Catch the min.
            }

            // Delete other records.
            $DB->delete_records_select('local_shop_billitem', " id IN ($itemlist) ");

            // Reorder records.
            $i = $minordering;
            foreach ($moveditems as $moveditem) {
                $moveditem->ordering = $i;
                $DB->update_record('local_shop_billitem', $moveditem);
                $i++;
            }
            recalculate($bill->id);
        }

        // Relocates.
        if ($cmd == 'relocate') {
            /*
             * Unlocks constraint
             * not safe,find better algorithm
             */
            $sql = "
                ALTER TABLE
                    {shop_}billitem}
                DROP INDEX
                    unique_ordering
            ";
            $DB->execute($sql);

            // Relocates.
            $relocated = required_param('relocated', PARAM_INT);
            $z = required_param('z', PARAM_INT);
            $where = required_param('at', PARAM_INT);
            if ($z > $where) {
                $gap = $z - $where;
                for ($i = $z - 1; $i >= $where; $i--) {
                    moveRecord(1, $i, $billid);
                }
                $sql = "
                    UPDATE
                        {shop_}billitem}
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

        // Unattach attachement.
        if ($cmd == 'unattach') {
            $fields = " id, DATE_FORMAT(emissiondate, '%Y%m%d') as date, userid ";
            $bill = $DB->get_record('local_shop_bill', array('id' => $billid), $fields);
            $itemdatapath = "/bills/" . md5($bill->userid) . "/B-" . $bill->date . "-" . $billid . "/";
            fs_deleteFile($itemdatapath . required_param('file', PARAM_TEXT));
        }

        // Unattach attachement.
        if ($cmd == 'reclettering') {
            $lettering = required_param('idnumber', PARAM_TEXT);
            if ($checkbill = $DB->get_record('local_shop_bill', array('idnumber' => $lettering))) {
                if ($checkbill->id != $billid) {
                    $params = array('id' => $this->theshop->id, 'view' => 'viewBill', 'billid' => $checkbill->id);
                    $badbillurl = new \moodle_url('/local/shop/bills/view.php', $params);
                    $errorline = get_string('uniqueletteringfailure', 'local_shop', $badbillurl);
                    $letteringfeedback = '<div class="bill_error">'.$errorline.'</div>';
                }
            } else {
                $DB->set_field('local_shop_bill', 'idnumber', $lettering, array('id' => $billid));
                $letteringfeedback = '<div class="bill_good">'.get_string('letteringupdated', 'local_shop').'</div>';
            }
        }

        // Work flow.
        if ($cmd == 'flowchange') {
            /*
             * this implements a statefull automaton on bills
             * trigers a state change handler if needed.
             * Typical resolution is a manual SOLDOUT order
             * for realizing production action when payed out.
             */
            $status = required_param('status', PARAM_TEXT);
            $priorstatus = $DB->get_field('local_shop_bill', 'status', array('id' => $billid));

            // Call a transition handler.
            $result = 1;
            if (file_exists($CFG->dirroot.'/local/shop/transitions.php')) {
                include_once($CFG->dirroot.'/local/shop/transitions.php');
                // Lower case because Moodle validation forces all functions to be lowercase.
                $transitionhandler = core_text::strtolower("bill_transition_{$priorstatus}_{$status}");
                if (function_exists($transitionhandler)) {
                    $result = $transitionhandler($billid);
                }
            }
        }
    }
}