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

/**
 * Module mod_cognitivefactory
 * @author Martin Ellermann, Valery Fremaux
 * @package mod-cognitivefactory
 * @date 2007/20/12
 */

/**
 * Requires and includes
 */
include_once($CFG->dirroot.'/mod/cognitivefactory/locallib.php');

define('BRAINSTORM_MAX_RESPONSES', 12);
define('BRAINSTORM_MAX_CATEGORIES', 8);
define('BRAINSTORM_MAX_COLUMNS', 6);

define('PHASE_COLLECT', '0');
define('PHASE_PREPARE', '1');
define('PHASE_ORGANIZE', '2');
define('PHASE_DISPLAY', '3');
define('PHASE_FEEDBACK', '4');

/**
 * This function is not implemented in this plugin, but is needed to mark
 * the vf documentation custom volume availability.
 */
function mod_cognitivefactory_supports_feature($feature) {
    assert(1);
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_GROUPMEMBERSONLY
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function cognitivefactory_supports($feature) {

    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        // case FEATURE_RATE:                    return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will create a new instance and return the id number
 * of the new instance.
 * @param object $cognitivefactory
 */
function cognitivefactory_add_instance($cognitivefactory) {
    global $DB, $COURSE;

    $cognitivefactory->timemodified = time();

    if (empty($cognitivefactory->globalteacherfeedback)) {
        $cognitivefactory->globalteacherfeedback = '';
    }

    $cognitivefactory->collectrequirementformat = FORMAT_MOODLE;
    if (!$cognitivefactory->numcolumns) {
        $cognitivefactory->numcolumns = 2;
    }

    $return = $DB->insert_record('cognitivefactory', $cognitivefactory);

    return $return;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will update an existing instance with new data.
 * @param object $cognitivefactory
 */
function cognitivefactory_update_instance($cognitivefactory) {
    global $DB, $COURSE;

    $cognitivefactory->id = $cognitivefactory->instance;
    $cognitivefactory->timemodified = time();
    if (!$cognitivefactory->numcolumns) $cognitivefactory->numcolumns = 2;

    $context = context_module::instance($cognitivefactory->coursemodule);
    $oldrecord = $DB->get_record('cognitivefactory', array('id' => $cognitivefactory->id));

    $cognitivefactory->collectrequirementformat = FORMAT_MOODLE;

    // Check for some changes that imply some cleaning.
    if ($oldrecord->singlegrade != $cognitivefactory->singlegrade) {
        $participants = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'id,firstname,lastname', 'lastname');
        if ($cognitivefactory->singlegrade) {
            // We are setting up single grades. compile the single grade with dissociated.
            foreach ($participants as $participant) {
                cognitivefactory_convert_to_single($cognitivefactory, $participant->id);
            }
        } else {
            // We are setting dissociated grading for which we MUST delete grades.
           $DB->delete_records('cognitivefactory_grades', array('cognitivefactoryid' => $cognitivefactory->id));
        }
    }

    return $DB->update_record('cognitivefactory', $cognitivefactory);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 * @param int $id
 */
function cognitivefactory_delete_instance($id) {
    global $DB;

    if (! $cognitivefactory = $DB->get_record('cognitivefactory', array('id' => "$id"))) {
        return false;
    }

    $result = true;

    $DB->delete_records('cognitivefactory_operators', array('cognitivefactoryid' => "$cognitivefactory->id"));
    $DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => "$cognitivefactory->id"));
    $DB->delete_records('cognitivefactory_responses', array('cognitivefactoryid' => "$cognitivefactory->id"));
    $DB->delete_records('cognitivefactory_categories', array('cognitivefactoryid' => "$cognitivefactory->id"));
    $DB->delete_records('cognitivefactory_grades', array('cognitivefactoryid' => "$cognitivefactory->id"));

    if (! $DB->delete_records('cognitivefactory', array('id' => "$cognitivefactory->id"))) {
        $result = false;
    }

    return $result;
}

/**
 * gives back an object for student abstract reports
 * @param object $course the current course
 * @param object $user the current user
 * @param object $mod the current course module
 * @param object $cognitivefactory the current cognitivefactory
 */
function cognitivefactory_user_outline($course, $user, $mod, $cognitivefactory) {
    if ($responses = cognitivefactory_get_responses($cognitivefactory->id, $user->id, 0, false)) {
        $responses_values = array_values($responses);
        // Printing last entered response for that user.
        $result->info = '"'.$responses_values[count($responses_values) - 1]->response.'"';
        $result->time = $responses_values[count($responses_values) - 1]->timemodified;
        return $result;
    }
    return null;
}

/**
 * gives back an object for student detailed reports
 * @param object $course the current course
 * @param object $user the current user
 * @param object $mod the current course module
 * @param object $cognitivefactory the current cognitivefactory instance
 */
function cognitivefactory_user_complete($course, $user, $mod, $cognitivefactory) {
    if ($responses = cognitivefactory_get_responses($cognitivefactory->id, $user->id, 0, false)) {
        $responses_values = array_values($responses);
        // Printing last entered response for that user.
        $result->info = '"'.$responses_values[count($responses_values) - 1]->response.'"';
        $result->time = $responses_values[count($responses_values) - 1]->timemodified;
        echo get_string('responded', 'cognitivefactory').": $result->info , last updated ".userdate($result->time);
    } else {
        print_string('notresponded', 'cognitivefactory');
    }
}

/**
 *
 *
 */
function cognitivefactory_cron() {
    // TODO : may cleanup some old group rubish ??
}

/**
 * Returns the users with data in one cognitivefactory
 *(users with records in cognitivefactory_responses, participants)
 * @uses CFG
 * @param int $cognitivefactoryid
 */
function cognitivefactory_get_participants($cognitivefactoryid) {
    global $CFG;

    // Get all participants.
    $sql = "
        SELECT DISTINCT 
            u.*
        FROM 
            {user} u,
            {cognitivefactory_responses} c
        WHERE 
            c.cognitivefactoryid = {$cognitivefactoryid} AND
            u.id = c.userid
    ";
    $participants = $DB->get_records_sql($sql);

    // Return students array (it contains an array of unique users).
    return ($participants);
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user. It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed null or object with an array of grades and with the maximum grade
 **/
function cognitivefactory_grades($cmid) {
    global $CFG;

    if (!$module = $DB->get_record('course_modules', array('id' => $cmid))) {
        return null;
    }

    if (!$cognitivefactory = $DB->get_record('cognitivefactory', array('id' => $module->instance))) {
        return null;
    }

    if ($cognitivefactory->grade == 0) {
        // No grading.
        return null;
    }

    $context = context_module::instance($cmid);

    $participants = get_users_by_capability($context, 'mod/cognitivefactory:gradable', 'u.id,lastname,firstname', 'lastname');
    if ($participants) {
        foreach ($participants as $participant) {
            $gradeset = cognitivefactory_get_gradeset($cognitivefactory->id, $participant->id);
            if (!$gradeset) {
                return null;
            }
            if ($cognitivefactory->grade > 0 ) {
                // Grading numerically
                if ($cognitivefactory->singlegrade) {
                    $finalgrades[$participant->id] = $gradeset->single;
                } else {
                    if ($cognitivefactory->seqaccesscollect && isset($gradeset->participate)) {
                        $total[] = $gradeset->participate;
                        $weights[] = $cognitivefactory->participationweight;
                    }
                    if ($cognitivefactory->seqaccessprepare && isset($gradeset->prepare)) {
                        $total[] = $gradeset->prepare;
                        $weights[] = $cognitivefactory->preparingweight;
                    }
                    if ($cognitivefactory->seqaccessorganize && isset($gradeset->organize)) {
                        $total[] = $gradeset->organize;
                        $weights[] = $cognitivefactory->organizeweight;
                    }
                    if ($cognitivefactory->seqaccessfeedback && isset($gradeset->feedback)) {
                        $total[] = $gradeset->feedback;
                        $weights[] = $cognitivefactory->feedbackweight;
                    }
                    $totalweights = array_sum($weights);
                    $totalgrade = 0;
                    for ($i = 0 ; $i < count(@$total) ; $i++) {
                        $totalgrade += $total[$i] * $weights[$i];
                    }
                    $totalgrade = ($totalweights != 0) ? round($totalgrade / $totalweights) : 0 ;
                    $finalgrades[$participant->id] = $totalgrade;
                }
                $return->grades = @$finalgrades;
                $return->maxgrade = $cognitivefactory->grade;
                return $return;
            } else {
                // Scales.
                $finalgrades = array();
                $scaleid = - ($cognitivefactory->grade);
                $maxgrade = '';
                if ($scale = $DB->get_record('scale', array('id' => $scaleid))) {
                    $scalegrades = make_menu_from_list($scale->scale);
                }
                if ($cognitivefactory->singlegrade) {
                    $finalgrades[$participant->id] = $scalegrades($gradeset->single);
                } else {
                    if ($cognitivefactory->setaccesscollect) {
                        $total[] = $scalegrades($gradeset->participate);
                        $weights[] = $cognitivefactory->participationweight;
                    }
                    if ($cognitivefactory->setaccessprepare) {
                        $total[] = $scalegrades($gradeset->prepare);
                        $weights[] = $cognitivefactory->preparingweight;
                    }
                    if ($cognitivefactory->setaccessorganize) {
                        $total[] = $scalegrades($gradeset->organize);
                        $weights[] = $cognitivefactory->organizeweight;
                    }
                    if ($cognitivefactory->setaccessfeedback) {
                        $total[] = $scalegrades($gradeset->feedback);
                        $weights[] = $cognitivefactory->feedbackweight;
                    }
                    $totalweights = array_sum($weights);
                    $totalgrade = 0;
                    for ($i = 0 ; $i < count(@$total) ; $i++) {
                        $totalgrade += $total[$i] * $weights[$i];
                    }
                    $totalgrade = ($totalweights != 0) ? round($totalgrade / $totalweights) : 0;
                    $finalgrades[$participant->id] = $totalgrade;
                }
                $return->grades = @$final;
                $return->maxgrade = $maxgrade;
                return $return;
            }
        }
    }
    return null;
}

/**
 * This function returns if a scale is being used by one newmodule
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function cognitivefactory_scale_used($cmid, $scaleid) {
    global $DB;

    $return = false;

    // Note : scales are assigned using negative index in the grade field of cognitivefactoryer (see mod/assignement/lib.php).
    $rec = $DB->get_record('cognitivefactory', array('id' => $cmid, 'grade' => -$scaleid));

    if (!empty($rec) && !empty($scaleid)) {
        $return = true;
    }
    return $return;
}

/**
 * This function allows the tool_dbcleaner to register integrity checks
 */
function cognitivefactory_dbcleaner_add_keys() {
    global $DB;

    $cognitivemoduleid = $DB->get_field('modules', 'id', array('name' => 'cognitivefactory'));

    $keys = array(
        array('cognitivefactory', 'course', 'course', 'id', ''),
        array('cognitivefactory', 'id', 'course_modules', 'instance', ' module = '.$cognitivemoduleid.' '),
        array('cognitivefactory_categories', 'cognitivefactoryid', 'cognitivefactory', 'id', ''),
        array('cognitivefactory_operators', 'cognitivefactoryid', 'cognitivefactory', 'id', ''),
        array('cognitivefactory_opdata', 'cognitivefactoryid', 'cognitivefactory', 'id', ''),
        array('cognitivefactory_responses', 'cognitivefactoryid', 'cognitivefactory', 'id', ''),
        array('cognitivefactory_responses', 'userid', 'user', 'id', ''),
        array('cognitivefactory_grades', 'cognitivefactoryid', 'cognitivefactory', 'id', ''),
        array('cognitivefactory_grades', 'userid', 'user', 'id', ''),
        array('cognitivefactory_userdata', 'cognitivefactoryid', 'cognitivefactory', 'id', ''),
        array('cognitivefactory_userdata', 'userid', 'user', 'id', ''),
    );

    return $keys;
}