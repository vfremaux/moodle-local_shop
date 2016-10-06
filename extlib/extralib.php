<?php
// this file is NOT PART of Moodle and MUST NOT be validated by Moodle validation tools. 
//
// It contains some code writings that may derrogate for a very good reason from 
// standard moodle rules.
//
// The compilation is avoided by pointing this lib directory from 

/**
 * Usually Moodle forbids the use of the eval() function as this might be
 * prone to security breach. This function provides a container bounded possibility of code
 * evaluation with some string limitations in the code fragment so it remains secure.
 *
 * @param string $code a PHP code fragment to evaluate.
 * @param string $inputvars an associative array of input vars. The array will be converted into plain local vars
 * to be used in the evaluated expression
 * @param string $outputvars this is the list of output vars to collect to feedback to the caller. 
 */
function evaluate($code, $inputvars, $outputvars) {

    // Some filters to block attacks. We block roughly function names patterns
    // to external streams. TThis should be probably reinforced in the future.
    if (preg_match('/file|mysql|pg_|write|read|fputs|fgets|exec|passthru|system/', $code)) {
        return false;
    }

    extract($inputvars);

    eval($code);

    $vars = explode(',', $outputvars);

    foreach($vars as $varname) {
        $output[$varname] = @$$varname;
    }

    return $output;
}