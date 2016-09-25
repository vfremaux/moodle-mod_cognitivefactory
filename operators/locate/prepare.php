<?PHP  // $Id: prepare.php,v 1.2 2011-05-15 11:17:46 vf Exp $

/**
* Module Brainstorm V2
* Operator : locate
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
require_once("$CFG->dirroot/mod/cognitivefactory/operators/locate/prepare_form.php");

if (!defined('MOODLE_INTERNAL')) die("This script canot be used this way.");

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);

if (!isset($currentoperator->configdata->xquantifier)) {
    $currentoperator->configdata->xquantifier = 'criteria X';
}
if (!isset($currentoperator->configdata->xminrange)) {
    $currentoperator->configdata->xminrange = '0.0';
}
if (!isset($currentoperator->configdata->xmaxrange)) {
    $currentoperator->configdata->xmaxrange = '10.0';
}
if (!isset($currentoperator->configdata->yquantifier)) {
    $currentoperator->configdata->yquantifier = 'criteria Y';
}
if (!isset($currentoperator->configdata->yminrange)) {
    $currentoperator->configdata->yminrange = '0.0';
}
if (!isset($currentoperator->configdata->ymaxrange)) {
    $currentoperator->configdata->ymaxrange = '10.0';
}
if (!isset($currentoperator->configdata->neighbourhood)) {
    $currentoperator->configdata->neighbourhood = '0.5';
}
if (!isset($currentoperator->configdata->quantifiertype)) {
    $currentoperator->configdata->quantifiertype = 'float';
}
if (!isset($currentoperator->configdata->width)) {
    $currentoperator->configdata->width = 400;
}
if (!isset($currentoperator->configdata->height)) {
    $currentoperator->configdata->height = 400;
}
if (!isset($currentoperator->configdata->showlabels)) {
    $currentoperator->configdata->showlabels = 1;
}
if (!isset($currentoperator->configdata->requirement)) {
    $currentoperator->configdata->requirement = '';
}
if (!isset($currentoperator->configdata->blindness)) {
    $currentoperator->configdata->blindness = $cognitivefactory->privacy;
}

echo $OUTPUT->heading(get_string("{$page}settings", 'cognitiveoperator_'.$page));

$form = new locate_prepare_form($url, array('oprequirementtype' => $cognitivefactory->oprequirementtype, 'cmid' => $cm->id));

if ($data = $form->get_data()) {
    // Play the add/update controller here
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $data);
}

$currentoperator->configdata->id = $cm->id;
$currentoperator->configdata->operator = $page;
$form->set_data($currentoperator->configdata);
echo "<img src=\"".$OUTPUT->pix_url('enabled', 'cognitiveoperator_'.$page).'" align="left" />';
$form->display();
