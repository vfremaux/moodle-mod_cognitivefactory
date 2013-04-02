<?PHP  // $Id: categorise.php,v 1.1 2004/08/24 16:40:57 diml Exp $

/**
* Module Brainstorm V2
* Operator : categorize
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");
include_once ($CFG->dirroot."/mod/cognitivefactory/operators/{$page}/locallib.php");

if (!cognitivefactory_legal_include()){
    error("The way you loaded this page is not the way this script is done for.");
}

$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);
$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
$userrestriction = ($cognitivefactory->privacy) ? null : 0 ;
$categories = categorize_get_categories($cognitivefactory->id, $userrestriction, $currentgroup);
$usehtmleditor = can_use_html_editor();

$strcategories = get_string('categories', 'cognitivefactory');
$strcommands = get_string('commands', 'cognitivefactory');

print_heading(get_string('categories', 'cognitivefactory'));
?>
<center>
<img src="<?php echo "$CFG->wwwroot/mod/cognitivefactory/operators/{$page}/pix/enabled.gif" ?>" align="left" />
<?php

if (count($categories)){
    $table->head = array("<b>$strcategories</b>", "<b>$strcommands</b>");
    $table->width = '80%';
    $table->align = array('left', 'left');
    $table->size = array('70%', '20%');
    foreach($categories as $category){
        if (($USER->id == $category->userid) || !$cognitivefactory->privacy){
            $action = "<a href=\"view.php?id={$cm->id}&amp;operator=categorize&amp;categoryid={$category->id}&amp;what=update\"><img src=\"{$CFG->pixpath}/t/edit.gif\" /></a>";
            $action .= "&nbsp;<a href=\"view.php?id={$cm->id}&amp;operator=categorize&amp;categoryid={$category->id}&amp;what=delete\"><img src=\"{$CFG->pixpath}/t/delete.gif\" /></a>";
        }
        else{
            $action = '';
        }
        $table->data[] = array(format_string($category->title), $action);
    }
    print_table($table);
}
else{
    print_simple_box_start('center');
    print_string('nocategories', 'cognitivefactory');
    print_simple_box_end();
}    
?>
<form name="addform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="categorize" />
<input type="hidden" name="what" value="add" />
<table width="80%">
    <tr>
        <td>
            <br/><input type="submit" name="go_btn" value="<?php print_string('addcategory', 'cognitivefactory') ?>" /><br/>
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

$noselected0 = (!$currentoperator->configdata->allowmultiple) ? 'checked="checked"' : '' ;
$yesselected0 = ($currentoperator->configdata->allowmultiple) ? 'checked="checked"' : '' ;
$noselected1 = (!$currentoperator->configdata->blindness) ? 'checked="checked"' : '' ;
$yesselected1 = ($currentoperator->configdata->blindness) ? 'checked="checked"' : '' ;
$noselected2 = (!$currentoperator->configdata->categoriesoncollect) ? 'checked="checked"' : '' ;
$yesselected2 = ($currentoperator->configdata->categoriesoncollect) ? 'checked="checked"' : '' ;
print_heading(get_string("{$page}settings", 'cognitivefactory'));
?>
<form name="addform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php echo $page ?>" />
<input type="hidden" name="what" value="saveconfig" />
<table cellspacing="5" width="80%">
    <tr valign="top">
        <td align="right"><b><?php print_string('requirement', 'cognitivefactory') ?>:</b></td>
        <td align="left">
<?php
if ($cognitivefactory->oprequirementtype == 0){ 
?>       
            <input type="text" size="80" name="config_requirement" value="<?php echo stripslashes($currentoperator->configdata->requirement); ?>" />
<?php
}
elseif ($cognitivefactory->oprequirementtype == 2){ 
    print_textarea($usehtmleditor, 20, 50, 680, 400, 'config_requirement', stripslashes($currentoperator->configdata->requirement));
    if (!$usehtmleditor){        
        $cognitivefactory->oprequirementtype = 1;
    }
    else{
        $htmleditorneeded = true;
    }
}
elseif ($cognitivefactory->oprequirementtype == 1){ 
?>       
            <textarea style="width:100%;height:150px" name="config_requirement"><?php echo stripslashes($currentoperator->configdata->requirement); ?></textarea>
<?php
}
?>
            <?php helpbutton('requirement', get_string('requirement', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
    <tr>
        <td align="right"><b><?php print_string('allowmultiple', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <input type="radio" name="config_allowmultiple" value="0" <?php echo $noselected0 ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_allowmultiple" value="1" <?php echo $yesselected0 ?> /> <?php print_string('yes') ?>
            <?php helpbutton('operatorrouter.html&amp;operator=categorize&amp;helpitem=allowmultiple', get_string('allowmultiple', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
    <tr>
        <td align="right"><b><?php print_string('maxitemspercategory', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <?php
            $maxitems_options[0] = get_string('unlimited', 'cognitivefactory');
            for($i = 1 ; $i <= count($responses) ; $i++){
                $maxitems_options[$i] = $i;
            }
            choose_from_menu($maxitems_options, 'config_maxitemspercategory', 0 + @$currentoperator->configdata->maxitemspercategory);
            helpbutton('operatorrouter.html&amp;operator=categorize&amp;helpitem=maxitemspercategory', get_string('maxitemspercategory', 'cognitivefactory'), 'cognitivefactory');
            ?>
        </td>
    </tr>
</table>
<?php
if (has_capability('mod/cognitivefactory:manage', $context)){
?>
<fieldset class="privateform">
<legend><?php print_string('foradminsonly', 'cognitivefactory') ?></legend>
<table width="80%" cellspacing="5">
    <tr>
        <td align="right"><b><?php print_string('blindness', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <input type="radio" name="config_blindness" value="0" <?php echo $noselected1 ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_blindness" value="1" <?php echo $yesselected1 ?> /> <?php print_string('yes') ?>
            <?php helpbutton('blindness', get_string('blindness', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
    <tr>
        <td align="right"><b><?php print_string('publishcategoriesoncollect', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <input type="radio" name="config_categoriesoncollect" value="0" <?php echo $noselected2 ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_categoriesoncollect" value="1" <?php echo $yesselected2 ?> /> <?php print_string('yes') ?>
            <?php helpbutton('operatorrouter.html&amp;operator=categorize&amp;helpitem=categoriesoncollect', get_string('publishcategoriesoncollect', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
</table>
</fieldset>
<?php
}
else{
?>
<input type="hidden" name="config_blindness" value="<?php echo 0 + @$currentoperator->configdata->blindness ?>" />
<?php
}
?>
<table width="80%" cellspacing="5">
    <tr>
        <td colspan="2">
            <br/><input type="submit" name="go_btn" value="<?php print_string('saveconfig', 'cognitivefactory') ?>" />
        </td>
    </tr>
</table>
</form>
</center>