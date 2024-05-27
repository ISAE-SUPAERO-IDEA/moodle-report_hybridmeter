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
 * Report data produced by HybridMeter processing, that comes from the aggregation of the indicators
 * computed on every course.
 *
 * @author Nassim Bennouar, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

use report_hybridmeter\config as config;
use report_hybridmeter\data_provider as data_provider;

/**
 * Report data produced by HybridMeter processing.
 */
class report_data {

    /**
     * @var $begintimestamp : Beginning of the analyzed period.
     */
    protected $begintimestamp;

    /**
     * @var $endtimestamp : End of the analyzed period.
     */
    protected $endtimestamp;

    /**
     * @var array $courses : List of analyzed courses.
     */
    protected $courses;

    /**
     * @var array $usedcourses : List of analysed courses that pass the "used" threshold.
     */
    protected $usedcourses;

    /**
     * @var array $digitalisedcourses : List of analysed courses that pass the "digitalized" threshold.
     */
    protected $digitalisedcourses;

    /**
     * @var int Number of students registered on digitalized courses.
     */
    protected $nbstudentsconcerneddigitalised;

    /**
     * @var int Number of students registered on used courses.
     */
    protected $nbstudentsconcernedused;

    /**
     * @var int Number of active students on digitalized courses.
     */
    protected $nbstudentsconcerneddigitalisedactive;

    /**
     * @var int Number of active students on used coursed.
     */
    protected $nbstudentsconcernedusedactive;

    /**
     * Construct the report based on courses data.
     * @param $courses
     * @throws \Exception
     */
    public function __construct($courses) {
        $config = config::get_instance();
        $dataprovider = data_provider::get_instance();

        $this->begintimestamp = $config->get_begin_date();
        $this->endtimestamp = $config->get_end_date();
        $this->courses = $courses;

        $this->digitalisedcourses = array_values(
            array_filter(
                $courses,
                function ($course) {
                    return $course[REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL] >=
                        config::get_instance()->get_digitalisation_treshold();
                }
            )
        );

        $this->usedcourses = array_values(
            array_filter(
                $courses,
                function ($course) {
                    return $course[REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL] >=
                        config::get_instance()->get_usage_treshold();
                }
            )
        );

        $this->nbstudentsconcerneddigitalised =
            $dataprovider->count_distinct_registered_students_of_courses(
                $this->getIds($this->digitalisedcourses),
                $config->get_student_roles()
            );

        $this->nbstudentsconcernedused =
            $dataprovider->count_distinct_registered_students_of_courses(
                $this->getIds($this->usedcourses),
                $config->get_student_roles()
            );

        $this->nbstudentsconcerneddigitalisedactive =
            $dataprovider->count_student_single_visitors_on_courses(
                $this->getIds($this->digitalisedcourses),
                $config->get_begin_date(),
                $config->get_end_date()
            );

        $this->nbstudentsconcernedusedactive =
            $dataprovider->count_student_single_visitors_on_courses(
                $this->getIds($this->usedcourses),
                $config->get_begin_date(),
                $config->get_end_date()
            );
    }

    /**
     * Count the number of digitalized courses on the period.
     * @return int
     */
    public function getnbcoursedigitalized() {
        return count($this->digitalisedcourses);
    }

    /**
     * Count the number of used courses on the period.
     * @return int
     */
    public function getnbcourseused() {
        return count($this->usedcourses);
    }

    /**
     * Count the number of analyzed courses.
     * @return int
     */
    public function getnbanalyzedcourses() {
        return count($this->courses);
    }

    /**
     * Represent the report as an associative array.
     * @return array
     */
    public function tomap() {
        return [
            REPORT_HYBRIDMETER_GENERAL_DIGITALISED_COURSES => $this->digitalisedcourses,
            REPORT_HYBRIDMETER_GENERAL_USED_COURSES => $this->usedcourses,
            REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES => $this->getNbCourseDigitalized(),
            REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES => $this->getNbCourseUsed(),
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED => $this->nbstudentsconcerneddigitalised,
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE => $this->nbstudentsconcerneddigitalisedactive,
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED => $this->nbstudentsconcernedused,
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE => $this->nbstudentsconcernedusedactive,
            REPORT_HYBRIDMETER_GENERAL_BEGIN_CAPTURE_TIMESTAMP => $this->begintimestamp,
            REPORT_HYBRIDMETER_GENERAL_END_CAPTURE_DATE => $this->endtimestamp,
            REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES => $this->getNbAnalyzedCourses(),
        ];
    }

    /**
     * Get the list of course ids.
     * @param $courses
     * @return array|int[]
     */
    protected function getids($courses) {
        return array_map(
            function ($course) {
                return intval($course["id"]);
            }, $courses);
    }
}
