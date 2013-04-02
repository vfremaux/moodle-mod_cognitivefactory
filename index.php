<?PHP  // $Id: index.php,v 1.2 2004/08/24 16:36:18 diml Exp $

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);           // Course Module ID

if (! $course = get_record('course', 'id', $id)) {
    error("Course ID is incorrect");
}

require_login($course->id);

add_to_log($course->id, 'cognitivefactory', 'view all', "index?id=$course->id", "");

if ($course->category) {
    $navigation = "<a href=\"../../course/view.php?id={$course->id}\">{$course->shortname}</a> ->";
} 
else {
    $navigation = "";
}

$strcognitivefactory = get_string('modulename', 'cognitivefactory');
$strcognitivefactorys = get_string('modulenameplural', 'cognitivefactory');

print_header($course->shortname.': '.format_string($strcognitivefactorys), format_string($course->fullname),
             "$navigation $strcognitivefactorys", '', '', true, '', navmenu($course));


if (! $cognitivefactorys = get_all_instances_in_course('cognitivefactory', $course)) {
    notice("There are no cognitivefactorys", "../../course/view.php?id={$course->id}");
}

if ( $allresponses = get_records('cognitivefactory_responses', 'userid', $USER->id)) {
    foreach ($allresponses as $aa) {
        $responses[$aa->cognitivefactoryid] = $aa;
    }
} 
else {
    $responses = array () ;
}


$timenow = time();

if ($course->format == 'weeks') {
    $table->head  = array (get_string('week'), get_string('question'), get_string('answer'));
    $table->align = array ('CENTER', 'LEFT', 'LEFT');
} 
else if ($course->format == 'topics') {
    $table->head  = array (get_string('topic'), get_string('question'), get_string('answer'));
    $table->align = array ('CENTER', 'LEFT', 'LEFT');
} 
else {
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
    } 
    else {
        //Show normal if the mod is visible
        $tt_href = "<a href=\"view.php?id={$cognitivefactory->coursemodule}\">".format_string($cognitivefactory->name).'</a>';
    }
    if ($course->format == 'weeks' || $course->format == 'topics') {
        $table->data[] = array ($printsection, $tt_href, $aa);
    } 
    else {
        $table->data[] = array ($tt_href, $aa);
    }
}
echo '<br />';
print_table($table);
print_footer($course); 
?>

