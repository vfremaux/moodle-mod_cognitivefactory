<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require('../../config.php');
require_once($CFG->dirroot.'/mod/cognitivefactory/lib.php');
require_once($CFG->dirroot.'/mod/cognitivefactory/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$action = optional_param('what', '', PARAM_TEXT);
$view = optional_param('view', '', PARAM_TEXT);
$page = optional_param('page', '', PARAM_TEXT);

$url = new moodle_url('/mod/cognitivefactory/view.php', array('id' => $id, 'view' => $view, 'page' => $page));
$PAGE->set_url($url);

cognitivefactory_check_jquery();

if (!$cm = get_coursemodule_from_id('cognitivefactory', $id)) {
    print_error('invalidcoursemodule');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('coursemisconf');
}
if (!$cognitivefactory = $DB->get_record('cognitivefactory', array('id' => $cm->instance))) {
    print_error('invalidcoursemodule');
}

$context = context_module::instance($cm->id);

$cognitivefactory->cmid = $cm->id;

require_login($course->id, false, $cm);

$strcognitivefactory = get_string('modulename', 'cognitivefactory');
$strcognitivefactorys = get_string('modulenameplural', 'cognitivefactory');

// Get the master MVC control parameters.

/// Display the choice and possibly results
$eventdata = array();
$eventdata['objectid'] = $cognitivefactory->id;
$eventdata['context'] = $context;

$event = \mod_cognitivefactory\event\course_module_viewed::create($eventdata);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->trigger();

// memorizes current view - typical session switch
if (!empty($view)) {
    $_SESSION['currentview'] = $view;
} 
elseif (empty($_SESSION['currentview'])) {
    $_SESSION['currentview'] = 'collect';
}

$view = $_SESSION['currentview'];

// Memorizes current page - typical session switch.
if (!empty($page)) {
    $_SESSION['currentpage'] = $page;
} elseif (empty($_SESSION['currentpage'])) {
    $_SESSION['currentpage'] = '';
}
$page = $_SESSION['currentpage'];

// !PART OF MVC Implementation

// be sure we know the page !
cognitivefactory_requires($cognitivefactory, $page);

// Get capabilities.
$isstudent = has_capability('mod/cognitivefactory:gradable', $context, $USER->id, false);
$ismanager = has_capability('mod/cognitivefactory:manage', $context);
$cangrade = has_capability('mod/cognitivefactory:grade', $context, $USER->id, false);

/// Print the page header

$PAGE->set_context($context);
$PAGE->set_title($course->shortname.': '.format_string($cognitivefactory->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);
$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'cognitivefactory'));

$out = $OUTPUT->header();

/// Check to see if groups are being used in this cognitivefactory

if ($groupmode = groups_get_activity_groupmode($cm)) {   // Groups are being used
    $currentgroup = groups_get_activity_group($cm, true);
    $out .= groups_print_activity_menu($cm, $url, '', true);
} else {
    $currentgroup = 0;
}

if ($ismanager) {
    if ($action != '') {
        include("{$CFG->dirroot}/mod/cognitivefactory/phase.controller.php");
    }
}

$out .= '<br/>';

if (!empty($cognitivefactory->description)) {
    $out .= $OUTPUT->box(format_text($cognitivefactory->description));
}

$out .= '<table width="100%"><tr><td>';

$collectstr = get_string('collect', 'cognitivefactory');
$preparestr = get_string('prepare', 'cognitivefactory');
$organizestr = get_string('organize', 'cognitivefactory');
$displaystr = get_string('display', 'cognitivefactory');
$feedbackstr = get_string('feedback', 'cognitivefactory');
$gradestr = get_string('grading', 'cognitivefactory');

/// make flow control activators

if ($cognitivefactory->flowmode == 'sequential') { // make flow control table
    $out .= cognitivefactory_print_phase_buttons($cognitivefactory);
}

/// main menu

if ($cognitivefactory->flowmode == 'parallel' || $ismanager) { // make a first row of tabs
    if (!$isstudent || $cognitivefactory->seqaccesscollect) {
        $rows[0][] = new tabobject('collect', "view.php?id={$cm->id}&amp;view=collect", $collectstr);
    } else {
        if ($view == 'collect') $view = 'prepare';
    }
    if (!$isstudent || $cognitivefactory->seqaccessprepare) {
        $rows[0][] = new tabobject('prepare', "view.php?id={$cm->id}&amp;view=prepare", $preparestr);
    } else {
        if ($view == 'prepare') $view = 'organize';
    } 
    if (!$isstudent || $cognitivefactory->seqaccessorganize) {
        $rows[0][] = new tabobject('organize', "view.php?id={$cm->id}&amp;view=organize", $organizestr);
    } else {
        if ($view == 'organize') $view = 'display';
    }
    if (!$isstudent || $cognitivefactory->seqaccessdisplay) {
        $rows[0][] = new tabobject('display', "view.php?id={$cm->id}&amp;view=display", $displaystr);
    } else {
        if ($view == 'display') $view = 'feedback';
    }
    if (!$isstudent || $cognitivefactory->seqaccessfeedback) {
        $rows[0][] = new tabobject('feedback', "view.php?id={$cm->id}&amp;view=feedback", $feedbackstr);
    }
    if ($cangrade) {
        $rows[0][] = new tabobject('grade', "view.php?id={$cm->id}&amp;view=grade", $gradestr);
    }
} else {
    // student view in sequential mode : force view
    $out .= $OUTPUT->heading(get_string($PHASES[$cognitivefactory->phase], 'cognitivefactory'));
    $view = $PHASES[$cognitivefactory->phase];
}
/// submenus

