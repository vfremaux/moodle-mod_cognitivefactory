<?php

include_once $CFG->dirroot.'/mod/cognitivefactory/operators/operator_prepare_form.class.php';

class map_prepare_form extends OperatorMoodleForm{
    
    function definition() {
        
        $mform = $this->_form;

        $context = context_module::instance($this->_customdata['cmid']);
        $this->add_standard_prepare_elements($mform, $this->_customdata['cmid']);

        $radioarr = array();
        $radioarr[] = &$mform->createElement('radio', 'config_quantified', '', get_string('no'), 0);
        $radioarr[] = &$mform->createElement('radio', 'config_quantified', '', get_string('yes'), 1);

        $mform->addGroup($radioarr, 'quantifgroup', get_string('quantified', 'cognitiveoperator_map'), '', false);
        $mform->addHelpButton('quantifgroup', 'quantified', 'cognitiveoperator_map');

        $radioarr = array();
        $radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('integer', 'cognitiveoperator_map'), 'integer');
        $radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('float', 'cognitiveoperator_map'), 'float');
        $radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('multiple', 'cognitiveoperator_map'), 'multiple');

        $mform->addGroup($radioarr, 'qtypegroup', get_string('quantifiertype', 'cognitiveoperator_map'), '', false);
        $mform->addHelpButton('qtypegroup', 'quantifiertype', 'cognitiveoperator_map');

        $radioarr = array();
        $radioarr[] = &$mform->createElement('radio', 'config_procedure', '', get_string('gridediting', 'cognitiveoperator_map'), 'gridediting');
        $radioarr[] = &$mform->createElement('radio', 'config_procedure', '', get_string('picktwoandqualify', 'cognitiveoperator_map'), 'picktwoandqualify');
        $radioarr[] = &$mform->createElement('radio', 'config_procedure', '', get_string('onetoonerandom', 'cognitiveoperator_map'), 'onetoonerandom');

        $mform->addGroup($radioarr, 'procgroup', get_string('procedure', 'cognitiveoperator_map'), '', false);
        $mform->addHelpButton('procgroup', 'procedure', 'cognitiveoperator_map');
        
        $mform->addElement('checkbox', 'config_checkcycles', get_string('checkcycles', 'cognitiveoperator_map'));
        $mform->addHelpButton('config_checkcycles', 'checkcycles', 'cognitiveoperator_map');

        if (has_capability('mod/cognitivefactory:manage', $context)) {

            $mform->addElement('header', 'headadmin', get_string('adminoptions', 'cognitivefactory'));
            $radioarr = array();
            $radioarr[] = &$mform->createElement('radio', 'config_blindness', '', get_string('no'), 0);
            $radioarr[] = &$mform->createElement('radio', 'config_blindness', '', get_string('yes'), 1);
    
            $mform->addGroup($radioarr, 'blindnessgroup', get_string('blindness', 'cognitiveoperator_hierarchize'), '', false);
        }
        $mform->setExpanded('headadmin');        
        
        $this->add_action_buttons(false);
    }

    function validation($data, $files = null) {
    }

    function set_data($defaults) {
        
        // shift all config values to config_ fields
        foreach ($defaults as $key => $value) {
            if (in_array($key, array('requirement', 'requirement_editor', 'quantified', 'quantifiertype', 'procedure', 'checkcycles', 'blindness'))) {
                $configkey = 'config_'.$key;
                $defaults->$configkey = $value;
                unset($defaults->$key);
            }
        }

        $defaults->config_requirementformat = FORMAT_HTML;

        if ($this->_customdata['oprequirementtype'] == 2) {
            $context = context_module::instance($this->_customdata['cmid']);
    
            $draftid_editor = file_get_submitted_draft_itemid('config_requirement_editor');
            $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'mod_cognitivefactory', 'config_requirement_editor', $defaults->id, array('subdirs' => true), $defaults->config_requirement);
            $defaults = file_prepare_standard_editor($defaults, 'config_requirement', $this->editoroptions, $context, 'mod_cognitivefactory', 'maprequirement', $defaults->id);
            $defaults->config_requirement = array('text' => $currenttext, 'format' => $defaults->config_requirementformat, 'itemid' => $draftid_editor);
        }

        parent::set_data($defaults);

    }
}