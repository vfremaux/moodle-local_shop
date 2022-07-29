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

            // Aggregates responses for each product.
            product_prepay_item($anitem, $catalogitem, $afullbill, $response);
        }
    }
    return $response;
}

/**
 *
 *
 */
function product_prepay_item(&$anitem, &$catalogitem, &$afullbill, &$response) {

    if ($catalogitem->isset == PRODUCT_BUNDLE) {
        shop_trace("[{$afullbill->transactionid}] Prepay Production : Scanning subelements");
        foreach ($catalogitem->elements as $element) {
            $elementcatalogitem = $afullbill->thecatalogue->get_product_by_code($element->code);
            $element->transactionid = $afullbill->transactionid;
            $element->customer = $afullbill->customer;
            $element->shopid = $afullbill->theshop->id;

            $billrec = new StdClass;
            $billrec->billid = $afullbill->id;
            $billrec->type = 'BILLING';
            $billrec->itemcode = $element->code;
            $billrec->abstract = $element->name;
            $billrec->description = format_text($element->description, $element->descriptionformat);
            $billrec->delay = 0;
            $billrec->unitcost = $element->get_price($anitem->quantity);
            $billrec->quantity = $anitem->quantity;
            $billrec->totalprice = $billrec->unitcost * $billrec->quantity;
            $billrec->taxcode = $element->taxcode;
            $billrec->bundleid = $catalogitem->id;
            $billrec->customerdata = ''; // TODO get effective production data for the bundle elements...
            $billrec->productiondata = $element->productiondata; // TODO get effective production data for the bundle elements...

            $fakebillitem = new \local_shop\BillItem($billrec, false, ['bill' => $afullbill], -1, true);
            $fakebillitem->id = $anitem->id; // To give to initialbillitemid and currentbillitemid.
            $fakebillitem->actionparams = $element->get_handler_params(); // TODO get effective production data for the bundle elements...
            $fakebillitem->transactionid = $anitem->transactionid;
            $response->public .= "<br/>\nID: ".$catalogitem->code.':'.$element->code;
            $response->private .= "<br/>\nID:".$catalogitem->code.':'.$element->code;
            $response->salesadmin .= "<br/>\nID:".$catalogitem->code.':'.$element->code;
            product_prepay_item($fakebillitem, $elementcatalogitem, $afullbill, $response);

            // If one product returns with error state, mark whole bundle as errored.
            if (!empty($fakebillitem->error)) {
                $anitem->error = true;
            }
        }
    } else {
        $response->public .= "<br/>\nID: ".$catalogitem->code;
        $response->private .= "<br/>\nID:".$catalogitem->code;
        $response->salesadmin .= "<br/>\nID:".$catalogitem->code;
    }

    $handler = $catalogitem->get_handler();

    if ($handler === false) {
        // The handler exists but is disabled.
        shop_trace("[{$afullbill->transactionid}] Prepay Production : Handler disabled for {$anitem->itemcode}");
        return;
    }

    if (!is_null($handler)) {
        if (method_exists($handler, 'produce_prepay')) {
            shop_trace("[{$afullbill->transactionid}] Prepay Production : preproducing for {$anitem->itemcode}");
            $errorstatus = false;
            if ($itemresponse = $handler->produce_prepay($anitem, $errorstatus)) {
                if (!$errorstatus) {
                    $response->public .= "<br/>\n".$itemresponse->public;
                    $response->private .= "<br/>\n".$itemresponse->private;
                    $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
                } else {
                    $message = "[{$afullbill->transactionid}] Prepay Production Error : ";
                    $message .= "Failure preproducing {$anitem->itemcode}. Mark product as aborted.";
                    $anitem->error = true;
                    shop_trace($message);
                }
            } else {
                $message = "[{$afullbill->transactionid}] Prepay Production Warning : ";
                $message .= 'Empty response {$anitem->itemcode}';
                shop_trace($message);
            }
        }
    } else {
        shop_trace("[{$afullbill->transactionid}] Prepay Production Error : No handler for $anitem->itemcode");
    }
}

/**
 * Production handler that is called after paiement is complete
 * @param \local_shop\Bill $afullbill
 */
