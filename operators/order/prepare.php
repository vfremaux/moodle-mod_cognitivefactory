<?PHP  // $Id: categorise.php,v 1.1 2004/08/24 16:40:57 diml Exp $

/**
* Module Brainstorm V2
* Operator : order
* @author Valery Fremaux
* @package mod-cognitivefactory 
* @date 10/01/2009*/
include_once("$CFG->dirroot/mod/cognitivefactory/operators/operator.class.php");

if (!cognitivefactory_legal_include()){
    error("The way you loaded this page is not the way this script is done for.");
}

$responses = cognitivefactory_get_responses($cognitivefactory->id, 0, 0);
$currentoperator = new BrainstormOperator($cognitivefactory->id, $page);
$usehtmleditor = can_use_html_editor();

if (!isset($currentoperator->configdata->absolute)){
    $currentoperator->configdata->absolute = 1;
}
if (!isset($currentoperator->configdata->blindness)){
    $currentoperator->configdata->blindness = $cognitivefactory->privacy;
}
if (!isset($currentoperator->configdata->requirement)){
    $currentoperator->configdata->requirement = '';
}

$noselected0 = (!$currentoperator->configdata->absolute) ? 'checked="checked"' : '' ;
$yesselected0 = ($currentoperator->configdata->absolute) ? 'checked="checked"' : '' ;
$noselected1 = (!$currentoperator->configdata->blindness) ? 'checked="checked"' : '' ;
$yesselected1 = ($currentoperator->configdata->blindness) ? 'checked="checked"' : '' ;

print_heading(get_string("{$page}settings", 'cognitivefactory'));
?>
<center>
<img src="<?php echo "$CFG->wwwroot/mod/cognitivefactory/operators/{$page}/pix/enabled.gif" ?>" align="left" />

<form name="addform" method="post" action="view.php">
<input type="hidden" name="id" value="<?php p($cm->id) ?>" />
<input type="hidden" name="operator" value="<?php echo $page ?>" />
<input type="hidden" name="what" value="saveconfig" />
<table width="80%" cellspacing="10">
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
        <td align="right"><b><?php print_string('absolute', 'cognitivefactory') ?>:</b></td>
        <td align="left">
            <input type="radio" name="config_absolute" value="0" <?php echo $noselected0 ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_absolute" value="1" <?php echo $yesselected0 ?> /> <?php print_string('yes') ?>
            <?php helpbutton('operatorrouter.html&amp;operator=order&amp;helpitem=absolute', get_string('absolute', 'cognitivefactory'), 'cognitivefactory'); ?>
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
        <td align="right" width="50%"><b><?php print_string('blindness', 'cognitivefactory') ?>:</b></td>
        <td align="left" width="50%">
            <input type="radio" name="config_blindness" value="0" <?php echo $noselected1 ?> /> <?php print_string('no') ?>&nbsp;-&nbsp;
            <input type="radio" name="config_blindness" value="1" <?php echo $yesselected1 ?> /> <?php print_string('yes') ?>
            <?php helpbutton('blindness', get_string('blindness', 'cognitivefactory'), 'cognitivefactory'); ?>
        </td>
    </tr>
</table>
</fieldset>
<?php
}
else{
// not very secure, but might be enough for the security context we are in
?>
<input type="hidden" name="config_blindness" value="<?php echo $currentoperator->configdata->blindness ?>" />
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