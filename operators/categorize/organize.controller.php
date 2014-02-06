<?php

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/********************************** Save categorization ********************************/
if ($action == 'savecategorization'){
	$behalfed = optional_param('behalfed', $USER->id, PARAM_INT);
    // first delete all old categorization - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $behalfed, 'operatorid' => 'categorize'))){
        // NOT AN ERROR
    }
    $keys = preg_grep("/^cat_/", array_keys($_POST));
    foreach($keys as $key){        
        preg_match("/^cat_(.*)/", $key, $matches);
        $catrecord = new StdClass();
        $catrecord->cognitivefactoryid = $cognitivefactory->id;
        $catrecord->userid = $behalfed;
        $catrecord->groupid = $currentgroup;
        $catrecord->operatorid = 'categorize';
        $catrecord->itemsource = $matches[1];
        $catrecord->timemodified = time();
        if (is_array($_POST[$key])){ // multiple was enabled
            foreach($_POST[$key] as $category){
                if (empty($category)) continue;
                $catrecord->itemdest = $category;
                if (!$DB->insert_record('cognitivefactory_opdata', $catrecord)){
                    print_error('erroinsert', 'cognitivefactory', '', get_string('category', 'cognitivefactory'));
                }
            }
        } else { // multiple was disabled
            $catrecord->itemdest = $_POST[$key];
            if (!$_POST[$key]) continue;
            if (!$DB->insert_record('cognitivefactory_opdata', $catrecord)){
                print_error('erroinsert', 'cognitivefactory', '', get_string('category', 'cognitivefactory'));
            }
        }
    }
}
