<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/configurator.php');

use \report_hybridmeter\classes\configurator as configurator;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$configurator = configurator::get_instance();

$task = optional_param('task', 'nothing', PARAM_ALPHAEXT);

if ($task == "disable") {
    $configurator->unset_debug();
}
else if ($task == 'enable') {
    $configurator->set_debug();
}

if ($configurator->get_debug()) {
    echo '<form action="" method="get">
            <p>Debug feature is ON</p>
            <input type="submit" name="task" value="disable"/>
          </form>';
}
else {
    echo '<form action="" method="get">
            <p>Debug feature is OFF</p>
            <input type="submit" name="task" value="enable"/>
          </form>';
}