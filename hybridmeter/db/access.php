<?php

defined('MOODLE_INTERNAL') || die();

/*$capabilities = array(
    'report/hybridmeter:view' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    ),
    'report/hybridmeter:download' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    ),
    'report/hybridmeter:set_up' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    )
);*/

$capabilities = array(
    'report/hybridmeter:all' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'contextlevel' => CONTEXT_MODULE,
    )
);