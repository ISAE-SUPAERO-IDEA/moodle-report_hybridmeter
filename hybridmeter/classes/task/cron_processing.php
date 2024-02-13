<?php
/*
 * Hybrid Meter
 * Copyright (C) 2020 - 2024  ISAE-SUPAERO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace report_hybridmeter\task;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../processing.php');
require_once(dirname(__FILE__)."/../../../../config.php");

use \report_hybridmeter\classes\processing as processing;

// Scheduled task that produces hybridmeter's serialized data
class cron_processing extends \core\task\scheduled_task {
    public function get_name(){
        return get_string('pluginname', 'report_hybridmeter');
    }

    public function execute() {
        $processing = new processing();
        $processing->launch();
    }
}