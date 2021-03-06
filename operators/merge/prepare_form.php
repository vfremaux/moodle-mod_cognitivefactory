<?php

include_once $CFG->dirroot.'/mod/cognitivefactory/operators/operator_prepare_form.class.php';

class merge_prepare_form extends OperatorMoodleForm{
    
    function definition() {
        
        $mform = $this->_form;

        $context = context_module::instance($this->_customdata['cmid']);
        $this->add_standard_prepare_elements($mform, $this->_customdata['cmid']);

        $maxitems_options = array();
        for ($i = 1 ; $i <= $this->_customdata['maxresponses'] ; $i++) {
            $maxitems_options[$i] = $i;
        }

        $mform->addElement('select', 'config_maxideasleft', get_string('maxideasleft', 'cognitiveoperator_merge'), $maxitems_options);
        $mform->addHelpButton('config_maxideasleft', 'maxideasleft', 'cognitiveoperator_merge');

        $mform->addElement('checkbox', 'config_allowreducesource', get_string('allowreducesource', 'cognitiveoperator_merge'));
        $mform->addHelpButton('config_allowreducesource', 'allowreducesource', 'cognitiveoperator_merge');
        
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
            if (in_array($key, array('requirement', 'maxideasleft', 'allowreducesource', 'blindness'))) {
                $configkey = 'config_'.$key;
                $defaults->$configkey = $value;
                unset($defaults->$key);
            }
        }

        $defaults->config_requirementformat = FORMAT_HTML;

        if ($this->_customdata['oprequirementtype'] == 2) {
            $context = context_module::instance($this->_customdata['cmid']);
    
            $draftid_editor = file_get_submitted_draft_itemid('config_requirement_editor');
            $currenttext = file_prepare_draft_area($draftid_editor, $context->id, 'mod_congnitivefactory', 'config_requirement_editor', $defaults->id, array('subdirs' => true), $defaults->config_requirement);
            $defaults = file_prepare_standard_editor($defaults, 'config_requirement', $this->editoroptions, $context, 'mod_cognitivefactory', 'mergerequirement', $defaults->id);
            $defaults->config_requirement = array('text' => $currenttext, 'format' => $defaults->config_requirementformat, 'itemid' => $draftid_editor);
        }

        parent::set_data($defaults);

    }
}