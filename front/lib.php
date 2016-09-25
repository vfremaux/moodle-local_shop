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

defined('MOODLE_INTERNAL') || die();

/**
 * @package   local_shop
 * @category  local
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/auth/ticket/lib.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

if (!defined('PHP_ROUND_HALF_EVEN')) define('PHP_ROUND_HALF_EVEN', 3);
if (!defined('PHP_ROUND_HALF_ODD')) define('PHP_ROUND_HALF_ODD', 3);

/**
* this function calculates an overall shipping additional line to be added to bill
* regarding order elements and location of customer. It will use all rules defined
* in shipping zones and shipping meta-information. 
*
* If shipzone has a 'billscopeamount' defined, this amount is used as unique shipping value
* once the shipping zone is assigned. When no zone can be disciminated using applicability
* rules, then the default zone of code '00' (if exists) is used against the same process.
*
* If shipzone has no billscopeamount defined, but has some product shipping information setup, 
* the order is scanned for entries matching the presence of shipping rules. If the rule has a 
* fixed value, then this value is used independantely of the quantity. If no value is defined, 
* but a formula, the formula is evaluated using $HT as unit untaxed price, $TTC ad unit taxed
* price, $Q as quantity, and $a, $b, $c as three coefficient values defined in the shipping.
*
* for security reasons all pseudo variables (startign with $ in formula are discarded, and the
* formula may not be parsable any more.  
*
* Applicability checks: 
*
* applicability of a zone is a set description that allow matching a country/zipcode condition.
* the applicability is a union of matching rules (rule1)op(rule2)
* 
* Rules can be combined using & (and) or | (or) operator
* 
* a Rule is divided in two sets : a set of accepted countrycodes, and a pattern of accepted zipcodes.
* Rule sample : [*][*] all locations in the world
* Rule sample : [fr,uk][*] all zips in uk and france
* Rule sample : [*][000$] all zip in all countries finishing by 000 
* Rule sample : [*][*000] similar to above
* Rule sample : [fr][^9.....$] zip with 6 digits starting with 9 in france (DOM-TOM)
* Rule sample : [fr][06...,83...,04...,05...] all cities in south east of france
*

/**
 * resolves a single geographic rule
 */
function shop_resolve_zone_rule($country, $zipcode, $rule) {

    if (preg_match('/\\(\\[(.*?)\\]\\[(.*?)\\]\\)/', $rule, $matches)) {
        $countries = strtolower($matches[1]);
        $zipcodes = $matches[2];
        $country = strtolower($country); // ensure we have no issues with case.
        if ($countries != '*') {
            if (!preg_match("/\\b$country\\b/", $countries)) {
                // echo "country $country fails matching $countries ";
                return false;
            }
            // echo 'country matches ';
        } else {
            // echo 'wildcard country ';
        }
        if ($zipcodes != '*') {
            $ziprules = explode(',', $zipcodes);
            foreach ($ziprules as $ru) {
                if (preg_match("/$ru/", $zipcode)) {
                    // echo "matching ";
                    return true;
                } else {
                    // echo "not matching $zipcode for /$ru/ ";
                }
            }
            return false;
        } else {
            // echo 'wildcard zipcode ';
        }
        return true;
    } else {
        // echo "no matching rule $rule ";
    }
    return false;
}

function shop_validate_customer($theShop) {
    global $SESSION, $DB, $CFG, $USER;

    if (!isset($SESSION->shoppingcart->errors)) $SESSION->shoppingcart->errors = new StdClass;
    $SESSION->shoppingcart->errors->customerinfo = array();

    if ($SESSION->shoppingcart->customerinfo['email'] == '') {
        $SESSION->shoppingcart->errors->customerinfo['customerinfo::email'] = '';
    }

    if (!isloggedin() && shop_has_potential_account($SESSION->shoppingcart->customerinfo['email'])) {
        $SESSION->wantsurl = new moodle_url('/local/shop/front/view.php', array('view' => 'customer', 'id' => $theShop->id, 'what' => 'revalidate'));
        $a = new StdClass();
        $a->wwwroot = $CFG->wwwroot;
        $SESSION->shoppingcart->errors->customerinfo['customerinfo::mail'] = get_string('existingmailpleaselogin', 'local_shop', $a);
    } elseif (isloggedin() && ($USER->email == $SESSION->shoppingcart->customerinfo['email'])) {
        // unmark that mail if mail matches (that is the logged user IS the customer user basd on mail)
        // @see revalidate command in customer.controller.php
        unset($SESSION->shoppingcart->errors->customerinfo['customerinfo::mail']);
    }

    if ($SESSION->shoppingcart->customerinfo['lastname'] == '') {
        $SESSION->shoppingcart->errors->customerinfo['customerinfo::lastname'] = '';
    }

    if ($SESSION->shoppingcart->customerinfo['firstname'] == '') {
        $SESSION->shoppingcart->errors->customerinfo['customerinfo::firstname'] = '';
    }

    if ($SESSION->shoppingcart->customerinfo['city'] == '') {
        $SESSION->shoppingcart->errors->customerinfo['customerinfo::city'] = '';
    }

    // we just require it if we are billing to the user
    if (!$SESSION->shoppingcart->usedistinctinvoiceinfo) {
        if ($SESSION->shoppingcart->customerinfo['address'] == '') {
            $SESSION->shoppingcart->errors->customerinfo['customerinfo::address'] = '';
        }
    
        if ($SESSION->shoppingcart->customerinfo['zip'] == '') {
            $SESSION->shoppingcart->errors->customerinfo['customerinfo::zip'] = '';
        }
    }

    return $SESSION->shoppingcart->errors->customerinfo;
}

