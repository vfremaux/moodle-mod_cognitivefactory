<?php

	/**
	* A view to collect and/or manage feedback
	* @package mod-cognitivefactory
	* @author valery fremaux (valery.fremaux@gmail.com)
	*
	*/

	if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

	$userid = optional_param('userid', $USER->id, PARAM_INT);
	if (!$isstudent && ($USER->id != $userid) || !$cognitivefactory->privacy){
		$userformincludes = cognitivefactory_have_reports($cognitivefactory);
		include 'user_selector_form.php';
	}


	if ($isstudent || ($userid != $USER->id)){

		$reporter = $DB->get_record('user', array('id' => $userid));
		echo $OUTPUT->heading(get_string('reportfor', 'cognitivefactory', fullname($reporter)));

	    $report = $DB->get_field('cognitivefactory_userdata', 'report', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));
	    $reportformat = $DB->get_field('cognitivefactory_userdata', 'reportformat', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));

	    echo $OUTPUT->heading(get_string('myreport', 'cognitivefactory'), 4);

		if (empty($report)){
		    echo $OUTPUT->box($OUTPUT->notification(get_string('noreportgiven', 'cognitivefactory')), 'cognitivefactory-errorbox');
		} else {
		    echo $OUTPUT->box('<p align="left">'.format_string(format_text($report, $reportformat)).'</p>', 'cognitivefactory-feedback');
		}

		if ($isstudent){
		    $options = array('id' => $cm->id, 'what' => 'editreport');
		    echo '<center>';
		    echo '<p>';
		    echo $OUTPUT->single_button(new moodle_url("editfeedback.php", $options), get_string('editareport', 'cognitivefactory'), 'get');
		    echo '</p>';
		    echo '</center>';
		} else {
		    $options = array('id' => $cm->id, 'view' => 'feedback', 'page' => 'report');
		    echo '<center>';
		    echo '<p>';
		    echo $OUTPUT->single_button(new moodle_url("view.php", $options), get_string('backtoreportlist', 'cognitivefactory'), 'get');
		    echo '</p>';
		    echo '</center>';
		}

	} else { // you are "teacher" here, see the list of who posted reports

	    $participants = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'u.id,firstname,lastname,picture,email,imagealt', 'lastname');
	    $participantids = array_keys($participants);
	    $reporters = cognitivefactory_have_reports($cognitivefactory, $participantids);
	    if (!empty($reporters)){
	        $havereported = array_keys($reporters);
	    } else {
	        $havereported = array();
	    }

		echo '<table width="100%">';

		echo '<tr><th>';
		echo get_string('havereport', 'cognitivefactory');
		echo '</th><th>';
		echo get_string('reportless', 'cognitivefactory');
		echo '</th></tr>';

		echo '<tr><td>';    

		echo '<table>';
	    $werereported = false;
	    foreach($participants as $participant){
	        if (@in_array($participant->id, $havereported)){
	            $werereported = true;
	            echo '<tr><td>';
	            echo $OUTPUT->user_picture($participant);
	            echo ' '.fullname($participant);
	            echo " -> <a href=\"view.php?id={$cm->id}&amp;view=feedback&amp;page=report&amp;userid={$participant->id}\">".get_string('seereport', 'cognitivefactory').'</a>';
	            echo " | <a href=\"view.php?id={$cm->id}&amp;view=grade&amp;gradefor={$participant->id}\">".get_string('dograde', 'cognitivefactory').'</a><br/>';
	            echo '</td></tr>';
	        } else {
	            $reportless[] = $participant;
	        }
	    }
	    if (!$werereported){
	        echo '<tr><td>'.get_string('noreports', 'cognitivefactory').'</td></tr>';
	    }
		echo '</table>';
		
		echo '</td><td>';

		echo '<table>';
	    if (!empty($reportless)){
	        foreach($reportless as $participant){
	            echo '<tr><td>';
	            echo $OUTPUT->user_picture($participant);
	            echo fullname($participant);
	            echo '</td></tr>';
	        }
	    }
		echo '</table>';

		echo '</td></tr></table>';

	}
