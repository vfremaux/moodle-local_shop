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

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

class users_controller extends front_controller_base {

    function process($cmd) {
        if ($cmd == 'navigate') {
            if ($back = optional_param('back', '', PARAM_TEXT)) {
                redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_prev_step('users'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'back' => 1)));
            } else {
                redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_next_step('users'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id)));
            }
        } elseif ($cmd == 'back') {
            // This can be decided into the user page.
            redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_prev_step('users'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'back' => 1)));
        }
    }
}