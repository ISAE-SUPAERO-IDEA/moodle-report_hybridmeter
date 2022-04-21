<?php

namespace report_hybridmeter\classes\tests;

require_once(__DIR__."/../test_scenario.php");

use \report_hybridmeter\classes\utils as utils;
use \report_hybridmeter\classes\configurator as configurator;
use \report_hybridmeter\classes\data_provider as data_provider;

class blacklist_scenario extends \report_hybridmeter\classes\test_scenario {
    public function __construct() {
        parent::__construct("blacklist incohérente");
    }

    public function inclusion() {
        include_once(__DIR__."/../configurator.php");
        include_once(__DIR__."/../data_provider.php");
    }

    public function common_tests() {
        /*$this->dump_config_blacklist("blacklist");
        $this->dump_config_blacklist("savelist_courses");
        $this->dump_config_blacklist("savelist_categories");*/
        $this->test_student_role();
        $this->dump_courses_with_student_activity_during_period();
    }

    public function specific_tests() {}

    protected function dump_whitelisted_courses() {
        echo "<h2>Dump des cours whitelistés</h2>";

        echo "<p>Tableau IDs</p>";

        echo "<p>Details</p>";

        //TODO
    }

    protected function dump_config_blacklist($type="blacklist") {
        switch($type) {
            case "savelist_courses" :
                $name = "cours en savelist";
                $SQL_table = "course";
                $index = "save_blacklist_courses";
                break;
            case "savelist_categories" :
                $name = "catégories en savelist";
                $SQL_table = "course_categories";
                $index = "save_blacklist_categories";
                break;
            case "blacklist" :
                $name = "cours blacklistés";
                $SQL_table = "course";
                $index = "blacklisted_courses";
                break;
            default :
                throw new \Error("Le paramètre \$type ne peut valoir que (savelist_courses|savelist_categories|blacklist)");
                return;
                break;
        }

        echo "<h3>Dump des ".$name."</h3>";

        $config = configurator::getInstance();
        $data = $config->get_data();

        $array = $data[$index];

        print_r($data[$index]);

        print_r($array);

        if($type=="blacklist")
            $array = array_keys($array);

        $length = count($array);

        if ($length == 0) {
            echo "<p>Il semble qu'il n'y ait pas de ".$name."</p>";
        }
        else {
            echo "<p>Voici les ids des ".$name." dans la configuration :</p>";

            echo utils::array_to_n_uplets_table_html($array);

            utils::precondition_ids(array_keys($array));

            global $DB;

            $where = "where id in (".$array[0];
            for($i = 1; $i < $length; $i++){
                $where .= ", ".$array[$i];
            }
            $where .= ")";

            $blacklisted_courses_details = $DB->get_records_sql(
                "select * from ".$DB->get_prefix().$SQL_table." ".$where
            );

            echo utils::objects_array_to_html($blacklisted_courses_details);
        }
    }

    protected function dump_courses_with_student_activity_during_period() {
        echo "<h3>Dump des cours qui ont reçu de l'activité durant la période courante</h3>";
        global $DB;

        $student_role = configurator::getInstance()->get_student_role();

        $query = "select distinct course.id as id, course.idnumber as idnumber, course.fullname as fullname, category.id as category_id, category.name as category_name
        from ".$DB->get_prefix()."course as course
        inner join ".$DB->get_prefix()."logstore_standard_log as logs on course.id=logs.courseid
        inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
        inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
        inner join ".$DB->get_prefix()."course_categories as category on category.id = course.category
        where role.shortname = ?
        and logs.timecreated between ? and ?
        and logs.eventname like '%course_viewed'";

        $records = $DB->get_records_sql($query, array($student_role, $begin_timestamp, $end_timestamp));

        print_r($records);
    }

    /*TODO : créer des scénarii blacklist plus spécifiques et passer cette classe en abstraite*/
}