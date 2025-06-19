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
 * controller for product instances.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Product.class.php');

use local_shop\Product;
use moodle_exception;

/**
 * A MVC controller to manage product instances.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 * @SuppressWarnings(PHPMD.ExitExpression)
 */
class productinstances_controller {

    /**  @var data to process */ 
    protected $data;

    /**  @var Mark data as received */ 
    protected $received;
    
    /**
     * Receives all needed parameters from outside for each action case.
     * @param string $cmd the action keyword
     * @param array $data incoming parameters from form when directly available, otherwise the
     * function should get them from request
     */
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

    /**
     * Processes the action
     * @param string $cmd
     */
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
                        throw new moodle_exception(get_string('objecterror', 'local_shop', $e->getMessage()));
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
                        throw new moodle_exception(get_string('objecterror', 'local_shop', $e->getMessage()));
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
                        throw new moodle_exception(get_string('objecterror', 'local_shop', $e->getMessage()));
                    }
                }
            }
        }
    }

    /**
     * Gives meta informaiton on controller.
     */
    public static function info() {
        return ['delete' => ['productids' => 'Array of integers pointing local_shop_product records.']];
    }
}