switch ($view) {
    case 'collect':
        $page = '';
        break;

    case 'prepare' :
        // This is a special location for controller as tabs are dynamically build as resulting of selection/unselection of operators
        $result = 0;
        if ($action != '') {
            $result = include 'prepare.controller.php';
        }
        $operators = cognitivefactory_get_operators($cognitivefactory->id);
        $operatorlist = cognitivefactory_get_operatorlist($operators, '|');
        if (empty($operatorlist)) {
            $page = 'select';
        } else if (!preg_match("/select|$operatorlist/", $page)) {
            $page = 'select';
        }
        /// prepare submenu
        if (!$isstudent || $cognitivefactory->seqaccessprepare)
            $rows[1][] = new tabobject('select', "view.php?id={$cm->id}&amp;view=prepare&amp;page=select", get_string('select', 'cognitivefactory'));
        foreach ($operators as $operator) {
            if (!$operator->active) continue;
            $rows[1][] = new tabobject($operator->id, "view.php?id={$cm->id}&amp;view=prepare&amp;page={$operator->id}", get_string($operator->name, 'cognitiveoperator_'.$operator->name));
        }
        break;

    case 'organize':
        $operators = cognitivefactory_get_operators($cognitivefactory->id);
        $operatorlist = cognitivefactory_get_operatorlist($operators, '|');
        if (empty($operatorlist)) {
            $page = 'summary';
        } else if (!preg_match("/select|$operatorlist/", $page)) {
            if (strstr('|', $operatorlist) !== false || $page == 'summary') {
                $page = 'summary';
            } else {
                // force using the only operator page if only one selected
                // this is nice for presenting a simplified "one simple process" interface.
                $page = $operatorlist;
            }
        }
        $rows[1][] = new tabobject('summary', "view.php?id={$cm->id}&amp;view=organize&amp;page=summary", get_string('summary', 'cognitivefactory'));
        foreach ($operators as $operator) {
            if (!$operator->active) continue;
            $rows[1][] = new tabobject($operator->id, "view.php?id={$cm->id}&amp;view=organize&amp;page={$operator->id}", get_string($operator->name, 'cognitiveoperator_'.$operator->name));
        }
        break;

    case 'display':
        $operators = cognitivefactory_get_operators($cognitivefactory->id);
        $operatorlist = cognitivefactory_get_operatorlist($operators, '|');
        if (empty($operatorlist)) {
            $page = 'summary';
        } else if (!preg_match("/summary|$operatorlist/", $page)) {
            $page = 'summary';
        }
        $rows[1][] = new tabobject('summary', "view.php?id={$cm->id}&amp;view=display&amp;page=summary", get_string('summary', 'cognitivefactory'));
        foreach ($operators as $operator) {
            if (!$operator->active) continue;
            $rows[1][] = new tabobject($operator->id, "view.php?id={$cm->id}&amp;view=display&amp;page={$operator->id}", get_string($operator->name, 'cognitiveoperator_'.$operator->name));
        }
        break;

    case 'feedback':
        if (!preg_match("/report|feedback/", $page)) {
            $page = 'report';
        }
        if ($cognitivefactory->seqaccessfeedback) {
            $rows[1][] = new tabobject('report', "view.php?id={$cm->id}&amp;view=feedback&amp;page=report", get_string('report', 'cognitivefactory'));
        }
        $rows[1][] = new tabobject('feedback', "view.php?id={$cm->id}&amp;view=feedback&amp;page=feedback", get_string('seefeedback', 'cognitivefactory'));
        break;

    case 'grade':
        $page = '';
        break;

    default:
}

$selected = null;
$activated = null;
if (!empty($page)) {
    $selected = $page;
    $activated = array($view, $page);
} else {
    $selected = $view;
}

// If sequential, bring back second row to first row before printing tags.

if (isset($rows)) {
    if (($cognitivefactory->flowmode == 'sequential') && !$ismanager) {
        $rows[0] = $rows[1];
        unset($rows[1]);
        $activated = array($page);
    }
    $out .= print_tabs($rows, $selected, '', $activated, true);
}

