<?php

include_once $CFG->dirroot.'/mod/cognitivefactory/operators/operator_prepare_form.class.php';

class locate_prepare_form extends OperatorMoodleForm{
    
    function definition() {
        
        $mform = $this->_form;
        $textoptions = array('size' => 50);
        $numericoptions = array('size' => 10);

        $context = context_module::instance($this->_customdata['cmid']);
        $this->add_standard_prepare_elements($mform, $this->_customdata['cmid']);

        $radioarr = array();
        $radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('integer', 'cognitiveoperator_locate'), 0);
        $radioarr[] = &$mform->createElement('radio', 'config_quantifiertype', '', get_string('float', 'cognitiveoperator_locate'), 1);

        $mform->addGroup($radioarr, 'qtypegroup', get_string('quantifiertype', 'cognitiveoperator_locate'), '', false);

        $mform->addElement('header', 'config_quantifiers', get_string('quantifiers', 'cognitiveoperator_locate'));

        $mform->addElement('text', 'config_xquantifier', get_string('xquantifier', 'cognitiveoperator_locate'), $textoptions);
        $mform->setType('config_xquantifier', PARAM_TEXT);
        $mform->addElement('text', 'config_xminrange', get_string('xminrange', 'cognitiveoperator_locate'), $numericoptions);
        $mform->setType('config_xminrange', PARAM_NUMBER);
        $mform->addElement('text', 'config_xmaxrange', get_string('xmaxrange', 'cognitiveoperator_locate'), $numericoptions);
        $mform->setType('config_xmaxrange', PARAM_NUMBER);

        $mform->addElement('text', 'config_yquantifier', get_string('yquantifier', 'cognitiveoperator_locate'), $textoptions);
        $mform->setType('config_yquantifier', PARAM_TEXT);
        $mform->addElement('text', 'config_yminrange', get_string('yminrange', 'cognitiveoperator_locate'), $numericoptions);
        $mform->setType('config_yminrange', PARAM_NUMBER);
        $mform->addElement('text', 'config_ymaxrange', get_string('ymaxrange', 'cognitiveoperator_locate'), $numericoptions);
        $mform->setType('config_ymaxrange', PARAM_NUMBER);

        $mform->addElement('header', 'detection', get_string('detection', 'cognitiveoperator_locate'));

        $mform->addElement('text', 'config_neighbourhood', get_string('neighbourhood', 'cognitiveoperator_locate'), $numericoptions);
        $mform->setType('config_neighbourhood', PARAM_NUMBER);

        $mform->addElement('checkbox', 'config_allowreducesource', get_string('allowreducesource', 'cognitiveoperator_merge'));

        if (has_capability('mod/cognitivefactory:manage', $context)) {

            $mform->addElement('header', 'headadmin', get_string('adminoptions', 'cognitivefactory'));
            $radioarr = array();
            $radioarr[] = &$mform->createElement('radio', 'config_blindness', '', get_string('no'), 0);
            $radioarr[] = &$mform->createElement('radio', 'config_blindness', '', get_string('yes'), 1);
    
            $mform->addGroup($radioarr, 'blindnessgroup', get_string('blindness', 'cognitiveoperator_locate'), '', false);
        }
        $mform->setExpanded('headadmin');        
        $mform->setExpanded('detection');        
        
        $this->add_action_buttons(false);
    }

    function validation($data, $files = null) {
    }

    function set_data($defaults) {
        
        // shift all config values to config_ fields
        foreach ($defaults as $key => $value) {
            if (in_array($key, array('requirement', 'requirement_editor', 'quantifiertype', 'xquantifier', 'xminrange', 'xmaxrange', 'yquantifier', 'yminrange', 'ymaxrange', 'neighbourhood', 'allowreducesource', 'blindness'))) {
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
            $defaults = file_prepare_standard_editor($defaults, 'config_requirement', $this->editoroptions, $context, 'mod_cognitivefactory', 'locaterequirement', $defaults->id);
            $defaults->config_requirement = array('text' => $currenttext, 'format' => $defaults->config_requirementformat, 'itemid' => $draftid_editor);
        }

        parent::set_data($defaults);

    }
    
}