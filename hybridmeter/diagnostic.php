<?php

require_once("classes/diagnostic/diagnostic_NU.php");
(new diagnostic_NU())->test();
echo "cool";

/*if(!@include("file.php"))
    echo "oe normal";

if(!include("jsaispas.php"))
    echo "leeeet's go";
else
    echo "fous le seum sah";


$old_error_reporting = ini_get('error_reporting');
error_reporting(0);
set_error_handler(function($errno,$errstr,$errfile,$errline) { 
    echo $errno.$errstr.$errfile.$errline."frrr";
    return true;
});
register_shutdown_function(function() {
    $last_error = error_get_last();
    echo "hohohooooo ".$last_error['message'].$last_error["file"].$last_error["line"];
});
echo $a;
//lolhaahha();
include("jsaispas.php");
error_reporting($old_error_reporting);
//print_r(error_get_last());*/