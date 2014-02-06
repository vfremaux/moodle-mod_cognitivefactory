<?php

include_once $CFG->dirroot.'/mod/cognitivefactory/operators/operator_prepare_form.class.php';

class schedule_prepare_form extends OperatorMoodleForm{
	
	function definition(){
						
		$mform = $this->_form;

		$this->add_standard_prepare_elements($mform, $this->_customdata['cmid']);

		$radioarr = array();
		$radioarr[] = &$mform->createElement('radio', 'config_quantifyedges', '', get_string('no'), 0);
		$radioarr[] = &$mform->createElement('radio', 'config_quantifyedges', '', get_string('yes'), 1);

		$mform->addGroup($radioarr, 'qedgesgroup', get_string('quantifyedges', 'cognitiveoperator_schedule'), '', false);

		$radioarr = array();
		$radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('integer', 'cognitiveoperator_schedule'), 'integer');
		$radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('float', 'cognitiveoperator_schedule'), 'float');
		$radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('multiple', 'cognitiveoperator_schedule'), 'multiple');

		$mform->addGroup($radioarr, 'qtypegroup', get_string('quantifiertype', 'cognitiveoperator_schedule'), '', false);
		
		$this->add_action_buttons(false);
	}

	function validation($data, $files = null){
	}

	function set_data($defaults){
		
		// shift all config values to config_ fields
		foreach($defaults as $key => $value){
			if (in_array($key, array('requirement', 'requirement_editor', 'quantifyedges', 'quantifiertype', 'blindness'))){
				$configkey = 'config_'.$key;
				$defaults->$configkey = $value;
				unset($defaults->$key);
			}
		}

		$defaults->config_requirementformat = FORMAT_HTML;

		if ($this->_customdata['oprequirementtype'] == 2){
			$context = context_module::instance($this->_customdata['cmid']);
	
			$draftid_editor = file_get_submitted_draft_itemid('config_requirement_editor');
			$currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'mod_cognitivefactory', 'config_requirement_editor', $defaults->id, array('subdirs' => true), $defaults->config_requirement);
			$defaults = file_prepare_standard_editor($defaults, 'config_requirement', $this->editoroptions, $context, 'mod_cognitivefactory', 'schedulerequirement', $defaults->id);
			$defaults->config_requirement = array('text' => $currenttext, 'format' => $defaults->config_requirementformat, 'itemid' => $draftid_editor);
		}

    	parent::set_data($defaults);

	}
}