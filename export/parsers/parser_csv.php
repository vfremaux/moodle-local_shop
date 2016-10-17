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
 * Controller for the customer screen responses.
 *
 * @package     local_shop
 * @categroy    local
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

class parser_csv {

    protected $file;

    protected $linedelimiter;

    protected $fielddelimiter;

    protected $encoding;

    protected $columnmap = null;

    /**
     * Headers found in the csv file.
     */
    protected $headers;

    /**
     * Required fields. Fields in this array must be found in the header.
     */
    protected $required;

    /**
     * Patterns fields. Fields matching this patterns are legitimate in header.
     * Patternized fields are f.E. numerically indexed fields as f1, f2, f3 ...
     */
    protected $patterns;

    /**
     * Meta fields are fields that may match a known prefix
     */
    protected $metas;

    /**
     * Optional fields. Fields in this array are legitimate to appear, but not mandatory.
     */
    protected $optionals;

    /**
     * Optional defaults. Gives default values for missing optional fields.
     */
    protected $optionaldefaults;

    public function __construct($filenameorrec, $linedelimiter = "\n", $fielddelimiter = ":", $encoding = 'UTF-8') {
        global $CFG;

        $this->linedelimiter = $linedelimiter;
        $this->fielddelimiter = $fielddelimiter;
        $this->encoding = $encoding;

        if (is_object($filenameorrec)) {
            $filepath = $CFG->dataroot.'/filestore/';
            // TODO : compute real file path.
        } else {
            $filepath = $filenameorrec;
        }

        if ($this->file = fopen($filepath, 'r')) {
            throw new Exception();
        }

        $required = array();
        $patterns = array();
        $metas = array();
        $optionals = array();
        $optionaldefaults = array();
    }

    /**
     *
     */
    public function set_required($required) {
        $this->required = $required;
    }

    public function set_patterns($patterns) {
        $this->patterns = $patterns;
    }

    public function set_metas($metas) {
        $this->metas = $metas;
    }

    public function set_optionals($optionals) {
        $this->optionals = $optionals;
    }

    public function set_optionaldefaults($optionaldefaults) {
        $this->optionaldefaults = $optionaldefaults;
    }

    /**
     * Set an eventual column mapping to map input columns
     * to object member names.
     */
    public function set_column_mapping($columnmap) {
        $this->columnmap = $columnmap;
    }

    public function parse() {

        // Get headers.
        $this->headers = $this->next();

        while ($line = $this->next()) {
            $linearr = explode($this->fielddelimiter, $line);
            $results[] = (object) array_combine($this->headers, $linearr);
        }

        return $results;
    }

    /**
     * Reads next non empty line.
     */
    protected function next() {

        if (empty($this->file)) {
            throw new Exception('CSV file was not opened');
        }

        $line = fgets($this->file, 1024);

        while ($this->is_empty_line_or_format($line)) {
            $line = fgets($this->file, 1024);
        }

        return $line;
    }

    protected function check_headers() {

        /*
         * Prepare the required markers from a scalar array to an associative array.
         */
        $required = array();
        foreach ($this->required as $r) {
            $required[$r] = 1;
        }
        $this->required = $required;

        /*
         * Prepare the required markers from a scalar array to an associative array.
         */
        $optionals = array();
        foreach ($this->optionals as $r) {
            $optionals[$r] = 1;
        }
        $this->optionals = $optionals;

        // Check for valid field names.
        foreach ($this->headers as $h) {
            $header[] = trim($h);

            $patternized = implode('|', $this->patterns)."\\d+";
            $metapattern = implode('|', $this->metas);

            if (!(isset($this->required[$h]) ||
                    isset($this->optionaldefaults[$h]) ||
                            isset($this->optionals[$h]) ||
                                    preg_match("/{$patternized}/", $h) ||
                                            preg_match("/{$metapattern}/", $h))) {
                // If the header is not present in any of the definitions.
                throw new Exception ("Required field missing : $h");
            }

            if (isset($this->required[$h])) {
                $this->required[$h] = 0;
            }
        }

        // Check for required fields.
        foreach ($this->required as $key => $value) {
            if ($value) {
                // Required field missing.
                throw new Exception ("Required field missing : $key");
            }
        }

        return true;
    }

    /**
     * Check a CSV input line format for empty or commented lines
     * Ensures compatbility to UTF-8 BOM or unBOM formats
     */
    protected function is_empty_line_or_format(&$text, $resetfirst = false) {
        static $textlib;
        static $first = true;

        // We may have a risk the BOM is present on first line.
        if ($resetfirst) {
            $first = true;
        }

        if (!isset($textlib)) {
            $textlib = new core_text();
        }

        if ($first && $this->encoding == 'UTF-8') {
            $text = $textlib->trim_utf8_bom($text);
            $first = false;
        }

        $text = preg_replace("/\n?\r?/", '', $text);

        if ($config->encoding != 'UTF-8') {
            $text = utf8_encode($text);
        }

        return preg_match('/^$/', $text) || preg_match('/^(\(|\[|-|#|\/| )/', $text);
    }

    public function __destruct() {
        if ($this->file) {
            fclose($this->file);
        }
    }
}
