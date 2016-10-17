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

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

class users_controller extends front_controller_base {

    public function receive($cmd, $data = array()) {
        if (!empty($data)) {
            // Data is fed from outside.
            $this->data = (object)$data;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'navigate':
                $this->data->back = optional_param('back', '', PARAM_TEXT);
                break;
        }
    }

    public function process($cmd) {
        if ($cmd == 'navigate') {
            if ($this->data->back) {
                $prev = $this->theshop->get_prev_step('users');
                $params = array('view' => $prev,
                                'shopid' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id,
                                'back' => 1);
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            } else {
                $next = $this->theshop->get_next_step('users');
                $params = array('view' => $next, 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id);
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            }
        } else if ($cmd == 'back') {
            // This can be decided into the user page.
            $next = $this->theshop->get_prev_step('users');
            $params = array('view' => $next,
                            'shopid' => $this->theshop->id,
                            'blockid' => 0 + @$this->theblock->id,
                            'back' => 1);
            redirect(new \moodle_url('/local/shop/front/view.php', $params));
        }
    }
}