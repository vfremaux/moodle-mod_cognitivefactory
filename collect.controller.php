<?php

if (!defined('MOODLE_INTERNAL')) {
    die('You cannot use this script directly');
}

/********************************  Asked for collecting form *******************************/
if ($action == 'docollect') {
    $form = new StdClass();
    $form->response = required_param_array('response', PARAM_TEXT);
    if (empty($form->response)) {
        $error = new StdClass();
        $error->message = get_string('emptyresponse', 'cognitivefactory');
        $error->on = 'response';
        $errors[] = $error;
    } else { // we have data from the form
        $newanswer = new StdClass();
        $newanswer->cognitivefactoryid = $cognitivefactory->id;
        $newanswer->userid = $USER->id;
        $newanswer->groupid = $currentgroup;
        $newanswer->timemodified = time();

        // responses now an array
        foreach ($form->response as $response) {
            if ($response == '') { // ignore unfilled response fields
                continue;
            }
            $newanswer->response = $response;
            if (! $DB->insert_record('cognitivefactory_responses', $newanswer)) {
                print_error('errorinsertresponse', 'cognitivefactory');
            }
        }
        // add_to_log($course->id, 'cognitivefactory', 'submit', "view.php?id={$cm->id}", $cognitivefactory->id, $cm->id);
    }
}
/********************************  Asked for collecting form *******************************/
elseif ($action == 'collect') {
    /// Allow users to enter their responses
    if (isguestuser()) {
        echo $OUTPUT->notification(get_string('guestscannotparticipate' , 'center'));    
        return -1;
    }
    include 'collect.html';
    return -1;
}
/********************************  Clear all ideas *******************************/
elseif ($action == 'clearall') {
    $allusersclear = optional_param('allusersclear', 0, PARAM_INT);
    if ($allusersclear) {
        $DB->delete_records('cognitivefactory_responses', array('cognitivefactoryid' => $cognitivefactory->id));
        $DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id));
    } else {
        $DB->delete_records('cognitivefactory_responses', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));
        $DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));
    }
}
/********************************  Clear all ideas *******************************/
elseif ($action == 'deleteitems') {
    $items = optional_param_array('items', array(), PARAM_INT);
    if (is_array($items)) {
        $idlist = implode("','", $items);
        $DB->delete_records_select('cognitivefactory_responses', " id IN ('$idlist') ");
        $select = "
            cognitivefactoryid = $cognitivefactory->id AND
            (itemsource IN ('$idlist') OR
            itemdest IN ('$idlist'))
        ";
        $DB->delete_records_select('cognitivefactory_opdata', $select);
    }
}
/********************************  perform import *******************************/
elseif ($action == 'doimport') {
    $clearalldata = optional_param('clearall', 0, PARAM_INT);
    $allusersclear = optional_param('allusersclear', 0, PARAM_INT);

    // Todo get ideas from storedfile
    $ideas = array();

    if ($clearalldata) {
        if ($allusersclear) {
            $DB->delete_records('cognitivefactory_responses', array('cognitivefactoryid' => $cognitivefactory->id));
            $DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id));
        } else {
            $DB->delete_records('cognitivefactory_responses', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));
            $DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id));
        }
    }
    $response = new StdClass();
    $response->cognitivefactoryid = $cognitivefactory->id;
    $response->userid = $USER->id;
    $response->groupid = $currentgroup;
    $response->timemodified = time();
    foreach ($ideas as $idea) {
        $response->response = mb_convert_encoding($idea, 'UTF-8', 'auto');
        if (! $DB->insert_record('cognitivefactory_responses', $response)) {
            print_error('errorinsertimport' , 'cognitivefactory');
        }
    }
}
