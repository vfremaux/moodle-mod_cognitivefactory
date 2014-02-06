<?PHP // $Id: locallib.php,v 1.3 2012-07-07 17:49:23 vf Exp $

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

global $PHASES;
$PHASES = array('collect', 'prepare', 'organize', 'display', 'feedback');

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
    global $USER, $DB;

    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = ? 
        $accessClause
    ";
    if (!$responses = $DB->get_records_select('cognitivefactory_responses', $select, array($cognitivefactoryid), $sort)) {
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
	global $DB;
	
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = ? 
        $accessClause
    ";

    return $DB->count_records_select('cognitivefactory_responses', $select, array($cognitivefactoryid));
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
	global $DB;
	
    $accessClause = cognitivefactory_get_accessclauses($userid, $groupid, $excludemyself);
    $select = "
        cognitivefactoryid = ? 
        $accessClause
    ";

    return $DB->count_records_select('cognitivefactory_opdata', $select, array($cognitivefactoryid));
}

/**
* a part of the operator plugin API
* @uses CFG
* @param int $cognitivefactoryid
* @returns array of operators
*/
function cognitivefactory_get_operators($cognitivefactoryid){
    global $CFG, $DB;

    $operators = array();
    $DIR = opendir($CFG->dirroot.'/mod/cognitivefactory/operators');
    while($opname = readdir($DIR)){
    	if ($opname == 'CVS') continue;
        if (!is_dir($CFG->dirroot.'/mod/cognitivefactory/operators/'.$opname)) continue;
        if (preg_match("/^(\\.|!)/", $opname)) continue; // allows masking unused or unimplemented operators
        unset($operator);
        // real operator name
        $operator = new StdClass();
        $operator->id = $opname;
        $operator->name = $opname;
        $oprecord = $DB->get_record('cognitivefactory_operators', array('cognitivefactoryid' => $cognitivefactoryid, 'operatorid' => $opname));
        if ($oprecord){
            $operator->active = $oprecord->active;
            $operator->configdata = unserialize($oprecord->configdata);
        } else {
            $operator->active = 0;
            $operator->configdata = new Object();
        }
        $operators[$opname] = $operator;
    }
    closedir($DIR);
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
    if (is_null($separator)) return $oparray;

    return implode($separator, $oparray);
}

