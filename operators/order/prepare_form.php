<?php

include_once $CFG->dirroot.'/mod/cognitivefactory/operators/operator_prepare_form.class.php';

class order_prepare_form extends OperatorMoodleForm{
    
    function definition() {
        
        $mform = $this->_form;

        $context = context_module::instance($this->_customdata['cmid']);
        $this->add_standard_prepare_elements($mform, $this->_customdata['cmid']);

        $radioarr = array();
        $radioarr[] = &$mform->createElement('radio', 'config_absolute', '', get_string('yes'), 1);
        $radioarr[] = &$mform->createElement('radio', 'config_absolute', '', get_string('no'), 0);

        $mform->addGroup($radioarr, 'absolutegroup', get_string('absolute', 'cognitiveoperator_order'), '', false);


        if (has_capability('mod/cognitivefactory:manage', $context)) {

            $mform->addElement('header', 'headadmin', get_string('adminoptions', 'cognitivefactory'));
            $radioarr = array();
            $radioarr[] = &$mform->createElement('radio', 'blindness', '', get_string('no'), 0);
            $radioarr[] = &$mform->createElement('radio', 'blindness', '', get_string('yes'), 1);
    
            $mform->addGroup($radioarr, 'blindnessgroup', get_string('blindness', 'cognitiveoperator_order'), '', false);
        }

        $mform->setExpanded('headadmin');        
        
        $this->add_action_buttons(false);
    }

    function validation($data, $files = null) {
    }

    function set_data($defaults) {
        
        // shift all config values to config_ fields
        foreach ($defaults as $key => $value) {
            if (in_array($key, array('requirement', 'requirement_editor', 'absolute', 'blindness'))) {
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
            $defaults = file_prepare_standard_editor($defaults, 'config_requirement', $this->editoroptions, $context, 'mod_cognitivefactory', 'orderrequirement', $defaults->id);
            $defaults->config_requirement = array('text' => $currenttext, 'format' => $defaults->config_requirementformat, 'itemid' => $draftid_editor);
        }

        parent::set_data($defaults);

    }
}