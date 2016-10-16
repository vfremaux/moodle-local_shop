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
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace \local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/classes/Tax.class.php');

use local_shop\Tax;

class taxes_controller {

    protected $data;

    protected $received;

    public function receive($cmd, $data = null) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new StdClass;
        }

        switch ($cmd) {
            case 'delete':
                $this->data->taxid = required_param('taxid', PARAM_INT);
                break;

            case 'edit':
                // Let data come from $data attribute.
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        // Delete a tax.
        if ($cmd == 'delete') {
            $tax = new Tax($this->data->taxid);
            $tax->delete();
        }

        if ($cmd == 'edit') {
            $tax = $this->data;
            if (empty($tax->taxid)) {
                $tax->id = $DB->insert_record('local_shop_tax', $tax);
            } else {
                $tax->id = $tax->taxid;
                unset($tax->taxid);
                $DB->update_record('local_shop_tax', $tax);
            }
            return new Tax($tax->id);
        }
    }
}