// Right column.
$out .= '</td></tr><tr><td>';

// Routing to active views.
// echo "routing $view : $page : $action "; // for debug only
if ($view == 'collect') {
    $out .= " <br/><br/>";
    echo $out; // start sending the page

    if (!$isstudent || $cognitivefactory->seqaccesscollect) {
        $result = 0;
        if ($action != '') {
            $result = include 'collect.controller.php';
        }
        if ($result != -1) {
            include 'collect.php';
        }
    } else {
        $view = 'unallowedphase';
    }
}

if ($view == 'prepare') {
    $out .= " <br/><br/>";
    echo $out; // start sending the page

    if (!$isstudent || $cognitivefactory->seqaccessprepare) {
        // here we call the local operator controller at a standard location if needed
        $operator = optional_param('operator', '', PARAM_ALPHA);
        $result = 0;
        if ($operator && $action) {
            $result = include $CFG->dirroot."/mod/cognitivefactory/operators/{$operator}/prepare.controller.php";
        }
        if ($result != -1) {
            switch($page) {
                case 'select':
                    include 'select.php';
                    break;
                default: 
                    if (file_exists($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/prepare.php")) {
                        include $CFG->dirroot."/mod/cognitivefactory/operators/{$page}/prepare.php";
                    }
                    break;
            }
        }
    } else {
        $view = 'unallowedphase';
    }
}
if ($view == 'organize') {
    $out .= " <br/><br/>";
    echo $out; // start sending the page

    if (!$isstudent || $cognitivefactory->seqaccessorganize) {
        // here we call the local operator controller at a standard location if needed
        $operator = optional_param('operator', '', PARAM_ALPHA);
        $result = 0;
        if ($operator && $action) {
            $result = include($CFG->dirroot.'/mod/cognitivefactory/operators/'.$operator.'/organize.controller.php');
        }
        if ($result != -1) {
            switch($page) {
                case 'summary':
                    include($CFG->dirroot.'/mod/cognitivefactory/summary.php');
                    break;

                default:
                    if (file_exists($CFG->dirroot.'/mod/cognitivefactory/operators/'.$page.'/organize.php')) {
                        include($CFG->dirroot.'/mod/cognitivefactory/operators/'.$page.'/organize.php');
                    }
                    break;
            }
        }
    } else {
        $view = 'unallowedphase';
    }
}

if ($view == 'display') {
    echo $out; // start sending the page

    if (!$isstudent || $cognitivefactory->seqaccessdisplay) {
        $operator = optional_param('operator', '', PARAM_ALPHA);
        $result = 0;
        if ($operator && $action) {
            $result = include $CFG->dirroot."/mod/cognitivefactory/operators/{$operator}/display.controller.php";
        }
        if ($result != -1) {
            switch($page) {
                case 'summary':
                    include($CFG->dirroot.'/mod/cognitivefactory/displaysummary.php');
                    break;
                default: 
                    if (file_exists($CFG->dirroot.'/mod/cognitivefactory/operators/'.$page.'/display.php')) {
                        include($CFG->dirroot.'/mod/cognitivefactory/operators/'.$page.'/display.php');
                    }
                    break;
            }
        }
    } else {
        $view = 'unallowedphase';
    }
}
if ($view == 'feedback') {
    echo $out; // start sending the page

    switch($page) {
        case 'report':
            if ($cognitivefactory->seqaccessfeedback) {
                include($CFG->dirroot.'/mod/cognitivefactory/report.php');
                break;
            }
        case 'feedback':
            include 'feedback.php';
            break;
        default:;
    }
}
if ($view == 'grade') {
    $result = 0;
    if ($action != '') {
        $result = include($CFG->dirroot.'/mod/cognitivefactory/grade.controller.php');
    }
    if ($result != -1) {
        include($CFG->dirroot.'/mod/cognitivefactory/grade.php');
    }
}
if ($view == 'unallowedphase') {
    $lang = current_language();
    include "{$CFG->dirroot}/mod/cognitivefactory/lang/{$lang}/unallowedphase.html";
}
if ($view == 'notmember') {
    $lang = current_language();
    include "{$CFG->dirroot}/mod/cognitivefactory/lang/{$lang}/notmember.html";
}

echo '</td></tr></table>';

/// Finish the page

if ($course->format == 'page') {
    include_once($CFG->dirroot.'/course/format/page/xlib.php');
    page_print_page_format_navigation($cm, $backtocourse = false);
} else {
    if ($COURSE->format != 'singleactivity') {
        echo '<div style="text-align:center;margin:8px">';
        echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $course->id)), get_string('backtocourse', 'cognitivefactory'), 'post', array('class' => 'backtocourse'));
        echo '</div>';
    }
}

echo $OUTPUT->footer($course);
