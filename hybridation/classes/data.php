<?php

namespace report_hybridation\classes;

defined('MOODLE_INTERNAL') || die();

define("MODULE_ASSIGN", "assign");
define("MODULE_ASSIGNMENT","assignment");
define("MODULE_BOOK","book");
define("MODULE_CHAT","chat");
define("MODULE_CHOICE","choice");
define("MODULE_DATA","data");
define("MODULE_FEEDBACK","feedback");
define("MODULE_FOLDER","folder");
define("MODULE_FORUM","forum");
define("MODULE_GLOSSARY","glossary");
define("MODULE_H5P","h5pactivity");
define("MODULE_IMSCP","imscp");
define("MODULE_LABEL","label");
define("MODULE_LESSON","lesson");
define("MODULE_LTI","lti");
define("MODULE_PAGE","page");
define("MODULE_QUIZ","quiz");
define("MODULE_RESOURCE","resource");
define("MODULE_SCORM","scorm");
define("MODULE_SURVEY","survey");
define("MODULE_URL","url");
define("MODULE_WIKI","wiki");
define("MODULE_WORKSHOP","workshop");

define("NOW",strtotime("now"));

class data {
	public function __construct(){
	}

	//compte le nombre de quiz en fonction du cours
	public function count_quiz_id(int $id){
		global $DB;
		return $DB->count_records('quiz', array('course'=>$id));
	}

	//compte le nombre d'activités par type en fonction du cours
	public function count_modules_types_id(int $id){
		global $DB;
		$output=array();
		$records=$DB->get_records_sql("select modules.name, count(modules.name) as count from mdl_course_modules as course_modules inner join mdl_modules as modules on course_modules.module=modules.id where course_modules.course=? group by modules.name",[$id]);
		foreach($records as $key => $object){
			$output[$object->name] = $object->count;
		}
		$records=$DB->get_records_sql("select name, 0 as count from mdl_modules except (select modules.name, 0 from mdl_course_modules as course_modules inner join mdl_modules as modules on course_modules.module=modules.id where course_modules.course=? group by modules.name)",[$id]);
		foreach($records as $key => $object){
			$output[$object->name] = $object->count;
		}
		//print_r($output);
		return $output;
		//select modules.name, count(modules.name) from mdl_course_modules as course_modules inner join mdl_modules as modules on course_modules.module=modules.id where course_modules.course=2 group by modules.name;
		//select fullname, name from mdl_course_modules as lol inner join mdl_modules as mdr on lol.module=mdr.id inner join mdl_course as hehe on lol.course=hehe.id;
	}

	//compte le nombre de course viewed en fonction du cours et de la période choisie
	public function count_hits_course_viewed(int $id, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$record=$DB->get_record_sql("select count(*) as lol
			from mdl_logstore_standard_log as logs
			inner join mdl_role_assignments as assign on logs.userid=assign.userid
			inner join mdl_role as role on assign.roleid=role.id
			inner join mdl_context as context on assign.contextid=context.id
			where role.shortname='student'
			and eventname='\\core\\event\\course_viewed'
			and courseid=".$id."
			and context.instanceid=".$id."
			and context.contextlevel=".CONTEXT_COURSE."
			and timecreated between ".$begin_date." and ".$end_date);
		return $record->lol;
	}

	//compte le nombre de hits toute nature confondue en fonction du cours et de la période choisie
	public function count_hits(int $id, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$record=$DB->get_record_sql("select count(*) as lol
			from mdl_logstore_standard_log as logs
			inner join mdl_role_assignments as assign on logs.userid=assign.userid
			inner join mdl_role as role on assign.roleid=role.id
			inner join mdl_context as context on assign.contextid=context.id
			where role.shortname='student'
			and courseid=".$id."
			and context.instanceid=".$id."
			and context.contextlevel=".CONTEXT_COURSE."
			and timecreated between ".$begin_date." and ".$end_date);
		return $record->lol;
	}

	//compte le nombre d'utilisateurs uniques en fonction du cours et de la période choisie
	public function count_single_users_course_viewed(int $id, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$record=$DB->get_record_sql("select count(distinct logs.userid) as lol
			from mdl_logstore_standard_log as logs
			inner join mdl_role_assignments as assign on logs.userid=assign.userid
			inner join mdl_role as role on assign.roleid=role.id
			inner join mdl_context as context on assign.contextid=context.id
			where role.shortname='student'
			and eventname='\\core\\event\\course_viewed'
			and courseid=".$id."
			and context.instanceid=".$id."
			and context.contextlevel=".CONTEXT_COURSE."
			and timecreated between ".$begin_date." and ".$end_date);
		return $record->lol;
	}

	//compte le nombre d'inscrits en fonction du cours
	public function count_registered_users(int $id){
		global $DB;
		$record=$DB->get_record_sql("select count(*) as lol
			from mdl_context as context
			inner join mdl_role_assignments as assign on context.id=assign.contextid
			inner join mdl_role as role on assign.roleid=role.id
			where role.shortname='student'
			and context.instanceid=".$id."
			and context.contextlevel=".CONTEXT_COURSE);
		return $record->lol;
	}

	//compte le nombre d'utilisateurs actifs en fonction du cours et de la période choisie
	public function count_active_single_users(int $id, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$record=$DB->get_record_sql("select count(distinct logs.userid) as lol
			from mdl_logstore_standard_log as logs
			inner join mdl_role_assignments as assign on logs.userid=assign.userid
			inner join mdl_role as role on assign.roleid=role.id
			inner join mdl_context as context on assign.contextid=context.id
			where role.shortname='student'
			and courseid=".$id."
			and context.instanceid=".$id."
			and context.contextlevel=".CONTEXT_COURSE."
			and timecreated between ".$begin_date." and ".$end_date);
		return $record->lol;
	}

	//compte le nombre de hits en fonction du type d'activité visée, du cours et de la période choisie
	public function count_hits_by_module_type(int $id, $module_type, int $begin_date=0, int $end_date=NOW){
		global $DB;
		$record=$DB->get_record_sql("select count(*) as lol
			from mdl_logstore_standard_log as logs
			inner join mdl_role_assignments as assign on logs.userid=assign.userid
			inner join mdl_role as role on assign.roleid=role.id
			inner join mdl_context as context on assign.contextid=context.id
			where role.shortname='student'
			and courseid=".$id."
			and logs.target='course_module'
			and logs.objecttable='".$module_type."'
			and context.instanceid=".$id."
			and context.contextlevel=".CONTEXT_COURSE."
			and timecreated between ".$begin_date." and ".$end_date);
		return $record->lol;
	}

	//récupère les cours actifs visibles sans la blacklist
	public function get_courses_sanitized($blacklist=array()){
		global $DB;
		$records=$DB->get_records_sql("select * from mdl_course
			where format='topics'
			and visible = 1
			and enddate > ".strtotime('now'));

		//TODO : blacklist
		return $records;
	}
}