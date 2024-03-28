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
 * @author Nassim Bennouar, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package
 */
namespace report_hybridmeter\classes\data;

use report_hybridmeter\classes\configurator as configurator;
use report_hybridmeter\classes\data_provider as data_provider;

class general_data {

    protected $begintimestamp;
    protected $endtimestamp;

    protected $courses;
    protected $usedcourses;
    protected $digitalisedcourses;

    protected $nbstudentsconcerneddigitalised;
    protected $nbstudentsconcernedused;

    protected $nbstudentsconcerneddigitalisedactive;
    protected $nbstudentsconcernedusedactive;

    public function __construct($courses, $configurator) {
        $dataprovider = data_provider::get_instance();

        $this->begintimestamp = $configurator->get_begin_timestamp();
        $this->endtimestamp = $configurator->get_end_timestamp();
        $this->courses = $courses;

        $this->digitalisedcourses = array_values(
            array_filter(
                $courses,
                function ($cours) {
                    return $cours[REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL] >=
                        configurator::get_instance()->get_data()["digitalisation_treshold"];
                }
            )
        );

        $this->usedcourses = array_values(
            array_filter(
                $courses,
                function ($cours) {
                    return $cours[REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL] >=
                        configurator::get_instance()->get_data()["usage_treshold"];
                }
            )
        );

        $this->nbstudentsconcerneddigitalised =
            $dataprovider->count_distinct_registered_students_of_courses(
                $this->getIds($this->digitalisedcourses)
            );

        $this->nbstudentsconcernedused =
            $dataprovider->count_distinct_registered_students_of_courses(
                $this->getIds($this->usedcourses),
            );

        $this->nbstudentsconcerneddigitalisedactive =
            $dataprovider->count_student_single_visitors_on_courses(
                $this->getIds($this->digitalisedcourses),
                $configurator->get_begin_timestamp(),
                $configurator->get_end_timestamp()
            );

        $this->nbstudentsconcernedusedactive =
            $dataprovider->count_student_single_visitors_on_courses(
                $this->getIds($this->usedcourses),
                $configurator->get_begin_timestamp(),
                $configurator->get_end_timestamp()
            );
    }

    public function getnbcoursedigitalized() {
        return count($this->digitalisedcourses);
    }

    public function getnbcourseused() {
        return count($this->usedcourses);
    }

    public function getnbanalyzedcourses() {
        return count($this->courses);
    }

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

    protected function getids($courses) {
        return array_map(
            function ($cours) {
                return intval($cours["id"]);
            }, $courses);
    }
}
