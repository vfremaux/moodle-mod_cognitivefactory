<?php

/**
* Controller for feedback
*
* @usecase editreport
* @usecase doreport
*/

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

/********************************  Asked for report form *******************************/
if ($action == 'editreport') {
    $form = new ReportForm();

    $formdata->cmid = $cm->id;
    $formdata->report = $DB->get_field('cognitivefactory_userdata', 'report', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));
    $formdata->reportformat = $DB->get_field('cognitivefactory_userdata', 'reportformat', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));

    $form->set_data($formdata);
    $form->display();

    return -1;
}
/********************************  Stores learner's report *******************************/
if ($action == 'doreport') {
    $report = required_param('report', PARAM_CLEANHTML);
    $reportformat = required_param('reportformat', PARAM_INT);
    $oldrecord = $DB->get_record('cognitivefactory_userdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));
    if ($oldrecord) {
        $oldrecord->report = $report;
        $oldrecord->reportformat = $reportformat;
        $oldrecord->timeupdated = time();
        if (!$DB->update_record('cognitivefactory_userdata', $oldrecord)) {
            print_error('errorupdatereport', 'cognitivefactory', '', get_string('report', 'cognitivefactory'));
        }
    } else {
        $newrecord->cognitivefactoryid = $cognitivefactory->id;
        $newrecord->userid = $USER->id;
        $newrecord->report = $report;
        $newrecord->reportformat = $reportformat;
        $newrecord->feedback = '';
        $newrecord->feedbackformat = 0;
        $newrecord->timeupdated = time();
        if (!$DB->insert_record('cognitivefactory_userdata', $newrecord)) {
            print_error('errorinsertreport', 'cognitivefactory', '', get_string('report', 'cognitivefactory'));
        }
        add_to_log($course->id, 'cognitivefactory', 'createreport', "/mod/cognitivefactory/view.php?id={$cm->id}", $cognitivefactory->id, $cm->id);
    }
}
