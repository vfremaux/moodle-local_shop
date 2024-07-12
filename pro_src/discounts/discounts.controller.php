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
 * Controller for the customer screen responses.
 *
 * @package     local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @usecase deletediscount
 * @usecase adddiscount
 */
namespace local_shop\backoffice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/pro/classes/Discount.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Bill.class.php');
require_once($CFG->dirroot.'/local/shop/classes/DBListUtils.class.php');

use StdClass;
use \local_shop\Discount;
use \local_shop\Bill;
use \context_system;

/**
 * Controller for discount management
 */
class discounts_controller {

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
            case 'delete': {
                $this->data->discountids = required_param_array('discountid', PARAM_INT);
                $this->data->shopid = required_param('shopid', PARAM_INT);
                break;
            }

            case 'up':
            case 'down': {
                $this->data->discountid = required_param('id', PARAM_INT);
                $this->data->shopid = required_param('shopid', PARAM_INT);
                break;
            }

            case 'edit': {
                // Let data come from $data attribute.
                break;
            }

            case 'disable':
            case 'enable': {
                $this->data->discountid = required_param('id', PARAM_INT);
                $this->data->shopid = required_param('shopid', PARAM_INT);
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
        global $DB;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        // Make a list utils for manipulating ordering list.
        $listutils = new DBListUtils('local_shop_discount', 'ordering', ['shopid' => $this->data->shopid]);

        if ($cmd == 'delete') {
            if ($this->data->discountids) {
                foreach ($this->data->discountids as $id) {
                    $discount = Discount::instance($id);
                    if ($discount) {
                        $discount->delete();
                        $listutils->reorder;
                    }
                }
            }
        }

        if ($cmd == 'edit') {

            $argumenteditor = $this->data->argument_editor;
            $this->data->argumentformat = $argumenteditor['format'];
            $this->data->argument = $argumenteditor['text'];
            unset($this->data->argument_editor);
            if (!empty($this->data->applydata)) {
                if (is_array($this->data->applydata)) {
                    $this->data->applydata = implode(',', $this->data->applydata);
                }
            } else {
                $this->data->applydata = '';
            }

            if (empty($this->data->operator)) {
                $this->data->operator = 'accumulate';
            }

            $this->data->timemodified = time();
            if (empty($this->data->id)) {
                // New record.
                $this->data->timecreated = time();
                $this->data->ordering = $listutils->get_max_ordering();
                $this->data->ordering++;
                $this->data->id = $DB->insert_record('local_shop_discount', $this->data);
            } else {
                // Existing record.
                $DB->update_record('local_shop_discount', $this->data);
            }

            $context = context_system::instance();

            if (!empty($this->mform)) {
                // Comming from real form.
                $drafteditor = file_get_submitted_draft_itemid('argument_editor');
                $this->data->argument = file_save_draft_area_files($drafteditor, $context->id, 'local_shop', 'discountargument',
                                                            $this->data->id, $this->mform->editoroptions, $this->data->argument);
                $this->data = file_postupdate_standard_editor($this->data, 'argument', $this->mform->editoroptions, $context, 'local_shop',
                                                        'discountargument', $this->data->id);
                $DB->update_record('local_shop_discount', $this->data);
            }

            return Discount::instance($this->data);
        }

        if ($cmd == 'up') {
            $listutils->up($this->data->discountid);
        }

        if ($cmd == 'down') {
            $listutils->down($this->data->discountid);
        }

        if ($cmd == 'disable') {
            $DB->set_field('local_shop_discount', 'enabled', 0, ['id' => $this->data->discountid]);
        }

        if ($cmd == 'enable') {
            $DB->set_field('local_shop_discount', 'enabled', 1, ['id' => $this->data->discountid]);
        }
    }
}
