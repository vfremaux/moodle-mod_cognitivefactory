<?php

class BrainstormOperator{

    var $id;
    var $cognitivefactoryid;
    var $configdata;

    function BrainstormOperator($cognitivefactoryid, $id){
        $this->id = $id;
        if ($id){
            $this->cognitivefactoryid = $cognitivefactoryid;
            $oprecord = get_record('cognitivefactory_operators', 'cognitivefactoryid', $cognitivefactoryid, 'operatorid', $id);
            $this->configdata = (isset($oprecord->configdata)) ? unserialize($oprecord->configdata) : new Object() ;
            $this->active = ($oprecord) ? $oprecord->active : 1 ;
        }
    }
}
?>