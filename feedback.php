<?php

	if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');
	
	if (!$isstudent){
		include 'user_selector_form.php';
	} else {
		$userid = $USER->id; // security
	}
	$feedbackuser = $DB->get_record('user', array('id' => $userid));
	
	$feedback = cognitivefactory_get_feedback($cognitivefactory, $userid);
	
	echo $OUTPUT->heading(get_string('feedbackfor', 'cognitivefactory', fullname($feedbackuser)));
	
	echo $feedback;
	
	if (!$isstudent){
		$options = array('id' => $cm->id, 'what' => 'editfeedback');
		echo $OUTPUT->single_button($options, get_string('edituserfeedback'), 'get');
	
		$options = array('id' => $cm->id, 'what' => 'editglobalfeedback');
		echo $OUTPUT->single_button($options, get_string('editglobalfeedback'), 'get');
	}