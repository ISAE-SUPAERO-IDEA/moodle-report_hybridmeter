<?php
defined('MOODLE_INTERNAL') || die();
$tasks = array(  
     array(
          'classname' => 'report_hybridmetrics\task\traitement_regulier', 
          'blocking' => 0,
          'minute' => '0',
          'hour' => '0',
          'day' => '*',
          'dayofweek' => '*', 
          'month' => '*'
     )
);
?>