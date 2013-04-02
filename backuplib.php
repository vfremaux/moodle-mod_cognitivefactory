<?php //$Id: backuplib.php,v 1.2 2012-06-18 15:19:31 vf Exp $
    //This php script contains all the stuff to backup/restore
    //cognitivefactory mods

    //This is the "graphical" structure of the cognitivefactory mod:
    //
    //                     cognitivefactory                                      
    //                    (CL, pk->id)
    //                        |
    //                        +----------------------------------------+
    //                        |                                        |
    //                   cognitivefactory_operator                   cognitivefactory_responses
    //               (IL, pk->id, fk->cognitivefactoryid)          (IL, pk->id, fk->userid, fk->groupid,
    //                                                             fk->cognitivefactoryid)
    //                                                                 |
    //         +--------------------------------+----------------------+---------------------+
    //         |                                |                      |                     |
    // cognitivefactory_userdata             cognitivefactory_operatordata         |        cognitivefactory_categories
    // (IL, pk->id,fk->cognitivefactoryid  (IL, pk->id, fk->cognitivefactoryid,    |    (IL, pk->id, fk->cognitivefactoryid,
    // fk->userid)                       fk->userid, fk->groupid,      |        pk->userid, pk->groupid)
    //                                         fk->operatorid)         |         
    //                                                                 |
    //                                                           cognitivefactory_grades
    //                                                       (IL, pk->id, fk->cognitivefactoryid,
    //                                                                fk->userid)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          IL->instance level info
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files
    //
    //-----------------------------------------------------------

    //This function executes all the backup procedure about this mod
    function cognitivefactory_backup_mods($bf, $preferences) {
        global $CFG;

        $status = true; 
        
        ////Iterate over cognitivefactory table
        $cognitivefactorys = get_records('cognitivefactory', 'course', $preferences->backup_course);

        if ($cognitivefactorys) {
            foreach ($cognitivefactorys as $cognitivefactory) {
                $status = cognitivefactory_backup_one_mod($bf, $preferences, $cognitivefactory);
            }
        }
        return $status;
    }

    function cognitivefactory_backup_one_mod($bf, $preferences, $cognitivefactory){

        if (is_numeric($cognitivefactory)) {
            $cognitivefactory = get_record('cognitivefactory', 'id', $cognitivefactory);
        }
        
        $status = true;

        //Start mod
        $status = $status && fwrite ($bf, start_tag('MOD', 3, true));
        //Print cognitivefactory data
        fwrite ($bf, full_tag('ID', 4, false, $cognitivefactory->id));
        fwrite ($bf, full_tag('MODTYPE', 4, false, 'cognitivefactory'));
        fwrite ($bf, full_tag('NAME', 4, false, $cognitivefactory->name));
        fwrite ($bf, full_tag('DESCRIPTION', 4, false, $cognitivefactory->description));
        fwrite ($bf, full_tag('COLLECTREQUIREMENT', 4, false, $cognitivefactory->collectrequirement));
        fwrite ($bf, full_tag('FLOWMODE', 4, false, $cognitivefactory->flowmode));
        fwrite ($bf, full_tag('SEQACCESSCOLLECT', 4, false, $cognitivefactory->seqaccesscollect));  
        fwrite ($bf, full_tag('SEQACCESSPREPARE', 4, false, $cognitivefactory->seqaccessprepare));  
        fwrite ($bf, full_tag('SEQACCESSORGANIZE', 4, false, $cognitivefactory->seqaccessorganize));  
        fwrite ($bf, full_tag('SEQACCESSDISPLAY', 4, false, $cognitivefactory->seqaccessdisplay));
        fwrite ($bf, full_tag('SEQACCESSFEEDBACK', 4, false, $cognitivefactory->seqaccessfeedback));
        fwrite ($bf, full_tag('PHASE', 4, false, $cognitivefactory->phase));  
        fwrite ($bf, full_tag('PRIVACY', 4, false, $cognitivefactory->privacy));  
        fwrite ($bf, full_tag('NUMRESPONSES', 4, false, $cognitivefactory->numresponses));  
        fwrite ($bf, full_tag('NUMRESPONSESINFORM', 4, false, $cognitivefactory->numresponsesinform));  
        fwrite ($bf, full_tag('NUMCOLUMNS', 4, false, $cognitivefactory->numcolumns));
        fwrite ($bf, full_tag('OPREQUIREMENTTYPE', 4, false, $cognitivefactory->oprequirementtype));
        fwrite ($bf, full_tag('SCALE', 4, false, $cognitivefactory->scale));
        fwrite ($bf, full_tag('SINGLEGRADE', 4, false, $cognitivefactory->singlegrade));
        fwrite ($bf, full_tag('PARTICIPATIONWEIGHT', 4, false, $cognitivefactory->participationweight));
        fwrite ($bf, full_tag('PREPARINGWEIGHT', 4, false, $cognitivefactory->preparingweight));
        fwrite ($bf, full_tag('ORGANIZEWEIGHT', 4, false, $cognitivefactory->organizeweight));
        fwrite ($bf, full_tag('FEEDBACKWEIGHT', 4, false, $cognitivefactory->feedbackweight));
        fwrite ($bf, full_tag('TIMEMODIFIED', 4, false, $cognitivefactory->timemodified));

        $status = $status && backup_cognitivefactory_operators($bf, $preferences, $cognitivefactory->id);

        //if we've selected to backup users info, then execute backup_cognitivefactory_slots and appointments
        if ($preferences->mods['cognitivefactory']->userinfo) {
            $status = $status && backup_cognitivefactory_categories($bf, $preferences, $cognitivefactory->id);
            $status = $status && backup_cognitivefactory_operatordata($bf, $preferences, $cognitivefactory->id);
            $status = $status && backup_cognitivefactory_responses($bf, $preferences, $cognitivefactory->id);
            $status = $status && backup_cognitivefactory_userdata($bf, $preferences, $cognitivefactory->id);
            $status = $status && backup_cognitivefactory_grades($bf, $preferences, $cognitivefactory->id);
        }
        //End mod
        $status = $status && fwrite($bf, end_tag('MOD', 3, true));

        return $status;
    }

    //Backup operator settings (executed from cognitivefactory_backup_mods)
    function backup_cognitivefactory_operators ($bf, $preferences, $cognitivefactoryid) {
        global $CFG;

        $status = true;
        
        $operators = get_records('cognitivefactory_operators', 'cognitivefactoryid', $cognitivefactoryid);

        //If there is operators
        if ($operators) {
            //Write start tag
            $status = $status && fwrite ($bf, start_tag('OPERATORS', 4, true));
            //Iterate over each operator
            foreach ($operators as $operator) {
                //Start slot
                $status = $status && fwrite ($bf, start_tag('OPERATOR', 5, true));
                //Print cognitivefactory_slots contents
                fwrite ($bf, full_tag('ID', 6, false, $operator->id));
                fwrite ($bf, full_tag('BRAINSTORMID', 6, false, $operator->cognitivefactoryid));  
                fwrite ($bf, full_tag('OPERATORID', 6, false, $operator->operatorid));  
                fwrite ($bf, full_tag('CONFIGDATA', 6, false, $operator->configdata));  
                fwrite ($bf, full_tag('ACTIVE', 6, false, $operator->active));  
                //End slot
                $status = $status && fwrite ($bf, end_tag('OPERATOR', 5, true));
            }
            //Write end tag
            $status = $status && fwrite($bf, end_tag('OPERATORS', 4, true));
        }
        return $status;
    }

    //Backup cognitivefactory categories (executed from cognitivefactory_backup_mods)
    function backup_cognitivefactory_categories($bf, $preferences, $cognitivefactoryid) {
        global $CFG;

        $status = true;

        $categories = get_records('cognitivefactory_categories', 'cognitivefactoryid', $cognitivefactoryid);

        $status = $status && fwrite ($bf, start_tag('CATEGORIES', 4, true));
        //Iterate over each categories
        foreach ($categories as $category) {
            //Start categories
            $status = $status && fwrite ($bf, start_tag('CATEGORY', 5, true));
            //Print category data
            fwrite ($bf, full_tag('ID', 6, false, $category->id));
            fwrite ($bf, full_tag('BRAINSTORMID', 6, false, $category->cognitivefactoryid));
            fwrite ($bf, full_tag('USERID', 6, false, $category->userid));
            fwrite ($bf, full_tag('GROUPID', 6, false, $category->groupid));
            fwrite ($bf, full_tag('TITLE', 6, false, $category->title));
            fwrite ($bf, full_tag('TIMEMODIFIED', 6, false, $category->timemodified));
            //End category
            $status = $status && fwrite ($bf, end_tag('CATEGORY', 5, true));
        }
        //Write end tag
        $status = $status && fwrite($bf, end_tag('CATEGORIES', 4, true));

        return $status;
    }

    //Backup cognitivefactory responses (executed from cognitivefactory_backup_mods)
    function backup_cognitivefactory_responses($bf, $preferences, $cognitivefactoryid) {
        global $CFG;

        $status = true;

        $responses = get_records('cognitivefactory_responses', 'cognitivefactoryid', $cognitivefactoryid);

        $status = $status && fwrite ($bf, start_tag('RESPONSES', 4, true));
        //Iterate over each response
        foreach ($responses as $response) {
            //Start responses
            $status = $status && fwrite ($bf, start_tag('RESPONSE', 5, true));
            //Print response data
            fwrite ($bf, full_tag('ID', 6, false, $response->id));
            fwrite ($bf, full_tag('BRAINSTORMID', 6, false, $response->cognitivefactoryid));  
            fwrite ($bf, full_tag('USERID', 6, false, $response->userid));
            fwrite ($bf, full_tag('GROUPID', 6, false, $response->groupid));
            fwrite ($bf, full_tag('RESPONSE', 6, false, $response->response));
            fwrite ($bf, full_tag('TIMEMODIFIED', 6, false, $response->timemodified));
            //End response
            $status = $status && fwrite ($bf, end_tag('RESPONSE', 5, true));
        }
        //Write end tag
        $status = $status && fwrite($bf, end_tag('RESPONSES', 4, true));

        return $status;
    }

    //Backup cognitivefactory operator data (executed from cognitivefactory_backup_mods)
    function backup_cognitivefactory_operatordata($bf, $preferences, $cognitivefactoryid) {
        global $CFG;

        $status = true;

        $data = get_records('cognitivefactory_operatordata', 'cognitivefactoryid', $cognitivefactoryid);

        $status = $status && fwrite ($bf, start_tag('DATA', 4, true));
        //Iterate over each datum
        foreach ($data as $datum) {
            //Start data
            $status = $status && fwrite ($bf, start_tag('DATUM', 5, true));
            //Print operator datum
            fwrite ($bf, full_tag('ID', 6, false, $datum->id));
            fwrite ($bf, full_tag('BRAINSTORMID', 6, false, $datum->cognitivefactoryid));  
            fwrite ($bf, full_tag('USERID', 6, false, $datum->userid));
            fwrite ($bf, full_tag('GROUPID', 6, false, $datum->groupid));
            fwrite ($bf, full_tag('OPERATORID', 6, false, $datum->operatorid));  
            fwrite ($bf, full_tag('ITEMSOURCE', 6, false, $datum->itemsource));  
            fwrite ($bf, full_tag('ITEMDEST', 6, false, $datum->itemdest));  
            fwrite ($bf, full_tag('INTVALUE', 6, false, $datum->intvalue));  
            fwrite ($bf, full_tag('FLOATVALUE', 6, false, $datum->floatvalue));  
            fwrite ($bf, full_tag('BLOBVALUE', 6, false, $datum->blobvalue)); 
            fwrite ($bf, full_tag('TIMEMODIFIED', 6, false, $datum->timemodified));
            //End datum
            $status = $status && fwrite ($bf, end_tag('DATUM', 5, true));
        }
        //Write end tag
        $status = $status && fwrite($bf, end_tag('DATA', 4, true));

        return $status;
    }

    //Backup cognitivefactory user specific data (executed from cognitivefactory_backup_mods)
    function backup_cognitivefactory_userdata($bf, $preferences, $cognitivefactoryid) {
        global $CFG;

        $status = true;

        $userdata = get_records('cognitivefactory_userdata', 'cognitivefactoryid', $cognitivefactoryid);

        $status = $status && fwrite ($bf, start_tag('USERDATA', 4, true));
        //Iterate over each datum
        foreach ($userdata as $datum) {
            //Start userdata
            $status = $status && fwrite ($bf, start_tag('DATUM', 5, true));
            //Print user datum
            fwrite ($bf, full_tag('ID', 6, false, $datum->id));
            fwrite ($bf, full_tag('BRAINSTORMID', 6, false, $datum->cognitivefactoryid));  
            fwrite ($bf, full_tag('USERID', 6, false, $datum->userid));
            fwrite ($bf, full_tag('REPORT', 6, false, $datum->report));
            fwrite ($bf, full_tag('REPORTFORMAT', 6, false, $datum->reportformat));  
            fwrite ($bf, full_tag('FEEDBACK', 6, false, $datum->feedback));  
            fwrite ($bf, full_tag('FEEDBACKFORMAT', 6, false, $datum->feedbackformat));  
            fwrite ($bf, full_tag('TIMEUPDATED', 6, false, $datum->timeupdated));
            //End datum
            $status = $status && fwrite ($bf, end_tag('DATUM', 5, true));
        }
        //Write end tag
        $status = $status && fwrite($bf, end_tag('USERDATA', 4, true));

        return $status;
    }

    //Backup cognitivefactory grading data (executed from cognitivefactory_backup_mods)
    function backup_cognitivefactory_grades($bf, $preferences, $cognitivefactoryid) {
        global $CFG;

        $status = true;

        $grades = get_records('cognitivefactory_grades', 'cognitivefactoryid', $cognitivefactoryid);

        $status = $status && fwrite ($bf, start_tag('GRADES', 4, true));
        //Iterate over each grade
        foreach ($grades as $grade) {
            //Start grade
            $status = $status && fwrite ($bf, start_tag('GRADE', 5, true));
            //Print grade
            fwrite ($bf, full_tag('ID', 6, false, $grade->id));
            fwrite ($bf, full_tag('BRAINSTORMID', 6, false, $grade->cognitivefactoryid));  
            fwrite ($bf, full_tag('USERID', 6, false, $grade->userid));
            fwrite ($bf, full_tag('GRADE', 6, false, $grade->grade));
            fwrite ($bf, full_tag('GRADEITEM', 6, false, $grade->gradeitem));  
            fwrite ($bf, full_tag('TIMEUPDATED', 6, false, $grade->timeupdated));
            //End grade
            $status = $status && fwrite ($bf, end_tag('GRADE', 5, true));
        }
        //Write end tag
        $status = $status && fwrite($bf, end_tag('GRADES', 4, true));

        return $status;
    }
 
   ////Return an array of info (name, value)
   function cognitivefactory_check_backup_mods($course, $user_data=false, $backup_unique_code) {
        //First the course data
        $info[0][0] = get_string('modulenameplural', 'cognitivefactory');
        if ($ids = cognitivefactory_ids ($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
            $info[1][0] = get_string('operators', 'cognitivefactory');
            if ($ids = cognitivefactory_operators_ids_by_course ($course)) {
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
            $info[2][0] = get_string('categories', 'cognitivefactory');
            if ($ids = cognitivefactory_categories_ids_by_course($course)) {
                $info[2][1] = count($ids);
            } else {
                $info[2][1] = 0;
            }
            $info[3][0] = get_string('responses', 'cognitivefactory');
            if ($ids = cognitivefactory_responses_ids_by_course($course)) {
                $info[3][1] = count($ids);
            } else {
                $info[3][1] = 0;
            }
            $info[4][0] = get_string('data', 'cognitivefactory');
            if ($ids = cognitivefactory_operatordata_ids_by_course($course)) {
                $info[4][1] = count($ids);
            } else {
                $info[4][1] = 0;
            }
            $info[5][0] = get_string('userdata', 'cognitivefactory');
            if ($ids = cognitivefactory_userdata_ids_by_course($course)) {
                $info[5][1] = count($ids);
            } else {
                $info[5][1] = 0;
            }
            $info[6][0] = get_string('grades', 'cognitivefactory');
            if ($ids = cognitivefactory_grades_ids_by_course($course)) {
                $info[6][1] = count($ids);
            } else {
                $info[6][1] = 0;
            }
        }
        return $info;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of cognitivefactorys id
    function cognitivefactory_ids ($course) {
        global $CFG;

        $sql = "
            SELECT 
                b.id, 
                b.course
            FROM 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}'
        ";
        return get_records_sql ($sql);
    }
   
    //Returns an array of cognitivefactory operators id
    function cognitivefactory_operators_ids_by_course ($course) {
        global $CFG;

        $sql = "
            SELECT 
                op.id , 
                op.cognitivefactoryid
            FROM 
                {$CFG->prefix}cognitivefactory_operators AS op, 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}' AND
                op.cognitivefactoryid = b.id
        ";
        return get_records_sql($sql);
    }

    //Returns an array of cognitivefactory categories id
    function cognitivefactory_categories_ids_by_course ($course) {
        global $CFG;

        $sql = "
            SELECT 
                c.id,
                c.cognitivefactoryid
            FROM 
                {$CFG->prefix}cognitivefactory_categories AS c, 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}' AND
                c.cognitivefactoryid = b.id
        ";
        return get_records_sql($sql);
    }

    //Returns an array of cognitivefactory responses id
    function cognitivefactory_responses_ids_by_course ($course) {
        global $CFG;

        $sql = "
            SELECT 
                r.id,
                r.cognitivefactoryid
            FROM 
                {$CFG->prefix}cognitivefactory_responses AS r, 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}' AND
                r.cognitivefactoryid = b.id
        ";
        return get_records_sql($sql);
    }

    //Returns an array of operatordata responses id
    function cognitivefactory_operatordata_ids_by_course ($course) {
        global $CFG;

        $sql = "
            SELECT 
                opd.id,
                opd.cognitivefactoryid
            FROM 
                {$CFG->prefix}cognitivefactory_operatordata AS opd, 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}' AND
                opd.cognitivefactoryid = b.id
        ";
        return get_records_sql($sql);
    }

    //Returns an array of userdata id
    function cognitivefactory_userdata_ids_by_course ($course) {
        global $CFG;

        $sql = "
            SELECT 
                ud.id,
                ud.cognitivefactoryid
            FROM 
                {$CFG->prefix}cognitivefactory_userdata AS ud, 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}' AND
                ud.cognitivefactoryid = b.id
        ";
        return get_records_sql($sql);
    }

    //Returns an array of grades id
    function cognitivefactory_grades_ids_by_course ($course) {
        global $CFG;

        $sql = "
            SELECT 
                g.id,
                g.cognitivefactoryid
            FROM 
                {$CFG->prefix}cognitivefactory_grades AS g, 
                {$CFG->prefix}cognitivefactory AS b
            WHERE 
                b.course = '{$course}' AND
                g.cognitivefactoryid = b.id
        ";
        return get_records_sql($sql);
    }
?>
