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

require_once($CFG->dirroot.'/local/shop/export/exportlib.php');

class shop_export_excel extends shop_export {

    protected $workbook = array();

    protected $worksheets = array();

    protected $xlsformats = array();

    /**
     *
     */
    public function open_export() {
        global $CFG;

        require_once($CFG->libdir.'/excellib.class.php');

        // Generate XLS.

        $this->workbook = new MoodleExcelWorkbook("-");
        if (!$this->workbook) {
            die("Null workbook");
        }

        // Sending HTTP headers.
        ob_end_clean();
        $this->workbook->send($this->filename);

        if (@$CFG->latinexcelexport) {
            $sheettitle = mb_convert_encoding($this->datadesc[0]['title'], 'ISO-8859-1', 'UTF-8');
        } else {
            $sheettitle = $this->datadesc[0]['title'];
        }

        $this->worksheets[0] = $this->workbook->add_worksheet($sheettitle);

        if ($this->datadesc[0]['purpose'] == 'default') {

            $i = 0;
            foreach ($this->datadesc[0]['columns'] as $col) {
                if ($col['width'] == 0)  {
                    continue;
                }
                $this->worksheets[0]->set_column($i, $i, $col['width']);
                $i++;
            }
        }

        $this->setup_xls_formats();
    }

    /**
     * a raster for xls printing of a report structure header
     * with all the relevant data about a user.
     *
     */
    protected function print_header() {
        global $OUTPUT;

        $i = 0;

        if (empty($this->datadesc[0]['columns'])) {
            echo $OUTPUT->notification(get_string('nocolumns', 'local_shop'));
        }

        foreach ($this->datadesc[0]['columns'] as $col) {
            if ($col['width'] == 0) {
                continue;
            }
            if (@$CFG->latinexcelexport) {
                $coltitle = mb_convert_encoding(get_string('export'.$col['name'], 'local_shop'), 'ISO-8859-1', 'UTF-8');
            } else {
                $coltitle = get_string('export'.$col['name'], 'local_shop');
            }

            $text = get_string('export'.$col['name'], 'local_shop');
            $this->worksheets[0]->write_string(0, $i, $text, $this->xls_formats[$this->datadesc[0]['colheadingformat']]);
            $i++;
        }
    }

    /**
     * a raster for xls printing of a data table
     * with all the relevant data about a user.
     *
     */
    protected function print_data() {

        $row = 1;

        if (empty($this->data[0])) {
            return;
        }

        if (empty($this->datadesc[0]['columns'])) {
            return;
        }

        foreach ($this->data[0] as $rowid => $datarow) {
            $i = 0;
            $dataarr = (array)$datarow;
            foreach ($this->datadesc[0]['columns'] as $col) {
                $isnumber = false;
                if ($col['width'] == 0) {
                    continue;
                }
                if ($col['format'] == 'float') {
                    $isnumber = true;
                    $col['format'] = 'smalltext';
                }
                if ($col['format'] == 'date') {
                    $isnumber = false;
                    if ($dataarr[$col['name']]) {
                        $dataarr[$col['name']] = date('Y/m/d h:m', $dataarr[$col['name']]);
                    }
                }
                if ($col['format'] == 'time') {
                    if ($dataarr[$col['name']]) {
                        $dataarr[$col['name']] = userdate($dataarr[$col['name']]);
                    } else {
                        $dataarr[$col['name']] = '---';
                    }
                }
                if ($isnumber) {
                    $this->worksheets[0]->write_number($row, $i, $dataarr[$col['name']], $this->xls_formats[$col['format']]);
                } else {
                    $this->worksheets[0]->write_string($row, $i, $dataarr[$col['name']], $this->xls_formats[$col['format']]);
                }
                $i++;
            }
            $row++;
        }
    }

    /**
     *
     *
     */
    public function render() {
        $this->print_header();
        $this->print_data();
    }

    /**
     * Terminates all operations
     *
     */
    public function close_export() {
        $this->workbook->close();
    }

    /**
     * sets up a set fo formats
     * @param object $workbook
     * @return array of usable formats keyed by a label
     *
     */
    public function setup_xls_formats() {

        if (!$this->workbook) {
            print_error('errorexcelcreation', 'local_shop');
        }

        $formats = array();

        $formats['title'] = $this->workbook->add_format();
        $formats['title']->set_size(20);

        $formats['section'] = $this->workbook->add_format();
        $formats['section']->set_size(10);
        $formats['section']->set_color(1);
        $formats['section']->set_fg_color(4);
        $formats['section']->set_bold(1);

        $formats['bold'] = $this->workbook->add_format();
        $formats['bold']->set_bold(1);

        // Normal text.
        $formats['largetext'] = $this->workbook->add_format();
        $formats['largetext']->set_size(14);

        $formats['mediumtext'] = $this->workbook->add_format();
        $formats['mediumtext']->set_size(12);

        $formats['smalltext'] = $this->workbook->add_format();
        $formats['smalltext']->set_size(9);

        $formats['time'] = $this->workbook->add_format();
        $formats['time']->set_size(9);
        $formats['time']->set_num_format('[h]:mm:ss');

        $formats['date'] = $this->workbook->add_format();
        $formats['date']->set_size(9);
        $formats['date']->set_num_format('aaaa/mm/dd hh:mm');

        $this->xlsformats = $formats;
    }
}