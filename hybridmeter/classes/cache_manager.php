<?php

namespace report_hybridmeter\classes;

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

    public static function getInstance() {
        if(self::$instance == null){
            self::$instance = new cache_manager();
        }

        return self::$instance;
    }

    public function append_stack_key($array_key, $data) {
        if(isset($this->cache[$array_key]) && !is_array($this->cache[$array_key]))
            throw new Exception("La clé est déjà associée à une variable qui n'est pas un tableau");
        
        if(!isset($this->cache[$array_key])){
            $this->cache[$array_key] = array();
        }

        array_push($this->cache[$array_key], $data);
    }

    public function update_associative_array_key($array_key, $key, $data) {
        if(isset($this->cache[$array_key]) && !is_array($this->cache[$array_key]))
            throw new Exception("La clé est déjà associée à une variable qui n'est pas un tableau");
        
        if(!isset($this->cache[$array_key])){
            $this->cache[$array_key] = array();
        }

        $this->cache[$array_key][$key] = $data;
    }

    public function update_key($key, $data) {
        $this->cache[$key] = $data;
    }

    public function unset_key($key) {
        unset($this->cache[$key]);
    }

    public function get_key($key) {
        return $this->cache[$key];
    }

    public function is_category_path_calculated($category_id) {
        return isset($this->cache["categories"][$category_id]);
    }

    public function update_category_path($category_id, $value) {
        $this->cache["categories"][$category_id] = $value;
    }

    public function get_category_path($category_id) {
        return $this->cache["categories"][$category_id];
    }
}