<?php 

/**
* Module Brainstorm V2
* Operator : scale
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
$current_operator = new BrainstormOperator($cognitivefactory->id, $page);

/************************* Save scales *************************************/
if ($action == 'savescalings'){
    // first delete all old scaling data - the fastest way to do it
    if (!delete_records('cognitivefactory_operatordata', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $USER->id, 'operatorid', 'scale')){
        // NOT AN ERROR ! there was nothing to delete there
    }

    $keys = preg_grep("/^scale_/", array_keys($_POST));
    if ($keys){
        $scalerecord->cognitivefactoryid = $cognitivefactory->id; 
        $scalerecord->operatorid = 'scale'; 
        $scalerecord->userid = $USER->id; 
        $scalerecord->groupid = $currentgroup; 
        $scalerecord->timemodified = time(); 
        foreach($keys as $key){
            preg_match("/^scale_(.*)/", $key, $matches);
            $scalerecord->itemsource = $matches[1];
            switch($current_operator->configdata->quantifiertype){
                case 'moodlescale':
                    $value = required_param($key, PARAM_TEXT);
                    $scalerecord->blobvalue = (int)$value;   
                    break;
                case 'integer':
                    $value = required_param($key, PARAM_TEXT);
                    $scalerecord->blobvalue = $value;
                    break;
                default:
                    $value = required_param($key, PARAM_TEXT);
                    $scalerecord->floatvalue = (double)$value ;
            }
            if ($value === ''){
                continue;
            }
            if (!insert_record('cognitivefactory_operatordata', $scalerecord)){
                error("Could not insert scale record");
            }
        }
    }
}
if ($action == 'clearall'){
    // first delete all old scaling data - the fastest way to do it
    if (!delete_records('cognitivefactory_operatordata', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $USER->id, 'operatorid', 'scale')){
        // NOT AN ERROR ! there was nothing to clear
    }
}
?>