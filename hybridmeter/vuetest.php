<?php

require(dirname(__FILE__).'/../../config.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();