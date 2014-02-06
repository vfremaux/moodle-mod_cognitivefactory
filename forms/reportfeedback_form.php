<?php

require $CFG->libdir.'/formslib.php';

class Cognitivefactory_ReportFeedback_Form extends moodleform{

	function definition(){
	 	$mform    =& $this->_form;

	 	$mform->addElement('hidden', 'id');
	 	$mform->setType('id', PARAM_INT);

	 	$mform->addElement('hidden', 'what');
	 	$mform->setType('what', PARAM_TEXT);

	 	$mform->addElement('hidden', 'userid');
	 	$mform->setType('userid', PARAM_INT);

		if ($this->_customdata['what'] == 'editreport'){
		 	$mform->addElement('editor', 'report', get_string('report', 'cognitivefactory'));
		} else { // editfeedback and editglobalfeedback
		 	$mform->addElement('editor', 'feedback', get_string('feedback', 'cognitivefactory'));
		}

	 	$this->add_action_buttons(true);

	}
	
	function validation($data, $files = array()){
	}
}