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

namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

class purchasereq_controller extends front_controller_base {

    function process($cmd) {
        global $SESSION;

        if ($cmd == 'collect') {

            $errors = array();

            $SESSION->shoppingcart->customerdata['completed'] = true;
            foreach ($SESSION->shoppingcart->order as $itemname => $itemcount) {
                $catalogitem = $this->thecatalog->get_product_by_shortname($itemname);

                $handler = $catalogitem->get_handler();

                $requireddata = $catalogitem->requireddata; // Take care, result of magic _get() is not directly testable.
                $requirements = json_decode($requireddata);
                if (!empty($requirements)) {
                    foreach ($requirements as $reqobj) {
                        for ($i = 0 ; $i < $itemcount ; $i++) {
                            $param = required_param($itemname.'/'.$reqobj->field.$i, PARAM_TEXT);
                            if (!is_null($handler) && !($handler === false)) {
                                if (!$handler->validate_required_data($itemname, $reqobj->field, $i, $param, $errors)) {
                                    $SESSION->shoppingcart->customerdata['completed'] = false;
                                    continue;
                                }
                            }
                            $SESSION->shoppingcart->customerdata[$itemname][$reqobj->field][$i] = $param;
                        }
                    }
                }
            }
        } elseif ($cmd == 'navigate') {
            // Comming from further form.
            if ($back = optional_param('back', false, PARAM_BOOL)) {
                redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_prev_step('purchaserequ'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id)));
            } else {
                // Going further silently.
                redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_next_step('purchaserequ'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id)));
            }
        }
    }
}