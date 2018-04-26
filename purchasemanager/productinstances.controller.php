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

require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');

use local_shop\Product;

class productinstances_controller {

    protected $data;

    protected $received;

    public function receive($cmd, $data = null) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'delete':
            case 'softdelete':
            case 'softrestore':
                $this->data->productids = required_param_array('productids', PARAM_INT);
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        // Delete a product instances ****************************** **.
        if ($cmd == 'delete') {

            require_sesskey();

            if (!empty($this->data->productids)) {
                foreach ($this->data->productids as $pid) {
                    try {
                        $product = new Product($pid);
                        $product->delete();
                    } catch (\Exception $e) {
                        print_error('objecterror', 'local_shop', $e->getMessage());
                    }
                }
            }
        }

        // Disables a product  ****************************** **.
        if ($cmd == 'softdelete') {
            require_sesskey();

            if (!empty($this->data->productids)) {
                foreach ($this->data->productids as $pid) {
                    try {
                        $product = new Product($pid);
                        $product->soft_delete();
                    } catch (\Exception $e) {
                        print_error('objecterror', 'local_shop', $e->getMessage());
                    }
                }
            }
        }

        // Disables a product  ****************************** **.
        if ($cmd == 'softrestore') {
            require_sesskey();

            if (!empty($this->data->productids)) {
                foreach ($this->data->productids as $pid) {
                    try {
                        $product = new Product($pid);
                        $product->soft_restore();
                    } catch (\Exception $e) {
                        print_error('objecterror', 'local_shop', $e->getMessage());
                    }
                }
            }
        }
    }

    public static function info() {
        return array('delete' => array('productids' => 'Array of integers pointing local_shop_product records.'));
    }
}