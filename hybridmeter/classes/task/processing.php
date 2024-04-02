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

/**
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
namespace report_hybridmeter\task;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../config.php");
require_once(dirname(__FILE__).'/../processing.php');
require_once(dirname(__FILE__).'/../configurator.php');

use report_hybridmeter\classes\processing as class_processing;

// Adhoc task that produces hybridmeter's serialized data.
class processing extends \core\task\adhoc_task {
    public function get_name() {
        return get_string('pluginname', 'report_hybridmeter');
    }

    public function execute() {
        \report_hybridmeter\classes\configurator::get_instance()->unschedule_calculation();
        $processing = new class_processing();
        $processing->launch();
    }
}
