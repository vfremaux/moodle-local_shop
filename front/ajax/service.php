<?php
// This file is part of Moodle - http://moodle.org/
// // Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// // Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// // You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../config.php');
require_once($CFG->dirroot.'/local/shop/front/lib.php');
require_once($CFG->dirroot.'/local/shop/locallib.php');
require_once($CFG->dirroot.'/local/shop/classes/Shop.class.php');
require_once($CFG->dirroot.'/local/shop/classes/Catalog.class.php');

use local_shop\Catalog;
use local_shop\Shop;

$PAGE->set_url(new moodle_url('/local/shop/front/ajax/service.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('embedded');

$shopid = required_param('id', PARAM_INT);
$theShop = new Shop($shopid);
$theCatalog = new Catalog($theShop->catalogid);

$renderer = shop_get_renderer();
$theBlock = null;
$renderer->load_context($theShop, $theBlock);

$output = '';

$action = optional_param('action', '', PARAM_TEXT);
if ($action == 'addparticipant') {
    $pt = json_decode(required_param('participant', PARAM_TEXT));

    if (empty($pt->lastname) || empty($pt->lastname) || empty($pt->email)) {
        $result = get_string('missingdata', 'local_shop');
    } else {

        if (!isset($SESSION->shoppingcart)) {
            $SESSION->shoppingcart = new StdClass();
            $SESSION->shoppingcart->participants = array();
        }

        if ($moodleuser = $DB->get_record('user', array('lastname' => $pt->lastname, 'email' => $pt->email))) {
            $pt->moodleid = $moodleuser->id;
        }

        $pt->lastname = strtoupper($pt->lastname);
        $pt->firstname = ucwords($pt->firstname);
        $pt->city = strtoupper($pt->city);

        $SESSION->shoppingcart->participants[$pt->email] = $pt;
    }
    $action = 'participantlist';
}

// -----------------------------------------------------------------------------------//
if ($action == 'deleteparticipant') {
    $ptid = required_param('participantid', PARAM_TEXT);
    $requiredroles = $theCatalog->check_required_roles();

    if (isset($SESSION->shoppingcart->participants[$ptid])) {
        unset($SESSION->shoppingcart->participants[$ptid]);
    }

    if ($requiredroles) {
        foreach ($requiredroles as $role) {
            foreach ($SESSION->shoppingcart->order as $shortname => $fooq) {
                if (isset($SESSION->shoppingcart->users[$shortname][$role][$ptid])) {
                    unset($SESSION->shoppingcart->users[$shortname][$role][$ptid]);
                    @$SESSION->shoppingcart->assigns[$shortname]--;
                }
            }
        }
    }

    $action = 'participantlist';
}
// -----------------------------------------------------------------------------------//
if ($action == 'participantlist') {
    if (!empty($result)) {
        $output .= $OUTPUT->box($result);
    }
    $output .= $renderer->participant_row(null);
    $i = 0;
    if (!empty($SESSION->shoppingcart->participants)) {
        foreach ($SESSION->shoppingcart->participants as $participant) {
            $output .= $renderer->participant_row($participant);
            $i++;
        }
    }
    for ( ; $i < $SESSION->shoppingcart->seats ; $i++) {
        $output .= $renderer->participant_blankrow();
    }
}

// -----------------------------------------------------------------------------------//
if ($action == 'addassign') {
    $ptid = required_param('participantid', PARAM_TEXT);
    $role = required_param('role', PARAM_TEXT);
    $shortname = required_param('product', PARAM_TEXT);
    
    if (!isset($SESSION->shoppingcart->users)) {
        $SESSION->shoppingcart->users = array();
    }
    $SESSION->shoppingcart->users[$shortname][$role][$ptid] = $SESSION->shoppingcart->participants[$ptid];
    @$SESSION->shoppingcart->assigns[$shortname]++;
    $action = 'assignlistobj';
}
// -----------------------------------------------------------------------------------//
if ($action == 'deleteassign') {
    $ptid = required_param('participantid', PARAM_TEXT);
    $role = required_param('role', PARAM_TEXT);
    $shortname = required_param('product', PARAM_TEXT);

    unset($SESSION->shoppingcart->users[$shortname][$role][$ptid]);
    @$SESSION->shoppingcart->assigns[$shortname]--;
    $SESSION->shoppingcart->assigns[$shortname] = max(0, @$SESSION->shoppingcart->assigns[$shortname]); // secures in case of failure...
    $action = 'assignlistobj';
}
// -----------------------------------------------------------------------------------//
if ($action == 'assignlist') {
    $role = required_param('role', PARAM_TEXT);
    $shortname = required_param('product', PARAM_TEXT);
    $renderer->role_list($role, $shortname);
}
// -----------------------------------------------------------------------------------//
if ($action == 'assignlistobj') {
    $requiredroles = $theCatalog->check_required_roles();

    $shortname = required_param('product', PARAM_TEXT);
    $a = new StdClass;
    $a->role = required_param('role', PARAM_TEXT);
    foreach ($requiredroles as $role) {
        $a->content[$role] = $renderer->role_list($role, $shortname);
    }

    $output = json_encode($a);
}

// -----------------------------------------------------------------------------------//
if ($action == 'assignalllistobj') {
    $requiredroles = $theCatalog->check_required_roles();

    $a = new StdClass;
    foreach ($requiredroles as $role) {
        foreach ($SESSION->shoppingcart->order as $shortname => $fooq) {
            $a->content[$role][$shortname] = $renderer->role_list($role, $shortname);
        }
    }

    $output = json_encode($a);
}

if ($action == 'addunit') {
    $shortname = required_param('productname', PARAM_TEXT);
    @$SESSION->shoppingcart->order[$shortname]++;
    $product = $theCatalog->get_product_by_shortname($shortname);
    $output = new StdClass();
    $output->html = $renderer->units($product);
    $output->quant = $SESSION->shoppingcart->order[$shortname];
    $output = json_encode($output);
}

if ($action == 'setunits') {
    $shortname = required_param('productname', PARAM_TEXT);
    $quant = required_param('quant', PARAM_INT);
    $product = $theCatalog->get_product_by_shortname($shortname);

    if ($product->maxdeliveryquant) {
        if ($quant > $product->maxdeliveryquant) {
            $quant = $product->maxdeliveryquant;
        }
    }
    @$SESSION->shoppingcart->order[$shortname] = $quant;

    $theBlock->view = 'shop'; // we are necessarily in shop
    $output = new StdClass();
    $output->html = $renderer->units($product);
    $output->quant = $SESSION->shoppingcart->order[$shortname];
    $output = json_encode($output);
}

if ($action == 'deleteunit') {
    $clearall = optional_param('clearall', false, PARAM_BOOL);
    $shortname = required_param('productname', PARAM_TEXT);
    if ($clearall) {
        unset($SESSION->shoppingcart->order[$shortname]);
    } else {
        @$SESSION->shoppingcart->order[$shortname]--;
    }
    if (@$SESSION->shoppingcart->order[$shortname] == 0) {
        unset($SESSION->shoppingcart->order[$shortname]);
    }

    $catalogitem = $theCatalog->get_product_by_shortname($shortname);

    $requiredroles = $theCatalog->check_required_roles();

    if ($catalogitem->quantaddressesusers) {
        // if seat based, remove last assign per unit removed
        foreach ($requiredroles as $role) {
            if (isset($SESSION->shoppingcart->{$role})) {
                array_pop($SESSION->shoppingcart->{$role});
            }
            if (empty($SESSION->shoppingcart->{$role})) {
                unset($SESSION->shoppingcart->{$role});
            }
        }
        if (!empty($SESSION->shoppingcart->assigns) && array_key_exists($shortname, $SESSION->shoppingcart->assigns)) {
            $SESSION->shoppingcart->assigns[$shortname]--;
            if ($SESSION->shoppingcart->assigns[$shortname] == 0) {
                unset($SESSION->shoppingcart->assigns[$shortname]);
            }
        }
    } else {
        // If non seat based, remove assign only when last unit is removed.
        foreach ($requiredroles as $role) {
            if (isset($SESSION->shoppingcart->{$role})) {
                unset($SESSION->shoppingcart->{$role});
            }
        }
        if (!isset($SESSION->shoppingcart->order[$shortname])) {
            unset($SESSION->shoppingcart->assigns[$shortname]);
        }
    }

    $output = new StdClass();
    $output->html = $renderer->units($catalogitem);
    $output->quant = 0 + @$SESSION->shoppingcart->order[$shortname];
    $output = json_encode($output);
}

if ($action == 'updateproduct') {
    $productcode = required_param('productcode', PARAM_TEXT);
}

if ($action == 'orderdetails') {
    $categories = $theCatalog->get_all_products($fooproducts); // loads categories with products
    $output = new StdClass;
    $output->html = $renderer->order_detail($categories);
    $output = json_encode($output);
}

if ($action == 'ordertotals') {
    $theCatalog->get_all_products($fooproducts); // loads categories with products
    $output = new StdClass;
    $output->html = $renderer->order_totals($theCatalog);
    $output = json_encode($output);
}

echo $output;