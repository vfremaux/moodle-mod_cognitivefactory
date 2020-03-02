<?php

require_once $CFG->libdir.'/formslib.php';

class import_form extends moodleform{
    
    function definition() {
        global $COURSE;
        
        $mform = $this->_form;
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        
        $maxbytes = $COURSE->maxbytes;
        
        $fileoptions = array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1);
        
        $mform->addElement('filepicker', 'inputs', get_string('importfile', 'cognitivefactory'), $fileoptions);

        $mform->addElement('checkbox', 'clearalldata', get_string('clearalldata', 'cognitivefactory'), get_string('clearalladvice', 'cognitivefactory'));
        
        $this->add_action_buttons();
        
    }
    
    function validation($data, $files = null) {
        
        return false;
    }
}