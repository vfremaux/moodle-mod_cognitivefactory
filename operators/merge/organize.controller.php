<?php

/**
* Module Brainstorm V2
* Operator : merge
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");

/********************************** Assign a choice of responses to a slot ********************************/
if ($action == 'assign'){
    $current_target = optional_param('to', null, PARAM_INT);
    /// first discard old assignation - the fastest way to do it

    $select = "
        cognitivefactoryid = {$cognitivefactory->id} AND
        operatorid = 'merge' AND
        groupid = {$currentgroup} AND
        intvalue = {$current_target}
    ";
    if (!$DB->delete_records_select('cognitivefactory_opdata', $select)){
        // IS NOT AN ERROR ; nothing previously assigned to delete
    }

    /// mark new assignation
    $checks = array_keys(merge_get_dataset_from_query('choose_'));
    if (count($checks)){
        foreach($checks as $check){
		    $mergerecord = new StdCLass;
		    $mergerecord->cognitivefactoryid = $cognitivefactory->id;
		    $mergerecord->userid = $USER->id;
		    $mergerecord->groupid = $currentgroup;
		    $mergerecord->operatorid = 'merge';
		    $mergerecord->timemodified = time();
            $mergerecord->itemsource = $check;
            $mergerecord->intvalue = $current_target;
            if (!$DB->insert_record('cognitivefactory_opdata', $mergerecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        }
        $nochecks = 1; // mark all checks out as they are not available any more in response list
    }    
}
/********************************** unassign the current target ********************************/
if ($action == 'unassign'){
    $unassigned = required_param('unassigned', PARAM_INT);
    /// first get old assignation to restore checks
    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'merge' AND
        groupid = ? AND
        intvalue = ?
    ";
    $oldassign = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactory->id, $currentgroup, $unassigned), 'id,id');

    /// discard old assignation - the fastest way to do it    
    if (!$DB->delete_records_select('cognitivefactory_opdata', $select, array($cognitivefactory->id, $currentgroup, $unassigned))){
        // IS NOT AN ERROR ; nothing previously assigned to delete
    }

    /// restore checks
    if ($oldassign){
        foreach($oldassign as $assign){
            $checks[] = $assign->id;
        }
    }    
}
/********************************** saves the merged data ********************************/
if ($action == 'savemerges' || $action == 'saveandreduce'){
    $merges = merge_get_dataset_from_query('merge_');
    $choices = merge_get_dataset_from_query('choice_');
    $kept = array();
    $added = array();
    //print_object($merges);
    //print_object($choices);

    /// delete all merging data    
    $sql = "
        UPDATE
            {cognitivefactory_opdata}
        SET
            blobvalue = NULL,
            itemdest = NULL
        WHERE
            cognitivefactoryid = {$cognitivefactory->id} AND
            operatorid = 'merge' AND
            groupid = {$currentgroup}
    ";
    $DB->execute($sql);

    /// saving changes, updating when necessary
    foreach($choices as $key => $choice){
        $mergerecord = new StdClass;
        $mergerecord->cognitivefactoryid = $cognitivefactory->id;
        $mergerecord->userid = $USER->id;
        $mergerecord->groupid = $currentgroup;
        $mergerecord->operatorid = 'merge';
        $mergerecord->timemodified = time();
        $mergerecord->itemsource = $choice;
        $mergerecord->itemdest = $choice;
        $mergerecord->intvalue = $key;
        $mergerecord->blobvalue = $merges[$key];

        $select = "
            cognitivefactoryid = ? AND
            userid = {$USER->id} AND
            operatorid = 'merge' AND
            itemsource = ? AND
            intvalue = ?     
        ";
        if ($oldid = $DB->get_field_select('cognitivefactory_opdata', 'id', $select, array($cognitivefactory->id, $choice, $key))){
            $mergerecord->id = $oldid;
            if ($choice){ // add the real records that should not be deleted when reducing
                $kept[] = $choice;
            }
            if (! $DB->update_record('cognitivefactory_opdata', $mergerecord)){
                print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        } else {
            if (!$newid = $DB->insert_record('cognitivefactory_opdata', $mergerecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            } else {
                $added[] = $newid;
            }
        }
    }
}
/********************************** Reduces entries ********************************/
if ($action == 'saveandreduce'){
    $groupClause = ($groupmode && $currentgroup) ? " AND groupid = $currentgroup " : '' ;
    /// adding new entries in responses
    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'merge' AND
        itemsource = 0 AND
        itemdest = 0
        $groupClause
    ";
    $newassignations = $DB->get_records_select('cognitivefactory_opdata', $select, array($cognitivefactory->id));
    if ($newassignations){
        /// get assignation records for addition

        foreach($newassignations as $assignation){
            // print_object($assignation);
            /// save a new response response with merged idea
            $responserecord = new StdClass;
            $responserecord->cognitivefactoryid = $cognitivefactory->id;
            $responserecord->userid = $assignation->userid;
            $responserecord->groupid = $assignation->groupid;
            $responserecord->response = $assignation->blobvalue;
            $responserecord->timemodified = time();
            // echo " adding response for ". $assignation->blobvalue."<br/>";
            if (!$newresponseid = $DB->insert_record('cognitivefactory_responses', $responserecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('addedresponses', 'cognitiveoperator_merge'));
            }
            /// tag assignation to refer to the newly created response
            $assignation->itemsource = $newresponseid;
            $assignation->itemdest = $newresponseid;

            /// add to selected so they will not be deleted later
            $kept[] = $newresponseid;

            // echo " updating operatordata for ". $assignation->blobvalue."<br/>";
            if (!$DB->update_record('cognitivefactory_opdata', $assignation)){
                print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        }
    }

    $nottodeletelist = implode("','", $kept);

    /// deleting all instances of my previous merges    
    $select = "
        cognitivefactoryid = ? AND
        operatorid = 'merge' AND
        userid = ?
        $groupClause
    ";
    // echo " <br/>deleting operatordata with $select";
    if (!$DB->delete_records_select('cognitivefactory_opdata', $select, array($cognitivefactory->id, $USER->id))){
    	// No error, nothing to delete ?
    }

    /// deleting in responses
    $select = "
        cognitivefactoryid = ? AND
        id NOT IN ('$nottodeletelist')
        $groupClause
    ";
    // echo "<br/>deleting responses with $select";
    if (!$DB->delete_records_select('cognitivefactory_responses', $select, array($cognitivefactory->id))){
    	// No errors, nothing to delete...
    }

    /// deleting in categorization
    $select = "
        cognitivefactoryid = ? AND
        responseid NOT IN ('$nottodeletelist')
        $groupClause
    ";
    // echo "<br/>deleting categories with $select";
    if (!$DB->delete_records_select('cognitivefactory_categorize', $select, array($cognitivefactory->id))){
    	// Not a real error. Nothing to delete ?
    } 
}
