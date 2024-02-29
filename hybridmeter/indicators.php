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
 * @author Nassim Bennouar
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 */
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/constants.php');
require_once(__DIR__.'/classes/configurator.php');
require_once(__DIR__.'/classes/logger.php');
require_once(__DIR__.'/classes/data_provider.php');
require_once(__DIR__.'/classes/cache_manager.php');

use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;
use \report_hybridmeter\classes\cache_manager as cache_manager;
use \report_hybridmeter\classes\logger as logger;

function hybridation_calculus(string $type, array $activity_data): float {
    $h = 0; // Hybridation value
    $c = 0; // Number of activity types
    $n = 0; // Nombre total d'activités
    $sigma_pk = 0; // Sum of activity weights
    $sigma_pk_vk = 0; // Sum of activity weight multiplicated by their hybridation value
    $sigma_pk_vk = 0; // Sum of activity weight multiplicated by their hybridation value
    $m = 1; // Malus
    foreach ($activity_data as $k => $nk) {
        //Possibilité d'accéder à des valeurs hardcodées pour le diagnostic
        $vk = configurator::get_instance()->get_coeff($type, $k); // Activity hybridation value
    
        if ($nk > 0 && $vk > 0) {
            $c ++; 
            $n += $nk;
            $pk = $nk / ($nk + REPORT_HYBRIDMETER_ACTIVITY_INSTANCES_DEVIATOR_CONSTANT); // Activity weight
            $sigma_pk += $pk;
            $sigma_pk_vk += $pk * $vk;
        }
    }
    if ($n <= 2) $m = 0.25;
    if($sigma_pk != 0){
        $p = $c / ($c + REPORT_HYBRIDMETER_ACTIVITY_VARIETY_DEVIATOR_CONSTANT); // Course weight
        $h = $m * $p * $sigma_pk_vk / $sigma_pk;
    }
    return round($h, 2);
}

function digitalisation_level(array $object, array $parameters): float {
    $activity_data = data_provider::get_instance()->count_activities_per_type_of_course($object['id']);
    return hybridation_calculus("digitalisation_coeffs", $activity_data);
}

function raw_data(array $object, array $parameters) {
    return data_provider::get_instance()->count_activities_per_type_of_course($object['id']);
}


function usage_level(array $object, array $parameters): float {
    $data_provider = data_provider::get_instance();
    $configurator = configurator::get_instance();
    $indicator=0;
    $total=0;
    $activity_data=$data_provider->count_hits_on_activities_per_type(
        $object['id'], 
        $configurator->get_begin_timestamp(),
        $configurator->get_end_timestamp()
    );
    return hybridation_calculus("usage_coeffs", $activity_data);
}

function get_category_path(array $object, array $parameters): string {
    $cache_manager = cache_manager::get_instance();

    if($cache_manager->is_category_path_calculated($object['category_id']))
        return $cache_manager->get_category_path($object['category_id']);

    $category_path = data_provider::get_instance()->get_category_path($object['category_id']);

    $cache_manager->update_category_path($object['category_id'], $category_path);

    return $category_path;
}

function is_course_active_last_month(array $object, array $parameters): int {
    $configurator = configurator::get_instance();
    $data_provider = data_provider::get_instance();

    $count=$data_provider->count_student_single_visitors_on_courses(
        [$object['id']], 
        $configurator->get_begin_timestamp(),
        $configurator->get_end_timestamp()
    );

    if ($count >= $configurator->get_data()["active_treshold"])
        return 1;
    else 
        return 0;
}

function active_students (array $object, array $parameters): int {
    $configurator = configurator::get_instance();
    return data_provider::get_instance()->count_student_single_visitors_on_courses(
        [$object['id']],
        $configurator->get_begin_timestamp(),
        $configurator->get_end_timestamp()
    );
}

function nb_registered_students (array $object, array $parameters): int {
    return data_provider::get_instance()->count_registered_students_of_course($object['id']);
}