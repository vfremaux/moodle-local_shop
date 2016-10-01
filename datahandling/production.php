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
 * @package    local_shop
 * @category   blocks
 * @author     Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This file is a library for production handling. Productino handling occurs
 * when order have been placed (prepay) or after order has been payed out
 * to register product records and trigger some moodle internal actions
 * if required.
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/shop/datahandling/handlercommonlib.php');

function produce_prepay(&$afullbill) {
    global $CFG, $DB;

    $response = new StdClass;
    $response->public = '';
    $response->private = '';
    $response->salesadmin = '';

    if (!empty($afullbill->items)) {
        foreach ($afullbill->items as $anitem) {
            // This refreshes some catalogitem information at production time.
            if ($anitem->type != 'BILLING') {
                // Pseudo items like discount or shipping. Do not try to produce them.
                continue;
            }
            $catalogitem = $afullbill->thecatalogue->get_product_by_code($anitem->catalogitem->code);
            $anitem->transactionid = $afullbill->transactionid;
            $anitem->customer = $afullbill->customer;
            $anitem->shopid = $afullbill->theshop->id;

            $handler = $catalogitem->get_handler();

            if ($handler === false) {
                // The handler exists but is disabled.
                shop_trace("[{$afullbill->transactionid}] Prepay Production : Handler disabled for {$anitem->itemcode}");
                continue;
            }

            if (!is_null($handler)) {
                if (method_exists($handler, 'produce_prepay')) {
                    shop_trace("[{$afullbill->transactionid}] Prepay Production : preproducing for $anitem->itemcode");
                    if ($itemresponse = $handler->produce_prepay($anitem, $afullbill->transactionid)) {
                        $response->public .= "<br/>\n".$itemresponse->public;
                        $response->private .= "<br/>\n".$itemresponse->private;
                        $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                    } else {
                        shop_trace("[{$afullbill->transactionid}] Prepay Production Warning : Empty response $anitem->itemcode");
                    }
                }
            } else {
                shop_trace("[{$afullbill->transactionid}] Prepay Production Error : No handler for $anitem->itemcode");
            }
        }
    }
    return $response;
}

/**
 * Production handler that is called after paiement is complete
 * @param \local_shop\Bill $afullbill
 */
function produce_postpay(&$afullbill) {
    global $CFG, $DB;

    $hasworked = false;

    $response = new StdClass();
    $response->public = '';
    $response->private = '';
    $response->salesadmin = '';

    foreach ($afullbill->items as $anitem) {

        if ($anitem->type != 'BILLING') {
            // Pseudo items like discount or shipping. Do not try to produce them.
            continue;
        }
        $catalogitem = $afullbill->thecatalogue->get_product_by_code($anitem->catalogitem->code);
        $anitem->transactionid = $afullbill->transactionid;
        $anitem->customer = $afullbill->customer;

        $handler = $catalogitem->get_handler();

        if ($handler === false) {
            // The handler exists but is disabled.
            shop_trace("[{$afullbill->transactionid}] Prepay Production : Handler disabled for {$anitem->itemcode}");
            continue;
        }

        if (!is_null($handler)) {
            if (method_exists($handler, 'produce_postpay')) {
                if ($itemresponse = $handler->produce_postpay($anitem)) {
                    $hasworked = true;
                    $response->public .= "<br/>\n".$itemresponse->public;
                    $response->private .= "<br/>\n".$itemresponse->private;
                    $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                } else {
                    shop_trace("[{$afullbill->transactionid}] Postpay Production Error : empty response fpr {$anitem->itemcode}");
                }
            } else {
                shop_trace("[{$afullbill->transactionid}] Postpay Production Warning : No handler for $anitem->itemcode");
            }
        } else {
            $e = new Stdclass;
            $e->abstract = $anitem->abstract;
            $e->quantity = $anitem->quantity;
            $response->public .= get_string('defaultpublicmessagepostpay', 'local_shop', $e);
            $response->private .= get_string('defaultprivatemessagepostpay', 'local_shop', $e);
            $response->salesadmin .= get_string('defaultsalesadminmessagepostpay', 'local_shop', $e);
        }
    }

    // Set the final COMPLETE status if has worked.
    if ($hasworked) {
        assert(true);
        // Set bill status to COMPLETE ? 
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

    /*
     * If interactive, we need all productionfeedback accumulated to sync the recorded information so we can print it out to
     * actual user on transaction feedback.
     */
    if ($interactive) {
        @$abill->onlinefeedback = $previousdata;
    }
}

/**
 * this runs a similar process than prepay, but only calling unit tests
 * @param object &$theshop
 */
function produce_unittests(&$theshop, &$products, $selected, &$errors, &$warnings, &$messages) {
    global $CFG, $DB;

    foreach ($products as $ci) {
        if (!in_array($ci->code, $selected)) {
            continue;
        }

        $catalogitem = $theshop->thecatalogue->get_product_by_code($ci->code);
        $handler = $catalogitem->get_handler();

        if ($handler === false) {
            // The handler exists but is disabled.
            continue;
        }

        if (!is_null($handler)) {
            $catalogitem->defaultcustomersupportcourse = @$theshop->defaultcustomersupportcourse;

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

    for ($i = 0; $i < $length; $i++) {
        $ix = rand(0, strlen($charset) - 1);
        $buf .= $charset[$ix];
        if (($i + 1) % 4 == 0) {
            $buf .= '-';
        }
    }

    // Shop out the last hyphen if there is one.
    $buf = preg_replace('/-$/', '', $buf);

    return $buf;
}