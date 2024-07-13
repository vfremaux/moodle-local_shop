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
 * Purchase front step controller
 *
 * @package   local_shop
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_shop\front;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

use StdClass;
use moodle_url;

/**
 * Front purchase controller : invoice step
 */
class invoice_controller extends front_controller_base {

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
            return;
        } else {
            $this->data = new StdClass();
        }

        switch ($cmd) {
            case 'navigate':
                $this->data->customerservice = optional_param('customerservice', '', PARAM_TEXT);
                break;
        }
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $SESSION;

        if ($cmd == 'navigate') {

            if ($this->data->customerservice) {
                if (!empty($theshop->defaultcustomersupportcourse) && $SESSION->shoppingcart->customerinfo->hasaccount) {
                    $targeturl = new moodle_url('/course/view.php', ['id' => $theshop->defaultcustomersupportcourse]);
                    if (isloggedin()) {
                        /*
                         * clear all session data and go back to shop front or go to
                         * customer service if asked to and is able to go there...
                         */
                        unset($SESSION->shoppingcart);
                        redirect(new moodle_url('/login/index.php', ['wantsurl' => urlencode($targeturl)]));
                    } else {
                        /*
                         * clear all session data and go back to shop front or go to
                         * customer service if asked to and is able to go there...
                         */
                        unset($SESSION->shoppingcart);
                        redirect($targeturl);
                    }
                }
            }

            $next = $this->theshop->get_next_step('invoice');
            $params = ['view' => $next, 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id];
            redirect(new \moodle_url('/local/shop/front/view.php', $params));
        }
    }
}
