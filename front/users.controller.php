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

use StdClass;
use moodle_url;

require_once($CFG->dirroot.'/local/shop/front/front.controller.php');

/**
 * Front purchase controller : users info step
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
class users_controller extends front_controller_base {

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
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'addparticipant':
                $this->data->participant = required_param('participant', PARAM_TEXT);
                break;

            case 'deleteparticipant':
                $this->data->participantid = required_param('participantid', PARAM_TEXT);
                break;

            case 'addassign':
            case 'deleteassign':
                $this->data->ptid = required_param('participantid', PARAM_TEXT);
                $this->data->role = required_param('role', PARAM_TEXT);
                $this->data->shortname = required_param('product', PARAM_TEXT);
                break;

            case 'assignlist':
            case 'assignlistobj':
                $this->data->role = required_param('role', PARAM_TEXT);
                $this->data->shortname = required_param('product', PARAM_TEXT);
                break;

            case 'navigate':
                $this->data->back = optional_param('back', '', PARAM_TEXT);
                break;
        }

        $this->received = true;
    }

    /**
     * Processes the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $SESSION, $DB, $OUTPUT;

        if (!$this->received) {
            throw new \coding_exception('Data must be received in controller before operation. this is a programming error.');
        }

        $output = '';

        if ($cmd == 'navigate') {
            if ($this->data->back) {
                $prev = $this->theshop->get_prev_step('users');
                $params = [
                    'view' => $prev,
                    'shopid' => $this->theshop->id,
                    'blockid' => ($this->theblock->id ?? 0),
                    'back' => 1,
                ];
                return new \moodle_url('/local/shop/front/view.php', $params);
            } else {
                $next = $this->theshop->get_next_step('users');
                $params = ['view' => $next, 'shopid' => $this->theshop->id, 'blockid' => ($this->theblock->id ?? 0)];
                return new moodle_url('/local/shop/front/view.php', $params);
            }
        } else if ($cmd == 'back') {
            // This can be decided into the user page.
            $next = $this->theshop->get_prev_step('users');
            $params = [
                'view' => $next,
                'shopid' => $this->theshop->id,
                'blockid' => ($this->theblock->id ?? 0),
                'back' => 1,
            ];
            return new moodle_url('/local/shop/front/view.php', $params);

        } else if ($cmd == 'addparticipant') {

            $pt = (object) json_decode($this->data->participant);

            if (empty($pt->lastname) || empty($pt->lastname) || empty($pt->email)) {
                $result = get_string('missingdata', 'local_shop');
                return $result;
            } else {

                if (!isset($SESSION->shoppingcart)) {
                    $SESSION->shoppingcart = new StdClass();
                    $SESSION->shoppingcart->participants = [];
                }

                if ($moodleuser = $DB->get_record('user', ['lastname' => $pt->lastname, 'email' => $pt->email])) {
                    $pt->moodleid = $moodleuser->id;
                }

                $pt->lastname = strtoupper($pt->lastname);
                $pt->firstname = ucwords($pt->firstname);
                $pt->city = strtoupper($pt->city);

                $SESSION->shoppingcart->participants[$pt->email] = $pt;
            }
            $cmd = 'participantlist';

        } else if ($cmd == 'deleteparticipant') {

            $ptid = $this->data->participantid; // The ptid is email.
            $requiredroles = $this->thecatalog->check_required_roles();

            if (isset($SESSION->shoppingcart->participants[$ptid])) {
                unset($SESSION->shoppingcart->participants[$ptid]);
            }

            if ($requiredroles) {
                foreach ($requiredroles as $role) {
                    foreach ($SESSION->shoppingcart->order as $shortname => $fooq) {
                        if (isset($SESSION->shoppingcart->users[$shortname][$role][$ptid])) {
                            unset($SESSION->shoppingcart->users[$shortname][$role][$ptid]);
                            if (array_key_exists($shortname, $SESSION->shoppingcart->assigns)) {
                                $SESSION->shoppingcart->assigns[$shortname]--;
                            }
                        }
                    }
                }
            }

            $cmd = 'participantlist';
        }

        if ($cmd == 'participantlist') {

            if (!empty($result)) {
                $output .= $OUTPUT->box($result);
            }
            $output .= $this->renderer->participant_row(null);
            $i = 0;
            if (!empty($SESSION->shoppingcart->participants)) {
                foreach ($SESSION->shoppingcart->participants as $participant) {
                    $output .= $this->renderer->participant_row($participant);
                    $i++;
                }
            }
            for (; $i < ($SESSION->shoppingcart->seats ?? 0); $i++) {
                $output .= $this->renderer->participant_blankrow();
            }
        }

        if ($cmd == 'addassign') {

            if (!isset($SESSION->shoppingcart->users)) {
                $SESSION->shoppingcart->users = [];
            }
            $sn = $this->data->shortname;
            $r = $this->data->role;
            $pt = $this->data->ptid;
            $SESSION->shoppingcart->users[$sn][$r][$pt] = $SESSION->shoppingcart->participants[$pt];
            if (array_key_exists($sn, $SESSION->shoppingcart->assigns)) {
                $SESSION->shoppingcart->assigns[$sn]++;
            } else {
                $SESSION->shoppingcart->assigns[$sn] = 1;
            }
            $cmd = 'assignlistobj';

        } else if ($cmd == 'deleteassign') {

            $sn = $this->data->shortname;
            $r = $this->data->role;
            $pt = $this->data->ptid;
            unset($SESSION->shoppingcart->users[$sn][$r][$pt]);
            if (array_key_exists($sn, $SESSION->shoppingcart->assigns)) {
                $SESSION->shoppingcart->assigns[$sn]--;
            }
            // Secures in case of failure...
            $SESSION->shoppingcart->assigns[$sn] = max(0, $SESSION->shoppingcart->assigns[$sn] ?? 0);
            $cmd = 'assignlistobj';

        } else if ($cmd == 'assignlist') {

            $this->renderer->role_list($this->data->role, $this->data->shortname);

        }

        // Needing bounce here.
        if ($cmd == 'assignlistobj') {

            $requiredroles = $this->thecatalog->check_required_roles();

            $a = new StdClass();
            $a->role = $this->data->role;
            foreach ($requiredroles as $role) {
                $a->content[$role] = $this->renderer->role_list($role, $this->data->shortname);
            }

            $output = json_encode($a);

        } else if ($cmd == 'assignalllistobj') {

            $requiredroles = $this->thecatalog->check_required_roles();

            $a = new StdClass();
            foreach ($requiredroles as $role) {
                foreach ($SESSION->shoppingcart->order as $shortname => $fooq) {
                    $a->content[$role][$shortname] = $this->renderer->role_list($role, $shortname);
                }
            }

            $output = json_encode($a);
        }

        return $output;
    }
}