/**
 * Checks
 */
function shop_has_potential_account($email) {
    global $DB, $SESSION;

    $potentialcustomer = $DB->get_record('local_shop_customer', array('email' => $SESSION->shoppingcart->customerinfo['email']));
    if (!empty($potentialcustomer->hasaccount)) {
        return true;
    }

    if ($potentialuser = $DB->get_record('user', array('email' => $SESSION->shoppingcart->customerinfo['email']))) {
        return true;
    }

    return false;
}

function shop_validate_invoicing() {
    global $SESSION;

    if (!isset($SESSION->shoppingcart->errors)) $SESSION->shoppingcart->errors = new StdClass;
    $SESSION->shoppingcart->errors->invoiceinfo = array();

    if ($SESSION->shoppingcart->invoiceinfo['organisation'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::organisation'] = '';
    }

    if ($SESSION->shoppingcart->invoiceinfo['address'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::address'] = '';
    }

    if ($SESSION->shoppingcart->invoiceinfo['zip'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::zip'] = '';
    }

    if ($SESSION->shoppingcart->invoiceinfo['lastname'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::lastname'] = '';
    }

    if ($SESSION->shoppingcart->invoiceinfo['firstname'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::firstname'] = '';
    }

    if ($SESSION->shoppingcart->invoiceinfo['country'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::country'] = '';
    }

    if ($SESSION->shoppingcart->invoiceinfo['city'] == '') {
        $SESSION->shoppingcart->errors->invoiceinfo['invoiceinfo::city'] = '';
    }

    return $SESSION->shoppingcart->errors->invoiceinfo;
}

/**
 * checks purchased products and quantities and calculates the neaded amount of seats.
 * We need check in catalog definition id product is seat driven or not. If seat driven
 * the quantity adds to seat couts. If not, 1 seat is added to the seat count.
 */
function shop_check_assigned_seats($requiredroles) {
    global $SESSION;

    $assigned = 0;

    if (!isset($SESSION->shoppingcart)) return 0;

    if ($requiredroles && !empty($SESSION->shoppingcart->users)) {
        foreach ($SESSION->shoppingcart->users as $product => $roleassigns) {
            foreach ($roleassigns as $role => $participants) {
                $assigned += count($participants);
            }
        }
    }

    return $assigned;
}

/**
 * ensures a transaction id is unique.
 *
 */
function shop_get_transid() {
    global $DB;

    $transid = strtoupper(substr(base64_encode(crypt(microtime() + rand(0,16), 'MOODLE_SHOP')), 0, 16));
    while ($DB->record_exists('local_shop_bill', array('transactionid' => $transid))) {
        $transid = strtoupper(substr(base64_encode(crypt(microtime() + rand(0,16))), 0, 16));
    }
    return $transid;
}

/**
 *
 */
function shop_get_payment_plugin(&$shopinstance, $pluginname = null) {
    global $CFG, $SESSION;

    if (is_null($pluginname)) {
        $pluginname = $SESSION->shoppingcart->paymode;
    }

    $paymentclass = 'shop_paymode_'.$pluginname;
    include_once $CFG->dirroot.'/local/shop/paymodes/'.$pluginname.'/'.$pluginname.'.class.php';
    return new $paymentclass($shopinstance);
}

/**
 * computes and defaults enrolement start and end time, against some local course
 *
 */
function shop_compute_enrol_time(&$handlerdata, $fieldtoreturn, $course) {

    $starttime = (empty($handlerdata->actionparams['starttime'])) ? time() : $handlerdata->actionparams['starttime'];

    switch ($fieldtoreturn) {
        case 'starttime':
            return $starttime;
            break;

        case 'endtime':
            if (!array_key_exists('endtime', $handlerdata->actionparams)) {
                if (!empty($data->renewable)) {
                    if (!empty($handlerdata->actionparams['duration'])) {
                        return $starttime + $handlerdata->actionparams['duration'] * DAYSECS;
                    } else {
                        // Ensure we have a non null product end time.
                        return $starttime + (365 * DAYSECS);
                    }
                } else {
                    // Product end time is null standing for illimited purchase.
                    return 0;
                }
            }

            // Relative forms of endtime.
            if (is_numeric($handlerdata->actionparams['endtime'])) {
                return $endtime;
            }

            if (preg_match('/\+(\d+))D/',$handlerdata->actionparams['endtime'], $matches)) {
                $days = $matches[1] * DAYSECS;
                $endtime = $starttime + $days;
            }

            if (preg_match('/\+(\d+))H/',$handlerdata->actionparams['endtime'], $matches)) {
                $days = $matches[1] * HOURSECS;
                $endtime = $starttime + $days;
            }
            return $endtime;
            break;
    }
}

/**
* builds a product ref from transation ID and catalogitem code. 
*
*/
function shop_generate_product_ref(&$anItem) {
    global $DB;

    $transactionid = $anItem->transactionid;
    $itemcode = $anItem->catalogitem->itemcode;

    $crypto = md5($transactionid.$itemcode);
    $productref = substr(base64_encode($crypto), 0, 8);

    // continue hashing till we get a real new one
    while ($existing = $DB->record_exists('local_shop_product', array('reference' => $productref))) {
        $productref = substr(base64_encode(md5($productref)), 0, 8);
    }

    return $productref;
}
