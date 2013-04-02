<?php

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

/********************************** Enable an operator ********************************/
if ($action == 'enable'){
    $operatorid = required_param('operatorid', PARAM_ALPHA);
    $oprecord = get_record('cognitivefactory_operators', 'cognitivefactoryid', $cognitivefactory->id, 'operatorid', $operatorid);
    if ($oprecord){
        $oprecord->active = 1;
        if (!update_record('cognitivefactory_operators', $oprecord)){
            error("could not update record");
        }        
    }
    else{
        $oprecord->cognitivefactoryid = $cognitivefactory->id;
        $oprecord->operatorid = $operatorid;
        $oprecord->active = 1;
        $oprecord->configdata = serialize(new Object());
        if (!insert_record('cognitivefactory_operators', $oprecord)){
            error("could not insert record");
        }
    }
}
/********************************** Disable an operator ********************************/
if ($action == 'disable'){
    $operatorid = required_param('operatorid', PARAM_ALPHA);
    $oprecordid = get_field('cognitivefactory_operators', 'id', 'cognitivefactoryid', $cognitivefactory->id, 'operatorid', $operatorid);
    $oprecord->id = $oprecordid;
    $oprecord->active = 0;
    if (!update_record('cognitivefactory_operators', $oprecord)){
        error("could not update record");
    }
}
?>