<?php
// This file is part of Moodle - http://moodle.org
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,1
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Schedule or unschedule the next report calculation.
 * @author Nassim Bennouar, Bruno Ilponse, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter\task;

use report_hybridmeter\config;
use report_hybridmeter\data_provider as data_provider;

/**
 * Schedule or unschedule the next report calculation.
 */
class scheduler {

    /**
     * Hybridmeter data_provider APÏ used to manipulate adhoc_tasks.
     * @var data_provider
     */
    private $dataprovider;

    /**
     * Singleton instance.
     * @var scheduler
     */
    protected static $instance = null;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->dataprovider = data_provider::get_instance();
    }

    /**
     * Get the singleton instance.
     * @return scheduler
     */
    public static function get_instance(): scheduler {
        if (null === static::$instance) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    /**
     * Schedule the next report calculation.
     * @param $timestamp int when to launch the calculation
     * @param $config config used to store
     * @return void
     */
    public function schedule_calculation(int $timestamp, config $config) {
        $this->dataprovider->schedule_adhoc_task($timestamp);
        $config->set_scheduled_date($timestamp);
    }

    /**
     * Unschedule the next report calculation.
     * @param config $config
     * @return void
     */
    public function unschedule_calculation(config $config) {
        $this->dataprovider->clear_adhoc_tasks();
        $config->unschedule_calculation();
    }
}
