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
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

class order_controller extends front_controller_base {

    function process($cmd) {
        global $SESSION, $CFG, $SITE, $DB;

        $config = get_config('local_shop');

        if ($cmd == 'navigate') {
            if ($back = optional_param('back', false, PARAM_BOOL)) {
                $prev = $this->theshop->get_prev_step('order');
                redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $prev, 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'back' => 1)));
            } else {

                // register paymode
                $SESSION->shoppingcart->paymode = required_param('paymode', PARAM_TEXT);

                $items = 0;
                foreach ($SESSION->shoppingcart->order as $shortname => $quant) {
                    $items += $quant;
                }

                $salesnotification = shop_compile_mail_template('transaction_input', array('TRANSACTION' => $SESSION->shoppingcart->transid,
                                                                           'SERVER' => $SITE->fullname,
                                                                           'SERVER_URL' => $CFG->wwwroot,
                                                                           'SELLER' => $config->sellername,
                                                                           'FIRSTNAME' => $SESSION->shoppingcart->customerinfo['firstname'],
                                                                           'LASTNAME' => $SESSION->shoppingcart->customerinfo['lastname'],
                                                                           'MAIL' => $SESSION->shoppingcart->customerinfo['email'],
                                                                           'CITY' => $SESSION->shoppingcart->customerinfo['city'],
                                                                           'COUNTRY' => $SESSION->shoppingcart->customerinfo['country'],
                                                                           'PAYMODE' => $SESSION->shoppingcart->paymode,
                                                                           'ITEMS' => $items,
                                                                           'AMOUNT' => sprintf("%.2f", round($SESSION->shoppingcart->untaxedtotal, 2)),
                                                                           'TAXES' => sprintf("%.2f", round($SESSION->shoppingcart->taxestotal, 2)),
                                                                           'TTC' => sprintf("%.2f", round($SESSION->shoppingcart->taxedtotal, 2))
                                                                            ), '');

                if ($salesrole = $DB->get_record('role', array('shortname' => 'sales'))) {
                    $systemcontext = \context_system::instance();
                    $seller = new \StdClass;
                    $seller->firstname = '';
                    $seller->lastname = $config->sellername;
                    $seller->email = $config->sellermail;
                    $seller->maildisplay = true;
                    $seller->id = $DB->get_field('user', 'id', array('email' => $config->sellermail));

                    // Add other name fields required by fullname
                    if ($morefields = get_all_user_name_fields(false)) {
                        foreach ($morefields as $f) {
                            if (!isset($seller->$f)) {
                                $seller->$f = '';
                            }
                        }
                    }

                    $title = $SITE->shortname . ' : ' . get_string('orderinput', 'local_shop');
                    $sent = ticket_notifyrole($salesrole->id, $systemcontext, $seller, $title, $salesnotification, $salesnotification, '');
                    if ($sent) {
                        shop_trace("[{$SESSION->shoppingcart->transid}] Ordering Controller : shop Transaction Confirm Notification to sales");
                    } else {
                        shop_trace("[{$SESSION->shoppingcart->transid}] Ordering Controller Warning : Seems no sales manager are assigned");
                    }
                }

                redirect(new \moodle_url('/local/shop/front/view.php', array('view' => $this->theshop->get_next_step('order'), 'shopid' => $this->theshop->id, 'blockid' => 0 + @$this->theblock->id, 'what' => 'place')));
            }
        }
    }
}