/**
* saves an operator configuration as a serialized object
* @param int $cognitivefactoryid
* @param int $operatorid
*/
function cognitivefactory_save_operatorconfig($cognitivefactoryid, $operatordata){
	global $DB;
	
	$oprecord = new StdClass();
    $oprecord->id = $DB->get_field('cognitivefactory_operators', 'id', array('cognitivefactoryid' => $cognitivefactoryid, 'operatorid' => $operatordata->operator));
    $configkeys = preg_grep("/^config_/", array_keys($_POST));
    $configdata = new StdClass();
    foreach($configkeys as $akey){
        preg_match('/config_(.*)$/', $akey, $matches);
        $key = $matches[1];
        if (is_array($_POST[$akey])){
	        $configdata->$key = required_param_array($akey, PARAM_CLEANHTML);
	        if (preg_match('/(.*)_editor$/', $key, $matches)){
	        	$canonickey = $matches[1];
	        	$editordata = $configdata->$key;
		        $configdata->$canonickey = format_text($editordata['text'], $editordata['format']);
	        }
        } else {
	        $configdata->$key = required_param($akey, PARAM_CLEANHTML);
        }
    }
    $config = serialize($configdata);
    $oprecord->configdata = $config;
    if ($oprecord->id){
        if (!$DB->update_record('cognitivefactory_operators', $oprecord)){
            print_error('errorupdate', 'cognitivefactory', '', get_string('operatorconfig', 'cognitivefactory'));
        }
    } else {
        if (!$DB->insert_record('cognitivefactory_operators', $oprecord)){
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatorconfig', 'cognitivefactory'));
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
function cognitivefactory_get_accessclauses($userid=null, $groupid=0, $excludemyself=false, $fieldprefix = ''){
    global $USER;

    if ($userid === null) $userid = $USER->id;
    $userClause = '';
    if ($excludemyself){
        $userClause = " AND {$fieldprefix}userid != $USER->id " ;
    } else {
        $userClause = ($userid) ? " AND {$fieldprefix}userid = $userid " : '' ;
    }
    $groupClause = ($groupid) ? " AND groupid = $groupid " : '' ;
    return "$userClause $groupClause";
}

/**
* get all grades for a list of users
* @param int $cognitivefactoryid
* @param array $userids
*/
function cognitivefactory_get_grades($cognitivefactoryid, $userids){
    global $CFG, $DB;

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
          {user} as u,
          {cognitivefactory_grades} as bg
       WHERE
          bg.cognitivefactoryid = ? AND
          u.id = bg.userid AND
          u.id in ('$useridlist')
    ";
    if (!$records = $DB->get_records_sql($sql, array($cognitivefactoryid))){
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
    global $CFG, $DB;

    $sql = "
       SELECT 
          id,
          userid,
          grade,
          gradeitem
       FROM
          {cognitivefactory_grades}
       WHERE
          cognitivefactoryid = ? AND
          userid = {$userid}
    ";
    if ($records = $DB->get_records_sql($sql, array($cognitivefactoryid))){
    	$gradeset = new StdClass();
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
    global $CFG, $DB;

    $sql = "
       SELECT 
          id,
          userid,
          grade,
          gradeitem,
          timeupdated
       FROM
          {cognitivefactory_grades}
       WHERE
          cognitivefactoryid = ? AND
          userid = {$userid}
    ";
    if ($records = $DB->get_records_sql($sql, array($cognitivefactoryid))){
		$gradeset = new StdClass();
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
        $graderecord = new StdClass();
        $graderecord->cognitivefactoryid = $cognitivefactory->id;
        $graderecord->gradeitem = 'single';
        $graderecord->grade = round($totalgrade);
        $graderecord->userid = $userid;
        $graderecord->timeupdated = $gradeset->timeupdated; // keeps old time
        $DB->delete_records('cognitivefactory_grades', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));
        if (!$DB->insert_record('cognitivefactory_grades', $graderecord)){
            print_error('errorinsert', 'cogntiviefactory', '', get_string('convertedgrades', 'cognitivefactory'));
        }
    }
}

/**
* get all ungrade user records (partial) in a user set
* @param int $cognitivefactoryid
* @param array $userids
*/
function cognitivefactory_get_ungraded($cognitivefactoryid, $userids){
    global $CFG, $DB;

    $useridlist = implode("','", $userids);
    $sql = "
       SELECT 
          u.id,
          u.firstname,
          u.lastname,
          u.email,
          u.picture,
          u.imagealt
       FROM
          {user} as u
       LEFT JOIN
          {cognitivefactory_grades} as bg
       ON
          u.id = bg.userid AND
          bg.cognitivefactoryid = ?
       WHERE
          bg.grade IS NULL AND
          u.id in ('$useridlist')
    ";
    if (!$records = $DB->get_records_sql($sql, array($cognitivefactoryid))){
        return array();
    }
    return $records;
}

/**
* if the feedback phase is in teacher mode, teachers can edit feedbacks for all students to view
* if the feedback phase in in student mode, each student has to write is own feedback and synthesis for teachers to review. 
*
* @param object $cognitivefactory the activity module instance
* @param int $userid the user ID
*/
function cognitivefactory_get_feedback($cognitivefactory, $userid = null){
    global $USER, $DB, $OUTPUT;

	$context = context_module::instance($cognitivefactory->cmid);
	$isteacher = has_capability('mod/cognitivefactory:grade', $context);
	
	$feedback = '';

	// get feedback of the user
    if (!$userid) $userid = $USER->id;
    
    if (!$isteacher || ($userid != $USER->id)){ // teachers cannot have feedback for themselves 
	    $text = $DB->get_field('cognitivefactory_userdata', 'feedback', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));	
	    $text = (empty($userfeedback)) ? get_string('nofeedback', 'cognitivefactory') : $text ;
	    $feedback .= $OUTPUT->box('<p align="left">'.$text.'</p>', 'cognitivefactory-feedback');
	}
    
    $feedback .= $OUTPUT->heading(get_string('globalfeedback', 'cognitivefactory'), 4);

    $text = $DB->get_field('cognitivefactory', 'globalteacherfeedback', array('id' => $cognitivefactory->id));	
    $text = (empty($userfeedback)) ? get_string('nofeedback', 'cognitivefactory') : $text ;
    $feedback .= $OUTPUT->box('<p align="left">'.$text.'</p>', 'cognitivefactory-feedback');

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
            if (($index > 0) && ($index % $cognitivefactory->numcolumns) == 0){
                $str .= '</tr><tr valign="top">';
            }
            $str .= '<th>' . ($index + 1) . '</th>';
            $str .= '<td>' . $deletecheckbox.$response->response . '</td>';
            $index++;
        }
    }

    if ($return){
        return $str;
    }
    echo $str;
}

/**
*
*
*/
function cognitivefactory_have_reports($cognitivefactory, $participantids = array()){
	global $DB;

    $participantlist = implode("','", $participantids);
    $participantclause =  (empty($participantlist)) ? '' : " userid IN ('$participantlist') AND " ;
    $select = "
       cognitivefactoryid = ? AND
       $participantclause
       report IS NOT NULL
    ";
    $records = $DB->get_records_select('cognitivefactory_userdata', $select, array($cognitivefactory->id), '', 'userid,userid');
    return $records;
}

function cognitivefactory_save_grades(&$cognitivefactory, $fromform){
	global $DB;
	
    $userid = $fromform->for;

    /// remove old records
    $DB->delete_records('cognitivefactory_grades', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));
    if ($cognitivefactory->singlegrade){
        $graderecord = new StdClass();
        $graderecord->cognitivefactoryid = $cognitivefactory->id;
        $graderecord->userid = $userid;
        $graderecord->grade = $fromform->grade;
        $graderecord->gradeitem = 'single';
        $graderecord->timeupdated = time();
        if (!$DB->insert_record('cognitivefactory_grades', $graderecord)){
            print_error('errorinsert', 'cognitivefactory', '', get_string('grade'));
        }        
    } else { // record dissociated grade
    	$graderecord = new StdClass();
        $graderecord->cognitivefactoryid = $cognitivefactory->id;
        $graderecord->userid = $userid;
        $graderecord->timeupdated = time();

        if ($cognitivefactory->seqaccesscollect){
            $graderecord->grade = $fromform->participate;
            $graderecord->gradeitem = 'participate';
            if (!$DB->insert_record('cognitivefactory_grades', $graderecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('grade'));
            }
        }
        if ($cognitivefactory->seqaccessprepare){
            $graderecord->grade = $fromform->prepare;
            $graderecord->gradeitem = 'prepare';
            if (!$DB->insert_record('cognitivefactory_grades', $graderecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('grade'));
            }
        }

        if ($cognitivefactory->seqaccessorganize){
            $graderecord->grade = $fromform->organize;
            $graderecord->gradeitem = 'organize';
            if (!$DB->insert_record('cognitivefactory_grades', $graderecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('grade'));
            }
        }

        if ($cognitivefactory->seqaccessorganize){
            $graderecord->grade = $fromform->feedback;
            $graderecord->gradeitem = 'feedback';
            if (!$DB->insert_record('cognitivefactory_grades', $graderecord)){
                print_error('errorinsert', 'cognitivefactory', '', get_string('grade'));
            }
        }
    }

    $userdatarecord = $DB->get_record('cognitivefactory_userdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));
    unset($userdatarecord->report);
    unset($userdatarecord->reportformat);
    $userdatarecord->feedback = $fromform->teacherfeedback['text'];
    $userdatarecord->feedbackformat = $fromform->teacherfeedback['format'];
    if (!$DB->update_record('cognitivefactory_userdata', $userdatarecord)){
        print_error('errorupdate', 'cognitivefactory', '', get_string('userfeedback', 'cognitivefactory'));
    }
}

function cognitivefactory_print_phase_buttons(&$cognitivefactory){
	global $USER;
	
	$str = '';
	
	$cmid = $cognitivefactory->cmid;
	$context = context_module::instance($cmid);
   	$ismanager = has_capability('mod/cognitivefactory:manage', $context);
	$cangrade = has_capability('mod/cognitivefactory:grade', $context, $USER->id, false);

    $collectstr = get_string('collect', 'cognitivefactory');
    $preparestr = get_string('prepare', 'cognitivefactory');
    $organizestr = get_string('organize', 'cognitivefactory');
    $displaystr = get_string('display', 'cognitivefactory');
    $feedbackstr = get_string('feedback', 'cognitivefactory');
    $gradestr = get_string('grading', 'cognitivefactory');

    $collectphaseclass = ($cognitivefactory->phase == PHASE_COLLECT) ? 'cognitivefactory-pressed' : 'cognitivefactory-raised' ;
    $preparephaseclass = ($cognitivefactory->phase == PHASE_PREPARE) ? 'cognitivefactory-pressed' : 'cognitivefactory-raised' ;
    $organizephaseclass = ($cognitivefactory->phase == PHASE_ORGANIZE) ? 'cognitivefactory-pressed' : 'cognitivefactory-raised' ;
    $displayphaseclass = ($cognitivefactory->phase == PHASE_DISPLAY) ? 'cognitivefactory-pressed' : 'cognitivefactory-raised' ;
    $feedbackphaseclass = ($cognitivefactory->phase == PHASE_FEEDBACK) ? 'cognitivefactory-pressed' : 'cognitivefactory-raised' ;
    $collectaccessclass = (!$cognitivefactory->seqaccesscollect) ? 'cognitivefactory-manager' : 'cognitivefactory-participant' ;
    $prepareaccessclass = (!$cognitivefactory->seqaccessprepare) ? 'cognitivefactory-manager' : 'cognitivefactory-participant' ;
    $organizeaccessclass = (!$cognitivefactory->seqaccessorganize) ? 'cognitivefactory-manager' : 'cognitivefactory-participant' ;
    $displayaccessclass = (!$cognitivefactory->seqaccessdisplay) ? 'cognitivefactory-manager' : 'cognitivefactory-participant' ;
    $feedbackaccessclass = (!$cognitivefactory->seqaccessfeedback) ? 'cognitivefactory-manager' : 'cognitivefactory-participant' ;

    if (!$ismanager){
    	$str = '<center>';
        if ($cognitivefactory->seqaccesscollect){
	        $str .= "<div class=\"cognitivefactory-phase {$collectphaseclass} {$collectaccessclass}\">{$collectstr}</div>";
	    }
        if ($cognitivefactory->seqaccessprepare){
	        $str .= "<div class=\"cognitivefactory-phase {$preparephaseclass} {$prepareaccessclass}\">{$preparestr}</div>";
	    }
        if ($cognitivefactory->seqaccessorganize){
        	$str .= "<div class=\"cognitivefactory-phase {$organizephaseclass} {$organizeaccessclass}\">{$organizestr}</div>";
    	}
        if ($cognitivefactory->seqaccessdisplay){
	        $str .= "<div class=\"cognitivefactory-phase {$displayphaseclass} {$displayaccessclass}\">{$displaystr}</div>";
	    }

        $str .= "<div class=\"cognitivefactory-phase {$feedbackphaseclass} {$feedbackaccessclass}\">{$feedbackstr}</div>";
    } else {
		$str .= '<center>';
        $str .= "<a href=\"view.php?id={$cmid}&amp;what=switchphase&amp;phase=0\"><div class=\"cognitivefactory-phase {$collectphaseclass} {$collectaccessclass}\">{$collectstr}</div></a>";
        $str .= "<a href=\"view.php?id={$cmid}&amp;what=switchphase&amp;phase=1\"><div class=\"cognitivefactory-phase {$preparephaseclass} {$prepareaccessclass}\">{$preparestr}</div></a>";
        $str .= "<a href=\"view.php?id={$cmid}&amp;what=switchphase&amp;phase=2\"><div class=\"cognitivefactory-phase {$organizephaseclass} {$organizeaccessclass}\">{$organizestr}</div></a>";
        $str .= "<a href=\"view.php?id={$cmid}&amp;what=switchphase&amp;phase=3\"><div class=\"cognitivefactory-phase {$displayphaseclass} {$displayaccessclass}\">{$displaystr}</div></a>";
        $str .= "<a href=\"view.php?id={$cmid}&amp;what=switchphase&amp;phase=4\"><div class=\"cognitivefactory-phase {$feedbackphaseclass} {$feedbackaccessclass}\">{$feedbackstr}</div></a>";
    }

    $str .= ($cangrade) ? "<a href=\"view.php?id={$cmid}&amp;view=grade\"><div class=\"cognitivefactory-phase cognitivefactory-raised cognitivefactory-manager\">{$gradestr}</div></a>" : '' ;
	$str .= '</center>';
	    
    return $str;
}

function cognitivefactory_check_jquery(){
	global $PAGE, $OUTPUT;

	$current = '1.8.2';
	
	if (empty($OUTPUT->jqueryversion)){
		$OUTPUT->jqueryversion = '1.8.2';
		$PAGE->requires->js('/mod/cognitivefactory/js/jquery-'.$current.'.min.js', true);
	} else {
		if ($OUTPUT->jqueryversion < $current){
			debugging('the previously loaded version of jquery is lower than required. This may cause issues to cognitivefactory functions. Programmers might consider upgrading JQuery version in the component that preloads JQuery library.', DEBUG_DEVELOPER, array('notrace'));
		}
	}
	
}

function cognitivefactory_requires(&$cognitivefactory, $page){
	global $CFG;
	
	$operators = cognitivefactory_get_operators($cognitivefactory->id);
	if (!in_array($page, array_keys($operators))) return;

	if (is_dir($CFG->dirroot.'/mod/cognitivefactory/operators/'.$page.'/js')){
		include_once $CFG->dirroot.'/mod/cognitivefactory/operators/'.$page.'/locallib.php';
		$funcname = $page.'_requires';
		if (function_exists($funcname)){
			$funcname();
		}
	}
}