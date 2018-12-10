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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/auth/ticket/lib.php');
require_once($CFG->dirroot.'/local/shop/lib.php');
require_once($CFG->dirroot.'/local/shop/classes/CatalogItem.class.php');
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

if (!defined('PHP_ROUND_HALF_EVEN')) {
    define('PHP_ROUND_HALF_EVEN', 3);
}
if (!defined('PHP_ROUND_HALF_ODD')) {
    define('PHP_ROUND_HALF_ODD', 3);
}

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
 *  but a formula, the formula is evaluated using $HT as unit untaxed price, $TTC ad unit taxed
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
 * @param string $country the country of the submitter
 * @param string $zipcode the zipcode of the submitter
 * @param string $rule a [countrylist][zipcodeslist] rule
 * @return true if the submitter matches
 */
function shop_resolve_zone_rule($country, $zipcode, $rule) {

    if (preg_match('/\\(\\[(.*?)\\]\\[(.*?)\\]\\)/', $rule, $matches)) {
        $countries = strtolower($matches[1]);
        $zipcodes = $matches[2];
        $country = strtolower($country); // Ensure we have no issues with case.
        if ($countries != '*') {
            if (!preg_match("/\\b$country\\b/", $countries)) {
                return false;
            }
        }

        if ($zipcodes != '*') {
            $ziprules = explode(',', $zipcodes);
            foreach ($ziprules as $ru) {
                if (preg_match("/{$ru}/", $zipcode)) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }
    return false;
}

/**
 * Validates customer information from the session stored shoppingcart. checks if every data
 * is receivable.
 * @param object $theshop the current shop to get settings from.
 */
function shop_validate_customer($theshop) {
    global $SESSION, $CFG, $USER;

    $shoppingcart = $SESSION->shoppingcart;

    if (!isset($shoppingcart->errors) || !is_object($shoppingcart->errors)) {
        $shoppingcart->errors = new StdClass;
    }
    $shoppingcart->errors->customerinfo = array();

    if ($shoppingcart->customerinfo['email'] == '') {
        $shoppingcart->errors->customerinfo['custommerinfo::email'] = '';
    }

    if ((!isloggedin() || isguestuser()) && shop_has_potential_account($shoppingcart->customerinfo['email'])) {
        $params = array('view' => 'customer', 'id' => $theshop->id, 'what' => 'revalidate');
        $SESSION->wantsurl = new moodle_url('/local/shop/front/view.php', $params);
        $a = new StdClass();
        $a->wwwroot = $CFG->wwwroot;
        $shoppingcart->errors->customerinfo['customerinfo::mail'] = get_string('existingmailpleaselogin', 'local_shop', $a);
    } else if ((isloggedin() && !isguestuser()) && ($USER->email == $shoppingcart->customerinfo['email'])) {
        /*
         * unmark that mail if mail matches (that is the logged user IS the customer user based on mail)
         * @see revalidate command in customer.controller.php
         */
        unset($shoppingcart->errors->customerinfo['customerinfo::mail']);
    }

    if ($shoppingcart->customerinfo['lastname'] == '') {
        $shoppingcart->errors->customerinfo['customerinfo::lastname'] = get_string('emptyfieldlastname', 'local_shop');
    }

    if ($shoppingcart->customerinfo['firstname'] == '') {
        $shoppingcart->errors->customerinfo['customerinfo::firstname'] = get_string('emptyfieldfirstname', 'local_shop');
    }

    if ($shoppingcart->customerinfo['city'] == '') {
        $shoppingcart->errors->customerinfo['customerinfo::city'] = get_string('emptyfieldcity', 'local_shop');
    }

    // We just require it if we are billing to the user.
    if (!$shoppingcart->usedistinctinvoiceinfo) {
        if ($shoppingcart->customerinfo['address'] == '') {
            $shoppingcart->errors->customerinfo['customerinfo::address'] = get_string('emptyfieldaddress', 'local_shop');
        }

        if ($shoppingcart->customerinfo['zip'] == '') {
            $shoppingcart->errors->customerinfo['customerinfo::zip'] = get_string('emptyfieldzip', 'local_shop');
        }
    }
}

/**
 * Checks if the customer as a potential account match.
 * @param string $email
 * @return boolean
 */
function shop_has_potential_account($email) {
    global $DB;

    if ($DB->record_exists('user', array('email' => $email, 'deleted' => 0))) {
        return true;
    }

    /*
     * User account should not be confirmed by a purchase, i.e. associated to and internal
     * moodle account by a purchase.
     */
    $select = " email = ? AND hasaccount > 0 ";
    $potentialcustomer = $DB->get_record_select('local_shop_customer', $select, array($email));
    if ($potentialcustomer) {
        return true;
    }

    return false;
}

/**
 * Validates invocing customer information
 */
function shop_validate_invoicing() {
    global $SESSION;

    $shoppingcart = $SESSION->shoppingcart;

    if (!isset($shoppingcart->errors)) {
         $shoppingcart->errors = new StdClass;
    }
    $shoppingcart->errors->invoiceinfo = array();

    if ($shoppingcart->invoiceinfo['organisation'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::organisation'] = '';
    }

    if ($shoppingcart->invoiceinfo['address'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::address'] = '';
    }

    if ($shoppingcart->invoiceinfo['zip'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::zip'] = '';
    }

    if ($shoppingcart->invoiceinfo['lastname'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::lastname'] = '';
    }

    if ($shoppingcart->invoiceinfo['firstname'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::firstname'] = '';
    }

    if ($shoppingcart->invoiceinfo['country'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::country'] = '';
    }

    if ($shoppingcart->invoiceinfo['city'] == '') {
        $shoppingcart->errors->invoiceinfo['invoiceinfo::city'] = '';
    }
}

/**
 * checks purchased products and quantities and calculates the neaded amount of seats.
 * We need check in catalog definition id product is seat driven or not. If seat driven
 * the quantity adds to seat couts. If not, 1 seat is added to the seat count.
 * @param array $resuiredroles
 */
function shop_check_assigned_seats($requiredroles) {
    global $SESSION;

    $assigned = 0;

    if (!isset($SESSION->shoppingcart)) {
        return 0;
    }

    if ($requiredroles && !empty($SESSION->shoppingcart->users)) {
        foreach (array_values($SESSION->shoppingcart->users) as $roleassigns) {
            foreach (array_values($roleassigns) as $participants) {
                $assigned += count($participants);
            }
        }
    }

    return $assigned;
}

/**
 * Provides a full built instance of a payment plugin initialized
 * on a shop reference.
 * @param objectref &$shopinstance a Shop instance
 * @param string $pluginname the payment plugin name
 * @return a shop_handler subclass instance.
 */
function shop_get_payment_plugin(&$shopinstance, $pluginname = null) {
    global $CFG, $SESSION;

    if (is_null($pluginname)) {
        $pluginname = $SESSION->shoppingcart->paymode;
    }

    $paymentclass = 'shop_paymode_'.$pluginname;
    include_once($CFG->dirroot.'/local/shop/paymodes/'.$pluginname.'/'.$pluginname.'.class.php');
    return new $paymentclass($shopinstance);
}

/**
 * computes and defaults enrolement start and end time, against some local course
 * startdata constraint.
 * @param arrayref &$handlerdata a complete parameter set for the product based on a billitem object.
 * @param string $fieldtoreturn 'starttime' or 'endtime'
 * @param objectref &$course a reference course
 */
function shop_compute_enrol_time(&$handlerdata, $fieldtoreturn, &$course) {

    $starttime = (empty($handlerdata->actionparams['starttime'])) ? time() : $handlerdata->actionparams['starttime'];
    if ($course->startdate > $starttime) {
        $starttime = $course->startdate;
    }

    switch ($fieldtoreturn) {
        case 'starttime':
            return $starttime;
            break;

        case 'endtime':
            if (!array_key_exists('endtime', $handlerdata->actionparams)) {
                // Do NOT use empty here for testing as results comes from a magic __get()!
                if ($handlerdata->catalogitem->renewable == 1) {
                    /*
                     * Note that renewable products MUST have an end time, either given
                     * by an explicit endtime timestamp, or a duration value.
                     * In case none of definitions are available, we default with a
                     * 1 year duration.
                     */
                    if (!empty($handlerdata->actionparams['duration'])) {
                        $endtime = $starttime + $handlerdata->actionparams['duration'] * DAYSECS;
                        return $endtime;
                    } else {
                        // Ensure we have a non null product end time.
                        $endtime = $starttime + (365 * DAYSECS);
                        return $endtime;
                    }
                } else {
                    // Product end time is null standing for illimited purchase.
                    return 0;
                }
            }

            // Relative forms of endtime. This applies to renewable or not renewable products.
            if (is_numeric($handlerdata->actionparams['endtime'])) {
                return $handlerdata->actionparams['endtime'];
            }

            if (preg_match('/\+(\d+))D/', $handlerdata->actionparams['endtime'], $matches)) {
                $days = $matches[1] * DAYSECS;
                $endtime = $starttime + $days;
            }

            if (preg_match('/\+(\d+))H/', $handlerdata->actionparams['endtime'], $matches)) {
                $days = $matches[1] * HOURSECS;
                $endtime = $starttime + $days;
            }
            return $endtime;
            break;
    }
}

/**
 * builds a product ref from transaction ID and catalogitem code.
 * Product refs are build with a unique context based 14 letters code, and
 * a 2 letters checksum completing a WWWW-XXXX-YYYY-ZZCC code.
 * @param object &$anitem a catalog item instance
 */
function shop_generate_product_ref(&$anitem) {
    global $DB;

    $transactionid = $anitem->transactionid;
    $itemcode = $anitem->catalogitem->itemcode;

    $crypto = md5($transactionid.$itemcode);
    $productref = core_text::strtoupper(core_text::substr(base64_encode($crypto), 0, 14));
    $productref .= shop_checksum($productref);

    // Adopt standard WWWW-XXXX-YYYY-ZZZZ form.
    $tmp = $productref;
    $productref = core_text::substr($tmp, 0, 4).'-'.core_text::substr($tmp, 4, 4);
    $productref .= '-'.core_text::substr($tmp, 8, 4).'-'.core_text::substr($tmp, 12, 4);

    // Continue hashing till we get a real new one.
    while ($DB->record_exists('local_shop_product', array('reference' => $productref))) {
        $productref = core_text::strtoupper(core_text::substr(base64_encode(md5($productref)), 0, 14));
        $productref .= shop_checksum($productref);

        // Addopt standard WWWW-XXXX-YYYY-ZZZZ form.
        $tmp = $productref;
        $productref = core_text::substr($tmp, 0, 4).'-'.core_text::substr($tmp, 4, 4);
        $productref .= '-'.core_text::substr($tmp, 8, 4).'-'.core_text::substr($tmp, 12, 4);
    }

    return $productref;
}

/**
 * builds a product ref from transation ID and catalogitem code.
 * @param object &$anitem a catalog item instance
 */
function shop_check_product_ref($productref) {

    if (empty($productref)) {
        return false;
    }

    // Strip out dashes.
    $productref = str_replace('-', '', $productref);

    // Get the payload.
    $payload = substr($productref, 0, 14);
    $crc = substr($productref, 14, 2);

    $checkcrc = shop_checksum($payload);

    return $checkcrc == $crc;
}

function shop_checksum($productref) {

    static $crcrange = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
    'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

    $crccount =  count($crcrange);

    // Adding 2 letters checksum.
    $productrefasarr = str_split($productref);
    $crc = 0;
    foreach ($productrefasarr as $letter) {
        $crc += ord($letter);
    }

    $crc2 = floor($crc / $crccount) % $crccount;
    $crc1 = $crc % $crccount;
    return $crcrange[$crc1].$crcrange[$crc2];
}