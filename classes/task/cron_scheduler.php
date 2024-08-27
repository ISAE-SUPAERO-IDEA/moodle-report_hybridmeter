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
 * Scheduled task that creates an adhoc processing task with capture period.
 * @author Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter\task;

use core\task\scheduled_task;

/**
 * Scheduled task that creates an adhoc processing task with capture period.
 */
class cron_scheduler extends scheduled_task {

    /**
     * Task name.
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'report_hybridmeter');
    }

    /**
     * Return the timestamp of the beginning of a month.
     * @param int $month
     * @param int $year
     * @return int
     */
    private function month_to_timestamp(int $month, int $year): int {
        return \DateTime::createFromFormat(
            "d n Y G i s",
            "1 ".$month." ".$year. " 0 00 00")
            ->getTimestamp();

    }

    /**
     * Task execution.
     * @return void
     */
    public function execute() {
        $config = \report_hybridmeter\config::get_instance();
        $autoscheduler = $config->get_autoscheduler();
        $scheduler = scheduler::get_instance();

        if ($autoscheduler != "none" && !$config->get_has_scheduled_calculation()) {
            $now = new \DateTime('now');
            $configured = false;
            $month = $now->format("n");
            $year = $now->format("Y");
            if ($autoscheduler == "yearly") {
                $begin = $this->month_to_timestamp(1, $year + 1);
                $end = $this->month_to_timestamp(1, $year + 2);
                $configured = true;
            }
            if ($autoscheduler == "quaterly") {
                $beginmonth = (floor(($month - 1) / 3) * 3) + 1;
                $endmonth = $beginmonth + 3;
                $begin = $this->month_to_timestamp($beginmonth, $year);
                $end = $this->month_to_timestamp($endmonth, $year);
                $configured = true;
            }
            if ($autoscheduler == "monthly") {
                $begin = $this->month_to_timestamp($month, $year);
                $end = $this->month_to_timestamp($month + 1, $year);
                $configured = true;
            }
            if ($configured) {
                $config->update_period($begin, $end - 1);
                $scheduler->schedule_calculation($end, $config);
            }
        }
    }
}
