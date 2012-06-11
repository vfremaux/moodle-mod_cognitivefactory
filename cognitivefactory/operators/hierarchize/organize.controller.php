<?php

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
include_once("$CFG->dirroot/mod/cognitivefactory/treelib.php");

/********************************** make tree from scratch ********************************/
// take all the response in your own group
if ($action == 'maketree'){
    // first delete all old location data - the fastest way to do it
    if (!delete_records('cognitivefactory_operatordata', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $USER->id, 'operatorid', 'hierarchize')){
        error("Could not delete order records");
    }
    
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
    if ($responses){
        $treerecord->cognitivefactoryid = $cognitivefactory->id;
        $treerecord->userid = $USER->id;
        $treerecord->groupid = $currentgroup;
        $treerecord->operatorid = 'hierarchize';
        $treerecord->itemdest = 0;
        $treerecord->intvalue = 1;
        $treerecord->timemodified = time();
        foreach($responses as $response){
            $treerecord->itemsource = $response->id;
            if (!insert_record('cognitivefactory_operatordata', $treerecord)){
                error("Could not insert tree record");
            }
            $treerecord->intvalue++;
        }
    }
}
/********************************** reset tree data for your own ********************************/
if ($action == 'clearall'){
    // first delete all old location data - the fastest way to do it
    if (!delete_records('cognitivefactory_operatordata', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $USER->id, 'operatorid', 'hierarchize')){
        error("Could not delete order records");
    }
}
if ($action == 'up'){
    $itemid = required_param('item', PARAM_INT);
    cognitivefactory_tree_up($cognitivefactory->id, $USER->id, $currentgroup, $itemid, 1);
}
if ($action == 'down'){
    $itemid = required_param('item', PARAM_INT);
    cognitivefactory_tree_down($cognitivefactory->id, $USER->id, $currentgroup, $itemid, 1);
}
if ($action == 'left'){
    $itemid = required_param('item', PARAM_INT);
    cognitivefactory_tree_left($cognitivefactory->id, $USER->id, $currentgroup, $itemid);
}
if ($action == 'right'){
    $itemid = required_param('item', PARAM_INT);
    cognitivefactory_tree_right($cognitivefactory->id, $USER->id, $currentgroup, $itemid);
}
?>