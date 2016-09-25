<?PHP  // $Id: prepare.php,v 1.2 2011-05-15 11:17:39 vf Exp $

/**
* Module Brainstorm V2
* Operator : hierarchize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
require_once("$CFG->dirroot/mod/cognitivefactory/operators/hierarchize/prepare_form.php");

if (!defined('MOODLE_INTERNAL')) die("This script canot be used this way.");

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);

if (!isset($currentoperator->configdata->maxlevels)) {
    $currentoperator->configdata->maxlevels = 0;
}
if (!isset($currentoperator->configdata->maxarity)) {
    $currentoperator->configdata->maxarity = 0;
}
if (!isset($currentoperator->configdata->requirement)) {
    $currentoperator->configdata->requirement = '';
}

echo $OUTPUT->heading(get_string("{$page}settings", 'cognitiveoperator_'.$page));

$form = new hierarchize_prepare_form($url, array('oprequirementtype' => $cognitivefactory->oprequirementtype, 'cmid' => $cm->id));

if ($data = $form->get_data()) {
    // Play the add/update controller here
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $data);
}

$currentoperator->configdata->id = $cm->id;
$currentoperator->configdata->operator = $page;
$form->set_data($currentoperator->configdata);
echo "<img src=\"".$OUTPUT->pix_url('enabled', 'cognitiveoperator_'.$page).'" align="left" />';
$form->display();
