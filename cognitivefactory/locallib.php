<?PHP // $Id: lib.php,v 1.2 2004/08/24 16:36:18 diml Exp $

/**
* Module Brainstorm V2
* @author Martin Ellermann
* @reengineering Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009
*/

/**
* Includes and requires
*/
// this calls a set of functions that should be part of some central library
// but which are yet not candidate for HQ integration
require_once($CFG->dirroot.'/mod/cognitivefactory/extralib.php');

/**
*
* @uses USER
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param string $sort
* @returns array of responses
*/
function cognitivefactory_get_responses($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false, $sort='response'){    
    global $USER;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = $cognitivefactoryid 
        $accessClause
    ";
    if (!$responses = get_records_select('cognitivefactory_responses AS od', $select, $sort)) {
        $responses = array () ;
    }
    return $responses;
}

/**
*
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param string $sort
* @returns array of responses
*/
function cognitivefactory_count_responses($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false){    

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = $cognitivefactoryid 
        $accessClause
    ";

    return count_records_select('cognitivefactory_responses AS od', $select);
}

/**
*
* @param int $cognitivefactoryid
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
* @param string $sort
* @returns array of responses
*/
function cognitivefactory_count_operatorinputs($cognitivefactoryid, $userid=null, $groupid=0, $excludemyself=false){    

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = $cognitivefactoryid 
        $accessClause
    ";

    return count_records_select('cognitivefactory_operatordata AS od', $select);
}

/**
* a part of the operator plugin API
* @uses CFG
* @param int $cognitivefactoryid
* @returns array of operators
*/
function cognitivefactory_get_operators($cognitivefactoryid){
    global $CFG;
    
    $operators = array();
    
    $DIR = opendir($CFG->dirroot.'/mod/cognitivefactory/operators');
    while($opname = readdir($DIR)){
        if (!is_dir($CFG->dirroot.'/mod/cognitivefactory/operators/'.$opname)) continue;
        if (ereg("^(\\.|!)", $opname)) continue; // allows masking unused or unimplemented operators
        unset($operator);
        // real operator name
        $operator->id = $opname;
        $oprecord = get_record('cognitivefactory_operators', 'cognitivefactoryid', $cognitivefactoryid, 'operatorid', $opname);
        if ($oprecord){
            $operator->active = $oprecord->active;
            $operator->configdata = unserialize($oprecord->configdata);
        }
        else{
            $operator->active = 0;
            $operator->configdata = new Object();
        }
        $operators[$opname] = $operator;
    }
    return $operators;
}

/**
*
* @param array $operators
* @param string $separator
* @returns separated list of operator names
*/
function cognitivefactory_get_operatorlist($operators, $separator=','){
    $oparray = array();
    foreach($operators as $operator){
        if (!$operator->active) continue;
        $oparray[] = $operator->id;
    }
    return implode($separator, $oparray);
}

/**
* saves an operator configuration as a serialized object
* @param int $cognitivefactoryid
* @param int $operatorid
*/
function cognitivefactory_save_operatorconfig($cognitivefactoryid, $operatorid){
    $oprecord->id = get_field('cognitivefactory_operators', 'id', 'cognitivefactoryid', $cognitivefactoryid, 'operatorid', $operatorid);
    $configkeys = preg_grep("/^config_/", array_keys($_POST));
    foreach($configkeys as $akey){
        preg_match('/config_(.*)$/', $akey, $matches);
        $key = $matches[1];
        $configdata->$key = required_param($akey, PARAM_CLEANHTML);
    }
    $config = serialize($configdata);
    $oprecord->configdata = addslashes($config);
    if ($oprecord->id){
        if (!update_record('cognitivefactory_operators', $oprecord)){
            error("Could not update config record");
        }
    }
    else{
        if (!insert_record('cognitivefactory_operators', $oprecord)){
            error("Could not create config record");
        }
    }
}

/**
* A utility function that gets a SQL clause controlling the range of ownership in results from other queries.
* will contribute to cleanup code from all locallib files. 
* @param int $userid
* @param int $groupid
* @param boolean $excludemyself
*/
function cognitivefactory_get_accessclauses($userid=null, $groupid=0, $excludemyself=false){
    global $USER;

    if ($userid === null) $userid = $USER->id;
    $userClause = '';
    if ($excludemyself){
        $userClause = " AND od.userid != $USER->id " ;
    }
    else{
        $userClause = ($userid) ? " AND od.userid = $userid " : '' ;
    }
    $groupClause = ($groupid) ? " AND od.groupid = $groupid " : '' ;
    return "$userClause $groupClause";
}

/**
*
*/
function cognitivefactory_legal_include(){
    if (preg_match("/mod\\/cognitivefactory\\/view.php$/", $_SERVER['PHP_SELF'])){
        return true;
    }
    return false;
}

/**
* get all grades for a list of users
* @param int $cognitivefactoryid
* @param array $userids
*/
function cognitivefactory_get_grades($cognitivefactoryid, $userids){
    global $CFG;
    
    $useridlist = implode("','", $userids);
    
    $sql = "
       SELECT 
          bg.id as gradeid,
          u.id,
          u.firstname,
          u.lastname,
          u.email,
          u.picture,
          bg.grade,
          bg.gradeitem
       FROM
          {$CFG->prefix}user as u,
          {$CFG->prefix}cognitivefactory_grades as bg
       WHERE
          bg.cognitivefactoryid = {$cognitivefactoryid} AND
          u.id = bg.userid AND
          u.id in ('$useridlist')
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    return $records;
}

