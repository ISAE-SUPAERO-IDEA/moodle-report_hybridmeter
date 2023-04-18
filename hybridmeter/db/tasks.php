<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'report_hybridmeter\task\cron_scheduler',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '0',
    ],
];