<?php

/**
* Module Brainstorm V2
* Operator : filter
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

/********************************** Saves a new filter ********************************/
if ($action == 'savefiltering' || $action == 'saveandreduce'){
    // first delete all old ordering data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'filter'))){
        print_error('errordelete', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
    }

    $inserted = array();
    $keys = preg_grep("/^keep_shadow_/", array_keys($_POST));
    foreach($keys as $key){        
        if ($_POST[$key] == 0) continue;
        preg_match("/^keep_shadow_(.*)/", $key, $matches);
        $filterrecord = new StdClass();
        $filterrecord->itemsource = $matches[1];
        $inserted[] = $matches[1];
        $filterrecord->cognitivefactoryid = $cognitivefactory->id;
        $filterrecord->operatorid = 'filter';
        $filterrecord->userid = $USER->id;
        $filterrecord->groupid = $currentgroup;
        $filterrecord->intvalue = 1;
        $filterrecord->timemodified = time();
        if (!$DB->insert_record('cognitivefactory_opdata', $filterrecord)){
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
    }
}
/********************************** Reduces entries ********************************/
if ($action == 'saveandreduce'){
    // first delete all old ordering data - the fastest way to do it
    $nottodeletelist = implode("','", $inserted);
    $groupClause = ($groupmode && $currentgroup) ? " AND groupid = $currentgroup " : '' ;

    /// deleting all instances of those entries in operatordata    
    $select = "
        cognitivefactoryid = $cognitivefactory->id AND
        operatorid = 'filter' AND
        (itemsource NOT IN ('$nottodeletelist') AND
        itemdest NOT IN ('$nottodeletelist'))
        $groupClause
    ";
    if (!$DB->delete_records_select('cognitivefactory_opdata', $select)){
        // print_error('errordelete', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
    }

    /// deleting in responses
    $select = "
        cognitivefactoryid = $cognitivefactory->id AND
        id NOT IN ('$nottodeletelist')
        $groupClause
    ";
    if (!$DB->delete_records_select('cognitivefactory_responses', $select)){
        print_error('errordelete', 'cognitivefactory', '', get_string('responses', 'cognitivefactory'));
    }

    /// deleting in filter
    $select = "
        cognitivefactoryid = $cognitivefactory->id AND
        responseid NOT IN ('$nottodeletelist')
        $groupClause
    ";
    if (!$DB->delete_records_select('cognitivefactory_categorize', $select)){
        print_error('errordelete', 'cognitivefactory', '', get_string('filterings', 'cognitivefactory'));
    }        
}
// use the generic pair comparison ordering procedure
$result = include "{$CFG->dirroot}/mod/cognitivefactory/operators/paircompare.controller.php";
if ($result == -1) return $result;
/*********************************** Resuming pair compare procedure ************************/
// this use case is specific to filter operator as we need producing a valid operator data set for filtering
// we set as deletable the lower ranked entries
if ($action == 'resumepaircompare'){
	echo $OUTPUT->heading(get_string('resumepaircompare', 'cognitiveoperator_'.$page));
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
    $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
    if (@$processedfinished){
        echo $OUTPUT->box(get_string('finished', 'cognitiveoperator_filter'));
    }

    // get ordered
    $sql = "
        SELECT
            r.id,
            r.response,
            od.intvalue,
            od.id as odid,
            od.operatorid
        FROM
            {cognitivefactory_responses} as r
        LEFT JOIN
            {cognitivefactory_opdata} as od
        ON
            r.id = od.itemsource AND 
            od.operatorid = 'filter' AND
            od.userid = {$USER->id} AND 
            od.itemsource != 0
        WHERE
            r.cognitivefactoryid = ?
        ORDER BY
            od.intvalue DESC
    ";
    $ordered = $DB->get_records_sql($sql, array($cognitivefactory->id));

    if ($ordered){
    	$table = new html_table();
        $table->head = array(get_string('response', 'cognitiveoperator_filter'), get_string('rank', 'cognitiveoperator_filter'), get_string('keepit', 'cognitiveoperator_filter'));
        $table->size = array('80%', '10%', '10%');
        $table->align = array('left', 'center', 'center');
        $datatoresponses = array();
        $i = 0;
        foreach($ordered as $response){
            $table->data[] = array($response->response, $response->intvalue, '');
            $datatoresponses[$i] = $response->id;
            $i++;
        }
        $tokeep = false;
        $keepodids = null;
        for ($i = count($ordered) - 1 ; $i >= 0 ; $i--){
            if (!isset($lastknownrank)) $lastknownrank = $table->data[$i][1];
            if (!$tokeep){
                if ($i > $current_operator->configdata->maxideasleft){
                    if ($table->data[$i][1] != $lastknownrank){
                        $lastknownrank = $table->data[$i][1];
                    }
                } else { // rank changes upper the maxideas number
                    if ($table->data[$i][1] != $lastknownrank){
                        $lastknownrank = $table->data[$i][1];
                        $tokeep = true;
                    }
                }
            }
            if ($tokeep){
                $keepodids[] = $datatoresponses[$i];
                $table->data[$i][2] = "<img src=\"".$OUTPUT->pix_url('check', 'cognitiveoperator_filter').'">'; 
            }
        }
        echo html_writer::table($table);

        print_string('recordingfilter', 'cognitiveoperator_filter');

        /// clean up database of all temp records

        $select = "
           cognitivefactoryid = {$cognitivefactory->id} AND
           operatorid = 'filter' AND
           userid = {$USER->id}
        ";
        $DB->delete_records_select('cognitivefactory_opdata', $select);

        /// record back all keep markers
        if($keepodids){
        	$orderrecord = new StdClass();
            $orderrecord->userid = $USER->id;
            $orderrecord->groupid = $currentgroup;
            $orderrecord->operatorid = 'filter';
            $orderrecord->cognitivefactoryid = $cognitivefactory->id;
            $orderrecord->timemodified = time();
            $orderrecord->intvalue = 1;
            foreach($keepodids as $keepid){
                $orderrecord->itemsource = $keepid;        
                if (!$DB->insert_record('cognitivefactory_opdata', $orderrecord)){
                    print_error('errorinsert', 'cognitivefactory', '', get_string('reorderedrecords', 'cognitiveoperator_filter'));
                }            
            }
        }
    }

    /// print final continue button
    echo $OUTPUT->continue_button("{$CFG->wwwroot}/mod/cognitivefactory/view.php?id={$cm->id}&amp;operator={$page}");
    return -1;    
}
