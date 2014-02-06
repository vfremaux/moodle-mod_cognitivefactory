<?php

/**
* Module Brainstorm V2
* Operator : map
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (!defined('MOODLE_INTERNAL')) die ("You cannot use this script this way");

/********************************** Saves locations ********************************/
if ($action == 'savemappings'){
    // first delete all old location data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'map'))){
    	// Not an error. Nothing to delete ?
    }

    $keys = preg_grep("/^map_/", array_keys($_POST));
    $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
    foreach($keys as $key){        
        preg_match("/^map_(.*)_(.*)/", $key, $matches);
        $maprecord = new StdClass;
        $maprecord->itemsource = $matches[1];
        $maprecord->itemdest = $matches[2];
        $maprecord->cognitivefactoryid = $cognitivefactory->id;
        $maprecord->operatorid = $page;
        $maprecord->userid = $USER->id;
        $maprecord->groupid = $currentgroup;
        if (!@$current_operator->configdata->quantified){
            $maprecord->intvalue = 1;
        }
        else{
            switch ($current_operator->configdata->quantifiertype){
                case 'integer' :
                    $input = required_param($key, PARAM_TEXT);
                    if (!empty($input))
                        $maprecord->intvalue = (int)$input;
                    break;
                case 'float' :
                    $input = required_param($key, PARAM_TEXT);
                    if (!empty($input))
                        $maprecord->floatvalue = (double)$input;
                    break;
                default :
                    $maprecord->blobvalue = required_param($key, PARAM_RAW);
            }
        }
        $maprecord->timemodified = time();
        if (!$DB->insert_record('cognitivefactory_opdata', $maprecord)){
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
    }
}
if ($action == 'clearmappings'){
    // delete all old location data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'map'))){
    	// Not an error. Nothing to delete ?
    }
}
if ($action == 'updatemultiple'){
	$form = new StdClass;
    $form->cognitivefactoryid = $cognitivefactory->id;    
    $form->itemsource = required_param('source', PARAM_INT);    
    $form->itemdest = required_param('dest', PARAM_INT);    
    $select = "
        cognitivefactoryid = {$cognitivefactory->id} AND
        userid = {$USER->id} AND
        itemsource = {$form->itemsource} AND
        itemdest = {$form->itemdest}
    ";
    $maprecord = $DB->get_record_select('cognitivefactory_opdata', $select);
    if($maprecord){
        $mapobject = unserialize($maprecord->blobvalue);
        $maparray = get_object_vars($mapobject);
        $i = 0;
        foreach($maparray as $key => $value){
            $keykey = 'item_name'.$i;
            $valuekey = 'item_value'.$i;
            $form->$keykey = $key;
            $form->$valuekey = $value;
            $i++;
        }
        $form->numparam = (($i % 3) + 1) * 3;    
    }
    $action = 'inputmultiple';
}
if ($action == 'inputmultiple'){
	$form = new StdClass;
    $form->cognitivefactoryid = $cognitivefactory->id;
    $form->itemsource = required_param('source', PARAM_INT);
    $form->itemdest = required_param('dest', PARAM_INT);
    $form->numparam = optional_param('numparam', 3, PARAM_INT);
    $keys = preg_grep('/^item_name/', array_keys($_POST));
    if($keys){
        foreach($keys as $key){
            preg_match('/^item_name(.*)/', $key, $matches);
            $keyid = $matches[1];
            $keykey = "item_name{$keyid}";
            $valuekey = "item_value{$keyid}";
            $form->$keykey = required_param("item_name{$keyid}", PARAM_TEXT);
            $form->$valuekey = required_param("item_value{$keyid}", PARAM_CLEANHTML);
        }
    }
    include "{$CFG->dirroot}/mod/cognitivefactory/operators/map/inputmultiple.html";
    return -1;
}
if ($action == 'doinputmultiple'){
	$form = new StdClass;
    $form->cognitivefactoryid = $cognitivefactory->id;    
    $form->itemsource = required_param('source', PARAM_INT);    
    $form->itemdest = required_param('dest', PARAM_INT);    
    $keys = preg_grep('/^item_name/', array_keys($_POST));
    $multiple = new StdClass;
    foreach($keys as $key){
        preg_match('/^item_name(.*)/', $key, $matches);
        $keyid = $matches[1];
        $keyvalue = required_param("item_name{$keyid}", PARAM_TEXT);
        if (empty($keyvalue)) continue; // discard empty field names
        $valuevalue = required_param("item_value{$keyid}", PARAM_CLEANHTML);
        $multiple->$keyvalue = $valuevalue;
    }
    if (isset($multiple)){
    	$maprecord = new StdClass;
        $maprecord->cognitivefactoryid = $cognitivefactory->id;
        $maprecord->userid = $USER->id;
        $maprecord->groupid = $currentgroup;
        $maprecord->operatorid = 'map';
        $maprecord->itemsource = $form->itemsource;
        $maprecord->itemdest = $form->itemdest;
        $maprecord->blobvalue = serialize($multiple);
        $maprecord->timemodified = time();
        $select = "
            cognitivefactoryid = {$cognitivefactory->id} AND
            userid = {$USER->id} AND
            itemsource = {$maprecord->itemsource} AND
            itemdest = {$maprecord->itemdest}
        ";
        if ($oldid = $DB->get_field_select('cognitivefactory_opdata', 'id', $select)){
            $maprecord->id = $oldid;
            if (!$DB->update_record('cognitivefactory_opdata', $maprecord)){
                print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        } else {
            if (!$DB->insert_record('cognitivefactory_opdata', $maprecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        }
    }
}
if ($action == 'deletemultiple'){
	$form = new StdClass;
    $form->itemsource = required_param('source', PARAM_INT);    
    $form->itemdest = required_param('dest', PARAM_INT);
    $select = "
        cognitivefactoryid = {$cognitivefactory->id} AND
        userid = {$USER->id} AND
        itemsource = {$form->itemsource} AND
        itemdest = {$form->itemdest}
    ";    
    if (!$DB->delete_records_select('cognitivefactory_opdata', $select)){
    	// Not an error. Nothing to delete ?
    }
}
