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
 *
 * @package    local_shop
 * @category   blocks
 * @author     Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file is a library for production handling. Productino handling occurs
 * when order have been placed (prepay) or after order has been payed out
 * to register product records and trigger some moodle internal actions
 * if required 
 */
require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

function produce_prepay(&$aFullBill) {
    global $CFG, $DB;

    $response = new StdClass;
    $response->public = '';
    $response->private = '';
    $response->salesadmin = '';

    if (!empty($aFullBill->items)) {
        foreach ($aFullBill->items as $anItem) {
            // this refreshes some catalogitem information at production time
            if ($anItem->type != 'BILLING') continue; // pseudo items like discount or shipping. Do not try to produce them
            $catalogitem = $aFullBill->thecatalogue->get_product_by_code($anItem->catalogitem->code);
            $anItem->transactionid = $aFullBill->transactionid;
            $anItem->customer = $aFullBill->customer;
            $anItem->shopid = $aFullBill->theshop->id;

            $handler = $catalogitem->get_handler();

            if ($handler === false) {
                // the handler exists but is disabled.
                shop_trace("[{$aFullBill->transactionid}] Prepay Production : Handler disabled for {$anItem->itemcode}");
                continue;
            }

            if (!is_null($handler)) {
                if (method_exists($handler, 'produce_prepay')) {
                    shop_trace("[{$aFullBill->transactionid}] Prepay Production : preproducing for $anItem->itemcode");
                    if ($itemresponse = $handler->produce_prepay($anItem, $aFullBill->transactionid)) {
                        $response->public .= "<br/>\n".$itemresponse->public;
                        $response->private .= "<br/>\n".$itemresponse->private;
                        $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                    } else {
                        shop_trace("[{$aFullBill->transactionid}] Prepay Production Warning : Empty response $anItem->itemcode");
                    }
                }
            } else {
                shop_trace("[{$aFullBill->transactionid}] Prepay Production Error : No handler for $anItem->itemcode");
            }
        }
    }
    return $response;
}

/*
* production handler that is called after paiement is complete
*/
function produce_postpay(&$aFullBill) {
    global $CFG, $DB;

    $hasworked = false;

    $response = new StdClass();
    $response->public = '';
    $response->private = '';
    $response->salesadmin = '';

    foreach ($aFullBill->items as $anItem) {

        if ($anItem->type != 'BILLING') continue; // pseudo items like discount or shipping. Do not try to produce them
        $catalogitem = $aFullBill->thecatalogue->get_product_by_code($anItem->catalogitem->code);
        $anItem->transactionid = $aFullBill->transactionid;
        $anItem->customer = $aFullBill->customer;

        $handler = $catalogitem->get_handler();

        if ($handler === false) {
            // the handler exists but is disabled.
            shop_trace("[{$aFullBill->transactionid}] Prepay Production : Handler disabled for {$anItem->itemcode}");
            continue;
        }

        if (!is_null($handler)) {
            if (method_exists($handler, 'produce_postpay')) {
                if ($itemresponse = $handler->produce_postpay($anItem)) {
                    $hasworked = true;
                    $response->public .= "<br/>\n".$itemresponse->public;
                    $response->private .= "<br/>\n".$itemresponse->private;
                    $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                } else {
                    shop_trace("[{$aFullBill->transactionid}] Postpay Production Error : empty response fpr {$anItem->itemcode}");
                }
            } else {
                shop_trace("[{$aFullBill->transactionid}] Postpay Production Warning : No handler for $anItem->itemcode");
            }
        } else {
            $e = new Stdclass;
            $e->abstract = $anItem->abstract;
            $e->quantity = $anItem->quantity;
            $response->public .= get_string('defaultpublicmessagepostpay', 'local_shop', $e);
            $response->private .= get_string('defaultprivatemessagepostpay', 'local_shop', $e);
            $response->salesadmin .= get_string('defaultsalesadminmessagepostpay', 'local_shop', $e);
        }
    }

    // set the final COMPLETE status if has worked
    if ($hasworked) {
        // $DB->set_field('local_shop_bill', 'status', 'COMPLETE', array('id' => $aFullBill->id));
    }
    return $response;
}

/**
 * Aggregates production data to the existing stored production data.
 * @param objectref $abill
 * @param object $productiondata a composite object with strings generated by handlers for each user class
 * @param boolean $interactive if true, feeds back production data information in the full bill so following
 * code can print the full updated production track.
 */
function shop_aggregate_production(&$abill, $productiondata, $interactive = false) {
    global $DB;

    $previousdata = (empty($abill->productionfeedback)) ? new StdClass : json_decode(base64_decode($abill->productionfeedback));
    @$previousdata->public .= $productiondata->public;
    @$previousdata->private .= $productiondata->private;
    @$previousdata->salesadmin .= $productiondata->salesadmin;
    $abill->productionfeedback = base64_encode(json_encode($previousdata));
    $abill->save(true);

    // if interactive, we need all productionfeedback accumulated to sync the recorded information so we can print it out to
    // actual user on transaction feedback.
    if ($interactive) @$abill->onlinefeedback = $previousdata;
}

/**
* this runs a similar process than prepay, but only calling unit tests
*
*/
function produce_unittests(&$theShop, &$products, $selected, &$errors, &$warnings, &$messages) {
    global $CFG, $DB;

    foreach ($products as $ci) {
        if (!in_array($ci->code, $selected)) {
            continue;
        }

        $catalogitem = $theShop->thecatalogue->get_product_by_code($ci->code);
        $handler = $catalogitem->get_handler();

        if ($handler === false) {
            // the handler exists but is disabled.
            continue;
        }

        if (!is_null($handler)) {
            $catalogitem->defaultcustomersupportcourse = @$theShop->defaultcustomersupportcourse;

            $catalogitem->actionparams = $catalogitem->handlerparams;

            if (method_exists($handler, 'unit_test')) {
                $handler->unit_test($catalogitem, $errors, $warnings, $messages);
            }

            if (!empty($catalogitem->required)) {
                $decoded = json_decode($catalogitem->required);
                if (!$decoded) {
                    $errors[] = get_string('requiredformaterror', 'local_shop');
                }
            }
        } else {
            $warnings[$catalogitem->code][] = "No handler for $catalogitem->itemcode";
        }
    }
}

/**
 * Generates a secret of some length from a given charset
 */
function produce_generate_secret($length, $charset = '1234567890abcdefghijklmnopqrstuvwxyz') {

    $buf = '';

    for ($i = 0 ; $i < $length ; $i++) {
        $ix = rand(0, strlen($charset) - 1);
        $buf .= $charset[$ix];
        if (($i +1) % 4 == 0) {
            $buf .= '-';
        }
    }

    // Shop out the last hyphen if there is one.
    $buf = preg_replace('/-$/', '', $buf);

    return $buf;
}