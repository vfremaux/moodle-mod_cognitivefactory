<?PHP  // $Id: prepare.php,v 1.2 2011-05-15 11:18:08 vf Exp $

/**
* Module Brainstorm V2
* Operator : scale
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
require_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
require_once("$CFG->dirroot/mod/cognitivefactory/operators/scale/prepare_form.php");

if (!defined('MOODLE_INTERNAL')) die("This script canot be used this way.");

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);

if (!isset($currentoperator->configdata->absolute)) {
    $currentoperator->configdata->absolute = 1;
}
if (!isset($currentoperator->configdata->quantifiertype)) {
    $currentoperator->configdata->quantifiertype = 'float';
}
if (!isset($currentoperator->configdata->scale)) {
    $currentoperator->configdata->scale = 0;
}
if (!isset($currentoperator->configdata->blindness)) {
    $currentoperator->configdata->blindness = $cognitivefactory->privacy;
}
if (!isset($currentoperator->configdata->barwidth)) {
    $currentoperator->configdata->barwidth = 400;
}
if (!isset($currentoperator->configdata->requirement)) {
    $currentoperator->configdata->requirement = '';
}
if (!isset($currentoperator->configdata->blindness)) {
    $currentoperator->configdata->blindness = 0;
}

$noselected = (!$currentoperator->configdata->absolute) ? 'checked="checked"' : '' ;
$yesselected = ($currentoperator->configdata->absolute) ? 'checked="checked"' : '' ;
$integerselected = ($currentoperator->configdata->quantifiertype == 'integer') ? 'checked="checked"' : '' ;
$floatselected = ($currentoperator->configdata->quantifiertype == 'float') ? 'checked="checked"' : '' ;
$scaleselected = ($currentoperator->configdata->quantifiertype == 'moodlescale') ? 'checked="checked"' : '' ;
$noselected1 = (!$currentoperator->configdata->blindness) ? 'checked="checked"' : '' ;
$yesselected1 = ($currentoperator->configdata->blindness) ? 'checked="checked"' : '' ;

echo $OUTPUT->heading(get_string("{$page}settings", 'cognitiveoperator_'.$page));

$form = new scale_prepare_form($url, array('oprequirementtype' => $cognitivefactory->oprequirementtype, 'cmid' => $cm->id));

if ($data = $form->get_data()) {
    // Play the update params controller here
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $data);
}


$currentoperator->configdata->id = $cm->id;
$currentoperator->configdata->operator = $page;
$form->set_data($currentoperator->configdata);
echo "<img src=\"".$OUTPUT->pix_url('enabled', 'cognitiveoperator_'.$page).'" align="left" />';
$form->display();
