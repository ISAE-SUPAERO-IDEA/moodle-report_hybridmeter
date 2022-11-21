<?php

namespace report_hybridmeter\classes;

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__)."/../../../config.php");
require_once(__DIR__."/../constants.php");
require_once(__DIR__."/utils.php");

defined('MOODLE_INTERNAL') || die();

use Exception;

class cache_manager {
    protected $cache;

    protected static $instance = null;

    public function __construct() {
        $this->cache = array();
    }

    public static function get_instance() {
        if(self::$instance == null){
            self::$instance = new cache_manager();
        }

        return self::$instance;
    }

    public function append_stack_key(string $key, $data) {
        if(isset($this->cache[$key]) && !is_array($this->cache[$key]))
            throw new Exception("The key is already associated with a variable that is not an array");
        
        if(!isset($this->cache[$key])){
            $this->cache[$key] = array();
        }

        array_push($this->cache[$key], $data);
    }

    public function update_associative_array_key(string $key, string $subkey, $data) {
        if(isset($this->cache[$key]) && !is_array($this->cache[$key]))
            throw new Exception("The key is already associated with a variable that is not an array");
        
        if(!isset($this->cache[$key])){
            $this->cache[$key] = array();
        }

        $this->cache[$key][$subkey] = $data;
    }

    public function update_key(string $key, $data) {
        $this->cache[$key] = $data;
    }

    public function unset_key(string $key) {
        unset($this->cache[$key]);
    }

    public function get_key(string $key) {
        return $this->cache[$key];
    }

    public function is_category_path_calculated(int $category_id) {
        return isset($this->cache["categories"][$category_id]);
    }

    public function update_category_path(int $category_id, $value) {
        $this->cache["categories"][$category_id] = $value;
    }

    public function get_category_path(int $category_id) {
        return $this->cache["categories"][$category_id];
    }
}
