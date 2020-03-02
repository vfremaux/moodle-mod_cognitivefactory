<?PHP  // $Id: prepare.php,v 1.2 2011-05-15 11:18:14 vf Exp $

/**
* Module Brainstorm V2
* Operator : schedule
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
require_once("$CFG->dirroot/mod/cognitivefactory/operators/schedule/prepare_form.php");

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);

if (!isset($currentoperator->configdata->quantifyedges)) {
    $currentoperator->configdata->quantifyedges = 0;
}
if (!isset($currentoperator->configdata->quantifiertype)) {
    $currentoperator->configdata->quantifiertype = 'float';
}

echo $OUTPUT->heading(get_string("{$page}settings", 'cognitiveoperator_'.$page));

$form = new schedule_prepare_form($url, array('oprequirementtype' => $cognitivefactory->oprequirementtype, 'cmid' => $cm->id));

if ($data = $form->get_data()) {
    // Play the update params controller here
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $data);
}

$currentoperator->configdata->id = $cm->id;
$currentoperator->configdata->operator = $page;
$form->set_data($currentoperator->configdata);
echo "<img src=\"".$OUTPUT->image_url('enabled', 'cognitiveoperator_'.$page).'" align="left" />';
$form->display();

