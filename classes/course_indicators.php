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
 * Data structure describing the indicators of a course.
 *
 * @author John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

/**
 * Data structure describing the indicators of a course.
 */
class course_indicators {

    /**
     * Full name of the course.
     * @var string
     */
    public $coursefullname;

    /**
     * Beginning of the analyzed period.
     * @var int
     */
    public $begindate;

    /**
     * End of the analyzed period.
     * @var int
     */
    public $enddate;

    /**
     * The digitalisation level of the course.
     * @var float
     */
    public $digitalisationlevel;

    /**
     * The usage level of the course.
     * @var float
     */
    public $usagelevel;

    /**
     * Number students registered on the course;
     * @var int
     */
    public $nbregisteredstudents;

    /**
     * Number of students that are active on the course.
     * @var int
     */
    public $nbactivestudents;

    /**
     * Whether the course is active or not.
     * @var bool
     */
    public $active;

    /**
     * Counts activities per type within the course.
     * @var array
     */
    public $countactivitiespertype;

    /**
     * Counts hits on activities for the course per type.
     * @var array
     */
    public $counthitsonactivitiespertype;

    /**
     * Construct the course indicators.
     * @param string $coursefullname
     * @param int $begindate
     * @param int $enddate
     * @param float $digitalisationlevel
     * @param float $usagelevel
     * @param int $nbregisteredstudents
     * @param int $nbactivestudents
     * @param bool $active
     * @param array $countactivitiespertype
     * @param array $counthitsonactivitiespertype
     */
    public function __construct(string $coursefullname,
                                int $begindate,
                                int $enddate,
                                float $digitalisationlevel,
                                float $usagelevel,
                                int $nbregisteredstudents,
                                int $nbactivestudents,
                                bool $active,
                                array $countactivitiespertype,
                                array $counthitsonactivitiespertype) {
        $this->coursefullname = $coursefullname;
        $this->begindate = $begindate;
        $this->enddate = $enddate;
        $this->digitalisationlevel = $digitalisationlevel;
        $this->usagelevel = $usagelevel;
        $this->nbregisteredstudents = $nbregisteredstudents;
        $this->nbactivestudents = $nbactivestudents;
        $this->active = $active;
        $this->countactivitiespertype = $countactivitiespertype;
        $this->counthitsonactivitiespertype = $counthitsonactivitiespertype;
    }
}
