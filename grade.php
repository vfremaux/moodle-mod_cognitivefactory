<?php 

/**
* Module Brainstorm V2
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*
* this screen is used for grading student's work
*/

    if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

    require_capability('mod/cognitivefactory:grade', $context);

    include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

    /// print participant selector
    $gradefor = optional_param('gradefor', 0, PARAM_INT);
    $students = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'u.id,lastname,firstname,email,picture', 'lastname');
    $grademenu_options[0] = get_string('summary', 'cognitivefactory');
    foreach ($students as $student) {
        $grademenu_options[$student->id] = fullname($student);
    }

    $str = '<form name="chooseform" method="POST" action="view.php">';
    $str .= "<input type=\"hidden\" name=\"id\" value=\"{$cm->id}\" />";
    $str .= html_writer::select($grademenu_options, 'gradefor', $gradefor, array('' => 'choosedots'), array( 'onchange' => "document.forms['chooseform'].submit();"));
    $str .= '</form>';
    
    $out .= $str;

    /// print grade form
    $str = '';

    if ($gradefor == 0) { // implement a global summary to check who has been graded and who has not

        $allstudentsids = array_keys($grademenu_options);
        $grades = cognitivefactory_get_grades($cognitivefactory->id, $allstudentsids);
        $ungraded = cognitivefactory_get_ungraded($cognitivefactory->id, $allstudentsids);
    
        $str .= $OUTPUT->heading(get_string('gradesummary', 'cognitivefactory'));    
    
        $str .= '<table width="90%">';
        $str .= '<tr>';
        $str .= '<td>';
        $str .= $OUTPUT->heading(get_string('ungraded', 'cognitivefactory'), 3, 'h2');
        $str .= '</td>';
        $str .= '<td>';
        $str .= $OUTPUT->heading(get_string('graded', 'cognitivefactory'), 3, 'h2');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td align="left"><!-- student ungraded -->';

        foreach ($ungraded as $student) {
            if (!has_capability('mod/cognitivefactory:gradable', $context)) continue;
            $str .= $OUTPUT->user_picture($student);
            $str .= ' '.fullname($student);
            $str .= " -> <a href=\"view.php?id={$cm->id}&amp;gradefor={$student->id}\">".get_string('dograde', 'cognitivefactory').'</a><br/>';
        }

        $str .= '</td>';
        $str .= '<td><!-- student already graded -->';
        if ($grades) {
            foreach ($grades as $grade) {
                $blendedgrades[$grade->id]->{$grade->gradeitem} = $grade->grade;
            }

            $gradestr = get_string('grade');
            if ($cognitivefactory->singlegrade) { // print for a single grading
                $table->head = array('', '', "<b>$gradestr</b>", '');
                $table->align = array('center', 'left', 'center', 'left');
                $table->size = array('15%', '60%', '15%', '10%');
                foreach ($blendedgrades as $studentid => $gradeset) {
                    $student = $DB->get_record('user', array('id' => $studentid));
                    $userpic = new user_picture();
                    $userpic->user = $student->id;
                    $userpic->courseid = $course->id;
                    $userpic->image->src = $student->picture;
                    $picture = $OUTPUT->user_picture($userpic);
                    $studentname = fullname($student);
                    $updatelink = "<a href=\"view.php?id={$cm->id}&amp;gradefor={$student->id}\"><img src=\"".$OUTPUT->pix_url('t/edit').'"></a><br/>';
                    $deletelink = "<a href=\"view.php?id={$cm->id}&amp;what=deletegrade&amp;for={$student->id}\"><img src=\"".$OUTPUT->pix_url('t/delete').'"></a><br/>';
                    $table->data[] = array($picture, $studentname, $gradeset->single, $updatelink.'&nbsp;'.$deletelink);
                }
                echo html_writer::table($table);
            } else { // print for a dissociated grading
                $participatestr = get_string('participation', 'cognitivefactory').'<br/>('.($cognitivefactory->participationweight * 100).'%)';
                $preparingstr = get_string('preparations', 'cognitivefactory').'<br/>('.($cognitivefactory->preparingweight * 100).'%)';
                $organizestr = get_string('organizations', 'cognitivefactory').'<br/>('.($cognitivefactory->organizeweight * 100).'%)';
                $feedbackstr = get_string('feedback', 'cognitivefactory').'<br/>('.($cognitivefactory->feedbackweight * 100).'%)';
                $finalstr = get_string('finalgrade', 'cognitivefactory');
                $table->head = array('', '', "<b>$participatestr</b>", "<b>$preparingstr</b>", "<b>$organizestr</b>", "<b>$feedbackstr</b>", "<b>$finalstr</b>", '');
                $table->align = array('center', 'left', 'center', 'left');
                $table->size = array('15%', '60%', '15%', '10%');
                foreach ($blendedgrades as $studentid => $gradeset) {
                    $student = $DB->get_record('user', array('id' => $studentid));
    
                    // get grade components
                    if ($cognitivefactory->seqaccesscollect && isset($gradeset->participate)) {
                        if (isset($gradeset->participate)) {
                            $participategrade = $gradeset->participate;  
                            $weights[] = $cognitivefactory->participationweight;
                            $gradeparts[] = $gradeset->participate;
                        } else {
                            $participategrade = '';  
                        }
                    } else {
                        $participategrade = "<img src=\"".$OUTPUT->pix_url('teachhat', 'cognitivefactory').'" width="30" />' ;
                    }
                    if ($cognitivefactory->seqaccessprepare) {
                        if (isset($gradeset->prepare)) {
                            $preparinggrade = $gradeset->prepare;  
                            $weights[] = $cognitivefactory->preparingweight;
                            $gradeparts[] = $gradeset->prepare;
                        } else {
                            $preparinggrade = '';  
                        }
                    } else {
                        $preparinggrade = "<img src=\"".$OUTPUT->pix_url('teachhat', 'cognitivefactory').'" width="30" />' ;
                    }
                    if ($cognitivefactory->seqaccessorganize) {
                        if (isset($gradeset->organize)) {
                            $organizegrade = $gradeset->organize;  
                            $weights[] = $cognitivefactory->organizeweight;
                            $gradeparts[] = $gradeset->organize;
                        } else {
                            $organizegrade = '';  
                        }
                    } else {
                        $organizegrade = "<img src=\"".$OUTPUT->pix_url('teachhat', 'cognitivefactory').'" width="30" />' ;
                    }
                    if ($cognitivefactory->seqaccessfeedback) {
                        if (isset($gradeset->feedback)) {
                            $feedbackgrade = $gradeset->feedback;  
                            $weights[] = $cognitivefactory->feedbackweight;
                            $gradeparts[] = $gradeset->feedback;
                        } else {
                            $feedbackgrade = '';  
                        }
                    } else {
                        $feedbackgrade = "<img src=\"".$OUTPUT->pix_url('teachhat', 'cognitivefactory').'" width="30" />' ;
                    }
    
                    // calculates final
    
                    $weighting = array_sum($weights);
                    $finalgrade = 0;
                    for ($i = 0 ; $i < count($gradeparts) ; $i++) {
                        $finalgrade += $gradeparts[$i] * $weights[$i];
                    }
                    $finalgrade = sprintf("%0.2f", $finalgrade / $weighting);
    
                    $picture = $OUTPUT->user_picture($student);
                    $studentname = ' '.fullname($student);
                    $updatelink = " <a href=\"view.php?id={$cm->id}&amp;gradefor={$student->id}\"><img src=\"".$OUTPUT->pix_url('t/edit').'"></a><br/>';
                    $deletelink = " <a href=\"view.php?id={$cm->id}&amp;what=deletegrade&amp;for={$student->id}\"><img src=\"".$OUTPUT->pix_url('t/delete').'"></a><br/>';
                    $table->data[] = array($picture, $studentname, $participategrade, $preparinggrade, $organizegrade, $feedbackgrade, "<b>$finalgrade</b>", $updatelink.'&nbsp;'.$deletelink);
                }
                $str .= html_writer::table($table);
            }
        }
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        
        echo $out;
        echo $str;

    } else { // grading a user
        
        include_once $CFG->dirroot.'/mod/cognitivefactory/user_grading_form.php';
        
        $form = new UserGradingForm($url, array('instanceid' => $cognitivefactory->id, 'gradefor' => $gradefor, 'cm' => $cm));
    
        if ($form->is_cancelled()) {
            redirect($url);        
        }
        
        if ($data = $form->get_data()) {
            cognitivefactory_save_grades($cognitivefactory, $data);
        }
    
        $user = $DB->get_record('user', array('id' => $gradefor));
    
        $data = cognitivefactory_get_gradeset($cognitivefactory->id, $gradefor);    
        if (empty($data)) $data = new StdClass();
        $data->id = $cm->id;
        $data->what = 'savegrade';
        $data->for = $gradefor;
        
        $form->set_data($data);

        echo $out; // start sending the page
        echo '<div>';
        echo $OUTPUT->heading(get_string('gradingof', 'cognitivefactory').' '.fullname($user));
        $form->display();
        echo '</div>';
    }
