<?php

if (!defined('MOODLE_INTERNAL')) die('You cannot use this script directly');

/********************************** Enable an operator ********************************/
if ($action == 'enable'){
    $operatorid = required_param('operatorid', PARAM_ALPHA);
    $oprecord = $DB->get_record('cognitivefactory_operators', array('cognitivefactoryid' => $cognitivefactory->id, 'operatorid' => $operatorid));
    if ($oprecord){
        $oprecord->active = 1;
        if (!$DB->update_record('cognitivefactory_operators', $oprecord)){
            print_error('errorupdate', 'cognitivefactory', '', get_string('operatorconfig', 'cognitivefactory'));
        }        
    } else {
		$oprecord = new Stdclass();
        $oprecord->cognitivefactoryid = $cognitivefactory->id;
        $oprecord->operatorid = $operatorid;
        $oprecord->active = 1;
        $oprecord->configdata = serialize(new Object());
        if (!$DB->insert_record('cognitivefactory_operators', $oprecord)){
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatorconfig', 'cognitivefactory'));
        }
    }
}
/********************************** Disable an operator ********************************/
if ($action == 'disable'){
    $operatorid = required_param('operatorid', PARAM_ALPHA);
    $oprecordid = $DB->get_field('cognitivefactory_operators', 'id', array('cognitivefactoryid' => $cognitivefactory->id, 'operatorid' => $operatorid));
    $oprecord = new StdClass();
    $oprecord->id = $oprecordid;
    $oprecord->active = 0;
    if (!$DB->update_record('cognitivefactory_operators', $oprecord)){
        print_error('errorupdate', 'cognitivefactory', '', get_string('operatorconfig', 'cognitivefactory'));
    }
}
