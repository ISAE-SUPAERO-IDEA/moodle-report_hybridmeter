<?php

abstract class test {
    protected static function error_handler($errno, $errstr, $errfile, $errline) {
        echo "Je sais pas ".$errno." ".$errstr." ".$errfile." ".$errline."<br/><br/>";
    }

    protected static function fatal_handler() {
        $last_error = error_get_last();
        if($last_error !== null) {
            self::error_handler($last_error['type'], $last_error['str']);
        }
    }

    public function test(){
        $old_error_reporting = ini_get('error_reporting');
        error_reporting(0);
        set_error_handler("diagnostic_component::error_handler");
        register_shutdown_function("diagnostic_component::fatal_handler");
        echo "<h1>Including libraries</h1>";
        $this->include_all();
        echo "<h1>Proceeding to tests</h1>";
        $this->tests();
        error_reporting($old_error_reporting);
    }

    abstract protected function tests();
    abstract protected function include_all();
}
