<?php

namespace report_hybridmeter\classes\tests;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../../config.php");
require_once(__DIR__."/../test_scenario.php");
require_once(__DIR__."/../configurator.php");
require_once(__DIR__."/../data_provider.php");
require_once(__DIR__."/../utils.php");

use \report_hybridmeter\classes\utils as utils;
use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;
use Exception;

class blacklist_scenario extends \report_hybridmeter\classes\test_scenario {
    public function __construct() {
        parent::__construct(get_string('inconsistent_blacklist', 'report_hybridmeter'));
    }

    public function inclusion() {
        include_once(__DIR__."/../configurator.php");
        include_once(__DIR__."/../data_provider.php");
    }

    public function common_tests() {
        /*$this->dump_config_blacklist("blacklist");
        $this->dump_config_blacklist("savelist_courses");
        $this->dump_config_blacklist("savelist_categories");*/
        $this->test_student_archetype();
        $this->dump_courses_with_student_activity_during_period();
    }

    public function specific_tests() {}

    protected function dump_whitelisted_courses() {
        echo "<h2>Dump of whitelisted courses</h2>";

        //TODO
    }

    protected function dump_config_blacklist(string $type="blacklist") {
        global $DB;

        switch($type) {
            case "savelist_courses" :
                $name = "courses in savelist";
                $SQL_table = "course";
                $index = "save_blacklist_courses";
                break;

            case "savelist_categories" :
                $name = "categories in savelist";
                $SQL_table = "course_categories";
                $index = "save_blacklist_categories";
                break;

            case "blacklist" :
                $name = "blacklisted courses";
                $SQL_table = "course";
                $index = "blacklisted_courses";
                break;
                
            default :
                throw new Exception("The parameter \$type can only be (savelist_courses|savelist_categories|blacklist)");
                return;
                break;
        }

        echo "<h3>Dump of ".$name."</h3>";

        $config = configurator::get_instance();
        $data = $config->get_data();

        $array = $data[$index];

        print_r($data[$index]);

        print_r($array);

        if($type=="blacklist")
            $array = array_keys($array);

        $length = count($array);

        if ($length == 0) {
            echo "<p>It seems that there is no ".$name."</p>";
        }
        else {
            echo "<p>Here are the ids of the ".$name." in the configuration:</p>";

            echo utils::array_to_n_uplets_table_html($array);

            utils::precondition_ids(array_keys($array));

            $where = "where id in (".$array[0];
            for($i = 1; $i < $length; $i++){
                $where .= ", ".$array[$i];
            }
            $where .= ")";

            $sql = "SELECT * FROM " . $DB->get_prefix() . $SQL_table . " " . $where;

            $blacklisted_courses_details = $DB->get_records_sql($sql,array());

            echo utils::objects_array_to_html($blacklisted_courses_details);
        }
    }

    protected function dump_courses_with_student_activity_during_period() {
        echo "<h3>Dump of courses that received activity in the current period</h3>";
        global $DB;

        $student_archetype = configurator::get_instance()->get_student_archetype();

        

        $sql = "SELECT DISTINCT course.id AS id, course.idnumber AS idnumber, course.fullname AS fullname,
                                category.id AS category_id, category.name AS category_name
                FROM ".$DB->get_prefix()."course course
                JOIN ".$DB->get_prefix()."logstore_standard_log logs ON course.id = logs.courseid
                JOIN ".$DB->get_prefix()."role_assignments assign ON logs.userid = assign.userid
                JOIN ".$DB->get_prefix()."role role ON assign.roleid = role.id
                JOIN ".$DB->get_prefix()."course_categories category ON category.id = course.category
                WHERE role.shortname = :studentarchetype
                      AND logs.timecreated between :begintimestamp and :endtimestamp
                      AND logs.eventname like '%course_viewed'";

        $params = array(
            'studentarchetype' => $student_archetype,
            'begintimestamp' => $begin_timestamp,
            'endtimestamp' => $end_timestamp,
        );

        $records = $DB->get_records_sql($sql, $params);

        print_r($records);
    }

    /*TODO : create more specific blacklist scenarios and make this class abstract*/
}