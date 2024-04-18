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
 * Indicators computation functions.
 *
 * @author Nassim Bennouar, Bruno Ilponse
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

/**
 * Indicators computation functions
 */
class indicators {

    /**
     * Get the category path from a category ID
     * @param $categoryid
     * @return string
     * @param $categoriespathcache array associating to each encountered category its category path name
     */
    public static function get_category_path($categoryid, &$categoriespathcache): string {
        if (isset($categoriespathcache[$categoryid])) {
            return $categoriespathcache[$categoryid];
        }

        $categorypath = data_provider::get_instance()->get_category_path($categoryid);
        $categoriespathcache[$categoryid] = $categorypath;

        return $categorypath;
    }

    /**
     * Compute the digitalization level of a course.
     * @param $courseid
     * @return float
     */
    public static function digitalisation_level($courseid): float {
        $activitydata = data_provider::get_instance()->count_activities_per_type_of_course($courseid);
        return self::hybridation_calculus("digitalisation_coeffs", $activitydata);
    }

    /**
     * Compute the usage level of a course.
     * @param $courseid
     * @return float
     */
    public static function usage_level($courseid): float {
        $dataprovider = data_provider::get_instance();
        $configurator = configurator::get_instance();
        $indicator = 0;
        $total = 0;
        $activitydata = $dataprovider->count_hits_on_activities_per_type(
            $courseid,
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );
        return self::hybridation_calculus("usage_coeffs", $activitydata);
    }

    /**
     * Check if the course has been used on the configured period.
     * @param $courseid
     * @return int 1 if the course has been used ; 0 otherwise.
     */
    public static function is_course_active_on_period($courseid): int {
        $configurator = configurator::get_instance();
        $dataprovider = data_provider::get_instance();

        $count = $dataprovider->count_student_single_visitors_on_courses(
            [$courseid],
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );

        if ($count >= $configurator->get_data()["active_treshold"]) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Count the number of student that have accessed the course during the period.
     * @param $courseid
     * @return int
     */
    public static function active_students($courseid): int {
        $configurator = configurator::get_instance();
        return data_provider::get_instance()->count_student_single_visitors_on_courses(
            [$courseid],
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );
    }

    /**
     * Count the number of students registered on the course.
     * @param $courseid
     * @return int
     */
    public static function nb_registered_students($courseid): int {
        return data_provider::get_instance()->count_registered_students_of_course($courseid);
    }

    /**
     * Count activities by type on the course.
     * @param $courseid
     * @return array
     */
    public static function raw_data($courseid): array {
        return data_provider::get_instance()->count_activities_per_type_of_course($courseid);
    }

    /**
     * Compute the hybridization indicator.
     * @param string $type : "digitalisation_coeffs" or ""usage_coeffs""
     * @param array $activitydata
     * @return float
     */
    public static function hybridation_calculus(string $type, array $activitydata): float {
        $h = 0; // Hybridation value.
        $c = 0; // Number of activity types.
        $n = 0; // Total number of activities.
        $sigmapk = 0; // Sum of activity weights.
        $sigmapkvk = 0; // Sum of activity weight multiplied by their hybridisation value.
        $m = 1; // Malus.
        foreach ($activitydata as $k => $nk) {
            $vk = configurator::get_instance()->get_coeff($type, $k); // Activity hybridisation value.

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

}
