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
 * Data describing a course.
 *
 * @author Nassim Bennouar, Bruno Ilponse, John Tranier
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright (C) 2020  ISAE-SUPAERO (https://www.isae-supaero.fr/)
 * @package report_hybridmeter
 */
namespace report_hybridmeter;

/**
 * Data describing a course.
 */
class course_data {

    /**
     * Course id
     * @var $id
     */
    protected $id;

    /**
     * Course full name.
     * @var string
     */
    protected $fullname;

    /**
     * Id of the parent category of the course.
     * @var int
     */
    protected $categoryid;

    /**
     * Name of the parent category of the course.
     * @var string
     */
    protected $categoryname;

    /**
     * Full category path of the course.
     * @var string
     */
    protected $categorypath;

    /**
     * Course ID number.
     * @var string
     */
    protected $idnumber;

    /**
     * Course URL.
     * @var string
     */
    protected $url;

    /**
     * Level of digitalization of the course.
     * @var float
     */
    protected $digitalisationlevel;

    /**
     * Level of usage of the course.
     * @var float
     */
    protected $usagelevel;

    /**
     * Does the course has been used during the considered period.
     * @var int
     */
    protected $activeonperiod;

    /**
     * Number of students active on this course.
     * @var int
     */
    protected $activestudents;

    /**
     * Number of students registered on this course.
     * @var int
     */
    protected $nbregisteredstudents;

    /**
     * Beginning date of the considered period.
     * @var string
     */
    protected $begindate;

    /**
     * Ending date of the considered period.
     * @var string
     */
    protected $enddate;

    /**
     * Nb of activities per type within this course.
     * @var array
     */
    protected $rawdata;

    /**
     * Construct the HybridMeter course data from Moodle course data.
     * @param object $course
     * @param string $wwwroot
     * @param array $categoriespathcache associating to each encountered category its category path name
     */
    public function __construct($course, $wwwroot, &$categoriespathcache) {
        $this->id = $course->id;
        $this->fullname = $course->fullname;
        $this->categoryid = $course->category_id;
        $this->categoryname = $course->category_name;
        $this->categorypath = indicators::get_category_path($course->category_id, $categoriespathcache);
        $this->idnumber = $course->idnumber;
        $this->url = $wwwroot."/course/view.php?id=".$this->id;
        $this->digitalisationlevel = indicators::digitalisation_level($this->id);
        $this->usagelevel = indicators::usage_level($this->id);
        $this->activeonperiod = indicators::is_course_active_on_period($this->id);
        $this->activestudents = indicators::active_students($this->id);
        $this->nbregisteredstudents = indicators::nb_registered_students($this->id);
        $this->rawdata = indicators::raw_data($this->id);
    }

    /**
     * Set the beginning date of the considered period.
     * @param int $begindate
     * @return void
     */
    public function set_begindate($begindate) {
        $this->begindate = $begindate;
    }

    /**
     * Set the ending date of the considered period.
     * @param int $enddate
     * @return void
     */
    public function set_enddate($enddate) {
        $this->enddate = $enddate;
    }

    /**
     * Represents the course data as an associative array
     * @return array
     */
    public function to_map() {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'category_id' => $this->categoryid,
            'category_name' => $this->categoryname,
            REPORT_HYBRIDMETER_FIELD_ID_MOODLE => $this->id,
            REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH => $this->categorypath,
            REPORT_HYBRIDMETER_FIELD_ID_NUMBER  => $this->idnumber,
            REPORT_HYBRIDMETER_FIELD_URL => $this->url,
            REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL => $this->digitalisationlevel,
            REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL => $this->usagelevel,
            REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE => $this->activeonperiod,
            REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS => $this->activestudents,
            REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS => $this->nbregisteredstudents,
            REPORT_HYBRIDMETER_FIELD_BEGIN_DATE => $this->begindate,
            REPORT_HYBRIDMETER_FIELD_END_DATE  => $this->enddate,
            'raw_data' => $this->rawdata,
        ];
    }
}
