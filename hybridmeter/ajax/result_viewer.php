<?php
	/*
	AJAX endpoint to manage HybridMeter configuration data

	*/ 
	require_once("../../../config.php");
    require_once("../classes/configurator.php");
    require_once("../classes/data.php");
    header('Content-Type: text/json');

    //Vérification des autorisations (rôle admin obligatoire)

	require_login();
	$context = context_system::instance();
	$PAGE->set_context($context);
	has_capability('report/hybridmeter:all', $context) || die();
	$id  = optional_param('id' , null, PARAM_INT);

	$data = new \report_hybridmeter\classes\data();
	$configurator = new \report_hybridmeter\classes\configurator($data);
	$path_serialized_data = $CFG->dataroot."/hybridmeter/records/serialized_data";
	$data_unserialized = unserialize(file_get_contents($path_serialized_data));


	echo (json_encode($data_unserialized["data"][$id], JSON_PRETTY_PRINT));