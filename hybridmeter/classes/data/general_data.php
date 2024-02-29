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

namespace report_hybridmeter\classes\data;

use report_hybridmeter\classes\configurator as configurator;
use report_hybridmeter\classes\data_provider as data_provider;

class general_data
{
    protected $begin_timestamp;
    protected $end_timestamp;

    protected $courses;
    protected $used_courses;
    protected $digitalised_courses;

    protected $nb_students_concerned_digitalised;
    protected $nb_students_concerned_used;

    protected $nb_students_concerned_digitalised_active;
    protected $nb_students_concerned_used_active;

    public function __construct($courses, $configurator)
    {
        $data_provider = data_provider::get_instance();

        $this->begin_timestamp = $configurator->get_begin_timestamp();
        $this->end_timestamp = $configurator->get_end_timestamp();
        $this->courses = $courses;

        $this->digitalised_courses = array_values(
            array_filter(
                $courses,
                function ($cours) {
                    return $cours[REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL] >=
                        configurator::get_instance()->get_data()["digitalisation_treshold"];
                }
            )
        );

        $this->used_courses = array_values(
            array_filter(
                $courses,
                function ($cours) {
                    return $cours[REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL] >=
                        configurator::get_instance()->get_data()["usage_treshold"];
                }
            )
        );

        $this->nb_students_concerned_digitalised =
            $data_provider->count_distinct_registered_students_of_courses(
                $this->getIds($this->digitalised_courses)
            );

        $this->nb_students_concerned_used =
            $data_provider->count_distinct_registered_students_of_courses(
                $this->getIds($this->used_courses),
            );

        $this->nb_students_concerned_digitalised_active =
            $data_provider->count_student_single_visitors_on_courses(
                $this->getIds($this->digitalised_courses),
                $configurator->get_begin_timestamp(),
                $configurator->get_end_timestamp()
            );

        $this->nb_students_concerned_used_active =
            $data_provider->count_student_single_visitors_on_courses(
                $this->getIds($this->used_courses),
                $configurator->get_begin_timestamp(),
                $configurator->get_end_timestamp()
            );
    }

    public function getNbCourseDigitalized()
    {
        return count($this->digitalised_courses);
    }

    public function getNbCourseUsed()
    {
        return count($this->used_courses);
    }

    public function getNbAnalyzedCourses()
    {
        return count($this->courses);
    }

    public function toMap()
    {
        return array(
            REPORT_HYBRIDMETER_GENERAL_DIGITALISED_COURSES => $this->digitalised_courses,
            REPORT_HYBRIDMETER_GENERAL_USED_COURSES => $this->used_courses,
            REPORT_HYBRIDMETER_GENERAL_NB_DIGITALISED_COURSES => $this->getNbCourseDigitalized(),
            REPORT_HYBRIDMETER_GENERAL_NB_USED_COURSES => $this->getNbCourseUsed(),
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED => $this->nb_students_concerned_digitalised,
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_DIGITALISED_ACTIVE => $this->nb_students_concerned_digitalised_active,
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED => $this->nb_students_concerned_used,
            REPORT_HYBRIDMETER_GENERAL_NB_STUDENTS_CONCERNED_USED_ACTIVE => $this->nb_students_concerned_used_active,
            REPORT_HYBRIDMETER_GENERAL_BEGIN_CAPTURE_TIMESTAMP => $this->begin_timestamp,
            REPORT_HYBRIDMETER_GENERAL_END_CAPTURE_DATE => $this->end_timestamp,
            REPORT_HYBRIDMETER_GENERAL_NB_ANALYSED_COURSES => $this->getNbAnalyzedCourses()
        );
    }

    protected function getIds($courses)
    {
        return array_map(
            function ($cours) {
                return intval($cours["id"]);
            }, $courses);
    }
}