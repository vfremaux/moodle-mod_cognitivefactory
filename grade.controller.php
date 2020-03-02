<?php

/**
* @package cognitivefactory
* @author Valery Fremaux / 1.8
* @date 05/01/2008
*
* A special controller for switching phases in sequential mode
*/

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');


/**************************************** delete an assessment ***********************************/
if ($action == 'deletegrade') {
    $userid = required_param('for', PARAM_INT);
    $DB->delete_records('cognitivefactory_grades', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $userid));
}
