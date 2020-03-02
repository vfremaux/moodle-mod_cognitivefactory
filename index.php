<?php

require('../../config.php');
require_once($CFG->dirroot.'/mod/cognitivefactory/lib.php');

$id = required_param('id', PARAM_INT);           // Course Module ID

if (! $course = $DB->get_record('course', array('id' => $id))) {
    print_error('coursemisconf');
}

require_login($course->id);

$eventdata = array('context' => context_course::instance($id));
$event = \mod_cognitivefactory\event\course_module_instance_list_viewed::create($eventdata);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strcognitivefactory = get_string('modulename', 'cognitivefactory');
$strcognitivefactorys = get_string('modulenameplural', 'cognitivefactory');

$url = new moodle_url('/mod/cognitivefactory/index.php', array('id' => $id));

$PAGE->set_url($url);
$PAGE->set_title($course->shortname.': '.format_string($strcognitivefactorys));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_button('');
$PAGE->set_headingmenu(navmenu($course));

echo $OUTPUT->header();

if (! $cognitivefactorys = get_all_instances_in_course('cognitivefactory', $course)) {
    echo $OUTPUT->notification("There are no cognitivefactorys", "../../course/view.php?id={$course->id}");
}

if ( $allresponses = $DB->get_records('cognitivefactory_responses', array('userid' => $USER->id))) {
    foreach ($allresponses as $aa) {
        $responses[$aa->cognitivefactoryid] = $aa;
    }
} else {
    $responses = array () ;
}


$timenow = time();

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array (get_string('week'), get_string('question'), get_string('answer'));
    $table->align = array ('CENTER', 'LEFT', 'LEFT');
} 
else if ($course->format == 'topics') {
    $table->head  = array (get_string('topic'), get_string('question'), get_string('answer'));
    $table->align = array ('CENTER', 'LEFT', 'LEFT');
} else {
    $table->head  = array (get_string('question'), get_string('answer'));
    $table->align = array ('LEFT', 'LEFT');
}

$currentsection = "";

foreach ($cognitivefactorys as $cognitivefactory) {
    if (!empty($responses[$cognitivefactory->id])) {
        $answer = $responses[$cognitivefactory->id];
    } else {
        $answer = '';
    }
    if (!empty($answer->answer)) {
        $aa = cognitivefactory_get_answer($cognitivefactory, $answer->answer);
    } else {
        $aa = '';
    }
    $printsection = '';
    if ($cognitivefactory->section !== $currentsection) {
        if ($cognitivefactory->section) {
            $printsection = $cognitivefactory->section;
        }
        if ($currentsection !== '') {
            $table->data[] = 'hr';
        }
        $currentsection = $cognitivefactory->section;
    }
    //Calculate the href
    if (!$cognitivefactory->visible) {
        //Show dimmed if the mod is hidden
        $tt_href = "<a class=\"dimmed\" href=\"view.php?id={$cognitivefactory->coursemodule}\">".format_string($cognitivefactory->name).'</a>';
    } else {
        //Show normal if the mod is visible
        $tt_href = "<a href=\"view.php?id={$cognitivefactory->coursemodule}\">".format_string($cognitivefactory->name).'</a>';
    }
    if ($course->format == 'weeks' || $course->format == 'topics') {
        $table->data[] = array ($printsection, $tt_href, $aa);
    } else {
        $table->data[] = array ($tt_href, $aa);
    }
}
echo '<br />';
echo html_writer::table($table);
echo $OUTPUT->footer($course); 
?>

