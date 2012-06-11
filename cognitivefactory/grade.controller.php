<?php

/**
* @package cognitivefactory
* @author Valery Fremaux / 1.8
* @date 05/01/2008
*
* A special controller for switching phases in sequential mode
*/
/**************************************** save a gradeset ***********************************/
if ($action == 'savegrade'){
    $userid = required_param('for', PARAM_INT);

    /// remove old records
    delete_records('cognitivefactory_grades', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $userid);
    
    if ($cognitivefactory->singlegrade){
        $grade = required_param('grade', PARAM_INT);
        $graderecord->cognitivefactoryid = $cognitivefactory->id;
        $graderecord->userid = $userid;
        $graderecord->grade = $grade;
        $graderecord->gradeitem = 'single';
        $graderecord->timeupdated = time();
        if (!insert_record('cognitivefactory_grades', $graderecord)){
            error("Could not insert grade");
        }        
    }
    else{ // record dissociated grade
        $graderecord->cognitivefactoryid = $cognitivefactory->id;
        $graderecord->userid = $userid;
        $graderecord->timeupdated = time();

        if ($cognitivefactory->seqaccesscollect){
            $participategrade = optional_param('participate', '', PARAM_INT);
            $graderecord->grade = $participategrade;
            $graderecord->gradeitem = 'participate';
            if (!insert_record('cognitivefactory_grades', $graderecord)){
                error("Could not insert grade");
            }
        }
            
        if ($cognitivefactory->seqaccessprepare){
            $preparegrade = optional_param('prepare', '', PARAM_INT);
            $graderecord->grade = $preparegrade;
            $graderecord->gradeitem = 'prepare';
            if (!insert_record('cognitivefactory_grades', $graderecord)){
                error("Could not insert grade");
            }
        }

        if ($cognitivefactory->seqaccessorganize){
            $organizegrade = optional_param('organize', '', PARAM_INT);
            $graderecord->grade = $organizegrade;
            $graderecord->gradeitem = 'organize';
            if (!insert_record('cognitivefactory_grades', $graderecord)){
                error("Could not insert grade");
            }
        }

        if ($cognitivefactory->seqaccessorganize){
            $feedbackgrade = optional_param('feedback', '', PARAM_INT);
            $graderecord->grade = $feedbackgrade;
            $graderecord->gradeitem = 'feedback';
            if (!insert_record('cognitivefactory_grades', $graderecord)){
                error("Could not insert grade");
            }
        }
    }

    $teacherfeedback = addslashes(optional_param('teacherfeedback', '', PARAM_CLEANHTML));
    $feedbackformat = addslashes(optional_param('feedbackformat', 0, PARAM_INT));
    $userdatarecord = get_record('cognitivefactory_userdata', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $userid);
    unset($userdatarecord->report);
    unset($userdatarecord->reportformat);
    $userdatarecord->feedback = $teacherfeedback;
    $userdatarecord->feedbackformat = $feedbackformat;
    if (!update_record('cognitivefactory_userdata', $userdatarecord)){
        error("Could not update user feedback record");
    }
}

/**************************************** delete an assessment ***********************************/
if ($action == 'deletegrade'){
    $userid = required_param('for', PARAM_INT);
    delete_records('cognitivefactory_grades', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $userid);
}
