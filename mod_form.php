<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->dirroot.'/mod/cognitivefactory/lib.php');

class mod_cognitivefactory_mod_form extends moodleform_mod {
	
    function definition() {
        global $CFG, $DB, $COURSE;
				
        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor(true, get_string('intro', 'cognitivefactory'));

		$mform->addElement('textarea', 'collectrequirement', get_string('collectrequirement', 'cognitivefactory'), array('cols' => 60, 'rows' => 8));
		
		$privacy_options = array(
        		'0' => get_string('no'),
        		'1' => get_string('yes')
        );
		$mform->addElement('select', 'privacy', get_string('privacy', 'cognitivefactory'), $privacy_options);

		$numresponses_options = range(0, BRAINSTORM_MAX_RESPONSES);
		$numresponses_options[0] = get_string('unlimited', 'cognitivefactory');
		$numresponsesinform_options = range(0, BRAINSTORM_MAX_RESPONSES);
		unset($numresponsesinform_options[0]);
		$numcolumns_options = range(1, BRAINSTORM_MAX_COLUMNS);

		$mform->addElement('select', 'numresponses', get_string('numinputs', 'cognitivefactory'), $numresponses_options);
		$mform->addHelpButton('numresponses', 'numinputs', 'cognitivefactory');

		$mform->addElement('select', 'numresponsesinform', get_string('numresponsesinform', 'cognitivefactory'), $numresponsesinform_options);
		$mform->addHelpButton('numresponsesinform', 'numinputs', 'cognitivefactory');

		$mform->addElement('select', 'numcolumns', get_string('numcolumns', 'cognitivefactory'), $numcolumns_options);
		$mform->addHelpButton('numcolumns', 'numcolumns', 'cognitivefactory');

		$oprequirementtype_options = array();
		$oprequirementtype_options[0] = get_string('textfield', 'cognitivefactory');
		$oprequirementtype_options[1] = get_string('textarea', 'cognitivefactory');
		$oprequirementtype_options[2] = get_string('whysiwhygtextarea', 'cognitivefactory');

		$mform->addElement('select', 'oprequirementtype', get_string('oprequirementtype', 'cognitivefactory'), $oprequirementtype_options);
		$mform->addHelpButton('oprequirementtype', 'oprequirementtype', 'cognitivefactory');

        $mode_options = array(
        	'sequential' => get_string('sequential', 'cognitivefactory'),
        	'parallel' => get_string('parallel', 'cognitivefactory')
        );

		$mform->addElement('select', 'flowmode', get_string('flowcontrol', 'cognitivefactory'), $mode_options);
		$mform->addHelpButton('flowmode', 'flowcontrol', 'cognitivefactory');

		$mform->addElement('header', 'phaseattribution', get_string('phaseattribution', 'cognitivefactory'));
		$mform->addHelpButton('phaseattribution', 'phaseattribution', 'cognitivefactory');
		
		$group1 = array();
		$group1[] = & $mform->createElement('radio', 'seqaccesscollect', '', get_string('student', 'cognitivefactory'), 1);
		$group1[] = & $mform->createElement('radio', 'seqaccesscollect', '', get_string('trainer', 'cognitivefactory'), 0);
		$mform->addGroup($group1, 'collectgroup', get_string('collect', 'cognitivefactory'), '', false);

		$group2 = array();
		$group2[] = & $mform->createElement('radio', 'seqaccessprepare', '', get_string('student', 'cognitivefactory'), 1);
		$group2[] = & $mform->createElement('radio', 'seqaccessprepare', '', get_string('trainer', 'cognitivefactory'), 0);
		$mform->addGroup($group2, 'preparegroup', get_string('prepare', 'cognitivefactory'), '', false);

		$group3 = array();
		$group3[] = & $mform->createElement('radio', 'seqaccessorganize', '', get_string('student', 'cognitivefactory'), 1);
		$group3[] = & $mform->createElement('radio', 'seqaccessorganize', '', get_string('trainer', 'cognitivefactory'), 0);
		$mform->addGroup($group3, 'organizegroup', get_string('organize', 'cognitivefactory'), '', false);

		$group4 = array();
		$group4[] = & $mform->createElement('radio', 'seqaccessdisplay', '', get_string('student', 'cognitivefactory'), 1);
		$group4[] = & $mform->createElement('radio', 'seqaccessdisplay', '', get_string('trainer', 'cognitivefactory'), 0);
		$mform->addGroup($group4, 'displaygroup', get_string('display', 'cognitivefactory'), '', false);

		$group5 = array();
		$group5[] = & $mform->createElement('radio', 'seqaccessfeedback', '', get_string('student', 'cognitivefactory'), 1);
		$group5[] = & $mform->createElement('radio', 'seqaccessfeedback', '', get_string('trainer', 'cognitivefactory'), 0);
		$mform->addGroup($group5, 'feedbackgroup', get_string('feedback', 'cognitivefactory'), '', false);

        $this->standard_grading_coursemodule_elements();

		$mform->addElement('header', 'extragrading', get_string('extragrading', 'cognitivefactory'));

		$splitted_options = array(
        		'0' => get_string('no'),
        		'1' => get_string('yes')
        );
		$mform->addElement('select', 'singlegrade', get_string('singlegrade', 'cognitivefactory'), $splitted_options);

		$mform->addElement('text', 'participationweight', get_string('participationweight', 'cognitivefactory'));
		$mform->addHelpButton('participationweight', 'gradeweights', 'cognitivefactory');
		$mform->setType('participationweight', PARAM_TEXT);
		$mform->setDefault('participationweight', 10);

		$mform->addElement('text', 'preparingweight', get_string('preparingweight', 'cognitivefactory'));
		$mform->addHelpButton('preparingweight', 'gradeweights', 'cognitivefactory');
		$mform->setType('preparingweight', PARAM_TEXT);
		$mform->setDefault('preparingweight', 10);

		$mform->addElement('text', 'organizeweight', get_string('organizeweight', 'cognitivefactory'));
		$mform->addHelpButton('organizeweight', 'gradeweights', 'cognitivefactory');
		$mform->setType('organizeweight', PARAM_TEXT);
		$mform->setDefault('organizeweight', 10);

		$mform->addElement('text', 'feedbackweight', get_string('feedbackweight', 'cognitivefactory'));
		$mform->addHelpButton('feedbackweight', 'gradeweights', 'cognitivefactory');
		$mform->setType('feedbackweight', PARAM_TEXT);
		$mform->setDefault('feedbackweight', 10);
		
		$mform->setExpanded('phaseattribution');
		
//-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
//-------------------------------------------------------------------------------
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        // Set up completion section even if checkbox is not ticked
        if (empty($data->completionsection)) {
            $data->completionsection = 0;
        }
        return $data;
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'choice'));
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }
    
}

