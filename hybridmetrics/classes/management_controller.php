<?php

namespace report_hybridmetrics\classes;

defined('MOODLE_INTERNAL') || die();

// TODO: Utile?
class management_controller{

	protected $data;

	public function __construct($data){
		$this->data=$data;
	}

	public function blacklistcourse($id){
		return $this->data->add_course_to_blacklist($id);
	}

    public function blacklistcategory($id) {
        return $this->data->add_category_to_blacklist($id);
    }

    public function whitelistcourse($id){
        return $this->data->remove_course_from_blacklist($id);
    }

    public function whitelistcategory($id){
        return $this->data->remove_category_from_blacklist($id);
    }

	/**
     * Records when a category is expanded or collapsed so that when the user
     *
     * @param \core_course_category $coursecat The category we're working with.
     * @param bool $expanded True if the category is expanded now.
     */
    /*public static function record_expanded_category(array $coursecat, $expanded = true) {
        // If this ever changes we are going to reset it and reload the categories as required.
        self::$expandedcategories = null;
        $categoryid = $coursecat['id'];
        $path = $coursecat->get_parents();
        /* @var \cache_session $cache */
  /*      $cache = \cache::make('report_hybridmetrics', 'userselections');
        $categories = $cache->get('categorymanagementexpanded');
        if (!is_array($categories)) {
            if (!$expanded) {
                // No categories recorded, nothing to remove.
                return;
            }
            $categories = array();
        }
        if ($expanded) {
            $ref =& $categories;
            foreach ($coursecat->get_parents() as $path) {
                if (!isset($ref[$path]) || !is_array($ref[$path])) {
                    $ref[$path] = array();
                }
                $ref =& $ref[$path];
            }
            if (!isset($ref[$categoryid])) {
                $ref[$categoryid] = true;
            }
        } else {
            $found = true;
            $ref =& $categories;
            foreach ($coursecat->get_parents() as $path) {
                if (!isset($ref[$path])) {
                    $found = false;
                    break;
                }
                $ref =& $ref[$path];
            }
            if ($found) {
                $ref[$categoryid] = null;
                unset($ref[$categoryid]);
            }
        }
        $cache->set('categorymanagementexpanded', $categories);
    }*/

	/*public function blacklistitem($type,$id){
		switch($type){
			case HYBRIDATION_COURSE_TYPE_NAME:
				$data->add_course_to_blacklist($id);
				break;
			case HYBRIDATION_CATEGORY_TYPE_NAME:
				$data->add_category_to_blacklist($id);
				break;
			default:
				die("unexpected input : please check security");
		}
	}	

	public function whitelistitem($type,$id){
		switch($type){
			case HYBRIDATION_COURSE_TYPE_NAME:
				$data->remove_course_from_blacklist($id);
				break;
			case HYBRIDATION_CATEGORY_TYPE_NAME:
				$data->remove_category_from_blacklist($id);
				break;
			default:
				die("unexpected input : please check security");
		}
	}	*/


}