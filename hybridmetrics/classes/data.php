<?php

namespace report_hybridmetrics\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/configurator.php');
require_once(dirname(__FILE__).'/utils.php');

require_once(__DIR__.'/configurator.php');



// TODO: refactoriser cette classe (Séparer fonctions de blacklist des fonctions de calcul)
class data {
	public function __construct(){
	}

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
	public function count_hits_course_viewed(int $id, int $begin_date=0, int $end_date=NOW){
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
	public function count_hits(int $id, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$record=$DB->get_record_sql("select count(*) as c
			from ".$DB->get_prefix()."logstore_standard_log as logs
			inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
			inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
			inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
			where role.shortname='student'
			and courseid=?
			and context.instanceid=?
			and context.contextlevel=?
			and timecreated between ? and ?",
			array($id, $id, CONTEXT_COURSE, $begin_date, $end_date));
		return $record->c;
	}

	//compte le nombre d'utilisateurs uniques en fonction du cours et de la période choisie
    public function count_single_users_course_viewed($ids, int $begin_date=0, int $end_date=NOW){
            global $DB;

            if(!is_array($ids)){
            	$ids=array($ids);
            }

            $length = count($ids);

            if($length === 0)
            	return 0;

            $where_courseid = "and enrol.courseid in (?";
            for($i = 1; $i < $length; $i++){
            	$where_courseid .= ", ?";
            }
            $where_courseid .= ")";

            $query="select count(distinct logs.userid) as c
			    from ".$DB->get_prefix()."logstore_standard_log as logs
			    inner join ".$DB->get_prefix()."user_enrolments as user_enrol on user_enrol.userid=logs.userid
			    inner join ".$DB->get_prefix()."enrol as enrol on user_enrol.enrolid=enrol.id
			    inner join ".$DB->get_prefix()."role as role on role.id=enrol.roleid
			    where role.shortname = 'student'
			    and eventname like'%course_viewed'
			    ".$where_courseid."
			    and logs.timecreated between ? and ?";

			$params = array_merge($ids, array($begin_date, $end_date));

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

	public function count_distinct_students($ids, int $begin_date=0, int $end_date=NOW){
		global $DB;

		$length = count($ids);

		if(count($ids) === 0)
			return 0;

		$where_courseid = "and courseid in (? ";
		for ($i = 1; $i < $length; $id++){
			$where_courseid .= " or courseid = ?";
		}

		$record = $DB->get_record_sql("select count(distinct logs.userid) as c
			from ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
			inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
			inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
			where role.shortname='student'
			context.contextlevel=?
			".$where_courseid,
			array_merge(array(CONTEXT_COURSE),$ids)
		);

		return $record->c;
	}

	//compte le nombre de hits en fonction du type d'activité visée, du cours et de la période choisie
	public function count_hits_by_module_type(int $id, $module_type, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$params=array($id, $module_type, $id, CONTEXT_COURSE, $begin_date, $end_date);

		$record=$DB->get_record_sql("select count(*) as c
			from ".$DB->get_prefix()."logstore_standard_log as logs
			inner join ".$DB->get_prefix()."role_assignments as assign on logs.userid=assign.userid
			inner join ".$DB->get_prefix()."role as role on assign.roleid=role.id
			inner join ".$DB->get_prefix()."context as context on assign.contextid=context.id
			where role.shortname='student'
			and courseid=?
			and logs.target='course_module'
			and logs.objecttable=?
			and context.instanceid=?
			and context.contextlevel=?
			and timecreated between ? and ?",
			$params);
		return $record->c;
	}
	//récupère les cours actifs visibles sans la blacklist
	public function get_whitelisted_courses(){
		global $DB;
		$config = new configurator();
		$data = $config->get_data();
		$blacklisted_courses = array_keys($data["blacklisted_courses"]);
		$blacklisted_categories = array_keys($data["blacklisted_categories"]);
		$query = "select * from ".$DB->get_prefix()."course where true";
		if (count($blacklisted_courses)>0) {
			$query.= " and id not in (".implode($blacklisted_courses,",").")";
		}
		if (count($blacklisted_categories)>0) {
			$query.= " and category not in (".implode($blacklisted_categories,",").")";
		}

		$records=$DB->get_records_sql($query);

		return $records;
	}

	//récupère les cours actifs visibles sans la blacklist
	public function get_courses_sanitized($blacklist = array()){
		global $DB;
		
		$idselector="";
		for($i=0; $i<count($blacklist); $i++){
			$idselector.=" and id <> ?";
		}

		$records=$DB->get_records_sql("select * from ".$DB->get_prefix()."course
			where id <> 1".$idselector, $blacklist);

		return $records;
	}

	public function get_courses_ids(array $ids){
		$output=array();
		foreach ( $ids as $id ){
			$output[$id]=get_course($id)->fullname;
		}
		return $output;
	}

	public function get_ids_blacklist(){
		global $DB;
		$records = $DB->get_records("report_hybridmetrics_blcours", array('blacklisted'=>1));
		return utils::id_objects_array_to_array($records);
	}

	public function set_category_blacklisted($id, $value){
		global $DB;
		$entry = $DB->get_record("report_hybridmetrics_blcat", array('id_category'=> $id));
		if (!$entry) {
			$entry = [
				"id_category" => $id,
				"blacklisted" => $value
			];
			$DB->insert_record("report_hybridmetrics_blcat", $entry);
		}
		else {
			$entry->blacklisted = $value;
        	$DB->update_record("report_hybridmetrics_blcat", $entry);
		}
	}
	public function set_course_blacklisted($id, $value){
		global $DB;
		$entry = $DB->get_record("report_hybridmetrics_blcours", array('id_course'=> $id));
		if (!$entry) {
			$entry = [
				"id_course" => $id,
				"blacklisted" => $value
			];
			$DB->insert_record("report_hybridmetrics_blcours", $entry);
		}
		else {
			$entry->blacklisted = $value;
        	$DB->update_record("report_hybridmetrics_blcours", $entry);
		}
	}
	public function search_course($query){
		return $DB->get_records_sql("select id, fullname, blacklisted from ".$DB->get_prefix()."course
			inner join ".$DB->get_prefix()."hybridmetrics_blacklist
			where id <> 1
			and upper(fullname) like ?",
			array('%'.mb_strtoupper($query).'%')
		);
	}

	public function count_adhoc_tasks(){
		global $DB;
		return $DB->count_records(
			"task_adhoc",
			array('classname' => '\\report_hybridmetrics\\task\\traitement')
		);
	}

	public function clear_adhoc_tasks(){
		global $DB;
		return $DB->delete_records(
			"task_adhoc",
			array('classname' => '\\report_hybridmetrics\\task\\traitement')
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
					array('classname' => '\\report_hybridmetrics\\task\\traitement')
				)
			)
		);
	}

	public function add_log_entry($timestamp, $backupfile){
		global $DB;

		return $DB->insert_record(
			'report_hybridmetrics_logs',
			array("timestamp" => $timestamp, "backupfile" => $backupfile)
		);
	}

	public function clear_running_tasks(){
		global $DB;
		return $DB->delete_records(
			"report_hybridmetrics_running"
		);
	}

	public function set_as_running($timestamp){
		global $DB;
		$this->clear_running_tasks();
		$DB->insert_record(
			"report_hybridmetrics_running",
			array("timestamp" => $timestamp)
		);
	}

	public function is_task_running(){
		global $DB;
		$count=$DB->count_records(
			"report_hybridmetrics_running"
		);

		return (($count > 0) ? 1 : 0);
	}
}