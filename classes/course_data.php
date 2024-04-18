<?php

namespace report_hybridmeter;

use Cassandra\Date;

class course_data {

    protected $id;
    protected $fullname;
    protected $category_id;
    protected $category_name;
    protected $categorypath;
    protected $idnumber;
    protected $url;
    protected $digitalisationlevel;
    protected $usagelevel;
    protected $activelastmonth;
    protected $activestudents;
    protected $nbregisteredstudents;
    protected $begindate;
    protected $enddate;
    protected $rawdata;

    public function __construct($course, $wwwroot) {
        $this->id = $course->id;
        $this->fullname = $course->fullname;
        $this->category_id = $course->category_id;
        $this->category_name = $course->category_name;
        $this->categorypath = indicators::get_category_path($course->category_id);
        $this->idnumber = $course->idnumber;
        $this->url = $wwwroot."/course/view.php?id=".$this->id;
        $this->digitalisationlevel = indicators::digitalisation_level($this->id);
        $this->usagelevel = indicators::usage_level($this->id);
        $this->activelastmonth = indicators::is_course_active_on_period($this->id);
        $this->activestudents = indicators::active_students($this->id);
        $this->nbregisteredstudents = indicators::nb_registered_students($this->id);
        $this->rawdata = indicators::raw_data($this->id);
    }

    public function set_begindate($begindate) {
        $this->begindate = $begindate;
    }

    public function set_enddate($enddate) {
        $this->enddate = $enddate;
    }

    public function to_map() {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            REPORT_HYBRIDMETER_FIELD_ID_MOODLE => $this->id,
            REPORT_HYBRIDMETER_FIELD_CATEGORY_PATH => $this->categorypath,
            REPORT_HYBRIDMETER_FIELD_ID_NUMBER  => $this->idnumber,
            REPORT_HYBRIDMETER_FIELD_URL => $this->url,
            REPORT_HYBRIDMETER_FIELD_DIGITALISATION_LEVEL => $this->digitalisationlevel,
            REPORT_HYBRIDMETER_FIELD_USAGE_LEVEL => $this->usagelevel,
            REPORT_HYBRIDMETER_FIELD_ACTIVE_COURSE => $this->activelastmonth,
            REPORT_HYBRIDMETER_FIELD_NB_ACTIVE_USERS =>$this->activestudents,
            REPORT_HYBRIDMETER_FIELD_NB_REGISTERED_STUDENTS => $this->nbregisteredstudents,
            REPORT_HYBRIDMETER_FIELD_BEGIN_DATE => $this->begindate,
            REPORT_HYBRIDMETER_FIELD_END_DATE  => $this->enddate,
            'raw_data' => $this->rawdata,
        ];
    }
}