function produce_postpay(&$afullbill) {

    $hasworked = false;

    $response = new StdClass();
    $response->public = '';
    $response->private = '';
    $response->salesadmin = '';

    if (empty($afullbill->items)) {
        return;
    }

    foreach ($afullbill->items as $anitem) {

        if ($anitem->type != 'BILLING') {
            // Pseudo items like discount or shipping. Do not try to produce them.
            continue;
        }
        $catalogitem = $afullbill->thecatalogue->get_product_by_code($anitem->catalogitem->code);
        $anitem->transactionid = $afullbill->transactionid;
        $anitem->customer = $afullbill->customer;

        product_postpay_item($anitem, $catalogitem, $afullbill, $response);

    }

    // Set the final COMPLETE status if has worked.
    if ($hasworked) {
        assert(true);
        // Set bill status to COMPLETE ?
    }
    return $response;
}

function product_postpay_item(&$anitem, &$catalogitem, &$afullbill, &$response) {

    // Recurse in elements when a bundle.
    if ($catalogitem->isset == PRODUCT_BUNDLE) {
        shop_trace("[{$afullbill->transactionid}] Postpay Production : Scanning subelements");
        foreach ($catalogitem->elements as $element) {
            $elementcatalogitem = $afullbill->thecatalogue->get_product_by_code($element->code);

            $billrec = new StdClass;

            $billrec->transactionid = $afullbill->transactionid;
            $billrec->customer = $afullbill->customer;
            $billrec->shopid = $afullbill->theshop->id;

            $billrec->billid = $afullbill->id;
            $billrec->type = 'BILLING';
            $billrec->itemcode = $element->code;
            $billrec->abstract = $element->name;
            $billrec->description = format_text($element->description, $element->descriptionformat);
            $billrec->delay = 0;
            $billrec->unitcost = $element->get_price($anitem->quantity);
            $billrec->quantity = $anitem->quantity;
            $billrec->totalprice = $billrec->unitcost * $billrec->quantity;
            $billrec->taxcode = $element->taxcode;
            $billrec->bundleid = $catalogitem->id;
            $billrec->customerdata = ''; // TODO get effective production data for the bundle elements...
            $billrec->productiondata = $element->productiondata; // TODO get effective production data for the bundle elements...

            $fakebillitem = new \local_shop\BillItem($billrec, false, ['bill' => $afullbill], -1, true);
            $fakebillitem->id = $anitem->id; // To give to initialbillitemid and currentbillitemid.
            $fakebillitem->actionparams = $element->get_handler_params(); // TODO get effective production data for the bundle elements...
            $fakebillitem->transactionid = $anitem->transactionid;
            product_postpay_item($fakebillitem, $elementcatalogitem, $afullbill, $response);
        }
    }

    $handler = $catalogitem->get_handler();

    if ($handler === false) {
        // The handler exists but is disabled.
        shop_trace("[{$afullbill->transactionid}] Postpay Production : Handler disabled for {$anitem->itemcode}");
        return;
    }

    if (!is_null($handler)) {
        if (method_exists($handler, 'produce_postpay')) {
            shop_trace("[{$afullbill->transactionid}] Postpay Production : producing for {$anitem->itemcode}");
            if ($itemresponse = $handler->produce_postpay($anitem)) {
                $hasworked = true;
                $response->public .= "<br/>\n".$itemresponse->public;
                $response->private .= "<br/>\n".$itemresponse->private;
                $response->salesadmin .= "<br/>\n".$itemresponse->salesadmin;
            } else {
                shop_trace("[{$afullbill->transactionid}] Postpay Production Error : empty response for {$anitem->itemcode}");
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

/**
 * Aggregates production data to the existing stored production data.
 * @param objectref $abill
 * @param object $productiondata a composite object with strings generated by handlers for each user class
 * @param boolean $interactive if true, feeds back production data information in the full bill so following
 * code can print the full updated production track.
 */
function shop_aggregate_production(&$abill, $productiondata, $interactive = false) {

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

            $requireddata = $catalogitem->requireddata;
            if (!empty($requireddata)) {
                $decoded = json_decode($requireddata);
                if (!$decoded) {
                    $errors[$catalogitem->code][] = get_string('requiredformaterror', 'local_shop');
                } else {
                    $messages[$catalogitem->code][] = get_string('requiredformatsuccess', 'local_shop');
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