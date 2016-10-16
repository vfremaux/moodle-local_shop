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
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

class purchasereq_controller extends front_controller_base {

    public function receive($cmd, $data = array()) {
        global $SESSION;

        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            return;
        } else {
            $this->data = new \StdClass;
        }

        $shoppingcart = $SESSION->shoppingcart;

        switch ($cmd) {
            case 'collect':
                foreach ($shoppingcart->order as $itemname => $itemcount) {
                    $catalogitem = $this->thecatalog->get_product_by_shortname($itemname);

                    $handler = $catalogitem->get_handler();

                    $requireddata = $catalogitem->requireddata; // Take care, result of magic _get() is not directly testable.
                    $requirements = json_decode($requireddata);
                    if (!empty($requirements)) {
                        foreach ($requirements as $reqobj) {
                            for ($i = 0; $i < $itemcount; $i++) {
                                $param = required_param($itemname.'/'.$reqobj->field.$i, PARAM_TEXT);
                                $this->data->customerdata[$itemname][$reqobj->field][$i] = $param;
                            }
                        }
                    }
                }
                break;
            case 'navigate':
                $this->data->back = optional_param('back', false, PARAM_BOOL);
                break;
        }
    }

    public function process($cmd) {
        global $SESSION;

        $shoppingcart = $SESSION->shoppingcart;

        if ($cmd == 'collect') {

            $errors = array();

            $shoppingcart->customerdata['completed'] = true;
            foreach ($shoppingcart->order as $itemname => $itemcount) {
                $catalogitem = $this->thecatalog->get_product_by_shortname($itemname);

                $handler = $catalogitem->get_handler();

                $requireddata = $catalogitem->requireddata; // Take care, result of magic _get() is not directly testable.
                $requirements = json_decode($requireddata);
                if (!empty($requirements)) {
                    foreach ($requirements as $reqobj) {
                        for ($i = 0; $i < $itemcount; $i++) {
                            $param = $this->data->customerdata[$itemname][$reqobj->field][$i];
                            if (!is_null($handler) && !($handler === false)) {
                                if (!$handler->validate_required_data($itemname, $reqobj->field, $i, $param, $errors)) {
                                    $shoppingcart->customerdata['completed'] = false;
                                    continue;
                                }
                            }
                            $shoppingcart->customerdata[$itemname][$reqobj->field][$i] = $param;
                        }
                    }
                }
            }
        } else if ($cmd == 'navigate') {
            // Coming from further form.
            if ($this->data->back) {
                $prev = $this->theshop->get_prev_step('purchaserequ');
                $params = array('view' => $prev,
                                'shopid' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id,
                                'back' => 1);
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            } else {
                // Going further silently.
                $next = $this->theshop->get_next_step('purchaserequ');
                $params = array('view' => $next,
                                'shopid' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id);
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            }
        }
    }
}