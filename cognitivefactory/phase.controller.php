<?php

/**
* @package cognitivefactory
* @author Valery Fremaux / 1.8
* @date 05/01/2008
*
* A special controller for switching phases in sequential mode
*/
if ($action == 'switchphase'){
    $cognitivefactory->phase = required_param('phase', PARAM_INT);
    update_record('cognitivefactory', $cognitivefactory);
}
