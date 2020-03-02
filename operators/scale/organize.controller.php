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
if ($action == 'savescalings') {
    // first delete all old scaling data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'scale'))) {
        // NOT AN ERROR ! there was nothing to delete there
    }
    
    $keys = preg_grep("/^iscale_/", array_keys($_POST));
    if ($keys) {
        foreach ($keys as $key) {
            $scalerecord = new StdClass();
            $scalerecord->cognitivefactoryid = $cognitivefactory->id; 
            $scalerecord->operatorid = 'scale'; 
            $scalerecord->userid = $USER->id; 
            $scalerecord->groupid = $currentgroup; 
            $scalerecord->timemodified = time(); 
            preg_match("/^iscale_(.*)/", $key, $matches);
            $scalerecord->itemsource = $matches[1];
            switch($current_operator->configdata->quantifiertype) {
                case 'moodlescale':
                    $value = required_param($key, PARAM_TEXT);
                    $scalerecord->intvalue = (int)$value;   
                    break;
                case 'integer':
                    $value = required_param($key, PARAM_TEXT);
                    $scalerecord->intvalue = $value;
                    break;
                default:
                    $value = required_param($key, PARAM_TEXT);
                    $scalerecord->floatvalue = (double)$value ;
            }
            if ($value === '') {
                continue;
            }
            if (!$DB->insert_record('cognitivefactory_opdata', $scalerecord)) {
                print_error('errorinsert', 'cognitivefactory', '', get_string('scalerecord', 'cognitiveoperator_'.$page));
            }
        }
    }
}
if ($action == 'clearall') {
    // first delete all old scaling data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'scale'))) {
        // NOT AN ERROR ! there was nothing to clear
    }
}
