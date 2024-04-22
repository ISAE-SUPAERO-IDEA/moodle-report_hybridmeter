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
 * HybridMeter configuration manager.
 *
 * @author Nassim Bennouar, Bruno Ilponse, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/../constants.php");

use report_hybridmeter\data_provider as data_provider;

/**
 * HybridMeter configuration manager.
 */
class configurator {
    /**
     * HybridMeter config
     * @var config
     */
    protected $config;


    /**
     * Singleton instance
     * @var configurator
     */
    protected static $instance = null;

    public function __construct() {
        global $CFG;

        $this->config = new config($CFG->dataroot."/hybridmeter/config.json");
    }

    /**
     * Get the singleton configuration instance.
     */
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new configurator();
        }
        return self::$instance;
    }

    /**
     * @return config
     */
    public function get_config() {
        return $this->config;
    }

    public function unschedule_calculation() {
        data_provider::get_instance()->clear_adhoc_tasks();
        $this->config->unschedule_calculation();
    }

    public function schedule_calculation($timestamp) {
        data_provider::get_instance()->clear_adhoc_tasks();
        $this->config->set_scheduled_date($timestamp);
        data_provider::get_instance()->schedule_adhoc_task($timestamp);
    }
}
