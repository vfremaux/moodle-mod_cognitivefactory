<?php

/**
* @package cognitivefactory
* @author Valery Fremaux / 1.8
* @date 22/12/2007
*
* This page shows view for importing inputs. 
* Inputs can be imported uploading a text file with one idea per line
* empty lines are ignored, so are lines starting with !, / or #
*/

    require_once("../../config.php");

    require_once $CFG->dirroot.'/mod/cognitivefactory/forms/import_form.php';
    require_once($CFG->dirroot."/mod/cognitivefactory/lib.php");
    require_once($CFG->dirroot."/mod/cognitivefactory/locallib.php");

    $id = required_param('id', PARAM_INT);           // Course Module ID
    $action = optional_param('what', '', PARAM_TEXT); 
    $view = optional_param('view', '', PARAM_TEXT); 
    $page = optional_param('page', '', PARAM_TEXT); 

    $url = new moodle_url($CFG->wwwroot.'/mod/cognitivefactory/view.php', array('id' => $id, 'view' => $view, 'page' => $page, 'what' => $action));
    $PAGE->set_url($url);

    if (! $cm = get_coursemodule_from_id('cognitivefactory', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (!$cognitivefactory = $DB->get_record('cognitivefactory', array('id' => $cm->instance))) {
        print_error('invalidcoursemodule');
    }

    $cognitivefactory->cmid = $cm->id;

/// security 

    $context = context_module::instance($cm->id);

    require_course_login($course->id, false, $cm);
    require_capability('mod/cognitivefactory:import', $context);
    
/// Forms and controllers

    $form = new import_form();

    if ($form->is_cancelled()) {
        redirect($CFG->wwwroot.'/mod/cognitivefactory/view.php?id='.$id.'&page=collect');
    }
    if ($data = $form->get_data()) {
        // TODO : process the file 
        $fs = get_file_storage();

        $draftitemid = $data->inputs;
        $usercontext = context_user::instance($USER->id);
        if (!$fs->is_area_empty($usercontext->id, 'user', 'draft', $draftitemid)) {
            $submittedfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid);
            $submittedfile = array_pop($submittedfiles);
            $content = $submittedfile->get_content();
            $lines = explode("\n", $content);
            
            $groupid = groups_get_activity_group($cm);
            
            if (!empty($data->clearalldata)) {
                $DB->delete_records('cognitivefactory_responses', array('cognitivefactoryid' => $cognitivefactory->id));
                $DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id));
                $DB->delete_records('cognitivefactory_grades', array('cognitivefactoryid' => $cognitivefactory->id));
                $DB->delete_records('cognitivefactory_userdata', array('cognitivefactoryid' => $cognitivefactory->id));
            }
            
            foreach ($lines as $l) {
                if (empty($l)) continue;
                if (preg_match('/^!#\(/', $l)) continue; // throw some comments out

                $entry = new StdClass();
                $entry->cognitivefactoryid = $cognitivefactory->id;
                $entry->response = $l;
                $entry->userid = $USER->id;
                $entry->groupid = $groupid;
                $entry->timemodified = time();
                $DB->insert_record('cognitivefactory_responses', $entry);
            }
            
        }
        
        redirect($CFG->wwwroot.'/mod/cognitivefactory/view.php?id='.$id.'&page=collect');
    }

/// Prepare header

    $strcognitivefactory = get_string('modulename', 'cognitivefactory');

    $url = new moodle_url($CFG->wwwroot.'/mod/cognitivefactory/import.php', array('id' => $cm->id));

    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_title($course->shortname.': '.format_string($cognitivefactory->name));
    $PAGE->set_heading(format_string($course->fullname));
    $PAGE->navbar->add($strcognitivefactory);
    $PAGE->navbar->add(get_string('importideas', 'cognitivefactory'));
    $PAGE->set_button($OUTPUT->update_module_button($cm->id, 'cognitivefactory'));

/// Print the page header

    echo $OUTPUT->header();

    echo $OUTPUT->heading_with_help(get_string('importideas', 'cognitivefactory'), 'importformat', 'cognitivefactory');
    
    echo $OUTPUT->box_start();

    $data = new StdClass();
    $data->id = $cm->id;
    $form->set_data($data);
    $form->display();

    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();
