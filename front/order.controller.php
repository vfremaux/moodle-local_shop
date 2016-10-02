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
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

class order_controller extends front_controller_base {

    public function process($cmd) {
        global $SESSION, $CFG, $SITE, $DB;

        $shoppingcart = $SESSION->shoppingcart;

        $config = get_config('local_shop');

        if ($cmd == 'navigate') {
            if ($back = optional_param('back', false, PARAM_BOOL)) {
                $prev = $this->theshop->get_prev_step('order');
                $params = array('view' => $prev,
                                'shopid' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id,
                                'back' => 1);
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            } else {

                // Register paymode.
                $shoppingcart->paymode = required_param('paymode', PARAM_TEXT);

                $items = 0;
                foreach ($shoppingcart->order as $shortname => $quant) {
                    $items += $quant;
                }

                $vatrs= array('TRANSACTION' => $shoppingcart->transid,
                              'SERVER' => $SITE->fullname,
                              'SERVER_URL' => $CFG->wwwroot,
                              'SELLER' => $config->sellername,
                              'FIRSTNAME' => $shoppingcart->customerinfo['firstname'],
                              'LASTNAME' => $shoppingcart->customerinfo['lastname'],
                              'MAIL' => $shoppingcart->customerinfo['email'],
                              'CITY' => $shoppingcart->customerinfo['city'],
                              'COUNTRY' => $shoppingcart->customerinfo['country'],
                              'PAYMODE' => $shoppingcart->paymode,
                              'ITEMS' => $items,
                              'AMOUNT' => sprintf("%.2f", round($shoppingcart->untaxedtotal, 2)),
                              'TAXES' => sprintf("%.2f", round($shoppingcart->taxestotal, 2)),
                              'TTC' => sprintf("%.2f", round($shoppingcart->taxedtotal, 2)));
                $salesnotification = shop_compile_mail_template('transaction_input', $vars, '');

                if ($salesrole = $DB->get_record('role', array('shortname' => 'sales'))) {
                    $systemcontext = \context_system::instance();
                    $seller = new \StdClass;
                    $seller->firstname = '';
                    $seller->lastname = $config->sellername;
                    $seller->email = $config->sellermail;
                    $seller->maildisplay = true;
                    $seller->id = $DB->get_field('user', 'id', array('email' => $config->sellermail));

                    // Add other name fields required by fullname.
                    if ($morefields = get_all_user_name_fields(false)) {
                        foreach ($morefields as $f) {
                            if (!isset($seller->$f)) {
                                $seller->$f = '';
                            }
                        }
                    }

                    $title = $SITE->shortname . ' : ' . get_string('orderinput', 'local_shop');
                    $sent = ticket_notifyrole($salesrole->id, $systemcontext, $seller, $title, $salesnotification,
                                              $salesnotification, '');
                    if ($sent) {
                        $message = "[{$SESSION->shoppingcart->transid}] Ordering Controller :";
                        $message .= "shop Transaction Confirm Notification to sales";
                        shop_trace($message);
                    } else {
                        $message = "[{$SESSION->shoppingcart->transid}] Ordering Controller Warning :";
                        $message .= " Seems no sales manager are assigned";
                        shop_trace($message);
                    }
                }

                $next = $this->theshop->get_next_step('order');
                $params = array('view' => $next,
                                'shopid' => $this->theshop->id,
                                'blockid' => 0 + @$this->theblock->id,
                                'what' => 'place');
                redirect(new \moodle_url('/local/shop/front/view.php', $params));
            }
        }
    }
}