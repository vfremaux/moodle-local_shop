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

use StdClass;
use coding_exception;
use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/front/front.controller.php');
require_once($CFG->dirroot.'/local/shop/mailtemplatelib.php');

/**
 * Front purchase controller : order step (pre checkout)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class order_controller extends front_controller_base {

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
            $this->received = true;
            return;
        } else {
            $this->data = new StdClass;
        }

        switch ($cmd) {
            case 'navigate':
                $this->data->back = optional_param('back', false, PARAM_BOOL);
                if (!$this->data->back) {
                    $this->data->paymode = required_param('paymode', PARAM_TEXT);
                }
                break;
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $SESSION, $CFG, $SITE, $DB;

        if (!$this->received) {
            throw new coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $shoppingcart = $SESSION->shoppingcart;

        $config = get_config('local_shop');

        if ($cmd == 'agreeeulas') {
            $shoppingcart->eulas = 'approved';
            return;
        }

        if ($cmd == 'reseteulas') {
            $shoppingcart->eulas = 'required';
            return;
        }

        if ($cmd == 'navigate') {
            if ($this->data->back) {
                $prev = $this->theshop->get_prev_step('order');
                $params = [
                    'view' => $prev,
                    'shopid' => $this->theshop->id,
                    'blockid' => 0 + @$this->theblock->id,
                    'back' => 1
                ];
                return new \moodle_url('/local/shop/front/view.php', $params);
            } else {

                if (empty($shoppingcart->transid)) {
                    // Locks a transition ID for new incomers.
                    $shoppingcart->transid = shop_get_transid();
                }

                // Register paymode.
                $shoppingcart->paymode = $this->data->paymode;

                $items = 0;
                foreach (array_values($shoppingcart->order) as $quant) {
                    $items += $quant;
                }

                $vars = [
                    'TRANSACTION' => $shoppingcart->transid,
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
                    'AMOUNT' => sprintf("%.2f", round($shoppingcart->finaluntaxedtotal, 2)),
                    'TAXES' => sprintf("%.2f", round($shoppingcart->finaltaxestotal, 2)),
                    'TTC' => sprintf("%.2f", round($shoppingcart->finaltaxedtotal, 2)),
                ];
                $salesnotification = shop_compile_mail_template('transaction_input', $vars, '');

                if ($salesrole = $DB->get_record('role', ['shortname' => 'sales'])) {
                    $systemcontext = context_system::instance();
                    $seller = new StdClass();
                    $seller->username = 'moodleseller';
                    $seller->firstname = '';
                    $seller->lastname = $config->sellername;
                    $seller->email = $config->sellermail;
                    $seller->maildisplay = true;
                    $seller->id = $DB->get_field('user', 'id', ['email' => $config->sellermail]);

                    // Add other name fields required by fullname.
                    $morefields = \local_shop\compat::get_name_fields_as_array();

                    if (!empty($morefields)) {
                        foreach ($morefields as $f) {
                            if (!isset($seller->$f)) {
                                $seller->$f = '';
                            }
                        }
                    }

                    if (!empty($config->presalenotification)) {
                        $title = $SITE->shortname.' Backoffice : '.get_string('orderinput', 'local_shop');
                        $sent = ticket_notifyrole($salesrole->id, $systemcontext, $seller, $title, $salesnotification,
                                                  $salesnotification, '');
                        if ($sent) {
                            $message = "[{$SESSION->shoppingcart->transid}] Ordering Controller:";
                            $message .= " Shop Transaction Confirm Notification to sales";
                            shop_trace($message);
                        } else {
                            $message = "[{$SESSION->shoppingcart->transid}] Ordering Controller Warning:";
                            $message .= " Failed emitting to at least one manager.";
                            shop_trace($message);
                        }
                    } else {
                        $message = "[{$SESSION->shoppingcart->transid}] Ordering Controller :";
                        $message .= " Order input with no notification to sales (disabled).";
                        shop_trace($message);
                    }
                } else {
                    $message = "[{$SESSION->shoppingcart->transid}] Ordering Controller Warning :";
                    $message .= " Seems sales role not installed";
                    shop_trace($message);
                }

                $next = $this->theshop->get_next_step('order');
                $params = [
                    'view' => $next,
                    'shopid' => $this->theshop->id,
                    'blockid' => 0 + @$this->theblock->id,
                    'what' => 'place',
                ];
                return new moodle_url('/local/shop/front/view.php', $params);
            }
        }
    }
}
