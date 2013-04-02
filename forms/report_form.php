<?php

require $CFG->libdir.'/formslib.php';

class Cognitivefactory_Report_Form extends moodleform{
	
	function definition(){

	 	$mform    =& $this->_form;
	 	
	 	$mform->addElement('hidden', 'id');
	 	$mform->addElement('hidden', 'view');
	 	
	 	$mform->addElement('htmleditor', 'report', get_string('report', 'cognitivefactory'));
	 	
	 	$this->add_action_buttons(true);
	}
	
}