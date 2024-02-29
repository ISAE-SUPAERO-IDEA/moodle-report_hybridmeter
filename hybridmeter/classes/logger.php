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
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__."/configurator.php");

use \report_hybridmeter\classes\configurator as configurator;

// Hybridmeter's logger
class logger {
    private static function var_dump_ret($object) {
        ob_start();
        var_dump($object);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    public static function log_var_dump($object) {
        if (configurator::get_instance()->get_debug()) {
            error_log("[Hybridmeter] ".self::var_dump_ret($object));
        }
    }
      
    public static function log($object) {
        if (configurator::get_instance()->get_debug()) {
            error_log("[Hybridmeter] ".print_r($object, 1));
        }
    }

    public static function file_log($object, $filename) {
        if (configurator::get_instance()->get_debug()) {
            $file = fopen(dirname(__FILE__).'/../'.$filename, 'a');
            fwrite($file, print_r($object,1));
        }
    }
}
