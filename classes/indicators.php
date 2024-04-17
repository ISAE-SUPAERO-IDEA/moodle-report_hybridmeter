<?php

namespace report_hybridmeter;

class indicators {

    public static function get_category_path($course): string {
        $cachemanager = cache_manager::get_instance();

        if ($cachemanager->is_category_path_calculated($course->category_id)) {
            return $cachemanager->get_category_path($course->category_id);
        }

        $categorypath = data_provider::get_instance()->get_category_path($course->category_id);

        $cachemanager->update_category_path($course->category_id, $categorypath);

        return $categorypath;
    }

    public static function digitalisation_level($courseid): float {
        $activitydata = data_provider::get_instance()->count_activities_per_type_of_course($courseid);
        return hybridation_calculus("digitalisation_coeffs", $activitydata);
    }

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
        return hybridation_calculus("usage_coeffs", $activitydata);
    }

    public static function is_course_active_last_month($courseid): int {
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

    public static function active_students ($courseid): int {
        $configurator = configurator::get_instance();
        return data_provider::get_instance()->count_student_single_visitors_on_courses(
            [$courseid],
            $configurator->get_begin_timestamp(),
            $configurator->get_end_timestamp()
        );
    }

    public static function nb_registered_students ($courseid): int {
        return data_provider::get_instance()->count_registered_students_of_course($courseid);
    }

    public static function raw_data($courseid): array {
        return data_provider::get_instance()->count_activities_per_type_of_course($courseid);
    }

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