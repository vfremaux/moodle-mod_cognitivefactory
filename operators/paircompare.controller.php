<?php
/**
* Module Brainstorm V2
* Operator : filter,ordering.
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/

/*********************************** Choosing randomly a first pair and initializing ************************/
if ($action == 'startpaircompare') {
    // delete all old ordering data
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => $page))) {
        // NOT AN ERROR : there was nothing to clear here
    }
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
    
    if (count($responses) >= 2) {
    
        $responseskeys = array_keys($responses);
        $rix = array_rand($responseskeys);
        $form = new StdClass();    
        $form->response1 = $responseskeys[$rix];
        $form->value1 = $responses[$form->response1]->response;
        unset($responses[$form->response1]);
        $responseskeys = array_keys($responses);
        $rix = array_rand($responseskeys);
        $form->response2 = $responseskeys[$rix];
        $form->value2 = $responses[$form->response2]->response;
        /// preparing all pair list
        $responseskeys = array_keys($responses);
        $pairs = array();
        for ($i = 0 ; $i < count($responseskeys) - 1 ; $i++) {
            for ($j = $i + 1 ; $j < count($responseskeys) ; $j++) {
                if ($responseskeys[$i] == $form->response1 and $responseskeys[$j] == $form->response2) continue;
                if ($responseskeys[$i] == $form->response2 and $responseskeys[$j] == $form->response1) continue;
                $pairs[] = $responseskeys[$i].'_'.$responseskeys[$j];
            }
        }
        $form->remains = count($pairs);
    
        $record = new StdClass();
        $record->userid = $USER->id;
        $record->groupid = $currentgroup;
        $record->operatorid = $page;
        $record->cognitivefactoryid = $cognitivefactory->id;
        $record->timemodified = time();
        $record->itemsource = 0;
        $record->blobvalue = implode(",", $pairs);
        if (!$DB->insert_record('cognitivefactory_opdata', $record)) {
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
    
        echo $OUTPUT->heading(get_string('paircompare','cognitiveoperator_order'));
        include "{$CFG->dirroot}/mod/cognitivefactory/operators/{$page}/paircompare.html";
        return -1;
    }
}
/*********************************** Choosing randomly a pair and propose it ************************/
if ($action == 'nextpaircompare') {
    $response1 = required_param('rep1', PARAM_INT);
    $response2 = required_param('rep2', PARAM_INT);
    $choice = required_param('choice', PARAM_INT);

    /// set or update counters
    $select = "
       cognitivefactoryid = {$cognitivefactory->id} AND
       operatorid = '{$page}' AND
       itemsource = {$response1} AND
       userid = {$USER->id}
    ";
    $orderrecord1 = $DB->get_record_select('cognitivefactory_opdata', $select);
    if (!$orderrecord1) {
        $orderrecord1 = new StdClass();
        $orderrecord1->userid = $USER->id;
        $orderrecord1->groupid = $currentgroup;
        $orderrecord1->operatorid = $page;
        $orderrecord1->cognitivefactoryid = $cognitivefactory->id;
        $orderrecord1->timemodified = time();
        $orderrecord1->itemsource = $response1;        
        $orderrecord1->intvalue = ($choice == $response1) ? 1 : 0 ;
        if (!$DB->insert_record('cognitivefactory_opdata', $orderrecord1)) {
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
    } else {
        if ($choice == $response1) {
            $orderrecord1->intvalue++;
            if (!$DB->update_record('cognitivefactory_opdata', $orderrecord1)) {
                print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        }
    }

    $select = "
       cognitivefactoryid = {$cognitivefactory->id} AND
       operatorid = '{$page}' AND
       itemsource = {$response2} AND
       userid = {$USER->id}
    ";
    $orderrecord2 = $DB->get_record_select('cognitivefactory_opdata', $select);
    if (!$orderrecord2) {
        $orderrecord2 = &$orderrecord1;
        $orderrecord2->itemsource = $response2;
        $orderrecord2->intvalue = ($choice == $response2) ? 1 : 0 ;
        if (!$DB->insert_record('cognitivefactory_opdata', $orderrecord2)) {
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
    } else {
        if ($choice == $response2) {
            $orderrecord2->intvalue++;
            if (!$DB->update_record('cognitivefactory_opdata', $orderrecord2)) {
                print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
            }
        }
    }

    /// getting marking record
    $select = "
       cognitivefactoryid = {$cognitivefactory->id} AND
       operatorid = '{$page}' AND
       itemsource = 0 AND
       userid = {$USER->id}
    ";
    if (!$marking = $DB->get_record_select('cognitivefactory_opdata', $select)) {
        print_error('errorbadrecordid', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
    }
    $markarray = explode(",", $marking->blobvalue);
    /// randomize new pair
    // We generate all possible pairs, and eliminate successively pairs that where already choosed.
    $pairs = array();
    $responses = cognitivefactory_get_responses($cognitivefactory->id, 0, $currentgroup, false);
    $guard = 0;
    if ($responses) {
        $responseskeys = array_keys($responses);
        for ($i = 0 ; $i < count($responseskeys) - 1 ; $i++) {
            for ($j = $i + 1 ; $j < count($responseskeys) ; $j++) {
                if ($responseskeys[$i] == $response1 and $responseskeys[$j] == $response2) continue;
                if ($responseskeys[$i] == $response2 and $responseskeys[$j] == $response1) continue;
                $pairs[] = $responseskeys[$i].'_'.$responseskeys[$j];
            }
        }
        do{
            $rix = array_rand($pairs);
            $pair = $pairs[$rix];
            $form = new StdClass();
            list($form->response1, $form->response2) = explode('_', $pair);
            unset($pairs[$rix]);
        } while (!empty($pairs) and (!in_array($form->response1.'_'.$form->response2, $markarray) && !in_array($form->response2.'_'.$form->response1, $markarray)));
    }

    /// updating mark record
    $pairkey1 = $form->response1.'_'.$form->response2;
    $pairkey2 = $form->response2.'_'.$form->response1;
    $pos = array_search($pairkey1, $markarray);
    if ($pos !== false) {
        if (count($markarray) == 1) {
            $markarray = array();
        } else {
            unset($markarray[$pos]);
        }
    }
    // echo " searching $pairkey2 in ". implode(",", $markarray).'<br/>';
    $pos = array_search($pairkey2, $markarray);  
    if ($pos !== false) {
        if (count($markarray) == 1) {
            $markarray = array();
        } else {
            unset($markarray[$pos]);
        }
    }

    $form->remains = count($markarray);
    if (empty($markarray)) {
        // bounce to last stage
        $processfinished = true;
        $action = 'resumepaircompare';
    } else {
        $marking->blobvalue = implode(",", $markarray);
        if (!$DB->update_record('cognitivefactory_opdata', $marking)) {
            print_error('errorupdate', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
        $form->value1 = $responses[$form->response1]->response;
        $form->value2 = $responses[$form->response2]->response;
        echo $OUTPUT->heading(get_string('paircompare','cognitiveoperator_'.$page));
        include "{$CFG->dirroot}/mod/cognitivefactory/operators/{$page}/paircompare.html";
        return -1;
    }
}
