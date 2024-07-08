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
 * Json checker
 *
 * @package   local_shop
 * @author    Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');

$test = [
    [
        'field' => 'the_field_name',
        'label' => 'some visible name',
        'type' => 'textfield',
        'desc' => 'some desc',
        'attrs' => ['size' => 80],
    ],
    [
        'field' => 'description_sample',
        'label' => 'Description (sample)',
        'type' => 'textarea',
        'desc' => 'Short Description (sample)',
    ],
    [
        'name' => 'template_sample',
        'label' => 'Model (sample)',
        'type' => 'select',
        'desc' => 'Course template (sample)',
        'options' => ['MOD1' => 'Model1', 'MOD2' => 'Model2'],
    ],
];

echo "JSON\n <br/>";
echo json_encode($test);
echo '<br/>';
echo "Serialize\n <br/>";
echo serialize($test);