/**
* get a complete grade set for a user
* @param int $cognitivefactoryid
* @param int $userid
*/
function cognitivefactory_get_gradeset($cognitivefactoryid, $userid){
    global $CFG;
    
    $sql = "
       SELECT 
          id,
          userid,
          grade,
          gradeitem
       FROM
          {$CFG->prefix}cognitivefactory_grades
       WHERE
          cognitivefactoryid = {$cognitivefactoryid} AND
          userid = {$userid}
    ";
    if ($records = get_records_sql($sql)){
        foreach($records as $gradeitem){
            $gradeset->{$gradeitem->gradeitem} = $gradeitem->grade;
        }
        return $gradeset;
    }
    return null;
}

/**
* get a complete grade set for a user
* @param object $cognitivefactory
* @param int $userid
*/
function cognitivefactory_convert_to_single($cognitivefactory, $userid){
    global $CFG;
    
    $sql = "
       SELECT 
          id,
          userid,
          grade,
          gradeitem,
          timeupdated
       FROM
          {$CFG->prefix}cognitivefactory_grades
       WHERE
          cognitivefactoryid = {$cognitivefactoryid} AND
          userid = {$userid}
    ";
    if ($records = get_records_sql($sql)){
        foreach($records as $gradeitem){
            $gradeset->{$gradeitem->gradeitem} = $gradeitem->grade;
        }
        $gradeset->timeupdated = $gradeitem->timeupdated;

        if ($cognitivefactory->seqaccesscollect && isset($gradeset->participate)){
            $total[] = $gradeset->participate;
            $weights[] = $cognitivefactory->participationweight;
        }
        if ($cognitivefactory->seqaccessprepare && isset($gradeset->prepare)){
            $total[] = $gradeset->prepare;
            $weights[] = $cognitivefactory->preparingweight;
        }
        if ($cognitivefactory->seqaccessorganize && isset($gradeset->organize)){
            $total[] = $gradeset->organize;
            $weights[] = $cognitivefactory->organizeweight;
        }
        if ($cognitivefactory->seqaccessfeedback && isset($gradeset->feedback)){
            $total[] = $gradeset->feedback;
            $weights[] = $cognitivefactory->feedbackweight;
        }
        
        $totalweights = array_sum($weights);
        $totalgrade = 0;
        for($i = 0 ; $i < count($total) ; $i++){
            $totalgrade += $total[$i] * $weights[$i];
        }
        $totalgrade = ($totalweights != 0) ? $totalgrade / $totalweights : 0 ;
        
        $graderecord->cognitivefactoryid = $cognitivefactory->id;
        $graderecord->gradeitem = 'single';
        $graderecord->grade = round($totalgrade);
        $graderecord->userid = $userid;
        $graderecord->timeupdated = $gradeset->timeupdated; // keeps old time
        
        delete_records('cognitivefactory_grades', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $userid);
        if (!insert_record('cognitivefactory_grades', $graderecord)){
            error("Could not record converted grade");
        }
    }
}

/**
* get all ungrade user records (partial) in a user set
* @param int $cognitivefactoryid
* @param array $userids
*/
function cognitivefactory_get_ungraded($cognitivefactoryid, $userids){
    global $CFG;
    
    $useridlist = implode("','", $userids);
    
    $sql = "
       SELECT 
          u.id,
          u.firstname,
          u.lastname,
          u.email,
          u.picture
       FROM
          {$CFG->prefix}user as u
       LEFT JOIN
          {$CFG->prefix}cognitivefactory_grades as bg
       ON
          u.id = bg.userid AND
          bg.cognitivefactoryid = {$cognitivefactoryid}
       WHERE
          bg.grade IS NULL AND
          u.id in ('$useridlist')
    ";
    if (!$records = get_records_sql($sql)){
        return array();
    }
    return $records;
}

/**
*
*
*/
function cognitivefactory_get_feedback($cognitivefactoryid, $userid=null){
    global $USER;
    
    if (!$userid) $userid = $USER->id;
    $feedback = get_field('cognitivefactory_userdata', 'feedback', 'cognitivefactoryid', $cognitivefactoryid, 'userid', $userid);
    return $feedback;
}

/**
* prints cols for responses
*
*/
function cognitivefactory_print_responses_cols(&$cognitivefactory, &$responses, $return = false, $printchecks = false){
    
    $index = 0;
    $str = '';
    if ($responses){
        foreach ($responses as $response){
            $deletecheckbox = ($printchecks) ? "<input type=\"checkbox\" name=\"items[]\" value=\"{$response->id}\" /> " : '' ;
            if (($index > 0) && $index % $cognitivefactory->numcolumns == 0){
                $str .= '</tr><tr valign="top">';
            }
            $str .= '<th>' . ($index + 1) . '</th>';
            $str .= '<td>' . $deletecheckbox.$response->response . '</td>';
            $index++;
        }
        if (!$return){
            echo $str;
            return;
        }
    }
    return $str;
}

/**
*
*
*/
function cognitivefactory_have_reports($cognitivefactoryid, &$participantids){
    
    $participantlist = implode("','", $participantids);
    
    $select = "
       cognitivefactoryid = $cognitivefactoryid AND
       userid IN ('$participantlist') AND
       report IS NOT NULL
    ";
    $records = get_records_select('cognitivefactory_userdata', $select, '', 'userid,userid');
    return $records;
}
?>