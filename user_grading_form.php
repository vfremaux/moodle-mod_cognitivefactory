<?php

require_once $CFG->libdir.'/formslib.php';

class UserGradingForm extends moodleform{
	
	function definition(){
		global $DB, $OUTPUT, $CFG;
		
		$mform = $this->_form;
		
		$cognitivefactoryid = $this->_customdata['instanceid'];
		$cognitivefactory = $DB->get_record('cognitivefactory', array('id' => $cognitivefactoryid));
		$gradefor = $this->_customdata['gradefor'];
		$currentgroup = groups_get_activity_group($this->_customdata['cm']);

		$grademenu = make_grades_menu($cognitivefactory->grade);
		
		$mform->addElement('hidden', 'id');
		$mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'what');
		$mform->setType('what', PARAM_TEXT);		

		$mform->addElement('hidden', 'for');
		$mform->setType('for', PARAM_INT);		

	    /// -------------------------------------------------------------------------------------
	    /// printing ideas
	    $mform->addElement('header', 'headcollect', get_string('responses', 'cognitivefactory'));
	
	    $responses = cognitivefactory_get_responses($cognitivefactory->id, $gradefor, 0, false);
	    if ($responses){
	        $str = '<table width="100%"><tr>';
	        $str .= cognitivefactory_print_responses_cols($cognitivefactory, $responses, true);
	        $str .= '</tr></table>';
	    } else {
	    	if ($cognitivefactory->seqaccesscollect){
		        $str = $OUTPUT->box(get_string('notresponded', 'cognitivefactory'), 'cognitivefactory-notification');
			} else {
		        $str = $OUTPUT->box(get_string('teacherphase', 'cognitivefactory'), 'cognitivefactory-notification');
			}
	    }
		$mform->addElement('html', $str);

	    if (!$cognitivefactory->singlegrade){
	    	if ($cognitivefactory->seqaccesscollect){
		        $mform->addElement('select', 'participate', get_string('gradeforparticipation', 'cognitivefactory'), $grademenu);
		    }
		}

	    // getting valid operator list
	    $operators = cognitivefactory_get_operators($cognitivefactory->id);

    /// -------------------------------------------------------------------------------------
    	/// printing preparing sets
	    $mform->addElement('header', 'headprepare', get_string('preparations', 'cognitivefactory'));
	    if ($operators){
	    	$str = '';
	    	if (!$cognitivefactory->seqaccessprepare){
		        $str .= $OUTPUT->box(get_string('teacherphase', 'cognitivefactory'), 'cognitivefactory-notification');
			}
	        $str .= '<table width="90%" cellspacing="10"><tr valign="top">';
	        $i = 0;
	        foreach($operators as $operator){ // print operator settings for each valid operator
	            if (!$operator->active) continue;
	            if ($i && ($i % 2 == 0)) echo '</tr><tr valign="top">';
	            $str .= '<td align="right" width="50%">';
	            $str .= $OUTPUT->heading(get_string($operator->name, 'cognitiveoperator_'.$operator->name), 4);
	            $str .= '<table width="90%" class="generaltable">';
	            foreach(get_object_vars($operator->configdata) as $key => $value){
	            	if(preg_match('/requirement/', $key)) continue;
	                $str .= "<tr valign=\"top\"><td align=\"right\" width=\"60%\" class=\"header\"><b>".get_string($key, 'cognitiveoperator_'.$operator->name).'</b>:</td>';
	                $str .= "<td>$value</td></tr>";
	            }            
	            $str .= '</table></td>';
	            $i++;
	        }
	        $str .= '</tr></table>';
	       	
	       	$mform->addElement('html', $str);
	    }

	    if (!$cognitivefactory->singlegrade){
		    if ($cognitivefactory->seqaccessprepare){
		        $mform->addElement('select', 'prepare', get_string('gradeforpreparation', 'cognitivefactory'), $grademenu);
		    }
		}

    /// -------------------------------------------------------------------------------------
	    /// printing organizations
	    $mform->addElement('header', 'headorganize', get_string('organizations', 'cognitivefactory'));
	    if ($operators){
	    	$str = '';
	        foreach($operators as $operator){ // print organisation result for this operator and this user
	            if (!$operator->active) continue;
	            include_once("{$CFG->dirroot}/mod/cognitivefactory/operators/{$operator->id}/locallib.php");
	            $displayfunction = $operator->name.'_display';
	            $opname = get_string($operator->name, 'cognitiveoperator_'.$operator->name);
                $str .= '<br/>';
                $str .= '<br/>';
                $str .= $OUTPUT->heading($opname, 4);
	            if (function_exists($displayfunction)){
	                $cognitivefactory->cm = &$cm;
	                echo "printing $displayfunction ";
	                $str .= $displayfunction($cognitivefactory, $gradefor, $currentgroup, true);
	            } else {
	               	$str .= get_string('notabletodisplayfor', 'cognitivefactory', $opname);
	                $str .= '<br/>';
	            }
	        }
	       	$mform->addElement('html', $str);
	    }

	    if (!$cognitivefactory->singlegrade){
		    if ($cognitivefactory->seqaccessorganize){
		        $mform->addElement('select', 'organize', get_string('gradefororganisation', 'cognitivefactory'), $grademenu);
		    }
		}

	    /// printing final feedback and report
	    $mform->addElement('header', 'headreport', get_string('report', 'cognitivefactory'));

	    $report = $DB->get_record('cognitivefactory_userdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $gradefor));
	    $str = $OUTPUT->box(format_string(format_text(@$report->report, @$report->reportformat)), 'cognitivefactory-feedback');
       	$mform->addElement('html', $str);
		
	    if (!$cognitivefactory->singlegrade){
		    if ($cognitivefactory->seqaccessfeedback){
		        $mform->addElement('select', 'feedback', get_string('gradeforfeedback', 'cognitivefactory'), $grademenu);
		    } 
	    }

	    $mform->addElement('header', 'headfeedback', get_string('feedback', 'cognitivefactory'));
       	$mform->addElement('editor', 'teacherfeedback', get_string('teacherfeedback', 'cognitivefactory'));
       	
	    if ($cognitivefactory->singlegrade){
		    $mform->addElement('header', 'head2', get_string('grading', 'cognitivefactory'));
		    $mform->addElement('select', 'grade', get_string('grade', 'cognitivefactory'), $grademenu);
		}
		
		$mform->setExpanded('headcollect');
		$mform->setExpanded('headprepare');
		$mform->setExpanded('headorganize');
		$mform->setExpanded('headreport');
		$mform->setExpanded('headfeedback');

		$this->add_action_buttons();
	}

	function validation($data, $files = array()){
	}

}