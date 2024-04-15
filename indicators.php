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
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/constants.php');

use report_hybridmeter\configurator as configurator;
use report_hybridmeter\data_provider as data_provider;
use report_hybridmeter\cache_manager as cache_manager;

function hybridation_calculus(string $type, array $activitydata): float {
    $h = 0; // Hybridation value.
    $c = 0; // Number of activity types.
    $n = 0; // Nombre total d'activités.
    $sigmapk = 0; // Sum of activity weights.
    $sigmapkvk = 0; // Sum of activity weight multiplicated by their hybridation value.
    $m = 1; // Malus.
    foreach ($activitydata as $k => $nk) {
        // Possibilité d'accéder à des valeurs hardcodées pour le diagnostic.
        $vk = configurator::get_instance()->get_coeff($type, $k); // Activity hybridation value.

        if ($nk > 0 && $vk > 0) {
            $c ++;
            $n += $nk;
            $pk = $nk / ($nk + REPORT_HYBRIDMETER_ACTIVITY_INSTANCES_DEVIATOR_CONSTANT); // Activity weight.
            $sigmapk += $pk;
            $sigmapkvk += $pk * $vk;
        }
    }
    if ($n <= 2) {
        $m = 0.25;
    }
    if ($sigmapk != 0) {
        $p = $c / ($c + REPORT_HYBRIDMETER_ACTIVITY_VARIETY_DEVIATOR_CONSTANT); // Course weight.
        $h = $m * $p * $sigmapkvk / $sigmapk;
    }
    return round($h, 2);
}

function digitalisation_level(array $object, array $parameters): float {
    $activitydata = data_provider::get_instance()->count_activities_per_type_of_course($object['id']);
    return hybridation_calculus("digitalisation_coeffs", $activitydata);
}

function raw_data(array $object, array $parameters) {
    return data_provider::get_instance()->count_activities_per_type_of_course($object['id']);
}


function usage_level(array $object, array $parameters): float {
    $dataprovider = data_provider::get_instance();
    $configurator = configurator::get_instance();
    $indicator = 0;
    $total = 0;
    $activitydata = $dataprovider->count_hits_on_activities_per_type(
        $object['id'],
        $configurator->get_begin_timestamp(),
        $configurator->get_end_timestamp()
    );
    return hybridation_calculus("usage_coeffs", $activitydata);
}

function get_category_path(array $object, array $parameters): string {
    $cachemanager = cache_manager::get_instance();

    if ($cachemanager->is_category_path_calculated($object['category_id'])) {
        return $cachemanager->get_category_path($object['category_id']);
    }

    $categorypath = data_provider::get_instance()->get_category_path($object['category_id']);

    $cachemanager->update_category_path($object['category_id'], $categorypath);

    return $categorypath;
}

function is_course_active_last_month(array $object, array $parameters): int {
    $configurator = configurator::get_instance();
    $dataprovider = data_provider::get_instance();

    $count = $dataprovider->count_student_single_visitors_on_courses(
        [$object['id']],
        $configurator->get_begin_timestamp(),
        $configurator->get_end_timestamp()
    );

    if ($count >= $configurator->get_data()["active_treshold"]) {
        return 1;
    } else {
        return 0;
    }
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
