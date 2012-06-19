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
foreach($students as $student){
    $grademenu_options[$student->id] = fullname($student);
}
echo '<form name="chooseform" method="POST" action="view.php">';
echo "<input type=\"hidden\" name=\"id\" value=\"{$cm->id}\" />";
choose_from_menu($grademenu_options, 'gradefor', $gradefor, '', "document.forms['chooseform'].submit();", '');
echo '</form>';

/// print grade form
echo '<center>';
if ($gradefor == 0){ // implement a global summary to check who has been graded and who has not
    
    $allstudentsids = array_keys($grademenu_options);
    $grades = cognitivefactory_get_grades($cognitivefactory->id, $allstudentsids);
    $ungraded = cognitivefactory_get_ungraded($cognitivefactory->id, $allstudentsids);

    print_heading(get_string('gradesummary', 'cognitivefactory'));    
?>
    <table width="80%">
        <tr>
            <td>
                <?php print_heading(get_string('ungraded', 'cognitivefactory'), '', 3, 'h2') ?>
            </td>
            <td>
                <?php print_heading(get_string('graded', 'cognitivefactory'), '', 3, 'h2') ?>
            </td>
        </tr>
        <tr>
            <td align="left"><!-- student ungraded -->
<?php
    foreach($ungraded as $student){
        if (!has_capability('mod/cognitivefactory:gradable', $context)) continue;
        print_user_picture($student->id, $course->id, $student->picture, false, false, true);
        echo fullname($student);
        echo " -> <a href=\"view.php?id={$cm->id}&amp;gradefor={$student->id}\">".get_string('dograde', 'cognitivefactory').'</a><br/>';
    }
?>
            </td>
            <td><!-- student already graded -->
<?php
    if ($grades){
        foreach($grades as $grade){
            $blendedgrades[$grade->id]->{$grade->gradeitem} = $grade->grade;
        }

        $gradestr = get_string('grade');
        if ($cognitivefactory->singlegrade){ // print for a single grading
            $table->head = array('', '', "<b>$gradestr</b>", '');
            $table->align = array('center', 'left', 'center', 'left');
            $table->size = array('15%', '60%', '15%', '10%');
            foreach($blendedgrades as $studentid => $gradeset){
                $student = get_record('user', 'id', $studentid, '', 'id,firstname,lastname,picture,email');
                $picture = print_user_picture($student->id, $course->id, $student->picture, false, true, true);
                $studentname = fullname($student);
                $updatelink = "<a href=\"view.php?id={$cm->id}&amp;gradefor={$student->id}\"><img src=\"{$CFG->pixpath}/t/edit.gif\"></a><br/>";
                $deletelink = "<a href=\"view.php?id={$cm->id}&amp;what=deletegrade&amp;for={$student->id}\"><img src=\"{$CFG->pixpath}/t/delete.gif\"></a><br/>";
                $table->data[] = array($picture, $studentname, $gradeset->single, $updatelink.'&nbsp;'.$deletelink);
            }
            print_table($table);
        }
        else{ // print for a dissociated grading
            $participatestr = get_string('participation', 'cognitivefactory').'<br/>('.($cognitivefactory->participationweight * 100).'%)';
            $preparingstr = get_string('preparations', 'cognitivefactory').'<br/>('.($cognitivefactory->preparingweight * 100).'%)';
            $organizestr = get_string('organizations', 'cognitivefactory').'<br/>('.($cognitivefactory->organizeweight * 100).'%)';
            $feedbackstr = get_string('feedback', 'cognitivefactory').'<br/>('.($cognitivefactory->feedbackweight * 100).'%)';
            $finalstr = get_string('finalgrade', 'cognitivefactory');
            $table->head = array('', '', "<b>$participatestr</b>", "<b>$preparingstr</b>", "<b>$organizestr</b>", "<b>$feedbackstr</b>", "<b>$finalstr</b>", '');
            $table->align = array('center', 'left', 'center', 'left');
            $table->size = array('15%', '60%', '15%', '10%');
            foreach($blendedgrades as $studentid => $gradeset){
                $student = get_record('user', 'id', $studentid, '', 'id,firstname,lastname,picture,email');

                // get grade components
                if ($cognitivefactory->seqaccesscollect && isset($gradeset->participate)){
                    if (isset($gradeset->participate)){
                        $participategrade = $gradeset->participate;  
                        $weights[] = $cognitivefactory->participationweight;
                        $gradeparts[] = $gradeset->participate;
                    }
                    else{
                        $participategrade = '';  
                    }
                }
                else{
                    $participategrade = "<img src=\"{$CFG->wwwroot}/mod/cognitivefactory/teachhat.jpg\" width=\"30\" />" ;
                }
                if ($cognitivefactory->seqaccessprepare){
                    if (isset($gradeset->prepare)){
                        $preparinggrade = $gradeset->prepare;  
                        $weights[] = $cognitivefactory->preparingweight;
                        $gradeparts[] = $gradeset->prepare;
                    }
                    else{
                        $preparinggrade = '';  
                    }
                }
                else{
                    $preparinggrade = "<img src=\"{$CFG->wwwroot}/mod/cognitivefactory/teachhat.jpg\" width=\"30\" />" ;
                }
                if ($cognitivefactory->seqaccessorganize){
                    if (isset($gradeset->organize)){
                        $organizegrade = $gradeset->organize;  
                        $weights[] = $cognitivefactory->organizeweight;
                        $gradeparts[] = $gradeset->organize;
                    }
                    else{
                        $organizegrade = '';  
                    }
                }
                else{
                    $organizegrade = "<img src=\"{$CFG->wwwroot}/mod/cognitivefactory/teachhat.jpg\" width=\"30\" />" ;
                }
                if ($cognitivefactory->seqaccessfeedback){
                    if (isset($gradeset->feedback)){
                        $feedbackgrade = $gradeset->feedback;  
                        $weights[] = $cognitivefactory->feedbackweight;
                        $gradeparts[] = $gradeset->feedback;
                    }
                    else{
                        $feedbackgrade = '';  
                    }
                }
                else{
                    $feedbackgrade = "<img src=\"{$CFG->wwwroot}/mod/cognitivefactory/teachhat.jpg\" width=\"30\" />" ;
                }
                
                // calculates final
                $weighting = array_sum($weights);
                $finalgrade = 0;
                for ($i = 0 ; $i < count($gradeparts) ; $i++){
                    $finalgrade += $gradeparts[$i] * $weights[$i];
                }
                $finalgrade = sprintf("%0.2f", $finalgrade / $weighting);

                $picture = print_user_picture($student->id, $course->id, $student->picture, false, true, true);
                $studentname = fullname($student);
                $updatelink = "<a href=\"view.php?id={$cm->id}&amp;gradefor={$student->id}\"><img src=\"{$CFG->pixpath}/t/edit.gif\"></a><br/>";
                $deletelink = "<a href=\"view.php?id={$cm->id}&amp;what=deletegrade&amp;for={$student->id}\"><img src=\"{$CFG->pixpath}/t/delete.gif\"></a><br/>";
                $table->data[] = array($picture, $studentname, $participategrade, $preparinggrade, $organizegrade, $feedbackgrade, "<b>$finalgrade</b>", $updatelink.'&nbsp;'.$deletelink);
            }
            print_table($table);
        }
    }
?>
            </td>
        </tr>
    </table>
<?php
}
else{ // grading a user
    /// starting form
    echo '<form name="gradesform" action="view.php" method="post">';
    echo "<input type=\"hidden\" name=\"id\" value=\"{$cm->id}\" />";
    echo "<input type=\"hidden\" name=\"what\" value=\"savegrade\" />";
    echo "<input type=\"hidden\" name=\"for\" value=\"{$gradefor}\" />";

    $gradeset = cognitivefactory_get_gradeset($cognitivefactory->id, $gradefor);

    $user = get_record('user', 'id', $gradefor);
    print_heading(get_string('gradingof', 'cognitivefactory').' '.fullname($user));

    /// printing ideas
    print_heading(get_string('responses', 'cognitivefactory'), '', 3);
    $responses = cognitivefactory_get_responses($cognitivefactory->id, $user->id, 0, false);
    if ($responses){
        echo '<table><tr>';
        cognitivefactory_print_responses_cols($cognitivefactory, $responses, false);
        echo '</tr></table>';
    }
    else {
        print_simple_box(get_string('notresponded', 'cognitivefactory'));
    }
    if (!$cognitivefactory->singlegrade && $cognitivefactory->seqaccesscollect){
        print_string('gradeforparticipation', 'cognitivefactory');
        echo ' : ';
        make_grading_menu($cognitivefactory, 'participate', @$gradeset->participate, false);
    } 

    // getting valid operator list
    $operators = cognitivefactory_get_operators($cognitivefactory->id);

    /// printing preparing sets
    print_heading(get_string('preparations', 'cognitivefactory'), '', 3);
    if ($operators){
        echo '<table width="90%" cellspacing="10"><tr valign="top">';
        $i = 0;
        foreach($operators as $operator){ // print operator settings for each valid operator
            if (!$operator->active) continue;
            if ($i && $i % 2 == 0) echo '</tr><tr valign="top">';
            echo '<td align="right" width="50%">';
            print_heading(get_string($operator->id, 'cognitivefactory'), '', 4);
            echo '<table width="90%">';
            foreach(get_object_vars($operator->configdata) as $key => $value){
                echo "<tr valign=\"top\"><td align=\"right\" width=\"60%\"><b>".get_string($key, 'cognitivefactory').'</b>:</td>';
                echo "<td>$value</td></tr>";
            }            
            echo '</table></td>';
            $i++;
        }
        echo '</tr></table>';
    }

    if (!$cognitivefactory->singlegrade && $cognitivefactory->seqaccessprepare){
        echo '<br/>';
        print_string('gradeforpreparation', 'cognitivefactory');
        echo ' : ';
        make_grading_menu($cognitivefactory, 'prepare', @$gradeset->prepare, false);
    } 

    /// printing organizations
    print_heading(get_string('organizations', 'cognitivefactory'), '', 3);
    if ($operators){
        foreach($operators as $operator){ // print organisation result for this operator and this user
            if (!$operator->active) continue;
            print_simple_box_start('center');
            include_once("{$CFG->dirroot}/mod/cognitivefactory/operators/{$operator->id}/locallib.php");
            $displayfunction = $operator->id.'_display';
            if (function_exists($displayfunction)){
                print_heading(get_string($operator->id, 'cognitivefactory'), '', 4);
                $cognitivefactory->cm = &$cm;
                $displayfunction($cognitivefactory, $gradefor, $currentgroup);
            }
            else{
               echo get_string('notabletodisplayfor', 'cognitivefactory', get_string($operator->id, 'cognitivefactory'));
            }
            print_simple_box_end();
        }
    }
    if (!$cognitivefactory->singlegrade && $cognitivefactory->seqaccessorganize){
        echo '<br/>';
        print_string('gradefororganisation', 'cognitivefactory');
        echo ' : ';
        make_grading_menu($cognitivefactory, 'organize', @$gradeset->organize, false);
    } 

    /// printing final feedback and report
    print_heading(get_string('feedback', 'cognitivefactory'), '', 3);
    $report = get_record('cognitivefactory_userdata', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $gradefor);
    print_simple_box(format_string(format_text(@$report->report, @$report->reportformat)));

    if (!$cognitivefactory->singlegrade && $cognitivefactory->seqaccessfeedback){
        echo '<br/>';
        print_string('gradeforfeedback', 'cognitivefactory');
        echo ' : ';
        make_grading_menu($cognitivefactory, 'feedback', @$gradeset->feedback, false);
    } 

    // print a final feedback form

    echo '<br/><br/><table width="80%"><tr valign="top"><td><b>'.get_string('feedback').':</b></td><td>';
    $usehtmleditor = can_use_html_editor();
    print_textarea($usehtmleditor, 20, 50, 680, 400, 'teacherfeedback', @$report->feedback);
    if (!$usehtmleditor){
		echo '<p align="right">';
        helpbutton('textformat', get_string('formattexttype'));
        print_string('formattexttype');
        echo ":&nbsp;";
        if (empty($report->feedbackformat)) {
           $report->feedbackformat = FORMAT_MOODLE;
        }
        choose_from_menu(format_text_menu(), 'feedbackformat', $report->feedbackformat, '');
    }
    else{
        $htmleditorneeded = 1;
    }
    echo '</td></tr></table>';

    // if single grading, print a single grade scale
    if ($cognitivefactory->singlegrade){
        echo '<br/>';
        print_string('grade');
        echo ' : ';
        make_grading_menu($cognitivefactory, 'grade', @$gradeset->single, false);
    } 

    /// print the submit button    
    echo '<br/><center>';
    echo "<br/><input type=\"submit\" name=\"go_btn\" value=\"". get_string('update') .'" />';

    /// end form
    echo '</form>';
}
echo '</center>';
?>