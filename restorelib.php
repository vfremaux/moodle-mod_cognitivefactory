<?php //$Id: restorelib.php,v 1.2 2012-06-18 15:19:34 vf Exp $
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

    //This function executes all the restore procedure about this mod
    function cognitivefactory_restore_mods($mod,$restore) {
        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code,$mod->modtype,$mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the SCHEDULER record structure
            $cognitivefactory->course = $restore->course_id;
            $cognitivefactory->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $cognitivefactory->description = backup_todb($info['MOD']['#']['DESCRIPTION']['0']['#']);
            $cognitivefactory->collectrequirement = backup_todb($info['MOD']['#']['COLLECTREQUIREMENT']['0']['#']);
            $cognitivefactory->flowmode = backup_todb($info['MOD']['#']['FLOWMODE']['0']['#']);
            $cognitivefactory->seqaccesscollect = backup_todb($info['MOD']['#']['SEQACCESSCOLLECT']['0']['#']); 
            $cognitivefactory->seqaccessprepare = backup_todb($info['MOD']['#']['SEQACCESSPREPARE']['0']['#']);
            $cognitivefactory->seqaccessorganize = backup_todb($info['MOD']['#']['SEQACCESSORGANIZE']['0']['#']);
            $cognitivefactory->seqaccessdisplay = backup_todb($info['MOD']['#']['SEQACCESSDISPLAY']['0']['#']);
            $cognitivefactory->seqaccessfeedback = backup_todb($info['MOD']['#']['SEQACCESSFEEDBACK']['0']['#']);
            $cognitivefactory->phase = backup_todb($info['MOD']['#']['PHASE']['0']['#']);
            $cognitivefactory->privacy = backup_todb($info['MOD']['#']['PRIVACY']['0']['#']);
            $cognitivefactory->numresponses = backup_todb($info['MOD']['#']['NUMRESPONSES']['0']['#']);
            $cognitivefactory->numresponsesinform = backup_todb($info['MOD']['#']['NUMRESPONSESINFORM']['0']['#']); 
            $cognitivefactory->numcolumns = backup_todb($info['MOD']['#']['NUMCOLUMNS']['0']['#']);
            $cognitivefactory->oprequirementtype = backup_todb($info['MOD']['#']['OPREQUIREMENTTYPE']['0']['#']);
            $cognitivefactory->scale = backup_todb($info['MOD']['#']['SCALE']['0']['#']);
            $cognitivefactory->singlegrade = backup_todb($info['MOD']['#']['SINGLEGRADE']['0']['#']);
            $cognitivefactory->participationweight = backup_todb($info['MOD']['#']['PARTICIPATIONWEIGHT']['0']['#']);
            $cognitivefactory->preparingweight = backup_todb($info['MOD']['#']['PREPARINGWEIGHT']['0']['#']);
            $cognitivefactory->organizeweight = backup_todb($info['MOD']['#']['ORGANIZEWEIGHT']['0']['#']);
            $cognitivefactory->feedbackweight = backup_todb($info['MOD']['#']['FEEDBACKWEIGHT']['0']['#']);
            $cognitivefactory->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);

            //We have to recode the scale field (foreing key on existing scale)
            // should be verified
            $scale = backup_getid($restore->backup_unique_code, 'scale', $cognitivefactory->scale);
            if ($scale) {
                $cognitivefactory->scale = $scale->new_id;
            }

            //The structure is equal to the db, so insert the cognitivefactory
            $newid = insert_record ('cognitivefactory', $cognitivefactory);

            //Do some output
            echo "<li>".get_string('modulename', 'cognitivefactory')." \"".format_string(stripslashes($cognitivefactory->name),true)."\"</li>";
            backup_flush(300);

            if ($newid) {
                // We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code,$mod->modtype,
                             $mod->id, $newid);

                // Now restore operators. All other data is user dependant.
                $status = cognitivefactory_operators_restore_mods ($mod->id, $newid, $info, $restore);

                // Now check if want to restore user data and do it.
                if ($restore->mods['cognitivefactory']->userinfo) {
                    $status = cognitivefactory_responses_restore_mods ($mod->id, $newid, $info, $restore);
                    $status = cognitivefactory_categories_restore_mods ($mod->id, $newid, $info, $restore);
                    $status = cognitivefactory_operatordata_restore_mods ($mod->id, $newid, $info, $restore);
                    $status = cognitivefactory_userdata_restore_mods ($mod->id, $newid, $info, $restore);
                    $status = cognitivefactory_grades_restore_mods ($mod->id, $newid, $info, $restore);
                }
            } 
            else {
                $status = false;
            }
        } 
        else {
            $status = false;
        }
        return $status;
    }


    //This function restores the cognitivefactory_operators
    function cognitivefactory_operators_restore_mods($old_cognitivefactory_id, $new_cognitivefactory_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the operators array
        $operators = $info['MOD']['#']['OPERATORS']['0']['#']['OPERATOR'];

        //Iterate over operators
        for($i = 0; $i < sizeof($operators); $i++) {
            $operator_info = $operators[$i];
            //traverse_xmlize($operator_info);                                                               //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            // We'll need this later!!
            $oldid = backup_todb($slot_info['#']['ID']['0']['#']);

            // Now, build the BRAINSTORM_OPERATOR record structure
            $operator->cognitivefactoryid = $new_cognitivefactory_id;
            $operator->operatorid = backup_todb($operator_info['#']['OPERATORID']['0']['#']);
            $operator->configdata = backup_todb($operator_info['#']['CONFIGDATA']['0']['#']);
            $operator->active = backup_todb($operator_info['#']['ACTIVE']['0']['#']);

            // We check if the code of the operator is available. we keep the record either but set it to unavailable
            if (!file_exists("{$CFG->dirroot}/mod/cognitivefactory/operators/{$operator->operatorid}/prepare.php")){
                $operator->active = 0;
            }
            
            // The structure is equal to the db, so insert the operator
            $newid = insert_record ('cognitivefactory_operators', $operator);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'cognitivefactory_operators', $oldid, $newid);
            } 
            else {
                $status = false;
            }
        }
        return $status;
    }

    //This function restores the cognitivefactory responses
    function cognitivefactory_responses_restore_mods($old_cognitivefactory_id, $new_cognitivefactory_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the responses array
        $responses = $info['MOD']['#']['RESPONSES']['0']['#']['RESPONSE'];

        //Iterate over responses
        for($i = 0; $i < sizeof($responses); $i++) {
            $response_info = $responses[$i];
            //traverse_xmlize($response_info);                                                         //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($response_info['#']['ID']['0']['#']);

            //Now, build the BRAINSTORM_RESPONSE record structure
            $response->cognitivefactoryid = $new_cognitivefactory_id;
            $response->userid = backup_todb($response_info['#']['USERID']['0']['#']);
            $response->groupid = backup_todb($response_info['#']['GROUPID']['0']['#']);
            $response->response = backup_todb($response_info['#']['RESPONSE']['0']['#']);
            $response->timemodified = backup_todb($response_info['#']['TIMEMODIFIED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $response->userid);
            if ($user) {
                $response->userid = $user->new_id;
            }

            //We have to recode the groupid field
            $group = backup_getid($restore->backup_unique_code, 'groups', $response->groupid);
            if ($group) {
                $response->groupid = $group->new_id;
            }

            //The structure is equal to the db, so insert the cognitivefactory response
            $newid = insert_record ('cognitivefactory_response', $response);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'cognitivefactory_response', $oldid, $newid);
            } 
            else {
                $status = false;
            }
        }
        return $status;
    }

    //This function restores the cognitivefactory categories
    function cognitivefactory_categories_restore_mods($old_cognitivefactory_id, $new_cognitivefactory_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the categories array
        $categories = $info['MOD']['#']['CATEGORIES']['0']['#']['CATEGORY'];

        //Iterate over categories
        for($i = 0; $i < sizeof($categories); $i++) {
            $category_info = $categories[$i];
            //traverse_xmlize($category_info);                                                         //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($category_info['#']['ID']['0']['#']);

            //Now, build the BRAINSTORM_CATEGORY record structure
            $category->cognitivefactoryid = $new_cognitivefactory_id;
            $category->userid = backup_todb($category_info['#']['USERID']['0']['#']);
            $category->groupid = backup_todb($category_info['#']['GROUPID']['0']['#']);
            $category->title = backup_todb($category_info['#']['TITLE']['0']['#']);
            $category->timemodified = backup_todb($category_info['#']['TIMEMODIFIED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $category->userid);
            if ($user) {
                $category->userid = $user->new_id;
            }

            //We have to recode the groupid field
            $group = backup_getid($restore->backup_unique_code, 'groups', $category->groupid);
            if ($group) {
                $category->groupid = $group->new_id;
            }

            //The structure is equal to the db, so insert the cognitivefactory category
            $newid = insert_record ('cognitivefactory_categories', $category);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'cognitivefactory_categories', $oldid, $newid);
            } 
            else {
                $status = false;
            }
        }
        return $status;
    }

    //This function restores the cognitivefactory operatordata
    function cognitivefactory_operatordata_restore_mods($old_cognitivefactory_id, $new_cognitivefactory_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the op data array
        $opdata = $info['MOD']['#']['CATEGORIES']['0']['#']['CATEGORY'];

        //Iterate over opdata
        for($i = 0; $i < sizeof($opdata); $i++) {
            $datum_info = $opdata[$i];
            //traverse_xmlize($datum_info);                                                         //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($datum_info['#']['ID']['0']['#']);

            //Now, build the BRAINSTORM_DATUM record structure
            $datum->cognitivefactoryid = $new_cognitivefactory_id;
            $datum->userid = backup_todb($datum_info['#']['USERID']['0']['#']);
            $datum->groupid = backup_todb($datum_info['#']['GROUPID']['0']['#']);
            $datum->operatorid = backup_todb($datum_info['#']['OPERATORID']['0']['#']);
            
            // We ignore if operator is not implemented here
            if (!file_exists("{$CFG->dirroot}/mod/cognitivefactory/operators/{$datum->operatorid}/prepare.php")){
                continue;
            }

            $datum->itemsource = backup_todb($datum_info['#']['ITEMSOURCE']['0']['#']);
            $datum->itemdest = backup_todb($datum_info['#']['ITEMDEST']['0']['#']);
            $datum->intvalue = backup_todb($datum_info['#']['INTVALUE']['0']['#']);
            $datum->floatvalue = backup_todb($datum_info['#']['FLOATVALUE']['0']['#']);
            $datum->blobvalue = backup_todb($datum_info['#']['BLOBVALUE']['0']['#']);
            $datum->timemodified = backup_todb($datum_info['#']['TIMEMODIFIED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $datum->userid);
            if ($user) {
                $datum->userid = $user->new_id;
            }

            //We have to recode the groupid field
            $group = backup_getid($restore->backup_unique_code, 'groups', $datum->groupid);
            if ($group) {
                $datum->groupid = $group->new_id;
            }

            //We have to recode the itemsource field
            $response = backup_getid($restore->backup_unique_code, 'cognitivefactory_responses', $datum->itemsource);
            if ($response) {
                $datum->itemsource = $response->new_id;
            }

            //We have to recode the itemdest field
            $response = backup_getid($restore->backup_unique_code, 'cognitivefactory_responses', $datum->itemdest);
            if ($response) {
                $datum->itemdest = $response->new_id;
            }

            //The structure is equal to the db, so insert the cognitivefactory datum
            $newid = insert_record ('cognitivefactory_operatordata', $datum);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'cognitivefactory_operatordata', $oldid, $newid);
            } 
            else {
                $status = false;
            }
        }
        return $status;
    }

    //This function restores the cognitivefactory userdata
    function cognitivefactory_userdata_restore_mods($old_cognitivefactory_id, $new_cognitivefactory_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the userdata array
        $userdata = $info['MOD']['#']['USERDATA']['0']['#']['DATUM'];

        //Iterate over userdata records
        for($i = 0; $i < sizeof($userdata); $i++) {
            $userdata_info = $userdata[$i];
            //traverse_xmlize($userdata_info);                                                         //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($userdata_info['#']['ID']['0']['#']);

            //Now, build the BRAINSTORM_USERDATA record structure
            $datum->cognitivefactoryid = $new_cognitivefactory_id;
            $datum->userid = backup_todb($userdata_info['#']['USERID']['0']['#']);
            $datum->report = backup_todb($userdata_info['#']['REPORT']['0']['#']);
            $datum->reportformat = backup_todb($userdata_info['#']['REPORTFORMAT']['0']['#']);
            $datum->feedback = backup_todb($userdata_info['#']['FEEDBACK']['0']['#']);
            $datum->feedbackformat = backup_todb($userdata_info['#']['FEEDBACKFORMAT']['0']['#']);
            $datum->timeupdated = backup_todb($userdata_info['#']['TIMEUPDATED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $datum->userid);
            if ($user) {
                $datum->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the cognitivefactory userdata
            $newid = insert_record ('cognitivefactory_userdata', $datum);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'cognitivefactory_userdata', $oldid, $newid);
            } 
            else {
                $status = false;
            }
        }
        return $status;
    }

    //This function restores the cognitivefactory grades
    function cognitivefactory_grades_restore_mods($old_cognitivefactory_id, $new_cognitivefactory_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the grades array
        $grades = $info['MOD']['#']['GRADES']['0']['#']['GRADE'];

        //Iterate over grades
        for($i = 0; $i < sizeof($grades); $i++) {
            $grades_info = $grades[$i];
            //traverse_xmlize($grades_info);                                                         //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($grades_info['#']['ID']['0']['#']);

            //Now, build the BRAINSTORM_GRADES record structure
            $grade->cognitivefactoryid = $new_cognitivefactory_id;
            $grade->userid = backup_todb($grades_info['#']['USERID']['0']['#']);
            $grade->grade = backup_todb($grades_info['#']['GRADE']['0']['#']);
            $grade->gradeitem = backup_todb($grades_info['#']['GRADEITEM']['0']['#']);
            $grade->timeupdated = backup_todb($grades_info['#']['TIMEUPDATED']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $grade->userid);
            if ($user) {
                $grade->userid = $user->new_id;
            }

            //The structure is equal to the db, so insert the cognitivefactory grade
            $newid = insert_record ('cognitivefactory_grades', $grade);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'cognitivefactory_grades', $oldid, $newid);
            } 
            else {
                $status = false;
            }
        }
        return $status;
    }

?>
