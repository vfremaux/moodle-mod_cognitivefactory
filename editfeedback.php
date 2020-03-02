<?php

    /**
    * @package mod_cognitivefactory
    * @category module
    * @author Martin Ellermann, Valery Fremaux > 1.8
    * @date 22/12/2007
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    *
    * This page prints a particular instance of a cognitivefactory and handles
    * top level interactions
    */

    /**
    * Include and requires
    */
    require_once("../../config.php");
    require_once($CFG->dirroot."/mod/cognitivefactory/lib.php");
    require_once($CFG->dirroot."/mod/cognitivefactory/locallib.php");
    require_once($CFG->dirroot."/mod/cognitivefactory/forms/reportfeedback_form.php");
    
    $id = required_param('id', PARAM_INT); // course module id
    $action = required_param('what', PARAM_TEXT); // which feedback either report
    $userid = optional_param('userid', $USER->id, PARAM_INT); // which feedback either report
        
    $url = new moodle_url($CFG->wwwroot.'/mod/cognitivefactory/editfeedback.php', array('id' => $id, 'what' => $action, 'userid' => $userid));
    $PAGE->set_url($url);

    if (! $cm = $DB->get_record('course_modules', array('id' => $id))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (!$cognitivefactory = $DB->get_record('cognitivefactory', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }
    
    $cognitivefactory->cmid = $cm->id;
    
/// Security

    require_login($course->id, false, $cm);
    $context = context_module::instance($cm->id);
    
    if (!has_capability('mod/cognitivefactory:grade', $context, $USER->id, false)) {
        $userid = $USER->id;
    }
    
/// Print the page header

    $PAGE->set_context($context);
    $PAGE->set_title($course->shortname.': '.format_string($cognitivefactory->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->navbar->add(get_string($action, 'cognitivefactory'));
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(true);
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'cognitivefactory'));

    $mform = new Cognitivefactory_ReportFeedback_Form($url, array('what' => $action));
    
    if ($mform->is_cancelled()) {
        redirect($CFG->wwwroot.'/mod/cognitivefactory/view.php?id='.$id.'&view=feedback');
    }

    if ($data = $mform->get_data()) {
        
        if ($action == 'editfeedback') {
            if ($userdata = $DB->get_record('cognitivefactory_userdata', array('userid' => $data->userid, 'cognitivefactoryid' => $cognitivefactory->id))) {
                $userdata->feedback = $data->feedback['text'];
                $userdata->feedbackformat = $data->feedback['format'];
                $userdata->timeupdated = time();
                $DB->update_record('cognitivefactory_userdata', $userdata);
            } else {
                $userdata = new StdClass();
                $userdata->userid = $data->userid;
                $userdata->cognitivefactoryid = $cognitivefactory->id;
                $userdata->report = '';
                $userdata->reportformat = FORMAT_HTML;
                $userdata->feedback = $data->feedback['text'];
                $userdata->feedbackformat = $data->feedback['format'];
                $userdata->timeupdated = time();
                $DB->insert_record('cognitivefactory_userdata', $userdata);
            }
        }
        if ($action == 'editglobalfeedback') {
            // register global feedback            
            $DB->set_field('cognitivefactory', 'globalteacherfeedback', $data->feedback, array('id' => $cognitivefactory->id));
        }
        if ($action == 'editreport') {
            if ($userdata = $DB->get_record('cognitivefactory_userdata', array('userid' => $data->userid, 'cognitivefactoryid' => $cognitivefactory->id))) {
                $userdata->report = $data->report['text'];
                $userdata->reportformat = $data->report['format'];
                $userdata->timeupdated = time();
                $DB->update_record('cognitivefactory_userdata', $userdata);
            } else {
                $userdata = new StdClass();
                $userdata->userid = $data->userid;
                $userdata->cognitivefactoryid = $cognitivefactory->id;
                $userdata->report = $data->report['text'];
                $userdata->reportformat = $data->report['format'];
                $userdata->feedback = '';
                $userdata->feedbackformat = FORMAT_HTML;
                $userdata->timeupdated = time();
                $DB->insert_record('cognitivefactory_userdata', $userdata);
            }
        }
        redirect($CFG->wwwroot.'/mod/cognitivefactory/view.php?id='.$id.'&view=feedback&userid='.$userid);
    } else {
        if ($action == 'editreport') {
            $text = $DB->get_field('cognitivefactory_userdata', 'report', array('userid' => $userid, 'cognitivefactoryid' => $cognitivefactory->id));
            $textformat = $DB->get_field('cognitivefactory_userdata', 'reportformat', array('userid' => $userid, 'cognitivefactoryid' => $cognitivefactory->id));
        } elseif ($action == 'editfeedback') {
            $text = $DB->get_field('cognitivefactory_userdata', 'feedback', array('userid' => $userid, 'cognitivefactoryid' => $cognitivefactory->id));
            $text = $DB->get_field('cognitivefactory_userdata', 'feedbackformat', array('userid' => $userid, 'cognitivefactoryid' => $cognitivefactory->id));
        } elseif ($action == 'editglobalfeedback') {
            $text = $DB->get_field('cognitivefactory', 'globalfeedback', array('id' => $cognitivefactory->id));
            $textformat = FORMAT_HTML;
        }
    }

    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string($action, 'cognitivefactory'));
        
    $field = ($action == 'editreport') ? 'report' : 'feedback';
    $fieldformat = "{$field}format";
    $formdata = new StdClass();
    $formdata->id = $cm->id;
    $formdata->what = $action;
    $formdata->userid = $userid;
    $formdata->$field = $text;
    $formdata->$fieldformat = $textformat;
    $mform->set_data($formdata);
    $mform->display();
    
    echo $OUTPUT->footer();