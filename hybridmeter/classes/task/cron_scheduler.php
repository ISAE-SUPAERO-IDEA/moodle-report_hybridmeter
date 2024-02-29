<?php
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

/**
 * @author Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
namespace report_hybridmeter\task;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../configurator.php');
require_once(dirname(__FILE__)."/../../../../config.php");

use \report_hybridmeter\classes\processing as processing;

// Scheduled task that creates an adhoc processing task with capture period
class cron_scheduler extends \core\task\scheduled_task {
    public function get_name(){
        return get_string('pluginname', 'report_hybridmeter');
    }
    private function month_to_timestamp($month, $year) {
        return \DateTime::createFromFormat(
            "d n Y G i s", 
            "1 ".$month." ".$year. " 0 00 00")
            ->getTimestamp();

    }
    public function execute() {
        $configurator = \report_hybridmeter\classes\configurator::get_instance();
        $autoscheduler = $configurator->get_autoscheduler();
        
        if ($autoscheduler != "none" && !$configurator->has_scheduled_calculation()) {
            $now = new \DateTime('now');
            $configured = false;
            $month = $now->format("n");
            $year = $now->format("Y");
            if ($autoscheduler=="yearly") {
                $begin = $this->month_to_timestamp(1, $year + 1);
                $end = $this->month_to_timestamp(1, $year + 2);
                $configured = true;
            }
            if ($autoscheduler=="quaterly") {
                $begin_month = (floor(($month -1) / 3) * 3)+ 1;
                $end_month = $begin_month + 3;
                $begin = $this->month_to_timestamp($begin_month, $year);
                $end = $this->month_to_timestamp($end_month, $year);
                $configured = true;
            }
            if ($autoscheduler=="monthly") {
                $begin = $this->month_to_timestamp($month, $year);
                $end = $this->month_to_timestamp($month+1, $year);
                $configured = true;
            }
            if ($configured) {
                $configurator->update([
                    "begin_date" => $begin, 
                    "end_date" => $end - 1,
                ]);
                $configurator->schedule_calculation($end);
            }
        }
    }
}
