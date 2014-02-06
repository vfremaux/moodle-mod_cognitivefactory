<?php

/**
* @package cognitivefactory
* @author Valery Fremaux / 1.8
* @date 05/01/2008
*
* A special controller for switching phases in sequential mode
*/
if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

if ($action == 'switchphase'){
    $cognitivefactory->phase = required_param('phase', PARAM_INT);
    $DB->update_record('cognitivefactory', $cognitivefactory);
}
