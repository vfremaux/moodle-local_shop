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

$test = array(
        array('field' => 'the_field_name',
              'label' => 'some visible name',
              'type' => 'textfield',
              'desc' => 'some desc',
              'attrs' => array('size' => 80)),
         array('field' => 'description_sample',
               'label' => 'Description (sample)',
               'type' => 'textarea',
               'desc' => 'Short Description (sample)'),
         array('name' => 'template_sample',
               'label' => 'Model (sample)',
               'type' => 'select',
               'desc' => 'Course template (sample)',
               'options' => array('MOD1' => 'Model1', 'MOD2' => 'Model2')));

echo "JSON\n <br/>";
echo json_encode($test);
echo '<br/>';
echo "Serialize\n <br/>";
echo serialize($test);