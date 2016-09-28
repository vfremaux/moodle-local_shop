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
 * @category   local
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Read the post from PayPal system and add 'what'.
$req = 'cmd=_notify-synch';

$tx_token = $_GET['tx'];
$auth_token = "GX_sTf5bW3wxRfFEbgofs88nQxvMQ7nsI8m21rzNESnl_79ccFTWj2aPgQ0";
$req .= "&tx=$tx_token&at=$auth_token";

// Post back to PayPal system to validate.

$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);

/*
 * If possible, securely post back to paypal using HTTPS
 * Your PHP server will need to be SSL enabled
 * $fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);
 */

if (!$fp) {
    // HTTP ERROR
} else {
    fputs ($fp, $header . $req);
    // Read the body data.
    $res = '';
    $headerdone = false;
    while (!feof($fp)) {
        $line = fgets ($fp, 1024);
        if (strcmp($line, "\r\n") == 0) {
            // read the header
            $headerdone = true;
        } else if ($headerdone) {
            // Header has been read. now read the contents.
            $res .= $line;
        }
    }
    
    // parse the data
    $lines = explode("\n", $res);
    $keyarray = array();
    if (strcmp ($lines[0], "SUCCESS") == 0) {
        for ($i = 1; $i < count($lines); $i++) {
            list($key, $val) = explode("=", $lines[$i]);
            $keyarray[urldecode($key)] = urldecode($val);
        }
        /* check the payment_status is Completed
         * check that txn_id has not been previously processed
         * check that receiver_email is your Primary PayPal email
         * check that payment_amount/payment_currency are correct
         * process payment
         */
        $firstname = $keyarray['first_name'];
        $lastname = $keyarray['last_name'];
        $itemname = $keyarray['item_name'];
        $amount = $keyarray['payment_gross'];
    
        echo ("<p><h3>Thank you for your purchase!</h3></p>");
        
        echo ("<b>Payment Details</b><br>\n");
        echo ("<li>Name: $firstname $lastname</li>\n");
        echo ("<li>Item: $itemname</li>\n");
        echo ("<li>Amount: $amount</li>\n");
        echo ("");
    } else if (strcmp ($lines[0], "FAIL") == 0) {
        assert(true);
        // Log for manual investigation.
    }

}

fclose ($fp);

?>

Your transaction has been completed, and a receipt for your purchase has been emailed to you.<br> You may log into your account at <a href='https://www.paypal.com'>www.paypal.com</a> to view details of this transaction.<br>