<?PHP  // $Id: prepare.php,v 1.2 2011-05-15 11:15:44 vf Exp $

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
require_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
require_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");
require_once("$CFG->dirroot/mod/cognitivefactory/operators/categorize/prepare_form.php");

if (!defined('MOODLE_INTERNAL')) die("This script cannot be used this way.");

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);
$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
$userrestriction = ($cognitivefactory->privacy) ? null : 0 ;
$categories = categorize_get_categories($cognitivefactory->id, $userrestriction, $currentgroup);
$usehtmleditor = can_use_html_editor();

$strcategories = get_string('categories', 'cognitiveoperator_categorize');
$strcommands = get_string('commands', 'cognitivefactory');

echo $OUTPUT->heading(get_string('categories', 'cognitiveoperator_categorize'));

$form = new categorize_prepare_form($url, array('oprequirementtype' => $cognitivefactory->oprequirementtype, 'maxresponses' => count($responses), 'cmid' => $cm->id));

if ($data = $form->get_data()){
	// Play the add/update controller here
    cognitivefactory_save_operatorconfig($cognitivefactory->id, $data);
}

echo "<img src=\"".$OUTPUT->pix_url('enabled', 'cognitiveoperator_'.$page).'" align="left" />';

if (count($categories)){
	$table = new html_table();
    $table->head = array("<b>$strcategories</b>", "<b>$strcommands</b>");
    $table->width = '80%';
    $table->align = array('left', 'left');
    $table->size = array('70%', '20%');
    foreach($categories as $category){
        if (($USER->id == $category->userid) || !$cognitivefactory->privacy){
            $action = "<a href=\"view.php?id={$cm->id}&amp;operator=categorize&amp;categoryid={$category->id}&amp;what=update\"><img src=\"".$OUTPUT->pix_url('t/edit').'" /></a>';
            $action .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;operator=categorize&amp;categoryid={$category->id}&amp;what=delete\"><img src=\"".$OUTPUT->pix_url('t/delete').'" /></a>';
        } else {
            $action = '';
        }
        $table->data[] = array(format_string($category->title), $action);
    }
    echo html_writer::table($table);
} else {
    echo $OUTPUT->box_start('center');
    print_string('nocategories', 'cognitiveoperator_categorize');
    echo $OUTPUT->box_end();
}    

?>
<form name="addform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="categorize" />
<input type="hidden" name="category" value="1" />
<input type="hidden" name="what" value="add" />
<table width="80%">
    <tr>
        <td>
            <br/><input type="submit" name="go_btn" value="<?php print_string('addcategory', 'cognitiveoperator_categorize') ?>" /><br/>
        </td>
    </tr>
</table>
</form>
<?php    

if (!isset($currentoperator->configdata->blindness)){
    $currentoperator->configdata->blindness = $cognitivefactory->privacy;
}
if (!isset($currentoperator->configdata->allowmultiple)){
    $currentoperator->configdata->allowmultiple = 0;
}
if (!isset($currentoperator->configdata->categoriesoncollect)){
    $currentoperator->configdata->categoriesoncollect = 0;
}
if (!isset($currentoperator->configdata->requirement)){
    $currentoperator->configdata->requirement = '';
}

echo $OUTPUT->heading(get_string("{$page}settings", 'cognitiveoperator_'.$page));

$currentoperator->configdata->id = $cm->id;
$currentoperator->configdata->operator = $page;
$form->set_data($currentoperator->configdata);
$form->display();

