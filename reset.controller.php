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
 * An action controller to reset shop parts.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use coding_exception;
use local_shop\Shop;

/**
 * An MVC controller for reseting shop or parts of shops.
 */
class reset_controller {

    /** @var object Action data context */
    protected $data;

    /** @var bool Marks data has been loaded for action. */
    protected $received;

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function should get them from request
     */
    public function receive($cmd, $data = []) {

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'reset': {
                $this->data->shopid = required_param('shopid', PARAM_INT);
                $this->data->bills = required_param('bills', PARAM_BOOL);
                $this->data->customers = required_param('customers', PARAM_BOOL);
                $this->data->catalogs = required_param('catalogs', PARAM_BOOL);
                break;
            }
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $OUTPUT, $DB, $CFG;

        if (!$this->received) {
            throw new coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $out = '<code>';

        if (!empty($this->data->bills) || !empty($this->data->customers) || !empty($this->data->catalogs)) {
            $out .= "Deleting bill records...\n";

            $params = [];
            if (!empty($this->data->shopid)) {
                $params = ['shopid' => $this->data->shopid];

                $deletedbills = $DB->get_records('local_shop_bill', $params, 'id', 'id,id');
                $out .= "Deleting bill records...\n";
                $DB->delete_records('local_shop_bill', $params);

                $deletedbillitems = $DB->get_records_list('local_shop_billitem', 'billid', array_keys($deletedbills));

                // We have billitems, delete them.
                if ($deletedbills) {
                    $out .= "Deleting bill item records...\n";
                    foreach (array_keys($deletedbills) as $bid) {
                        $DB->delete_records('local_shop_billitem', ['billid' => $bid]);
                    }
                }

                // Delete products.
                if ($deletedbillitems) {
                    $out = "Deleting product records...\n";
                    foreach (array_keys($deletedbillitems) as $biid) {
                        $select = 'currentbillitemid = ? OR initialbillitemid = ?';
                        $deletedproducts = $DB->get_records_select('local_shop_product', $select, [$biid, $biid], 'id', 'id,id');
                        $DB->delete_records_select('local_shop_product', $select, [$biid, $biid]);

                        if ($deletedproducts) {
                            $out = "Deleting product event records...\n";
                            foreach (array_keys($deletedproducts) as $pid) {
                                $DB->delete_records('local_shop_productevent', ['productid' => $pid]);
                            }
                        }
                    }
                }
            } else {
                // Delete all data.
                $DB->delete_records('local_shop_bill', null);
                $DB->delete_records('local_shop_billitem', null);
                $DB->delete_records('local_shop_product', null);
                $DB->delete_records('local_shop_productevent', null);

                // Empties the merchant trace.
                $cmd = "echo '' > {$CFG->dataroot}/merchant_trace.log";
                exec($cmd);
            }
            $out .= $OUTPUT->notification(get_string('billsdeleted', 'local_shop'), 'success');
        }

        $out .= "</code>";

        if (!empty($this->data->customers)) {
            $DB->delete_records('local_shop_customer', null);
            $out .= $OUTPUT->notification(get_string('customersdeleted', 'local_shop'), 'success');
        }

        if (!empty($this->data->catalogs)) {
            if (!empty($this->data->shopid)) {
                $theshop = new Shop($this->data->shopid);
                $DB->delete_records('local_shop_catalogitem', ['catalogid' => $theshop->config->catalogid]);
                $DB->delete_records('local_shop_catalog', ['id' => $this->theshop->config->catalogid]);
            } else {
                $DB->delete_records('local_shop_catalogitem', []);
                $DB->delete_records('local_shop_catalog', []);
            }
            $out .= $OUTPUT->notification(get_string('catalogsdeleted', 'local_shop'), 'success');
        }

        return $out;
    }
}
