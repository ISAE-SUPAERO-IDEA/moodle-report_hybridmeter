<?php

namespace report_hybridmeter\classes;

require_once(__DIR__."/configurator.php");
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/task/traitement.php');

defined('MOODLE_INTERNAL') || die();

use \report_hybridmeter\classes\configurator as configurator;

class data_provider {

    protected static $instance = null;

    public function __construct(){
    }

    public static function getInstance() {
        if (self::$instance == null){
            self::$instance = new data_provider();
        }

        return self::$instance;
    }

    /*Fonctions qui permettent de calculer les indicateurs*/

    //compte le nombre d'activités par type en fonction du cours
    public function count_modules_types_id(int $id){
        global $DB;
        $output=array();
        $records=$DB->get_records_sql("select modules.name, count(modules.name) as count from ".$DB->get_prefix()."course_modules as course_modules inner join ".$DB->get_prefix()."modules as modules on course_modules.module=modules.id where course_modules.course=? group by modules.name",[$id]);
        foreach($records as $key => $object){
            $output[$object->name] = $object->count;
        }
        $records=$DB->get_records_sql("select ".$DB->get_prefix()."modules.name as name, 0 as count from ".$DB->get_prefix()."modules where name not in (select modules.name as name from ".$DB->get_prefix()."course_modules as course_modules inner join ".$DB->get_prefix()."modules as modules on course_modules.module=modules.id where course_modules.course=? group by modules.name)",[$id]);
        foreach($records as $key => $object){
            $output[$object->name] = $object->count;
        }
        return $output;
    }

    //compte le nombre de course viewed en fonction du cours et de la période choisie
    public function count_hits_course_viewed(int $id, int $begin_date, int $end_date){
        global $DB;
        $record=$DB->get_record_sql("select count(*) as c
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
            where role.shortname='student'
            and eventname='\\core\\event\\course_viewed'
            and courseid=?
            and context.instanceid=?
            and context.contextlevel=?
            and timecreated between ? and ?",
            array($id, $id, CONTEXT_COURSE, $begin_date, $end_date));
        return $record->c;
    }

    //compte le nombre de hits toute nature confondue en fonction du cours et de la période choisie
    public function count_hits(int $id, int $begin_date, int $end_date){
        global $DB;
        $record=$DB->get_record_sql("select count(*) as c
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            where role.shortname='student'
            and courseid=?
            and timecreated between ? and ?",
            array($id, $begin_date, $end_date));
        return $record->c;
    }

    //compte le nombre d'utilisateurs uniques en fonction du cours et de la période choisie
    public function count_single_users_course_viewed($ids, int $begin_date, int $end_date){
        global $DB;

        if(!is_array($ids)){
            $ids=array($ids);
        }

        $length = count($ids);

        if($length === 0)
            return 0;


        if(!is_numeric($ids[0]))
            return -1;


        $where_compil = "(".$ids[0];
        for($i = 1; $i < $length; $i++){
            if(!is_numeric($ids[$i]))
                return -1;
            $where_compil .= ", ".$ids[$i];
        }
        $where_compil .= ")";

        $query="select count(distinct logs.userid) as c
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
            where role.shortname = 'student'
            and eventname like '%course_viewed'
            and context.contextlevel = ?
            and context.instanceid in ".$where_compil."
            and logs.courseid in ".$where_compil."
            and logs.timecreated between ? and ?";

        $params = array(CONTEXT_COURSE, $begin_date, $end_date);

        $record=$DB->get_record_sql($query, $params);
        return $record->c;
    }

    //compte le nombre d'inscrits en fonction du cours
    public function count_registered_users(int $id){
        global $DB;
        $record=$DB->get_record_sql("select count(*) as c
            from ".$DB->get_prefix()."context as context
            inner join ".$DB->get_prefix()."role_assignments as assign on context.id=assign.contextid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            where role.shortname='student'
            and context.instanceid=?
            and context.contextlevel=?",
            array($id, CONTEXT_COURSE));
        return $record->c;
    }

