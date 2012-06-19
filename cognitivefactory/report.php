<?php

	/**
	* A view to collect and/or manage feedback
	* @package mod-cognitivefactory
	* @author valery fremaux (valery.fremaux@gmail.com)
	*
	*/

	if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

	$isstudent = has_capability('mod/cognitivefactory:gradable', $context, $USER->id, false);
	
	print_heading(get_string('report', 'cognitivefactory'));
	
	if ($isstudent){
	    $form->report = get_field('cognitivefactory_userdata', 'report', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $USER->id);
	    $form->reportformat = get_field('cognitivefactory_userdata', 'reportformat', 'cognitivefactoryid', $cognitivefactory->id, 'userid', $USER->id);
	    echo '<b>'.get_string('myreport', 'cognitivefactory').':</b><br/>';
	    print_box(format_string(format_text(@$form->report, @$form->reportformat)));
	    if (!empty($feedback)){
	        echo '<b>'.get_string('teacherfeedback', 'cognitivefactory').':</b><br/>';
	        print_box($feedback);
	    }
	    $options = array('id' => $cm->id, 'what' => 'editreport');
	    echo '<center>';
	    print_single_button("view.php",  $options, get_string('editareport', 'cognitivefactory'));
	    echo '</center>';
	} else { // you are "teacher" here, see the list of who posted reports
	    $participants = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'u.id,firstname,lastname,picture,email', 'lastname');
	    $reportstatus = cognitivefactory_have_reports($cognitivefactory->id, array_keys($participants));
	    if (!empty($reportstatus)){
	        $havereported = array_keys($reportstatus);
	    } else {
	        $havereported = array();
	    }
?>
<table width="90%">
    <tr>
        <th>
            <?php print_string('havereport', 'cognitivefactory') ?>
        </th>
        <th>
            <?php print_string('reportless', 'cognitivefactory') ?>
        </th>
    <tr>
        <td>    
            <table>
<?php
	    $werereported = false;
	    foreach($participants as $participant){
	        if (@in_array($participant->id, $havereported)){
	            $werereported = true;
	            echo '<tr><td>';
	            print_user_picture($participant->id, $course->id, $participant->picture, false, false, true);
	            echo fullname($participant);
	            echo " -> <a href=\"view.php?id={$cm->id}&amp;view=grade&amp;gradefor={$participant->id}\">".get_string('dograde', 'cognitivefactory').'</a><br/>';
	            echo '</td></tr>';
	        } else {
	            $reportless[] = $participant;
	        }
	    }
	    if (!$werereported){
	        echo '<tr><td>'.get_string('noreports', 'cognitivefactory').'</td></tr>';
	    }
?>
            </table>
        </td>
        <td>
            <table>
<?php
	    if (!empty($reportless)){
	        foreach($reportless as $participant){
	            echo '<tr><td>';
	            print_user_picture($participant->id, $course->id, $participant->picture, false, false, true);
	            echo fullname($participant);
	            echo '</td></tr>';
	        }
	    }
?>
            </table>
        </td>
    </tr>
</table>
<?php
	}
?>
