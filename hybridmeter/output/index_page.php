<?php

namespace report_hybridmeter\output;                                                                                                         
 
use renderable;                                                                                                                     
use renderer_base;                                                                                                                  
use templatable;                                                                                                                    
use stdClass;                                                                                                                       
 // TODO: voir utilité de cette classe
class index_page implements renderable, templatable {

    var $buttontext = null;
    var $link = null;                                                                                                          
 
    public function __construct($buttontext, $link) {                                                                                        
        $this->buttontext = $buttontext;
        $this->link = $link;
    }
                                                                                                             
    public function export_for_template(renderer_base $output) {                                                                    
        $data = new stdClass();                                                                                                     
        $data->buttontext = $this->buttontext;
        $data->link=$this->link;                                                                                   
        return $data;                                                                                                               
    }
}