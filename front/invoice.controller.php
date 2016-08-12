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

class invoice_controller extends front_controller_base {

    function process($cmd) {
        global $SESSION;

        if ($cmd == 'navigate') {
            // clear all session data and go back to shop front or go to customer service if asked to and is able to go there...
            unset($SESSION->shoppingcart);

            if (optional_param('customerservice', '', PARAM_TEXT)) {
                if (!empty($theShop->defaultcustomersupportcourse) && $SESSION->shoppingcart->customerinfo->hasaccount) {
                    $targeturl = new \moodle_url('/course/view.php', array('id' => $theShop->defaultcustomersupportcourse));
                    if (isloggedin()) {
                        redirect(new \moodle_url('/login/index.php', array('wantsurl' => urlencode($targeturl))));
                    } else {
                        redirect($targeturl);
                    }
                }
            }

            redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_next_step('invoice'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id)));
        }
    }
}