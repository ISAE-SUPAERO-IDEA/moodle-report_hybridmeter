<?php

defined('MOODLE_INTERNAL') || die();

/*$capabilities = array(
	'report/hybridmetrics:view' => array(
		'riskbitmask' => RISK_PERSONAL,
		'captype' => 'read',
		'archetypes' => array(
			'manager' => CAP_ALLOW
		),
		'contextlevel' => CONTEXT_MODULE
	),
	'report/hybridmetrics:download' => array(
		'riskbitmask' => RISK_PERSONAL,
		'captype' => 'read',
		'archetypes' => array(
			'manager' => CAP_ALLOW
		),
		'contextlevel' => CONTEXT_MODULE
	),
	'report/hybridmetrics:set_up' => array(
		'riskbitmask' => RISK_SPAM,
		'captype' => 'write',
		'archetypes' => array(
			'manager' => CAP_ALLOW
		),
		'contextlevel' => CONTEXT_MODULE
	)
);*/

$capabilities = array(
	'report/hybridmetrics:all' => array(
		'riskbitmask' => RISK_PERSONAL,
		'captype' => 'read',
		'archetypes' => array(
			'manager' => CAP_ALLOW
		),
		'contextlevel' => CONTEXT_MODULE
	)
);