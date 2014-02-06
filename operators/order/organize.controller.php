<?php

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

/********************************** Saves ordering ********************************/
if ($action == 'saveorder'){
    // first delete all old ordering data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'order'))){
        // NOT AN ERROR : there was nothing here
    }

    $keys = preg_grep("/^order_/", array_keys($_POST));
    if ($keys){
    	$orderrecord= new StdClass;
        $orderrecord->cognitivefactoryid = $cognitivefactory->id; 
        $orderrecord->operatorid = 'order'; 
        $orderrecord->userid = $USER->id; 
        $orderrecord->groupid = $currentgroup; 
        $orderrecord->timemodified = time(); 
        foreach($keys as $key){
            preg_match("/^order_(.*)/", $key, $matches);
            $orderrecord->intvalue = $matches[1];
            $orderrecord->itemsource = required_param($key, PARAM_INT);            
            if (!$DB->insert_record('cognitivefactory_opdata', $orderrecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        }
    }
}
if ($action == 'clearall'){
    // delete all old ordering data
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'order'))){
        // NOT AN ERROR : there was nothing to clear here
    }
}
$result = include "{$CFG->dirroot}/mod/cognitivefactory/operators/paircompare.controller.php";
if ($result == -1) return $result;
/*********************************** Resuming pair compare procedure ************************/
// this use case is specific to order operator as we need producing a valid operator data set for ordering
if ($action == 'resumepaircompare'){
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
    if (@$processedfinished){
        echo $OUTPUT->box(get_string('finished', 'cognitivefactory'));
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
            od.operatorid = 'order' AND
            od.userid = ?
        WHERE
            r.cognitivefactoryid = ?
        ORDER BY
            od.intvalue DESC
    ";
    $ordered = $DB->get_records_sql($sql, array($USER->id, $cognitivefactory->id));
    if ($ordered){
    	$table = new html_table();
        $table->head = array(get_string('response', 'cognitiveoperator_'.$page), get_string('rank', 'cognitiveoperator_'.$page));
        $table->size = array('80%', '20%');
        $table->align = array('left', 'center');
        foreach($ordered as $response){
            $table->data[] = array($response->response, $response->intvalue);
        }
        echo html_writer::table($table);
        print_string('reordering', 'cognitiveoperator_'.$page);
        /// reordering
        $i = 0;
        foreach($ordered as $response){
            if (!empty($response->operatorid)){ // if was ordered within the procedure, just update order
                unset($record);
                $record->id = $response->odid;
                $record->intvalue = $i;
                if (!$DB->update_record('cognitivefactory_opdata', $record)){
                    print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
                }            
            } else { // mark order in new record 
            	$orderrecord = new StdClass;
                $orderrecord->userid = $USER->id;
                $orderrecord->groupid = $currentgroup;
                $orderrecord->operatorid = 'order';
                $orderrecord->cognitivefactoryid = $cognitivefactory->id;
                $orderrecord->timemodified = time();
                $orderrecord->itemsource = $response->id;        
                $orderrecord->intvalue = $i;
                if (!$DB->insert_record('cognitivefactory_opdata', $orderrecord)){
                    print_error('errorinsert', 'cognitivefactory', '', get_string('reorderedrecord', 'cognitiveoperator_order'));
                }            
            }
            $i++;
        }
    }
    /// clean up database of temp records
    $select = "
       cognitivefactoryid = ? AND
       operatorid = 'order' AND
       userid = ? AND
       itemsource = 0
    ";
    $DB->delete_records_select('cognitivefactory_opdata', $select, array($cognitivefactory->id, $USER->id));

    /// print final continue button
    echo $OUTPUT->continue_button("{$CFG->wwwroot}/mod/cognitivefactory/view.php?id={$cm->id}&amp;operator={$page}");
    return -1;    
}

