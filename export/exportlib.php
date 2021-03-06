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

abstract class shop_export {

    protected $filename;

    protected $data;

    protected $datadesc;

    /**
     * array of export options
     * 'addtimestamp' => 0/1
     */
    protected $options;

    public function __construct($data, $datadesc, $options) {

        $this->filename = $datadesc[0]['filename'];
        $this->data = $data;
        $this->datadesc = $datadesc;
        $this->options = $options;

        if (!empty($options['addtimestamp'])) {
            $parts = pathinfo($this->filename);
            $this->filename = $parts['filename'].'-'.date('Ymdhi', time()).'.'.$parts['extension'];
        }

        if (empty($this->datadesc[0]['purpose'])) {
            $this->datadesc[0]['purpose'] = 'default';
        }
    }

    abstract public function open_export();

    abstract public function render();

    abstract public function close_export();
}