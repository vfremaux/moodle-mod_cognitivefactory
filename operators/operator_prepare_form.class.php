<?php

require_once $CFG->libdir.'/formslib.php';

abstract class OperatorMoodleForm extends moodleform{
    
    function add_standard_prepare_elements(&$mform, $cmid) {
        global $COURSE;
        
        $context = context_module::instance($cmid);
        
        $textareaoptions = array('cols' => 50, 'rows' => 5);
        $textoptions = array('size' => 50);

        $maxfiles = 99;                // TODO: add some setting
        $maxbytes = $COURSE->maxbytes; // TODO: add some setting    
        $this->editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $context);
                
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'operator');
        $mform->setType('operator', PARAM_TEXT);
        
        switch ($this->_customdata['oprequirementtype']) {
            case 0 :
                $mform->addElement('text', 'config_requirement', get_string('requirement', 'cognitivefactory'), $textoptions);
                $mform->setType('config_requirement', PARAM_TEXT);
                break;
            case 1 :
                $mform->addElement('textarea', 'config_requirement', get_string('requirement', 'cognitivefactory'), $textareaoptions);
                $mform->setType('config_requirement', PARAM_TEXT);
                break;
            case 2 :
                $mform->addElement('editor', 'config_requirement_editor', get_string('requirement', 'cognitivefactory'), null, $this->editoroptions);
                break;
        }
    }
    
}