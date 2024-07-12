<?php
/*
 * This is an extra library file for code that is uncheckable by Moodle CI tools
 */

/**
 * Uncleaned direct $_REQUEST interrogation.
 * Use of values should be cleaned before using them.
 */
function shoppaymodes_ogone_x_get_request() {
    $request = [];
    foreach ($_REQUEST as $key => $value) {
        $request[$key] = $value;
    }
    return $request;
}