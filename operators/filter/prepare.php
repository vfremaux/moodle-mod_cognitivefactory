<?PHP  // $Id: prepare.php,v 1.2 2011-05-15 11:17:33 vf Exp $

/**
* Module Brainstorm V2
* Operator : filter
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
require_once("$CFG->dirroot/mod/cognitivefactory/operators/filter/prepare_form.php");

if (!defined('MOODLE_INTERNAL')) die("This script canot be used this way.");

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);
$usehtmleditor = can_use_html_editor();

if (!isset($currentoperator->configdata->maxideasleft)){
    $currentoperator->configdata->maxideasleft = count($responses);
}
if (!isset($currentoperator->configdata->requirement)){
    $currentoperator->configdata->requirement = '';
}
if (!isset($currentoperator->configdata->allowreducesource)){
    $currentoperator->configdata->allowreducesource = 0;
}
if (!isset($currentoperator->configdata->candeletemore)){
    $currentoperator->configdata->candeletemore = 0;
}

echo $OUTPUT->heading(get_string("{$page}settings", 'cognitiveoperator_'.$page));

$form = new filter_prepare_form($url, array('oprequirementtype' => $cognitivefactory->oprequirementtype, 'cmid' => $cm->id, 'maxresponses' => count($responses)));

if ($data = $form->get_data()){
	// Play the add/update controller here
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $data);
}

$currentoperator->configdata->id = $cm->id;
$currentoperator->configdata->operator = $page;
$form->set_data($currentoperator->configdata);
echo "<img src=\"".$OUTPUT->pix_url('enabled', 'cognitiveoperator_'.$page).'" align="left" />';
$form->display();
