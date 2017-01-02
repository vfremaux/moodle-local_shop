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
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');

use local_shop\Shop;

class reset_controller {

    protected $data;

    protected $received;

    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function shoudl get them from request
     */
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
            case 'reset':
                $this->data->shopid = required_param('shopid', PARAM_INT);
                $this->data->bills = required_param('bills', PARAM_BOOL);
                $this->data->customers = required_param('customers', PARAM_BOOL);
                $this->data->catalogs = required_param('catalogs', PARAM_BOOL);
                break;
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $OUTPUT, $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $out = '';

        if (!empty($this->data->bills) || !empty($this->data->customers) || !empty($this->data->catalogs)) {
            $params = array();
            if (!empty($this->data->shopid)) {
                $params = array('shopid' => $this->data->shopid);

                $deletedbills = $DB->get_records('local_shop_bill', $params, 'id', 'id,id');
                $DB->delete_records('local_shop_bill', $params);

                $deletedbillitems = $DB->get_records_list('local_shop_billitem', 'billid', array_keys($deletedbills));

                if ($deletedbills) {
                    foreach ($deletedbills as $bid) {
                        $DB->delete_records_select('local_shop_billitem', array('billid' => $bid));
                    }
                }

                // Delete products.
                if ($deletebillitems) {
                    foreach (array_keys($deletebillitems) as $biid) {
                        $deletedproducts = $DB->get_records_select('local_shop_product', 'currentbillitemid = ? or initialbillid = ?', array($biid, $biid), 'id', 'id,id');
                        $DB->delete_records_select('local_shop_product', 'currentbillitemid = ? or initialbillid = ?', array($biid, $biid));

                        if ($deletedproducts) {
                            foreach ($deletedproducts as $pid) {
                                $DB->delete_records('local_shop_productevent', array('productid' => $pid));
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
            }
            $out .= $OUTPUT->notification(get_string('billsdeleted', 'local_shop'));
        }
        if (!empty($this->data->customers)) {
            $DB->delete_records('local_shop_customer', null);
            $out .= $OUTPUT->notification(get_string('customersdeleted', 'local_shop'));
        }
        if (!empty($this->data->catalogs)) {
            if (!empty($this->data->shopid)) {
                $theshop = new Shop($this->data->shopid);
                $DB->delete_records('local_shop_catalogitem', array('catalogid' => $theshop->config->catalogid));
                $DB->delete_records('local_shop_catalog', array('id' => $this->theshop->config->catalogid));
            } else {
                $DB->delete_records('local_shop_catalogitem', array());
                $DB->delete_records('local_shop_catalog', array());
            }
            $out .= $OUTPUT->notification(get_string('catalogsdeleted', 'local_shop'));
        }

        return $out;
    }
}
