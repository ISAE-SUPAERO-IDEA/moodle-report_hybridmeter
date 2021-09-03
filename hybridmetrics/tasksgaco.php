<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'report_hybridmetrics\task\traitement_regulier.php',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ]
];
