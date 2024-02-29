<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// This file is part of Moodle - http://moodle.org
//
//  Moodle is free software: you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation, either version 3 of the License, or
//  (at your option) any later version.
//
//  Moodle is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License
//  along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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