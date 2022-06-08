<?php

require(dirname(__FILE__).'/../../config.php');
require_once('classes/configurator.php');

use \report_hybridmeter\classes\configurator as configurator;

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
has_capability('report/hybridmeter:all', $context) || die();

$configurator = configurator::get_instance();

$disable = optional_param('disable', 'nothing', PARAM_ALPHAEXT);
$enable = optional_param('enable', 'nothing', PARAM_ALPHAEXT);

if ($disable != 'nothing')
    $configurator->unset_debug();
else if ($enable != 'nothing')
    $configurator->set_debug();

if ($configurator->get_debug()) {
    echo '<form action="" method="get">
            <p>Debug feature is ON</p>
            <input type="submit" name="disable" value="Disable"/>
          </form>';
}
else {
    echo '<form action="" method="get">
            <p>Debug feature is OFF</p>
            <input type="submit" name="enable" value="Enable"/>
          </form>';
}