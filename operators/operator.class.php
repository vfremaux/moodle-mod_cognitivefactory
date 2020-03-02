<?php

class BrainstormOperator{

    var $id;
    var $name;
    var $cognitivefactoryid;
    var $configdata;

    function __construct($cognitivefactoryid, $id) {
        global $DB;
        
        $this->id = $id;
        if ($id) {
            $this->cognitivefactoryid = $cognitivefactoryid;
            $oprecord = $DB->get_record('cognitivefactory_operators', array('cognitivefactoryid' => $cognitivefactoryid, 'operatorid' => $id));
            $this->configdata = (isset($oprecord->configdata)) ? unserialize($oprecord->configdata) : new StdClass() ;
            $this->active = ($oprecord) ? $oprecord->active : 1 ;
        }
    }
    
}
