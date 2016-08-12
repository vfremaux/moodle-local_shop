<?php

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