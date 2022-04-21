<?php

namespace report_hybridmeter\classes;

require_once(__DIR__."/configurator.php");
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/task/traitement.php');

defined('MOODLE_INTERNAL') || die();

use \report_hybridmeter\classes\utils as utils;
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

    /*Fonctions d'indicateurs*/

    //retourne le nombre d'activités pour chaque type d'activité pour le cours d'ID $id_course
    public function count_activities_per_type_of_course(int $id_course){
        global $DB;
        $output=array();

        $records=$DB->get_records_sql(
            "select modules.name, count(modules.name) as count
            from ".$DB->get_prefix()."course_modules as course_modules
            inner join ".$DB->get_prefix()."modules as modules on course_modules.module=modules.id
            where course_modules.course=? group by modules.name",
            [$id_course]
        );
        foreach($records as $key => $object){
            $output[$object->name] = $object->count;
        }

        $records=$DB->get_records_sql(
            "select ".$DB->get_prefix()."modules.name as name, 0 as count
            from ".$DB->get_prefix()."modules
            where name not in (
                select modules.name as name
                from ".$DB->get_prefix()."course_modules as course_modules
                inner join ".$DB->get_prefix()."modules as modules on course_modules.module=modules.id
                where course_modules.course=? group by modules.name
            )",
            [$id_course]
        );
        foreach($records as $key => $object){
            $output[$object->name] = $object->count;
        }

        return $output;
    }

    //retourne le nombre de visite d'étudiant sur le cours d'ID $id_course
    //durant la période allant de $begin_timestamp à $end_timestamp
    public function count_student_visits_on_course(int $id_course, int $begin_timestamp, int $end_timestamp){
        global $DB;
        $student_role = configurator::getInstance()->get_student_role();

        $record=$DB->get_record_sql(
            "select count(*) as count
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
            where role.shortname=?
            and eventname='\\core\\event\\course_viewed'
            and courseid=?
            and context.instanceid=?
            and context.contextlevel=?
            and timecreated between ? and ?",
            array($student_role, $id_course, $id_course, CONTEXT_COURSE, $begin_timestamp, $end_timestamp)
        );

        return $record->count;
    }

    //compte le nombre d'utilisateurs uniques en fonction du cours et de la période choisie
    public function count_student_single_visitors_on_courses($ids_courses, int $begin_timestamp, int $end_timestamp){
        global $DB;
        $student_role = configurator::getInstance()->get_student_role();

        utils::precondition_ids($ids_courses);

        $length = count($ids_courses);

        if($length === 0) {
            return 0;
        }

        $where_compil = "(".$ids_courses[0];
        for($i = 1; $i < $length; $i++){
            $where_compil .= ", ".$ids_courses[$i];
        }
        $where_compil .= ")";

        $query="select count(distinct logs.userid) as count
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
            where role.shortname = ?
            and eventname like '%course_viewed'
            and context.contextlevel = ?
            and context.instanceid in ".$where_compil."
            and logs.courseid in ".$where_compil."
            and logs.timecreated between ? and ?";

        $params = array($student_role, CONTEXT_COURSE, $begin_timestamp, $end_timestamp);

        $record=$DB->get_record_sql($query, $params);
        return $record->count;
    }

    //retourne le nombre d'inscrits au cours d'ID $id_course selon la table d'assignement
    public function count_registered_students_of_course(int $id_course){
        global $DB;
        $student_role = configurator::getInstance()->get_student_role();
        $record=$DB->get_record_sql("select count(*) as count
            from ".$DB->get_prefix()."context as context
            inner join ".$DB->get_prefix()."role_assignments as assign on context.id=assign.contextid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            where role.shortname=?
            and context.instanceid=?
            and context.contextlevel=?",
            array($student_role, $id_course, CONTEXT_COURSE));
        return $record->count;
    }

    //retourne le nombre distinct d'étudiants inscrits dans au moins un cours
    //dont l'id est élément de la liste $ids_courses (selon la table d'enrôlement)
    public function count_distinct_registered_students_of_courses($ids_courses){
        global $DB;

        utils::precondition_ids($ids_courses);

        $length = count($ids_courses);

        if($length === 0) {
            return 0;
        }

        $where_courseid = "enrol.courseid in (".$ids_courses[0];

        for($i = 1; $i < $length; $i++){
            if(!is_numeric($ids_courses[$i]))
                return -1;
            $where_courseid .= ", ".$ids_courses[$i];
        }
        $where_courseid .= ")";


        $record = $DB->get_record_sql(
            "select count(distinct user_enrolments.userid) as count from mdl_user_enrolments as user_enrolments
            inner join mdl_enrol as enrol on user_enrolments.enrolid=enrol.id
            where ".$where_courseid." and (enrol.enrolenddate>? or enrol.enrolenddate=0)",
            array(strtotime("now"))
        );

        return $record->count;
    }

    //compte le nombre de clics sur les activités de l'espace de cours d'ID $id_course
    //par type d'activité et sur la période allant de $begin_timestamp à $end_timestamp
    public function count_hits_on_activities_per_type(int $id_course, int $begin_timestamp, int $end_timestamp){
        global $DB;
        $student_role = configurator::getInstance()->get_student_role();
        $params=array($student_role, $id_course, $id_course, CONTEXT_COURSE, $begin_timestamp, $end_timestamp);

        $records = $DB->get_records_sql(
            "select logs.objecttable as module, count(*) as count
            from ".$DB->get_prefix()."logstore_standard_log as logs
            inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
            inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
            inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
            where role.shortname=?
            and courseid=?
            and logs.target='course_module'
            and context.instanceid=?
            and context.contextlevel=?
            and timecreated between ? and ?
            group by logs.objecttable",
            $params
        );

        $output = array_map(function($obj): int {
            return $obj->count;
        }, $records);

        return $output;
    }


    /*Fonction de récupération des cours*/

    //retourne les cours d'une catégorie dans l'ordre choisi dans les paramètres moodle
    public function get_children_courses_ordered(int $id_category) {
        global $DB;

        $records = $DB->get_records_sql(
            "select * from ".$DB->get_prefix()."course
            where category = ? order by sortorder asc",
            array($id_category)
        );

        $output = array();

        $index = 0;
        foreach ($records as $record) {
            $output[$index] = $record;
            $index++;
        }

        return $output;
    }

    //retourne les sous catégories d'une catégorie dans l'ordre choisi dans les paramètres moodle
    public function get_children_categories_ordered(int $id_category) {
        global $DB;

        $records = $DB->get_records_sql(
            "select * from ".$DB->get_prefix()."course_categories
            where parent = ? order by sortorder asc",
            array($id_category)
        );

        $output = array();

        $index = 0;
        foreach ($records as $record) {
            $output[$index] = $record;
            $index++;
        }

        return $output;
    }

    public function get_children_courses_ids(int $id_category) {
        global $DB;

        $records = $DB->get_records(
            "course",
            array("category" => $id_category)
        );

        return array_map(
            function($course) {
                return $course->id;
            },
            $records
        );
    }

    public function get_children_categories_ids(int $id_category) {
        global $DB;
        
        $records = $DB->get_records(
            "course_categories",
            array("parent" => $id_category)
        );

        return array_map(
            function($category) {
                return $category->id;
            },
            $records
        );
    }

    public function get_courses_tree(int $id_category=1) {
        global $DB;

        $children_categories = $this->get_children_categories_ordered($id_category);
        $children_courses = $this->get_children_courses_ordered($id_category);
        $cat_data = [];


        $cat_data['data'] = $DB->get_record("course_categories", array("id" => $id_category));

        $cat_data['children_courses'] = $children_courses;

        $cat_data['children_categories'] = array_map(
            function ($category) {
                return $this->get_courses_tree($category->id);
            },
            $children_categories
        );

        return $cat_data;
    }

    //retourne le chemin complet de la catégorie $id_category
    public function get_category_path(int $id_category) {
        return $this->get_category_path_rec($id_category, "");
    }


    protected function get_category_path_rec(int $id_category, $output) {
        global $DB;

        $record = $DB->get_record(
            "course_categories",
            array("id" => $id_category)
        );

        if($record->parent == 0){
            return $output.$record->name;
        }
        else
            return $this->get_category_path_rec($record->parent, $output.$record->name."/");
    }

    //retourne les ids des cours actifs visibles non blacklistés dans un tableau
    public function get_whitelisted_courses_ids(){
        global $DB;

        $config = configurator::getInstance();
        $data = $config->get_data();
        $blacklisted_courses = array_keys($data["blacklisted_courses"]);
        
        //le cours qui correspond au site est blacklisté par défaut
        array_push($blacklisted_courses, 1);

        $query = "select course.id as id from ".
        $DB->get_prefix()."course as course where true";
         
        if (count($blacklisted_courses)>0) {
            $query .= " and course.id not in (".implode($blacklisted_courses,",").")";
        }

        $records=$DB->get_records_sql($query);
        
        $output = array_map(
            function($course) {
                return $course->id;
            },
            $records
        );

        return $output;
    }

    /* retourne dans un tableau d'objets les id, idnumber, nom complet et nom de catégorie des cours
     * dont l'id est un élément du tableau $ids et qui ont été visités par au moins un apprenant
     * durant la période allant du timestamp $begin_date au timestamp $end_date. 
     */
    public function filter_living_courses_on_period($ids_courses, $begin_timestamp, $end_timestamp){
        global $DB;
        $student_role = configurator::getInstance()->get_student_role();

        utils::precondition_ids($ids_courses);

        if(count($ids_courses) === 0) {
            return array();
        }

        $query = "select distinct course.id as id, course.idnumber as idnumber, course.fullname as fullname, category.id as category_id, category.name as category_name
        from ".$DB->get_prefix()."course as course
        inner join ".$DB->get_prefix()."logstore_standard_log as logs on course.id=logs.courseid
        inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
        inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
        inner join ".$DB->get_prefix()."course_categories as category on category.id = course.category
        where role.shortname = ?
        and logs.timecreated between ? and ?
        and logs.eventname like '%course_viewed'
        and course.id in (".implode($ids_courses,",").")";

        $records = $DB->get_records_sql($query, array($student_role, $begin_timestamp, $end_timestamp));
        
        return $records;
    }

    /*Gestion des tâches adhoc*/

    //compte le nombre de tâches adhoc
    public function count_adhoc_tasks(){
        global $DB;
        return $DB->count_records(
            "task_adhoc",
            array('classname' => '\\report_hybridmeter\\task\\traitement')
        );
    }

    //déprogramme toutes les tâches adhoc
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

    //programme une tâche adhoc au timestamp $timestamp
    public function schedule_adhoc_task($timestamp){
        $task = new \report_hybridmeter\task\traitement();
        $task->set_next_run_time($timestamp);
        \core\task\manager::queue_adhoc_task($task);
    }
}