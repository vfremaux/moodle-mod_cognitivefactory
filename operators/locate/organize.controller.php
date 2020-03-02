<?php

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (!defined('MOODLE_INTERNAL')) die("You cannot use this script this way.");

/********************************** Saves locations ********************************/
if ($action == 'savelocations') {
    // first delete all old location data - the fastest way to do it
    if (!$DB->delete_records('cognitivefactory_opdata', array('cognitivefactoryid' => $cognitivefactory->id, 'userid' => $USER->id, 'operatorid' => 'locate'))) {
        // not a real error. othing to delete ?
    }

    $keys = preg_grep("/^ixquantifier_/", array_keys($_POST));
    $current_operator = new BrainstormOperator($cognitivefactory->id, $page);
    foreach ($keys as $key) {        
        preg_match("/^ixquantifier_(.*)/", $key, $matches);
        $locaterecord = new StdClass;
        $locaterecord->itemsource = $matches[1];
        $locaterecord->cognitivefactoryid = $cognitivefactory->id;
        $locaterecord->operatorid = $page;
        $locaterecord->userid = $USER->id;
        $locaterecord->groupid = $currentgroup;
        $quantifiers = new StdClass;
        switch ($current_operator->configdata->quantifiertype) {
            case 'integer' :
                // will floor numbers
                $quantifiers->x = (int)required_param('ixquantifier_'.$locaterecord->itemsource, PARAM_INT);
                $quantifiers->y = (int)required_param('iyquantifier_'.$locaterecord->itemsource, PARAM_INT);
                // avoid negative numbers
                $quantifiers->x = max(0, $quantifiers->x);
                $quantifiers->y = max(0, $quantifiers->y);
                break;
            case 'float' :
                $quantifiers->x = (double)required_param('ixquantifier_'.$locaterecord->itemsource, PARAM_NUMBER);
                $quantifiers->y = (double)required_param('iyquantifier_'.$locaterecord->itemsource, PARAM_NUMBER); 
                break;
            default :
                $quantifiers->x = (string)required_param('ixquantifier_'.$locaterecord->itemsource, PARAM_TEXT);
                $quantifiers->y = (string)required_param('iyquantifier_'.$locaterecord->itemsource, PARAM_TEXT); 
        }
        $quantifiers->x = min($quantifiers->x, $current_operator->configdata->xmaxrange);
        $quantifiers->x = max($quantifiers->x, $current_operator->configdata->xminrange);
        $quantifiers->y = min($quantifiers->y, $current_operator->configdata->ymaxrange);
        $quantifiers->y = max($quantifiers->y, $current_operator->configdata->yminrange);
        $locaterecord->blobvalue = serialize($quantifiers);
        $locaterecord->timemodified = time();
        if (!$DB->insert_record('cognitivefactory_opdata', $locaterecord)) {
            print_error('errorinsert', 'cognitivefactory', '', get_string('operatordata', 'cognitivefactory'));
        }
    }
}
