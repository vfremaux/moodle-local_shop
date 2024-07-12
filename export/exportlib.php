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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2017 Valery Fremaux <valery.fremaux@gmail.com> (activeprolearn.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

abstract class shop_export {

    protected $filename;

    protected $data;

    protected $datadesc;

    /**
     * array of export options
     * 'addtimestamp' => 0/1
     */
    protected $options;

    /**
     * Constructor
     * @param object $data
     * @param array $datadesc
     * @param array $options
     */
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

    /**
     * Open export storage.
     */
    abstract public function open_export();

    /**
     * Render data in export.
     */
    abstract public function render();

    /**
     * Render close export storage.
     */
    abstract public function close_export();
}