    public function count_distinct_students($ids){
        global $DB;

        $length = count($ids);

        if(count($ids) === 0)
            return 0;

        if($length === 0)
            return 0;


        if(!is_numeric($ids[0]))
            return -1;

        $where_courseid = "enrol.courseid in (".$ids[0];
        for($i = 1; $i < $length; $i++){
            if(!is_numeric($ids[$i]))
                return -1;
            $where_courseid .= ", ".$ids[$i];
        }
        $where_courseid .= ")";


        $record = $DB->get_record_sql(
            "select count(distinct user_enrolments.userid) as c from mdl_user_enrolments as user_enrolments
            inner join mdl_enrol as enrol on user_enrolments.enrolid=enrol.id
            where ".$where_courseid." and (enrol.enrolenddate>? or enrol.enrolenddate=0)",
            array(strtotime("now"))
        );

        return $record->c;
    }

    //compte le nombre de hits en fonction du type d'activité visée, du cours et de la période choisie
    public function count_hits_by_module_type(int $id, int $begin_date, int $end_date){
        global $DB;
        $params=array($id, $id, CONTEXT_COURSE, $begin_date, $end_date);

        $records = $DB->get_records_sql("select logs.objecttable as module, count(*) as c
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
            where role.shortname='student'
            and courseid=?
            and logs.target='course_module'
            and context.instanceid=?
            and context.contextlevel=?
            and timecreated between ? and ?
            group by logs.objecttable",
            $params);
        return $records;
    }


    /*Fonctions utilitaires*/


    public function get_subcategories_id(int $id){
        global $DB;

        return array_values(
            array_map(
                function($category){
                    return $category->id;
                },
                $DB->get_records(
                    "course_categories",
                    array("parent"=>$id)
                )
            )
        );
    }

    //récupère les cours actifs visibles sans la blacklist
    public function get_whitelisted_courses(){
        global $DB;

        $config = configurator::getInstance();
        $data = $config->get_data();
        $blacklisted_courses = array_keys($data["blacklisted_courses"]);
        
        //le cours qui correspond au site est blacklisté par défaut
        array_push($blacklisted_courses, 1);
        $blacklisted_categories = array_keys($data["blacklisted_categories"]);
        $query = "select id, fullname from ".$DB->get_prefix()."course where true";
        if (count($blacklisted_courses)>0) {
            $query .= " and id not in (".implode($blacklisted_courses,",").")";
        }
        if (count($blacklisted_categories)>0) {
            $query .= " and category not in (".implode($blacklisted_categories,",").")";
        }

        $records=$DB->get_records_sql($query);

        return $records;
    }

    public function filter_living_courses_period($courses, $begin_timestamp, $end_timestamp){
        global $DB;

        if(count($courses) == 0)
            return array();

        $query = "select distinct course.id, course.fullname from ".$DB->get_prefix()."course as course
        inner join ".$DB->get_prefix()."logstore_standard_log as logs on course.id=logs.courseid
        inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
        inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
        where role.shortname = 'student'
        and logs.timecreated between ? and ?
        and logs.eventname like '%course_viewed'
        and course.id in (".implode($courses,",").")";

        $records = $DB->get_records_sql($query, array($begin_timestamp, $end_timestamp));
        
        return $records;
    }

    /*Fonctions de gestion des tâches adhoc*/

    public function count_adhoc_tasks(){
        global $DB;
        return $DB->count_records(
            "task_adhoc",
            array('classname' => '\\report_hybridmeter\\task\\traitement')
        );
    }

    public function clear_adhoc_tasks(){
        global $DB;
        return $DB->delete_records(
            "task_adhoc",
            array('classname' => '\\report_hybridmeter\\task\\traitement')
        );
    }

    public function get_adhoc_tasks_list(){
        global $DB;
        return array_values(
            array_map(
                function($task){
                    return array(
                        'id' => $task->id,
                        'nextruntime' => $task->nextruntime
                    );
                },
                $DB->get_records(
                    "task_adhoc",
                    array('classname' => '\\report_hybridmeter\\task\\traitement')
                )
            )
        );
    }

    public function schedule_adhoc_task($timestamp){
        $task = new \report_hybridmeter\task\traitement();
        $task->set_next_run_time($timestamp);
        \core\task\manager::queue_adhoc_task($task);
    }
}